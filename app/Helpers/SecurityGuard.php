<?php

namespace App\Helpers;

use App\Models\SecurityBan;
use App\Models\SecuritySetting;
use Carbon\Carbon;

class SecurityGuard
{
    /**
     * Record an attempt for a specific rule.
     * TODO: Implement rate limiting logic via Cache/Redism and check against settings.
     * 
     * @param string $ruleKey The setting key for the rule (e.g., 'bruteforce_login_ip')
     * @param string|null $ip
     * @param string|null $username
     */
    public static function recordAttempt($ruleKey, $ip = null, $username = null)
    {
        $settings = SecuritySetting::get('security_bruteforce_rules', []);

        // Default if not found in settings
        $rule = isset($settings[$ruleKey]) ? $settings[$ruleKey] : null;

        if (!$rule || empty($rule['enable'])) {
            return; // Rule disabled or not found
        }

        $maxAttempts = (int)($rule['max_attempts'] ?? 5);
        $windowMinutes = (int)($rule['window_minutes'] ?? 10);
        $banAction = $rule['ban_action'] ?? 'ban_1_day';

        // Identifier for cache key
        $identifier = $ip ?: $username; 
        if (!$identifier) return;

        // Cache Key: security:bruteforce:{ruleKey}:{identifier}
        $cacheKey = "security:bruteforce:{$ruleKey}:{$identifier}";

        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            \Illuminate\Support\Facades\Cache::increment($cacheKey);
        } else {
            \Illuminate\Support\Facades\Cache::put($cacheKey, 1, now()->addMinutes($windowMinutes));
        }

        $currentAttempts = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);

        if ($currentAttempts > $maxAttempts) {
            // Trigger Ban
            $reason = "Vi phạm quy tắc bảo mật: " . ($rule['label'] ?? $ruleKey);
            
            if ($ip) {
                self::banIp($ip, $banAction, $reason);
            }
            // Note: Some rules might want to ban User instead of IP, or both. 
            // The current simple implementation assumes we largely ban based on what we are tracking (IP or User).
            // Most rules in the blade file passed 'ip' logic (login_ip, otp, etc).
            // 'login_acc' is an exception where we might track username.

            // Reset counter after ban to avoid repeated banning? 
            // Or keep it so they stay banned if they keep trying? 
            // Usually valid to clear it so we don't spam ban records, OR just check isBanned first.
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
    }

    /**
     * Check if should ban based on current counters.
     * 
     * @param string $ruleKey
     * @param string|null $ip
     * @param string|null $username
     * @return bool
     */
    public static function shouldBan($ruleKey, $ip = null, $username = null)
    {
        // Not strictly needed if recordAttempt does the banning, but can be used for pre-checks
        return false;
    }

    /**
     * Ban an IP address.
     * 
     * @param string $ip
     * @param string $status Enum value (ban, ban_1_day, etc.)
     * @param string|null $reason
     * @return SecurityBan
     */
    public static function banIp($ip, $status = 'ban', $reason = null)
    {
        return self::createBan('ip', null, $ip, $status, $reason);
    }

    /**
     * Ban a username.
     * 
     * @param string $username
     * @param string $status Enum value
     * @param string|null $reason
     * @return SecurityBan
     */
    public static function banUser($username, $status = 'ban', $reason = null)
    {
        return self::createBan('user', $username, null, $status, $reason);
    }

    /**
     * Internal method to create ban record.
     */
    private static function createBan($type, $username, $ip, $status, $reason)
    {
        $bannedUntil = null;
        $now = Carbon::now();

        switch ($status) {
            case 'ban_1_day':
                $bannedUntil = $now->copy()->addDay();
                break;
            case 'ban_2_day':
                $bannedUntil = $now->copy()->addDays(2);
                break;
            case 'ban_3_day':
                $bannedUntil = $now->copy()->addDays(3);
                break;
            case 'ban_4_day':
                $bannedUntil = $now->copy()->addDays(4);
                break;
            case 'ban': // Permanent
            default:
                $bannedUntil = null;
                break;
        }

        // Check if already actively banned to avoid duplicates?
        // Simple check:
        $existing = SecurityBan::where('type', $type)
            ->where(function($q) use ($ip, $username) {
                if ($ip) $q->where('ip', $ip);
                if ($username) $q->where('username', $username);
            })
            ->where(function($q) {
                $q->whereNull('banned_until')->orWhere('banned_until', '>', Carbon::now());
            })
            ->first();

        if ($existing) {
            // Already banned, maybe update reason or extend? 
            // For now, just return exists
            return $existing;
        }

        return SecurityBan::create([
            'type' => $type,
            'username' => $username,
            'ip' => $ip,
            'reason' => $reason,
            'status' => $status,
            'banned_until' => $bannedUntil,
            'created_at' => now(),
            'strikes' => 1
        ]);
    }

    /**
     * Check if IP or User is currently banned.
     * 
     * @param string|null $ip
     * @param string|null $username
     * @return bool
     */
    public static function isBanned($ip = null, $username = null)
    {
        $webrtCIp = null;

        // Try to find user to get WebRTC IP
        if ($username) {
            $user = \App\Models\User::where('username', $username)->first();
            if ($user && !empty($user->webrtc_ip)) {
                $webrtCIp = $user->webrtc_ip;
            }
        } elseif (auth()->check()) {
            $user = auth()->user();
            if (!empty($user->webrtc_ip)) {
                $webrtCIp = $user->webrtc_ip;
            }
        }

        $query = SecurityBan::query();

        $query->where(function ($q) use ($ip, $username, $webrtCIp) {
            if ($ip) {
                $q->orWhere(function($sub) use ($ip) {
                    $sub->where('type', 'ip')->where('ip', $ip);
                });
            }
            if ($webrtCIp) {
                 $q->orWhere(function($sub) use ($webrtCIp) {
                    $sub->where('type', 'ip')->where('ip', $webrtCIp);
                });
            }
            if ($username) {
                 $q->orWhere(function($sub) use ($username) {
                    $sub->where('type', 'user')->where('username', $username);
                });
            }
        });

        // Check active bans: banned_until IS NULL (perm) OR banned_until > NOW
        $query->where(function ($q) {
            $q->whereNull('banned_until')
              ->orWhere('banned_until', '>', Carbon::now());
        });

        return $query->exists();
    }
}
