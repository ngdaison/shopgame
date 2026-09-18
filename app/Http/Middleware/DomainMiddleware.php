<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Domain;
use Helper;

class DomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // 1. Strict Domain Whitelist Enforcement
        // We always allow /admin access to prevent locking out the site owner
        if (!$request->is('admin/*') && !$request->is('admin')) {
            $allowedDomains = setting('allowed_domains');
            $allowedList = !empty($allowedDomains) ? array_map('trim', explode(',', $allowedDomains)) : [];
            
            if (!in_array($host, $allowedList)) {
                abort(404);
            }
        }

        $domain = true;


        if ($domain) {
            // Check redirects from Config
            $childDomains = setting('child_domains');
            if ($childDomains) {
                $lines = explode("\n", str_replace("\r", "", $childDomains));
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    // Check if it is a redirect rule
                    if (str_contains($line, '=')) {
                        $parts = explode('=', $line);
                        
                        // Strict check: exactly 2 parts (handled by validation mostly, but safe here too)
                        if (count($parts) === 2) {
                            $source = trim($parts[0]);
                            $target = trim($parts[1]);
                            
                            if ($host === $source) {
                                // Cleanup target: remove protocol and trailing slashes
                                $cleanTarget = preg_replace("~^(?:f|ht)tps?://~i", "", rtrim($target, '/'));

                                // Handle non-standard ports
                                $port = $request->getPort();
                                $portSuffix = ($port && !in_array($port, [80, 443]) && !str_contains($cleanTarget, ':')) ? ":$port" : "";
                                $targetHost = $cleanTarget . $portSuffix;

                                if (!preg_match("~^(?:f|ht)tps?://~i", $targetHost)) {
                                    $targetHost = protocol() . $targetHost;
                                }
                                return redirect()->to($targetHost . $request->getRequestUri(), 301);
                            }
                        }
                    }
                }
            }
        }




        if (!$request->is('admin/*') && !$request->is('install/*')) {
            $host = $request->getHost();
            $fullHost = $request->getHttpHost();

            \Illuminate\Support\Facades\Log::info("DomainMiddleware: Processing Request", [
                'host' => $host,
                'full_host' => $fullHost,
                'locale' => app()->getLocale()
            ]);

            $domainModel = null;

            try {
                // 1. Resolve Domain (Exact Branding Match)
                $domainModel = \App\Models\DomainSetting::where('domain', $host)->where('is_redirect', 0)->first();
                
                // 2. Resolve Alias Redirection (Search in redirect_to array of Branding records)
                if (!$domainModel || $domainModel->is_redirect) {
                    $targetBranding = \App\Models\DomainSetting::where('is_redirect', 0)
                        ->whereJsonContains('redirect_to', $host)
                        ->first();

                    if ($targetBranding && !$request->is('admin/*') && !$request->is('admin')) {
                        $target = $targetBranding->domain;
                        // Cleanup target: remove protocol and trailing slashes
                        $cleanTarget = preg_replace("~^(?:f|ht)tps?://~i", "", rtrim($target, '/'));

                        // Handle non-standard ports
                        $port = $request->getPort();
                        $portSuffix = ($port && !in_array($port, [80, 443]) && !str_contains($cleanTarget, ':')) ? ":$port" : "";
                        $targetHost = $cleanTarget . $portSuffix;

                        if (!preg_match("~^(?:f|ht)tps?://~i", $targetHost)) {
                            $targetHost = protocol() . $targetHost;
                        }
                        \Illuminate\Support\Facades\Log::info("DomainMiddleware: Redirecting Alias", ['from' => $host, 'to' => $targetHost]);
                        return redirect()->to($targetHost . $request->getRequestUri(), 301);
                    }
                }

                // 3. Resolve Legacy Redirection Rows (Handle existing separate redirect records)
                // If we didn't find an alias hit above, check if this host itself is a redirect row
                if (!$domainModel) {
                     $legacyRedirect = \App\Models\DomainSetting::where('domain', $host)->where('is_redirect', 1)->first();
                     if ($legacyRedirect && $legacyRedirect->redirect_to && !$request->is('admin/*') && !$request->is('admin')) {
                        $targets = (array)$legacyRedirect->redirect_to;
                        $target = trim($targets[0] ?? '');
                        
                            // Cleanup target: remove protocol and trailing slashes
                            $cleanTarget = preg_replace("~^(?:f|ht)tps?://~i", "", rtrim($target, '/'));

                            // Handle non-standard ports
                            $port = $request->getPort();
                            $portSuffix = ($port && !in_array($port, [80, 443]) && !str_contains($cleanTarget, ':')) ? ":$port" : "";
                            $targetHost = $cleanTarget . $portSuffix;

                            if (!preg_match("~^(?:f|ht)tps?://~i", $targetHost)) {
                                $targetHost = protocol() . $targetHost;
                            }
                            \Illuminate\Support\Facades\Log::info("DomainMiddleware: Redirecting Legacy Row", ['from' => $host, 'to' => $targetHost]);
                            return redirect()->to($targetHost . $request->getRequestUri(), 301);
                    }
                }
                
                
                if (!$domainModel) {
                     // Check Main Domain Fallback
                     $mainDomain = setting('main_domain');
                     $mainDomainHost = parse_url($mainDomain, PHP_URL_HOST) ?? $mainDomain;
                     
                     if ($mainDomainHost && $mainDomainHost !== $host) {
                         // Fallback logic if needed
                     }
                }
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("DomainMiddleware: DB Error " . $e->getMessage());
            }

            if ($domainModel) {
                // Share Domain ID globally
                app()->instance('current_domain_id', $domainModel->id);
                // Share Domain Model globally
                app()->instance('current_domain_model', $domainModel);

                // Log RAW Domain Value
                \Illuminate\Support\Facades\Log::info("DomainMiddleware: Domain Match Found", [
                    'id' => $domainModel->id,
                    'raw_banner' => $domainModel->banner, 
                    'raw_logo' => $domainModel->logo_dark
                ]);

                // Populate Helper::$domainSettings for legacy code
                $langIso = app()->getLocale();
                $langObj = \App\Models\Language::where('iso_code', $langIso)->first();
                
                $effective = Helper::getEffectiveConfig($domainModel->id, $langObj ? $langObj->id : null);
                Helper::$domainSettings = (array)$effective;
            } else {
                \Illuminate\Support\Facades\Log::info("DomainMiddleware: No Domain Matched for host [$host]");
                app()->instance('current_domain_id', null);
            }

        }
        // Check if there is a main domain configured? 
        // The requirement says "Main domain" is always present in admin.
        // But if accessing via IP or localhost and it's not in DB?
        // Nothing to do, use default settings.
        
        // However, "Main domain" in DB might be different from current host (e.g. dev env)
        // But we only override if we find a match.


        return $next($request);
    }
}

// Add a helper for protocol if not exists
if (!function_exists('protocol')) {
    function protocol() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    }
}
