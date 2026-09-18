<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Models\DomainSetting;
use Illuminate\Support\Facades\Cache;
use App\Services\TranslationService;
use Helper;

class LanguageController extends Controller
{

    public function index()
    {
        $languages = Language::all();
        // Fetch all unique domains that have a Global Configuration (language_id = null) in DomainSetting
        // OR simply fetch all unique domains configured.
        // User wants: "only those present in /admin/domain"
        // Let's take all domains from DomainSetting.
        $availableDomains = DomainSetting::select('domain')->distinct()->pluck('domain');

        return view('admin.language.index', compact('languages', 'availableDomains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'iso_code' => 'required|string|max:10',
            'status' => 'required|boolean',
        ]);

        Language::create($request->all());

        return response()->json(['status' => true, 'message' => 'Thêm ngôn ngữ mới thành công.']);
    }


    public function update(Request $request, $id)
    {
        $language = Language::findOrFail($id);

        if ($language->iso_code === 'vn') {
             return response()->json(['status' => false, 'message' => 'Không thể sửa ngôn ngữ mặc định.']);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'iso_code' => 'required|string|max:10',
            'status' => 'required|boolean',
        ]);

        $language->update($request->all());

        return response()->json(['status' => true, 'message' => 'Cập nhật ngôn ngữ thành công.']);
    }

    public function delete(Request $request)
    {
        $language = Language::findOrFail($request->id);

        if ($language->iso_code === 'vn' || $language->is_default) {
            return response()->json(['status' => false, 'message' => 'Không thể xoá ngôn ngữ mặc định.']);
        }
        $language->delete();

        return response()->json(['status' => true, 'message' => 'Xoá ngôn ngữ thành công.']);
    }

    public function translation($id)
    {
        $language = Language::findOrFail($id);
        $translationsArray = $language->translations ?? [];
        
        // Transform array into a structure the view expects (key, value objects)
        $translations = [];
        foreach ($translationsArray as $key => $value) {
            $translations[] = (object)[
                'key' => $key,
                'value' => $value
            ];
        }

        return view('admin.language.translation', compact('language', 'translations'));
    }

    public function updateTranslation(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        if ($language->iso_code === 'vn' || $language->is_default) {
            return response()->json(['status' => 'error', 'message' => 'Không thể dịch ngôn ngữ mặc định.']);
        }
        $key = $request->key;
        $value = $request->value;

        if (!$key) {
            return response()->json(['status' => 'error', 'message' => 'Missing data']);
        }

        $translations = $language->translations ?? [];
        $translations[$key] = $value;
        $language->update(['translations' => $translations]);

        // Clear cache
        $cacheKey = 'trans_' . $language->iso_code . '_' . md5($key);
        Cache::forget($cacheKey);

        return response()->json(['status' => 'success', 'message' => 'Saved']);
    }

    public function deleteTranslation(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        if ($language->iso_code === 'vn' || $language->is_default) {
            return response()->json(['status' => 'error', 'message' => 'Không thể xoá dịch ngôn ngữ mặc định.']);
        }
        $key = $request->key;

        if (!$key) {
            return response()->json(['status' => 'error', 'message' => 'Missing key']);
        }

        $translations = $language->translations ?? [];
        if (isset($translations[$key])) {
            unset($translations[$key]);
            $language->update(['translations' => $translations]);
        }

        // Clear cache
        $cacheKey = 'trans_' . $language->iso_code . '_' . md5($key);
        Cache::forget($cacheKey);

        return response()->json(['status' => 'success', 'message' => 'Deleted']);
    }

    public function bulkDeleteTranslation(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        if ($language->iso_code === 'vn' || $language->is_default) {
            return response()->json(['status' => 'error', 'message' => 'Không thể xoá dịch ngôn ngữ mặc định.']);
        }
        $keys = $request->keys;

        if (empty($keys) || !is_array($keys)) {
            return response()->json(['status' => 'error', 'message' => 'Không có mục nào được chọn']);
        }

        $translations = $language->translations ?? [];
        $deletedCount = 0;

        foreach ($keys as $key) {
            if (isset($translations[$key])) {
                unset($translations[$key]);
                $deletedCount++;
                
                // Clear cache for each key
                $cacheKey = 'trans_' . $language->iso_code . '_' . md5($key);
                Cache::forget($cacheKey);
            }
        }

        if ($deletedCount > 0) {
            $language->update(['translations' => $translations]);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Đã xoá ' . $deletedCount . ' mục thành công',
            'count' => $deletedCount
        ]);
    }

    public function bulkAutoTranslate(Request $request, $id)
    {
        // Close session immediately to allow concurrent requests (prevent locking)
        session_write_close();
        
        set_time_limit(0);
        $language = Language::findOrFail($id);
        $keys = $request->keys;
        $target = $language->iso_code;

        if (empty($keys)) {
            return response()->json(['status' => 'error', 'message' => 'No items selected']);
        }

        $targetMap = [
            'vn' => 'vi',
            'kr' => 'ko',
            'ja' => 'ja'
        ];
        $targetApi = $targetMap[$target] ?? $target;

        // Phase 1: Translate (Parallelizable Batch)
        $newTranslations = [];
        $hasApiError = false;
        $translatedCount = 0;

        try {
            // Call batch translation handling retries and placeholders internally
            $batchResults = TranslationService::translateBatch($keys, $targetApi);
            
            foreach ($batchResults as $original => $translated) {
                if ($translated && $translated !== $original) {
                    $newTranslations[$original] = $translated;
                    $translatedCount++;
                }
            }
        } catch (\Exception $e) {
            $hasApiError = true;
            \Log::error('Batch translation failed', ['error' => $e->getMessage()]);
        }

        if (empty($newTranslations)) {
             return response()->json([
                'status' => $hasApiError ? 'warning' : 'success', 
                'message' => 'Processed ' . count($keys) . ' items, no changes needed.',
                'count' => 0
            ]);
        }

        // Phase 2: Save (Fast, Critical Section)
        // Use Cache Lock to prevent race conditions during concurrent updates
        try {
            $lock = Cache::lock('lang_update_' . $id, 10);
            
            // Block for up to 10 seconds waiting for lock
            $lock->block(10, function() use ($id, $newTranslations) {
                // Re-fetch language inside lock to get latest state
                $language = Language::findOrFail($id);
                $bgTranslations = $language->translations ?? [];
                
                // Merge new translations
                foreach ($newTranslations as $k => $v) {
                    $bgTranslations[$k] = $v;
                    // Clear individual cache if needed
                    Cache::forget('trans_' . $language->iso_code . '_' . md5($k));
                }
                
                $language->update(['translations' => $bgTranslations]);
            });
            
            Cache::flush();

        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return response()->json(['status' => 'error', 'message' => 'Server busy (lock timeout), please try again.']);
        }

        if ($hasApiError) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Đã dịch ' . $translatedCount . ' mục. Một vài lỗi.',
                'count' => $translatedCount
            ]);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Đã dịch ' . $translatedCount . ' mục',
            'count' => $translatedCount
        ]);
    }

    public function autoTranslate(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        $text = $request->text;
        $target = $language->iso_code;

        if (!$text) {
            return response()->json(['status' => 'error', 'message' => 'No text to translate']);
        }

        $targetMap = [
            'vn' => 'vi',
            'kr' => 'ko',
            'ja' => 'ja'
        ];
        $target = $targetMap[$target] ?? $target;

        try {
            $translated = TranslationService::translate($text, $target);
            return response()->json(['status' => 'success', 'translated' => $translated]);
        } catch (\Exception $e) {
            \Log::warning('Auto-translate failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Lỗi dịch: ' . $e->getMessage()]);
        }
    }

    public function regenerateTranslations($id)
    {
        set_time_limit(0);
        $language = Language::findOrFail($id);
        
        // Try to find vn.json in different locations
        $paths = [
            base_path('lang/vn.json'),
            resource_path('lang/vn.json'),
            public_path('vn.json')
        ];
        
        $jsonPath = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $jsonPath = $path;
                break;
            }
        }
        
        if (!$jsonPath) {
            return response()->json(['status' => 'error', 'message' => 'File vn.json not found inside lang/ or public/']);
        }

        $vnTranslations = json_decode(file_get_contents($jsonPath), true);
        if (!$vnTranslations) {
            return response()->json(['status' => 'error', 'message' => 'vn.json is empty or invalid']);
        }

        $newTranslations = [];
        $count = 0;
        
        // Reset all translations to default (key = value)
        foreach ($vnTranslations as $key => $value) {
            // Use the key as the value (resetting to default)
            $newTranslations[$key] = $key;
            $count++;
        }

        $language->update(['translations' => $newTranslations]);
        Cache::flush();

        return response()->json(['status' => 'success', 'message' => 'Đã tạo lại bản dịch mặc định cho ' . $count . ' mục thành công']);
    }


    public function theme($id, $domain_id = null)
    {

        $language = Language::findOrFail($id);
        
        // Load all available domains
        $allDomains = \App\Models\DomainSetting::all();
        
        // Prepare list for the table
        $domainConfigs = $allDomains->map(function ($domain) use ($language) {
            // Check if this domain has an override in the language's JSON
            $hasOverride = isset($language->domain_settings[$domain->id]) && !empty($language->domain_settings[$domain->id]);
            
            return (object)[
                'id' => $domain->id,
                'domain' => $domain->domain,
                'has_override' => $hasOverride,
                // We don't need the actual override content for the list
            ];
        });

        // If a specific domain is selected for editing
        $activeConfig = null;
        if ($domain_id) {
            $targetDomain = $allDomains->firstWhere('id', $domain_id);
            if (!$targetDomain) {
                return redirect()->route('admin.language.theme', ['id' => $id])->with('error', 'Domain not found');
            }

            // Get existing override from JSON
            $currentOverrides = $language->domain_settings ?? [];
            $overrideConfig = $currentOverrides[$domain_id] ?? [];


            $activeConfig = (object)[
                'domain_id' => $targetDomain->id,
                'domain' => $targetDomain->domain,
                'language_id' => $id,
                'logo_light' => $overrideConfig['logo_light'] ?? '',
                'logo_dark' => $overrideConfig['logo_dark'] ?? '',
                'favicon' => $overrideConfig['favicon'] ?? '',
                'logo_share' => $overrideConfig['logo_share'] ?? '',
                'banner' => $overrideConfig['banner'] ?? '',
                'default_theme' => $overrideConfig['default_theme'] ?? 'light',
                'title' => $overrideConfig['title'] ?? '',
                'description' => $overrideConfig['description'] ?? '',
                'keywords' => $overrideConfig['keywords'] ?? '',
                'admin_email' => $overrideConfig['admin_email'] ?? '',
                'youtube_id' => $overrideConfig['youtube_id'] ?? '',
                'background_image_url' => $overrideConfig['background_image_url'] ?? '',
                'primary_color' => $overrideConfig['primary_color'] ?? '#000000',
            ];
        }

        return view('admin.language.theme', [
            'language' => $language,
            'configs' => $domainConfigs, // List for the table
            'activeConfig' => $activeConfig, // Data for the edit modal/form if ID passed
            'folder' => 'public' // Helper for upload path if needed
        ]);
    }

    public function updateTheme(Request $request, $id, $domain_id)
    {
        // Check protection
        $checkLang = Language::findOrFail($id);
        if ($checkLang->iso_code === 'vn' || $checkLang->is_default) {
             return redirect()->back()->with('error', 'Không thể cấu hình ngôn ngữ mặc định.');
        }
        $payload = $request->validate([
            'logo_light_file'           => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'logo_dark_file'            => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'favicon_file'              => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'logo_share_file'           => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
            'banner_file'               => 'nullable|image|mimes:png,jpg,jpeg,gif,svg|max:4096',
            'background_image_url_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg|max:4096',
            'default_theme'             => 'nullable|string|in:light,dark,auto,default',
            'title'                     => 'nullable|string|max:255',
            'description'               => 'nullable|string',
            'keywords'                  => 'nullable|string',
            'admin_email'               => 'nullable|email',
            'youtube_id'                => 'nullable|string',
            'primary_color'             => 'nullable|string|max:20',
        ]);

        // We are updating the OVERRIDE for this (Lang, Domain) pair.
        // We store this in JSON format in the pivot table.
        

        // 1. Get existing configs
        $language = Language::findOrFail($id);
        $domainSettings = $language->domain_settings ?? []; // Ensure it's an array
        
        // 2. Init current config for this domain
        $currentConfig = $domainSettings[$domain_id] ?? [];

        $fileFields = [
            'logo_light_file'           => 'logo_light',
            'logo_dark_file'            => 'logo_dark',
            'favicon_file'              => 'favicon',
            'logo_share_file'           => 'logo_share',
            'banner_file'               => 'banner',
            'background_image_url_file' => 'background_image_url',
        ];

        // Handle file uploads
        foreach ($fileFields as $fileInput => $dbField) {
            if ($request->hasFile($fileInput)) {
                $currentConfig[$dbField] = Helper::uploadFile($request->file($fileInput), 'public');
            } elseif ($request->input('delete_' . $dbField)) {
                $currentConfig[$dbField] = null;
            }
        }

        // Handle text fields
        $textFields = [
            'default_theme', 'title', 'description', 'keywords', 
            'admin_email', 'youtube_id', 'primary_color'
        ];
        foreach ($textFields as $field) {
            if ($request->has($field)) {
                $currentConfig[$field] = $request->input($field);
            }
        }

        // 3. Update the specific domain key
        $domainSettings[$domain_id] = $currentConfig;

        // 4. Save back to language
        $language->domain_settings = $domainSettings;
        $language->save();

        Cache::flush();

        return redirect()->route('admin.language.theme', ['id' => $id])
            ->with('success', 'Đã cập nhật cấu hình ghi đè thành công.');
    }

    public function deleteTheme(Request $request)
    {
        // Expects language_id and domain_id via POST
        $request->validate([
            'language_id' => 'required|integer',
            'domain_id' => 'required|integer', 
        ]);


        $language = Language::find($request->language_id);
        
        if ($language && ($language->iso_code === 'vn' || $language->is_default)) {
            return response()->json(['status' => false, 'message' => 'Không thể xoá cấu hình ngôn ngữ mặc định.']);
        }
        
        if ($language && $language->domain_settings && isset($language->domain_settings[$request->domain_id])) {
            $settings = $language->domain_settings;
            unset($settings[$request->domain_id]);
            $language->domain_settings = $settings;
            $language->save();
            
            Cache::flush();
            return response()->json(['status' => true, 'message' => 'Đã xoá cấu hình ghi đè.']);
        }
        
        return response()->json(['status' => false, 'message' => 'Không tìm thấy cấu hình ghi đè.']);
    }
}
