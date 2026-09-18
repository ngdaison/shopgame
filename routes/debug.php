<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-branding', function () {
    $keys = ['notice_homepage', 'notice_featured_homepage', 'dashboard_text_1', 'footer_text_1'];
    $results = [];
    foreach ($keys as $key) {
        $results[$key] = [
            'value' => \App\Helpers\Helper::branding($key),
            'domain_id' => app()->bound('current_domain_id') ? app('current_domain_id') : 'null',
            'current_domain_model' => app()->bound('current_domain_model') ? 'exists' : 'null'
        ];
    }

    $shop_info = \App\Helpers\Helper::getConfig('shop_info', []);
    $general = \App\Helpers\Helper::getConfig('general', []);
    $notices = \App\Models\SystemNotice::all()->pluck('value', 'name')->toArray();

    return response()->json([
    'branding_results' => $results,
    'shop_info_raw' => $shop_info,
    'general_raw' => $general,
    'notices_raw' => $notices
    ]);
});
