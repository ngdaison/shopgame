<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ClearPendingVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = Auth::id();

        // Define ALL verification related session keys
        $verificationKeys = [
            'verify_pending_changes',
            'verify_method',
            'verify_allowed',
            'verify_intent',
            'verify_is_email',
            'verify_pending_email',
            'verify_user_id',
            'verify_remember',
            'verify_email',
            'verify_2fa'
        ];

        // Check if there is ANY pending verification state
        $hasSession = false;
        foreach ($verificationKeys as $key) {
            if (session()->has($key)) {
                $hasSession = true;
                break;
            }
        }

        $isAuth = Auth::check();

        if ($hasSession || $isAuth) {
            $routeName = $request->route() ? $request->route()->getName() : null;

            // List of allowed routes where the state should PERSIST
            $allowedRoutes = [
                'account.verify',
                'account.verify.post',
                'account.verify.resend',
                'account.verify.cancel',
                'account.login_verify',
                'account.login_verify.post',
                'account.security.otp.send',
                'account.security.2fa.enable',
                'account.security.2fa.disable',
                'account.email.verify',
                'account.email.send-verification',
                'account.profile.index',
                // Background tasks must be whitelisted to avoid clearing session
                'account.heartbeat',
                'account.offline',
                'account.notifications.read_all',
                'upload.image',
                'account.upload.image',
            ];

            // If the route is not whitelisted, we clear only if it's NOT an internal technical route
            if ($routeName && !in_array($routeName, $allowedRoutes)) {
                // Special case: ignore some common internal routes that don't have names
                if (str_contains($request->path(), '_debugbar') || str_contains($request->path(), 'telescope')) {
                    return $next($request);
                }

                // Clear session state
                if ($hasSession) {
                    session()->forget($verificationKeys);
                }

                // Also Clear Action Cache if Authenticated
                if ($isAuth) {
                    if (Cache::has('verify_pending_changes_' . $userId) || Cache::has('verify_is_email_' . $userId)) {
                        Cache::forget('verify_pending_changes_' . $userId);
                        Cache::forget('verify_method_' . $userId);
                        Cache::forget('verify_pending_email_' . $userId);
                        Cache::forget('verify_is_email_' . $userId);
                    }
                }
            }
        }

        return $next($request);
    }
}
