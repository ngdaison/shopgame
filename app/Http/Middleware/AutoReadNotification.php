<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoReadNotification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $currentUrl = $request->fullUrl(); // http://.../?foo=bar
            $currentPath = $request->path();   // account/tickets/CODE

            // 1. Direct match with full URL
            // 2. Match without query params (if stored link has no params but current has)
            // 3. Fallback: Like match for some dynamic cases if needed (careful with false positives)

            // Simplest effective strategy: matches the absolute link stored
            // Note: Notification 'link' usually stores fully route() which is absolute http://domain...
            
            \App\Models\Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->where(function($q) use ($currentUrl) {
                    $q->where('link', $currentUrl)
                      ->orWhere('link', $currentUrl . '/') // Trailing slash case
                      ->orWhere('link', url()->current()); // Base URL without query params
                })
                ->update(['is_read' => true]);
        }
        
        return $next($request);
    }
}
