<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use App\Models\Language;

class Localization
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

        $currencyParams = ['currency', 'usd', 'vnd']; // Filter typical typos if needed, but sticking to strict 'currency' param is better.
        // Hande Global Query Params (currency, language)
        $qCurrency = $request->query('currency');
        $qLanguage = $request->query('language') ?? $request->query('lang');

        if ($qCurrency || $qLanguage) {
             // 1. Handle Currency
            if ($qCurrency) {
                $code = strtoupper($qCurrency);
                 
                // Allow VND explicitly (often base currency, might not be in DB or is default)
                if ($code === 'VND') {
                     session(['currency_code' => $code]);
                     \Illuminate\Support\Facades\Cookie::queue('currency_code', $code, 60 * 24 * 365);
                     
                     // If we want to ensure it's not trying to look up a non-existent model later?
                     // Helper::formatCurrency handles missing model gracefully by switch case.
                } else {
                    $currencyExists = \App\Models\Currency::where('code', $code)->where('status', true)->exists();
                    if ($currencyExists) {
                        session(['currency_code' => $code]);
                        \Illuminate\Support\Facades\Cookie::queue('currency_code', $code, 60 * 24 * 365);
                    }
                }
            }

            // 2. Handle Language
            if ($qLanguage) {
                $qLocale = strtolower($qLanguage);
                $isValidLang = false;
                if ($qLocale === strtolower(\Helper::getDefaultLocale())) {
                    $isValidLang = true;
                } else {
                     $isValidLang = \App\Models\Language::where('iso_code', $qLocale)->where('status', true)->exists();
                }

                if ($isValidLang) {
                    session(['locale' => $qLocale]);
                    \Illuminate\Support\Facades\Cookie::queue('locale', $qLocale, 60 * 24 * 365);
                    \Illuminate\Support\Facades\Cache::flush();
                }
            }

            // 3. Redirect to Clean URL (reload)
            // Remove these specific query params to prevent getting stuck in a loop if logic fails, 
            // and to clean the URL for the user.
            $currentQuery = $request->query();
            unset($currentQuery['currency'], $currentQuery['language'], $currentQuery['lang']);
            
            $url = $request->url();
            if (!empty($currentQuery)) {
                $url .= '?' . http_build_query($currentQuery);
            }
            
            return redirect($url);
        }

        $locale = $request->route('locale');
        $defaultLocale = \Helper::getDefaultLocale();

        if ($locale) {
            // Validate locale prefix (already standard logic)
            $isValid = false;
            if ($locale === 'vn' || $locale === 'vi') {
                $isValid = true;
                $locale = 'vn';
            } else {
                $status = \Illuminate\Support\Facades\Cache::remember('lang_status_' . $locale, 3600, function() use ($locale) {
                    return Language::where('iso_code', $locale)->where('status', true)->exists();
                });
                if ($status) {
                    $isValid = true;
                }
            }

            if (!$isValid) {
                \Illuminate\Support\Facades\Log::warning("LocalizationMiddleware: Invalid prefix [$locale], 404");
                abort(404);
            }

            // Set and Persist
            App::setLocale($locale);
            session(['locale' => $locale]);
            \Illuminate\Support\Facades\Cookie::queue('locale', $locale, 60 * 24 * 365);
            
            // ALWAYS clean generated URLs by default now
            URL::defaults(['locale' => null]);

            // ALWAYS redirect valid prefix to clean URL
            $uri = $request->getRequestUri(); // e.g., /en/something or /en
            
            // Remove /locale or /locale/ from the start
            $escapedLocale = preg_quote($locale, '#');
            $target = preg_replace("#^/{$escapedLocale}(/|$)#", '/', $uri);
            
            if (!$target) {
                $target = '/';
            }

            \Illuminate\Support\Facades\Log::info("LocalizationMiddleware: Redirecting prefix to clean URL", ['target' => $target]);
            session()->save(); 
            return redirect()->to($target);
        } else {
            // No prefix in URL
            $sessionLocale = session('locale');
            $cookieLocale = request()->cookie('locale');
            
            // Priority: Session > Cookie > Setting
            $finalLocale = $sessionLocale ?? ($cookieLocale ?? $defaultLocale);

            \Illuminate\Support\Facades\Log::info("LocalizationMiddleware: No Prefix Block", [
                'final_locale' => $finalLocale
            ]);

            // Set locale but NEVER redirect to a prefixed URL anymore
            App::setLocale($finalLocale);
            URL::defaults(['locale' => null]);
        }

        // Forget the parameter so it doesn't interfere with Controllers
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
