<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use LaravelWebauthn\WebauthnAuthenticatable;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
  use HasApiTokens;
  use HasFactory;
  use Notifiable;
  use WebauthnAuthenticatable;

  public function hasRole($role)
  {
    if (empty($this->role)) {
      return false;
    }
    $roles = explode(',', $this->role);
    $roles = array_map('strtolower', array_map('trim', $roles));
    return in_array(strtolower(trim($role)), $roles);
  }

  public function hasAnyRole(array $roles)
  {
    foreach ($roles as $role) {
      if ($this->hasRole($role)) {
        return true;
      }
    }
    return false;
  }

    protected $_permissionCache = null;

    protected function loadPermissions()
    {
        if ($this->_permissionCache !== null) {
            return $this->_permissionCache;
        }

        if (empty($this->role)) {
            return $this->_permissionCache = [];
        }

        // Support both comma and semicolon separators
        $roleNames = preg_split('/[;,]/', (string)$this->role, -1, PREG_SPLIT_NO_EMPTY);
        $roleNames = array_map('trim', $roleNames);
        $roleNames = array_map('strtolower', $roleNames);

        $roles = Role::all();
        $allPermissions = [];
        $matchedRoles = [];

        foreach ($roles as $role) {
            if (in_array(strtolower($role->name), $roleNames)) {
                $matchedRoles[] = $role->name;
                $perms = $role->permissions;
                if (is_string($perms)) {
                    $perms = json_decode($perms, true);
                }
                if (is_array($perms)) {
                    $allPermissions = array_merge($allPermissions, $perms);
                }
            }
        }
        
        // \Illuminate\Support\Facades\Log::debug("User {$this->id} Matched Roles: " . implode(',', $matchedRoles));

        return $this->_permissionCache = array_unique($allPermissions);
    }

    public function hasPermission($permission)
    {
        $perms = $this->loadPermissions();
        
        // Match exact, or with/without _view suffix
        $target = str_replace('_view', '', strtolower($permission));
        
        foreach ($perms as $p) {
            $current = str_replace('_view', '', strtolower($p));
            if ($current === $target || $p === $permission) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyPermissionInGroup($prefix)
    {
        $perms = $this->loadPermissions();
        $prefix = strtolower($prefix);
        foreach ($perms as $p) {
            if (str_starts_with(strtolower($p), $prefix)) {
                return true;
            }
        }
        return false;
    }

  public function isAdmin()
  {
      return $this->hasAnyPermissionInGroup('admin_');
  }

  public function isStaff()
  {
      return $this->hasAnyPermissionInGroup('staff_');
  }

  public function isPartner()
  {
      return $this->hasAnyPermissionInGroup('partner_');
  }

  public function getRoleLevel()
  {
      if (empty($this->role)) {
          return 0;
      }

      $roleNames = explode(',', $this->role);
      $roleNames = array_map('trim', $roleNames);

      return Role::whereIn('name', $roleNames)->max('level') ?? 0;
  }

  /**
   * Get the date since when history should be visible for this user based on their roles.
   * If any role has hide_old_history enabled, we use that role's creation date.
   */
  public function getHistoryLimitDate()
  {
      if (empty($this->role)) {
          return null;
      }

      $roleNames = explode(',', $this->role);
      $roleNames = array_map('trim', $roleNames);

      $rolesWithLimit = Role::whereIn('name', $roleNames)
          ->whereNotNull('hide_old_history')
          ->get();

      if ($rolesWithLimit->isEmpty()) {
          return null;
      }

      return $rolesWithLimit->min('hide_old_history');
  }

  /**
   * Apply history limit to a query if set.
   */
  public function applyHistoryLimit($query, $column = 'created_at')
  {
      $limitDate = $this->getHistoryLimitDate();
      if ($limitDate) {
          $query->where($column, '>=', $limitDate);
      }
      return $query;
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'email',
    'username',
    'password',
    'fullname',
    'phone',
    'avatar',
    'balance',
    'balance_1',
    'balance_2',
    'total_deposit',
    'total_withdraw',
    'status',
    'role',
    'colla_type',
    'colla_percent',
    'colla_balance',
    'colla_pending',


    'received_gift',
    'referral_by',
    'referral_code',
    'access_token',
    'ip_address',
    'last_action',

    'register_by',
    'user_agent',
    'full_name',
    'google2fa_secret',

    'last_login_at',
    'last_login_ip',

    'gender',
    'staff_group_ids',
    'username_changed_at',
    'email_changed_at',
    'login_verify_email',
    'login_verify_google2fa',
    'secure_order_view',
    'notify_login_success',
    'phone_changed_at',
    'campaign_id',
    'webrtc_ip',
    'webrtc_updated_at',
    'email_verified_at',
    'domain',
    'social_links',
    'has_password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',

    'received_gift' => 'boolean',
    'staff_group_ids' => 'array',
    'colla_type' => 'array',
    'username_changed_at' => 'datetime',
    'last_login_at' => 'datetime',
    'email_changed_at' => 'datetime',
    'login_verify_email' => 'boolean',
    'login_verify_google2fa' => 'boolean',
    'secure_order_view' => 'boolean',
    'notify_login_success' => 'boolean',
    'phone_changed_at' => 'datetime',
    'social_links' => 'array',
    'has_password' => 'boolean',
    'hide_old_history' => 'date',
  ];

  /**
   * Check if user has a specific social provider linked
   */
  public function hasSocialLinked(string $provider): bool
  {
    $links = $this->social_links ?? [];
    return !empty($links[strtolower($provider)]);
  }

  /**
   * Get the social provider ID for a given provider
   */
  public function getSocialId(string $provider): ?string
  {
    $links = $this->social_links ?? [];
    return $links[strtolower($provider)] ?? null;
  }

  /**
   * Link a social provider to this user
   */
  public function linkSocial(string $provider, string $socialId): void
  {
    $links = $this->social_links ?? [];
    $links[strtolower($provider)] = $socialId;
    $this->social_links = $links;
    $this->save();
  }

  /**
   * Unlink a social provider from this user
   */
  public function unlinkSocial(string $provider): void
  {
    $links = $this->social_links ?? [];
    unset($links[strtolower($provider)]);
    $this->social_links = $links;
    $this->save();
  }

  // History
  public function histories()
  {
    return $this->hasMany(History::class);
  }

  // Transaction
  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }

  // Referral
  public function referrals()
  {
    return $this->hasMany(User::class , 'referral_by', 'id');
  }

  public function referrer()
  {
    return $this->belongsTo(User::class , 'referral_by', 'id');
  }

  // Affiliate
  public function affiliate()
  {
    return $this->hasOne(Affiliate::class , 'user_id', 'id');
  }

  // staff_group_ids
  public function getStaffGroupIdsAttribute($value)
  {
    return $value === null ? [] : json_decode($value, true);
  }

  // Inventory
  public function inventories()
  {
    return $this->hasMany(Inventory::class , 'username', 'username');
  }

  // Banks
  public function banks()
  {
    return $this->hasMany(UserBank::class);
  }

  public function categories()
  {
    return $this->morphToMany(Category::class , 'categoryable');
  }

  /**
   * Get the latest IP address for display
   */
  public function getIpAddressAttribute($value)
  {
    // Try to decode existing value if it is JSON
    if (!empty($value) && is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
      $decoded = json_decode($value, true);
      if (is_array($decoded) && !empty($decoded)) {
        return end($decoded);
      }
    }

    // If value is a plain string IP, return it
    if (!empty($value) && filter_var($value, FILTER_VALIDATE_IP)) {
      return $value;
    }

    // Fallback: Check last_login_ip then register_ip
    if (!empty($this->last_login_ip)) {
      return $this->last_login_ip;
    }

    if (!empty($this->register_ip)) {
      return $this->register_ip;
    }

    return 'N/A';
  }

  public function updateIpAddress($newIp)
  {
    // Reload from DB to ensure we have the absolute latest state
    // preventing any stale instance issues during the request lifecycle
    $freshUser = $this->fresh();

    // Use getRawOriginal to avoid the accessor when getting data for update
    $rawIp = $freshUser->getRawOriginal('ip_address');
    $ipHistory = is_string($rawIp) ? json_decode($rawIp, true) : ($rawIp ?? []);

    if (!is_array($ipHistory)) {
      $ipHistory = [];
    }

    // Get the last recorded IP
    $lastIp = end($ipHistory);

    // Only add if the new IP is different from the last one
    if ($lastIp !== $newIp) {
      $ipHistory[now()->format('Y-m-d H:i:s')] = $newIp;

      // Update directly on the fresh instance if possible, or update current
      $this->attributes['ip_address'] = json_encode($ipHistory);
      $this->save();
    }
  }

  /**
   * Common logic to run after a successful login (regular or 2FA)
   */
  public function postLoginInit($request)
  {
    if (!$this->access_token) {
      $this->update([
        'access_token' => $this->createToken('access_token')->plainTextToken,
      ]);
    }

    $now = now();
    $this->update([
      'last_login_at' => $now,
      'last_login_ip' => $request->ip(),
      'ip' => $request->ip(),
      'user_agent' => $request->userAgent(),
    ]);

    // Update domain if not set
    if (empty($this->domain)) {
      $this->update([
        'domain' => \Helper::getDomain(),
      ]);
    }

    // Update IP address with deduplication
    $this->updateIpAddress($request->ip());

    // Set online status immediately
    \Illuminate\Support\Facades\Cache::put('user-is-online-' . $this->id, true, now()->addMinutes(2));

    // Essential for CheckLastLogin middleware
    session(['last_login_at' => $now]);
    session(['last_login_ip' => $request->ip()]);

    // Create login history
    $this->histories()->create([
      'role' => $this->role,
      'data' => [],
      'content' => 'Đăng nhập thành công qua WEB, số dư ' . \Helper::formatCurrency($this->balance),
      'user_id' => $this->id,
      'username' => $this->username,
      'ip' => $request->ip(),
      'ip_address' => $request->ip(),
      'domain' => \Helper::getDomain(),
    ]);

    // Notify user if enabled
    if ($this->notify_login_success) {
      try {
        \Helper::sendEmailTemplate('login_success_notification', $this->email, [
          'username' => $this->username,
          'ip' => $request->ip(),
          'device' => $request->userAgent(),
          'time' => now()->format('Y-m-d H:i:s'),
          'title' => config('app.name')
        ]);
      }
      catch (\Exception $e) {
      // Ignore mail errors
      }
    }
  }

  /**
   * Send the password reset notification.
   *
   * @param  string  $token
   * @return void
   */
  public function sendPasswordResetNotification($token)
  {
    // Generate a random padding string (1000-2000 chars)
    $tokenPadding = \Illuminate\Support\Str::random(rand(1000, 2000));

    // Create a payload array with token, email, and padding
    $payload = [
      't' => $token,
      'e' => $this->getEmailForPasswordReset(),
      'p' => $tokenPadding
    ];

    // JSON encode and then Base64 encode to make it one long string
    $jsonPayload = json_encode($payload);
    $base64Payload = base64_encode($jsonPayload);

    // Make Base64 URL safe (replace + with -, / with _, remove =)
    $urlSafeToken = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

    $url = route('password.reset', ['token' => $urlSafeToken]);

    // Attempt to send using the dynamic template system
    $sent = \Helper::sendEmailTemplate('reset_password', $this->email, [
      'action_url' => $url,
      'link' => $url, // Changed/Added for compatibility
      'username' => $this->username ?? $this->name ?? 'Member',
    ]);

    // If template sending failed (e.g. key not found), fallback to the hardcoded notification
    if (!$sent) {
      $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
  }
}
