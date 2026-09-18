<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class SetCurrencyController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return RedirectResponse
     */
    public function __invoke($code)
    {
        $code = strtoupper($code);
        
        // Validate currency exists and is active
        $isValid = \App\Models\Currency::where('code', $code)
            ->where('status', true)
            ->exists();

        if ($isValid) {
            // Store currency preference in session and cookie
            session(['currency' => $code]);
            \Illuminate\Support\Facades\Cookie::queue('currency', $code, 60 * 24 * 365);
            
            // Clear cache to refresh currency data
            Cache::flush();
            
            // Get the referer path
            $referer = request()->headers->get('referer');
            if ($referer) {
                $urlParts = parse_url($referer);
                $path = $urlParts['path'] ?? '/';
                
                // Reconstruct URL with updated query params
                $finalUrl = $path;
                
                // Parse existing query params
                $queryParams = [];
                if (isset($urlParts['query'])) {
                    parse_str($urlParts['query'], $queryParams);
                }
                
                // Update or add currency param
                $queryParams['currency'] = $code;
                
                // Rebuild query string
                $finalUrl .= '?' . http_build_query($queryParams);
                
                return redirect($finalUrl);
            }

            return redirect('/');
        }

        return redirect('/');
    }
}
