<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TrackUtmSource
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('utm_source') || $request->has('a')) {
            $utmSource = $request->input('utm_source') ?? $request->input('a');
            
            // Store cookie for 30 days (43200 minutes)
            Cookie::queue('utm_source', $utmSource, 43200);

            // Link Tracking Logic
            $campaign = \App\Models\Campaign::where('tracking_code', $utmSource)
                ->where('status', true)
                ->first();

            if ($campaign) {
                // Log click
                \App\Models\CampaignLog::create([
                    'campaign_id' => $campaign->id,
                    'ip'          => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);

                // Increment click counter
                $campaign->increment('clicks');

                // Destination Link Redirect
                $destination = $campaign->referral_link;
                
                if ($destination) {
                    if (!str_starts_with($destination, 'http')) {
                        $destination = url($destination);
                    }
                } else {
                    // Fallback to current URL but strip the tracking parameters
                    $query = $request->query();
                    unset($query['a'], $query['utm_source']);
                    $destination = $request->url() . (count($query) ? '?' . http_build_query($query) : '');
                }

                // Redirect to clean up the URL or reach the target destination
                return redirect()->to($destination);
            }
        }

        return $next($request);
    }
}
