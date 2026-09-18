<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DomainSetting;
use Helper;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $domain = $user->domain;

        if (!$domain) {
            abort(403, 'Tài khoản chưa được gán tên miền quản lý.');
        }

        $config = DomainSetting::where('domain', $domain)->firstOrFail();

        // Allowed settings for Partner (subset of Admin)
        // We reuse the Admin view structure but might limit fields in the View or here.
        // Actually, we'll create a specific view for Partner Settings to hide 'Social Login' and 'Redirects'.

        return view('partner.settings', compact('config'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $domainName = $user->domain;

        if (!$domainName) {
            abort(403);
        }

        $domain = DomainSetting::where('domain', $domainName)->firstOrFail();

        $payload = $request->validate([
            'logo_light_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'logo_dark_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'favicon_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'logo_share_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'banner_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg|max:4096',
            'background_image_url_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg|max:4096',
            'default_theme' => 'nullable|in:light,dark',
            'title' => 'nullable|string|max:255',
            'email_app_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'admin_email' => 'nullable|email',
            'youtube_id' => 'nullable|string',
            'primary_color' => 'nullable|string|max:20',

            // New fields
            'font' => 'nullable|string|max:50',
            'intro_text' => 'nullable|string',
            'buy_button_text' => 'nullable|string|max:100',
            'buy_button_image' => 'nullable|string|max:255',
            'show_banner_top' => 'nullable|boolean',
            'show_run_notify' => 'nullable|boolean',
            'analytics_tags' => 'nullable|string',
            'footer_text_1' => 'nullable|string',
            'footer_text_2' => 'nullable|string',
            'dashboard_text_1' => 'nullable|string',
            'notice_homepage' => 'nullable|string',
            'notice_featured_homepage' => 'nullable|string',
        ]);

        $fileFields = [
            'logo_light_file' => 'logo_light',
            'logo_dark_file' => 'logo_dark',
            'favicon_file' => 'favicon',
            'logo_share_file' => 'logo_share',
            'banner_file' => 'banner',
            'background_image_url_file' => 'background_image_url',
        ];

        foreach ($fileFields as $fileInput => $dbField) {
            if ($request->hasFile($fileInput)) {
                $payload[$dbField] = Helper::uploadFile($request->file($fileInput), 'public');
            }
            elseif ($request->input('delete_' . $dbField)) {
                $payload[$dbField] = null;
            }
        }

        $updateData = collect($payload)->except(array_keys($fileFields))->toArray();

        // Boolean checkboxes
        $updateData['show_banner_top'] = $request->has('show_banner_top') ? 1 : 0;
        $updateData['show_run_notify'] = $request->has('show_run_notify') ? 1 : 0;

        if (isset($updateData['title'])) {
            $updateData['email_app_name'] = $updateData['title'];
        }

        $domain->update($updateData);

        Cache::flush();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật cấu hình thành công.'
        ]);
    }

    public function notices()
    {
        $user = auth()->user();
        $domain = $user->domain;

        if (!$domain) {
            abort(403, 'Tài khoản chưa được gán tên miền quản lý.');
        }

        $config = DomainSetting::where('domain', $domain)->firstOrFail();

        return view('partner.notices', compact('config'));
    }

    public function updateNotices(Request $request)
    {
        $user = auth()->user();
        $domainName = $user->domain;

        if (!$domainName) {
            abort(403);
        }

        $domain = DomainSetting::where('domain', $domainName)->firstOrFail();

        $payload = $request->validate([
            'notice_homepage' => 'nullable|string',
            'notice_featured_homepage' => 'nullable|string',
        ]);

        $domain->update($payload);

        Cache::flush();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thông báo thành công.'
        ]);
    }
}
