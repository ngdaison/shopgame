<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\DomainSetting;
use App\Models\Config;
use Illuminate\Http\Request;
use Helper;
use Illuminate\Support\Facades\Cache;

class DomainController extends Controller
{
    public function index()
    {
        $configs = DomainSetting::where('is_redirect', 0)->get();
        $managedDomains = $configs->pluck('domain')->toArray();

        // Get all aliases already used in any branding config
        $usedAliases = [];
        foreach ($configs as $cfg) {
            if (is_array($cfg->redirect_to)) {
                $usedAliases = array_merge($usedAliases, $cfg->redirect_to);
            }
        }
        $usedAliases = array_unique($usedAliases);

        $allowedDomainsStr = setting('allowed_domains');
        $allowedDomains = !empty($allowedDomainsStr) ? array_map('trim', explode(',', $allowedDomainsStr)) : [];

        // Filter out domains that are already managed OR used as aliases
        $availableDomains = array_filter($allowedDomains, function ($domain) use ($managedDomains, $usedAliases) {
            return !in_array($domain, $managedDomains) && !in_array($domain, $usedAliases);
        });

        return view('admin.domain.index', compact('configs', 'availableDomains', 'allowedDomains'));
    }

    public function edit($id)
    {
        $config = DomainSetting::findOrFail($id);

        $allowedDomainsStr = setting('allowed_domains');
        $allowedDomains = !empty($allowedDomainsStr) ? array_map('trim', explode(',', $allowedDomainsStr)) : [];

        $allConfigs = DomainSetting::where('is_redirect', 0)->get();
        $managedDomains = $allConfigs->pluck('domain')->toArray();

        // Get all aliases used by OTHER branding configs
        $otherUsedAliases = [];
        foreach ($allConfigs as $cfg) {
            if ($cfg->id == $id)
                continue;
            if (is_array($cfg->redirect_to)) {
                $otherUsedAliases = array_merge($otherUsedAliases, $cfg->redirect_to);
            }
        }
        $otherUsedAliases = array_unique($otherUsedAliases);

        // 1. Current pointing domains (aliases stored in THIS branding record)
        $pointingDomains = is_array($config->redirect_to) ? $config->redirect_to : [];

        // 2. Available domains for this record:
        // - In allowed list
        // - NOT a managed branding domain (except self, but self is already handled by managedDomains check mostly)
        // - NOT used as an alias by ANOTHER record
        $otherDomains = array_filter($allowedDomains, function ($domain) use ($managedDomains, $otherUsedAliases, $config) {
            if ($domain === $config->domain)
                return false;
            return !in_array($domain, $managedDomains) && !in_array($domain, $otherUsedAliases);
        });

        $languages = \App\Models\Language::where('name', '!=', 'Mặc định')->where('is_default', false)->get();
        $currencies = \App\Models\Currency::where('status', true)->where('is_default', false)->get();

        return view('admin.domain.edit', compact('config', 'otherDomains', 'pointingDomains', 'languages', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $domain = DomainSetting::findOrFail($id);

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
            'font' => 'nullable|string|max:50',
            'intro_text' => 'nullable|string',
            'buy_button_text' => 'nullable|string|max:100',
            'buy_button_image' => 'nullable|string|max:255',
            'show_banner_top' => 'nullable|boolean',
            'show_run_notify' => 'nullable|boolean',
            'analytics_tags' => 'nullable|string',
            'redirect_to' => 'nullable',
            'language_id' => 'nullable|integer',
            'currency_id' => 'nullable|integer',
            'google_client_id' => 'nullable|string',
            'google_client_secret' => 'nullable|string',
            'google_client_status' => 'nullable|integer',
            'facebook_client_id' => 'nullable|string',
            'facebook_client_secret' => 'nullable|string',
            'facebook_client_status' => 'nullable|integer',
            'discord_client_id' => 'nullable|string',
            'discord_client_secret' => 'nullable|string',
            'discord_client_status' => 'nullable|integer',
            'footer_text_1' => 'nullable|string',
            'footer_text_2' => 'nullable|string',
            'dashboard_text_1' => 'nullable|string',
            'notice_homepage' => 'nullable|string',
            'notice_featured_homepage' => 'nullable|string',
            'fake_top_deposit' => 'nullable|string',
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

        if (isset($updateData['language_id']) && empty($updateData['language_id'])) {
            $updateData['language_id'] = null;
        }

        if (isset($updateData['currency_id']) && empty($updateData['currency_id'])) {
            $updateData['currency_id'] = null;
        }

        // Boolean checkboxes
        $updateData['show_banner_top'] = $request->has('show_banner_top') ? 1 : 0;
        $updateData['show_run_notify'] = $request->has('show_run_notify') ? 1 : 0;

        // Social Config
        $socialConfig = [
            'auth_google' => [
                'client_key' => $request->input('google_client_id'),
                'client_secret' => $request->input('google_client_secret'),
                'client_status' => $request->input('google_client_status', 0),
            ],
            'auth_facebook' => [
                'client_key' => $request->input('facebook_client_id'),
                'client_secret' => $request->input('facebook_client_secret'),
                'client_status' => $request->input('facebook_client_status', 0),
            ],
            'auth_discord' => [
                'client_key' => $request->input('discord_client_id'),
                'client_secret' => $request->input('discord_client_secret'),
                'client_status' => $request->input('discord_client_status', 0),
            ],
        ];
        $updateData['social_config'] = $socialConfig;

        // Handle Redirect Aliases robustly
        $sourceDomains = $request->input('redirect_to', []);
        if (is_string($sourceDomains)) {
            $sourceDomains = array_filter(array_map('trim', explode(',', $sourceDomains)));
        }
        elseif (!is_array($sourceDomains)) {
            $sourceDomains = [];
        }

        $updateData['is_redirect'] = 0; // Always a Branding record
        $updateData['redirect_to'] = array_values(array_unique($sourceDomains));

        $domain->update($updateData);

        Cache::flush();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật cấu hình tên miền ' . $domain->domain . ' thành công.'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
            $exists = DomainSetting::where('domain', $value)->exists();
            if ($exists) {
                $fail('Cấu hình cho tên miền này đã tồn tại.');
            }
        },
            ],
            'redirect_to' => 'nullable',
        ]);

        $sourceDomains = $request->input('redirect_to');
        if (is_string($sourceDomains) && !empty($sourceDomains)) {
            $sourceDomains = array_filter(array_map('trim', explode(',', $sourceDomains)));
        }
        elseif (!is_array($sourceDomains)) {
            $sourceDomains = [];
        }

        // Create the Main/Target Domain
        // If it's a redirect, we store it in the Branding row's redirect_to array
        $targetDomain = DomainSetting::create([
            'domain' => $request->domain,
            'is_redirect' => 0,
            'redirect_to' => array_values($sourceDomains),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Đã thêm cấu hình thành công cho ' . $request->domain]);
        }

        return redirect()->route('admin.domain.index')->with('success', 'Đã thêm cấu hình thành công cho ' . $request->domain);
    }

    public function deleteConfig(Request $request)
    {
        $request->validate([
            'domain' => 'required|string',
            'id' => 'nullable|integer'
        ]);

        if ($request->id) {
            DomainSetting::where('id', $request->id)->delete();
        }
        else {
            DomainSetting::where('domain', $request->domain)->delete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Đã xoá cấu hình branding thành công cho ' . $request->domain
            ]);
        }
        return redirect()->route('admin.domain.index')->with('success', 'Đã xoá cấu hình branding thành công cho ' . $request->domain);
    }

    public function show($id)
    {
        $config = DomainSetting::findOrFail($id);
        $domain = $config->domain;
        return view('admin.dashboard', compact('domain'));
    }
}
