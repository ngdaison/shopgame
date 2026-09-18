<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class SetLocaleController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return RedirectResponse
     */
    public function __invoke($locale)
    {
        $defaultLocale = strtolower(\Helper::getDefaultLocale());
        $locale = strtolower($locale);
        $isValid = false;

        // Check if locale is default (no prefix needed)
        if ($locale === $defaultLocale) {
            $isValid = true;
        } else {
            $isValid = \App\Models\Language::where('iso_code', $locale)->where('status', true)->exists();
        }

        if ($isValid) {
            session(['locale' => $locale]);
            \Illuminate\Support\Facades\Cookie::queue('locale', $locale, 60 * 24 * 365);
            
            Cache::flush();
            
            // Get the referer path
            $referer = request()->headers->get('referer');
            if ($referer) {
                $urlParts = parse_url($referer);
                $path = $urlParts['path'] ?? '/';
                
                // Identify if the path has a 2-letter locale prefix
                $segments = explode('/', trim($path, '/'));
                if (isset($segments[0]) && strlen($segments[0]) === 2) {
                    $prefix = strtolower($segments[0]);
                    // Check if the first segment is an existing language or the default
                    $isLangPrefix = \App\Models\Language::where('iso_code', $prefix)->where('status', true)->exists();
                    if ($isLangPrefix || $prefix === $defaultLocale) {
                        array_shift($segments); // Remove the old prefix
                    }
                }
                
                $newPath = implode('/', $segments);
                
                // Reconstruct the URL - ALWAYS clean (no prefix)
                $finalUrl = '/' . $newPath;
                $finalUrl = str_replace('//', '/', $finalUrl);
                
                // Keep query params if any
                if (isset($urlParts['query'])) {
                    $finalUrl .= '?' . $urlParts['query'];
                }
                
                return redirect($finalUrl);
            }

            return redirect('/');
        }

        return redirect('/');
    }
}
