<?php

namespace App\Http\Middleware;

use App\Helpers\SecurityGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSecurityBans
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $user = $request->user();
        $username = $user ? $user->username : null;

        if (SecurityGuard::isBanned($ip, $username)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access Denied. You are banned.'], 403);
            }
            abort(403, 'Access Denied. You have been banned from accessing this resource.');
        }

        return $next($request);
    }
}
