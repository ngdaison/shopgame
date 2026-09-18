<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Check if there is a redirect for this host (New System)
        // We join with domain_settings to match the host string
        $redirect = \App\Models\DomainRedirect::whereHas('source', function ($query) use ($host) {
            $query->where('domain', $host);
        })->with('target')->first();

        $targetDomain = null;

        if ($redirect && $redirect->status && $redirect->target) {
            $targetDomain = $redirect->target->domain;
        } else {
            // Check Legacy/Main System (DomainSetting)
            $domainSetting = \App\Models\DomainSetting::where('domain', $host)->first();
            if ($domainSetting && $domainSetting->is_redirect && !empty($domainSetting->redirect_to)) {
                $targets = $domainSetting->redirect_to;
                // Ensure it's an array
                if (is_string($targets)) {
                     $targets = json_decode($targets, true) ?? [];
                }
                
                if (is_array($targets) && count($targets) > 0) {
                    $targetDomain = $targets[0];
                }
            }
        }

        if ($targetDomain) {
            // Prevent redirect loop if already on target (sanity check)
            if ($targetDomain !== $host) {
                 // Build target URL
                 $scheme = $request->getScheme(); // http or https
                 
                 // Get port if non-standard
                 $port = $request->getPort();
                 $portSuffix = '';
                 if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
                     $portSuffix = ':' . $port;
                 }

                 $targetUrl = $scheme . '://' . $targetDomain . $portSuffix . $request->getRequestUri();
                 
                 return redirect($targetUrl, 301);
            }
        }

        return $next($request);
    }
}
