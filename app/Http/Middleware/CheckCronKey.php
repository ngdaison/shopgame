<?php

namespace App\Http\Middleware;

use App\Models\SecuritySetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCronKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $securitySettings = SecuritySetting::get('security_other', []);
        $serverCronKey = $securitySettings['cron_key'] ?? null;
        $inputCronKey = $request->input('key');

        if (!empty($serverCronKey) && $inputCronKey !== $serverCronKey) {
            return response()->json([
                'status' => 401,
                'message' => 'Sai key cron job',
            ], 401);
        }

        return $next($request);
    }
}
