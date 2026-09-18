<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Helper;

class CurrencyRateService
{
    protected $baseUrl = 'https://api.fxratesapi.com';

    public function __construct()
    {
        // No API Key required for public endpoints
    }

    /**
     * Get list of supported currencies (Codes only).
     * Cached for 12 hours.
     */
    public function getSupportedCurrencies()
    {
        $cacheKey = 'fxratesapi_supported_codes_full';
        
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $response = Http::get($this->baseUrl . '/currencies');
            
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data)) {
                    $supported = [];
                    foreach ($data as $code => $detail) {
                        $supported[$code] = [
                            'currencyName' => $detail['name'] ?? $code,
                            'symbol' => $detail['symbol'] ?? '',
                            'status' => 'AVAILABLE'
                        ];
                    }
                    
                    Cache::put($cacheKey, $supported, 60 * 60 * 24); // 24 hours
                    return $supported;
                }
            }
            
            Log::error('CurrencyRateService: Failed to fetch supported currencies', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('CurrencyRateService: Exception fetching supported', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get latest rates.
     * Base is usually USD.
     * We fetch all or filter if needed.
     */
    public function getLatestRates(array $symbols = [])
    {
        try {
            // Using /latest endpoint
            // Optional: ?currencies=VND,USD,... to filter
            $url = $this->baseUrl . '/latest';
            $params = [];
            
            if (!empty($symbols)) {
                 $params['currencies'] = implode(',', $symbols);
            }

            $response = Http::get($url, $params);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['success']) && $json['success'] === true) {
                    return $json['rates'] ?? null;
                }
            }

            Log::error('CurrencyRateService: Failed to fetch rates', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('CurrencyRateService: Exception fetching rates', ['error' => $e->getMessage()]);
            return null;
        }
    }
    /**
     * Convert currency.
     * Used for Auto Mode to get direct rate.
     */
    public function convert($from, $to, $amount = 1)
    {
        try {
            $url = $this->baseUrl . '/convert';
            $response = Http::get($url, [
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['success']) && $json['success'] === true) {
                    return $json['result'] ?? null;
                }
            }
            
            Log::error('CurrencyRateService: Convert failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('CurrencyRateService: Exception convert', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
