<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Partner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 1. Check if user has ANY partner permission
        if (!$user->isPartner()) {
            return abort(403, 'Bạn không có quyền truy cập trang Đối Tác.');
        }

        // 2. Map Granular Partner Permissions
        $routeName = $request->route()->getName();
        $permissionMap = [
            'partner.dashboard' => 'partner_dashboard_view',
            'partner.settings.index' => 'partner_settings_view',
            'partner.settings.update' => 'partner_settings_update',
            'partner.settings.notices' => 'partner_notices_view',
            'partner.settings.notices.update' => 'partner_notices_update',
        ];

        $matchedPermission = null;
        $routeName = trim((string)$routeName);

        if (isset($permissionMap[$routeName])) {
            $matchedPermission = $permissionMap[$routeName];
        } else {
            foreach ($permissionMap as $pattern => $perm) {
                if (str_starts_with($routeName, $pattern . '.')) {
                    $matchedPermission = $perm;
                    break;
                }
            }
        }

        if ($matchedPermission) {
            $hasPerm = $user->hasPermission($matchedPermission);
            \Illuminate\Support\Facades\Log::info("Partner MIDI: User {$user->id}, Route {$routeName}, Perm {$matchedPermission}, Result: " . ($hasPerm ? 'TRUE' : 'FALSE'));
            if (!$hasPerm) {
                if ($request->wantsJson()) {
                    return response()->json(['status' => false, 'message' => 'Bạn không có quyền ' . $matchedPermission], 403);
                }
                return abort(403, 'Bạn không có quyền ' . $matchedPermission);
            }
        } else {
            // Strict Mode: Unmapped routes are DENIED!
            $safeRoutes = ['partner.dashboard', 'partner.api'];
            $isSafe = false;
            foreach ($safeRoutes as $safe) {
                if (str_starts_with((string)$routeName, $safe)) {
                    $isSafe = true; break;
                }
            }
            
            if (!$isSafe) {
                if ($request->wantsJson()) {
                    return response()->json(['status' => false, 'message' => 'Tính năng chưa được phân quyền hoặc bạn không có quyền truy cập.'], 403);
                }
                return abort(403, 'Tính năng chưa được phân quyền hoặc bạn không có quyền truy cập.');
            }
        }

        // 3. Check if user has a domain assigned
        if (empty($user->domain)) {
            return abort(403, 'Tài khoản của bạn chưa được gán tên miền quản lý.');
        }

        // 4. Check if status is active
        if ($user->status !== 'active') {
            return abort(401, 'Tài khoản của bạn đã bị khóa.');
        }

        return $next($request);
    }
}
