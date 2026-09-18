<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\ApiConfig;
use App\Models\SystemNotice;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GeneralController extends Controller
{
  public function index(Request $request)
  {
    $languages = \App\Models\Language::where('name', '!=', 'Mặc định')->where('is_default', false)->get();
    $currencies = \App\Models\Currency::where('status', true)->where('is_default', false)->get();
    return view('admin.settings.general', compact('languages', 'currencies'));
  }


  public function update(Request $request)
  {
    $type = $request->input('type', null);

    if ($type === 'general') {
      $domainRegex = '/^((([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,})|localhost|(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}))(:\d+)?$/i';

      $payload = $request->validate([
        'logo_light_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
        'logo_dark_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
        'favicon_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
        'logo_share_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048',
        'banner_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:4096',
        'background_image_url_file' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,ico|max:4096',
        'allowed_domains' => 'nullable|string',
        'admin_email' => 'nullable|email',
        'default_theme' => 'nullable|string|in:light,dark,auto,default',
        'font_family' => 'nullable|string',
        'primary_color' => 'nullable|string',
        'youtube_id' => 'nullable|string',
        'title' => 'nullable|string',
        'email_app_name' => 'nullable|string',
        'description' => 'nullable|string',
        'keywords' => 'nullable|string',
        'upload_provider' => 'required|string|in:public,s3,do_spaces,imgbb',
        'time_wait_free' => 'nullable|integer',
        'rate_robux' => 'nullable|string',
        'default_language_id' => 'nullable|integer',
        'default_currency_id' => 'nullable|integer',
        'currency_api_provider' => 'nullable|string',
        'currency_api_key' => 'nullable|string',
      ]);

      $fileFields = [
        'logo_light_file' => 'logo_light',
        'logo_dark_file' => 'logo_dark',
        'favicon_file' => 'favicon',
        'logo_share_file' => 'logo_share',
        'banner_file' => 'banner',
        'background_image_url_file' => 'background_image_url',
      ];

      // Fetch existing config to preserve values if no new file uploaded
      $existingConfig = Config::where('name', $type)->first();
      $existingValue = $existingConfig ? $existingConfig->value : [];

      foreach ($fileFields as $fileInput => $dbField) {
        if ($request->hasFile($fileInput)) {
          $payload[$dbField] = Helper::uploadFile($request->file($fileInput), 'public');
        }
        elseif ($request->input('delete_' . $dbField)) {
          $payload[$dbField] = null;
        }
        else {
          // Keep existing value if it exists
          $payload[$dbField] = $existingValue[$dbField] ?? null;
        }
        // Remove the temporary file field from payload
        unset($payload[$fileInput]);
      }

      if ($request->has('delete_primary_color')) {
        $payload['primary_color'] = null;
      }



      $config = Config::firstOrCreate(['name' => $type], ['value' => []]);


      // Logic for old logo removal deleted as they are now per-domain

      $config->update([
        'value' => $payload,
      ]);

      Cache::forget('general_settings');

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật cài đặt chung thành công.'
      ]);
    }
    elseif ($type === 'theme_custom') {
      $payload = $request->validate([
        'card_stats' => 'nullable|boolean',
        'product_info_type' => 'nullable|integer',
        'buy_button_img' => 'nullable|string',
        'enable_custom_theme' => 'nullable|boolean',
        'show_thongbao' => 'nullable|boolean',
        'show_lsmua' => 'nullable|boolean',
        'show_banner' => 'nullable|boolean',
        'show_all_account_img' => 'nullable|boolean',
        'minigame_show_value' => 'nullable|boolean',
        'pin_type' => 'nullable|string',
        'excluded_log_paths' => 'nullable|string',
        'fake_top_deposit' => 'nullable|string',
      ]);

      $config = Config::firstOrCreate(['name' => $type], ['value' => []]);

      $config->update([
        'value' => $payload,
      ]);

      Cache::forget('theme_custom');

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật giao diện thành công.'
      ]);
    }
    elseif ($type === 'contact_info') {
      $payload = $request->validate([
        'email' => 'nullable|string',
        'twitter' => 'nullable|string',
        'discord' => 'nullable|string',
        'facebook' => 'nullable|string',
        'telegram' => 'nullable|string',
        'phone_no' => 'nullable|string',
        'instagram' => 'nullable|string',
      ]);

      $config = Config::firstOrCreate(['name' => $type], ['value' => $payload]);

      $config->update([
        'value' => $payload,
      ]);

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật thông tin liên hệ thành công.'
      ]);
    }
    elseif ($type === 'shop_info') {
      $payload = $request->validate([
        'footer_text_1' => 'nullable|string',
        'footer_text_2' => 'nullable|string',
        'dashboard_text_1' => 'nullable|string',
        'notice_homepage' => 'nullable|string',
        'notice_featured_homepage' => 'nullable|string',
      ]);

      $config = Config::firstOrCreate(['name' => $type], ['value' => $payload]);

      $config->update([
        'value' => $payload,
      ]);

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật thông tin liên hệ thành công.'
      ]);
    }
    elseif ($type === 'affiliate_config') {

      $config = Config::firstOrCreate(['name' => $type], ['value' => $request->all()]);

      $config->update([
        'value' => $request->all(),
      ]);

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật cấu hình cộng tác viên thành công.'
      ]);
    }
    else if ($type === 'ticket_config') {
      $payload = $request->validate([
        'ticket_categories' => 'nullable|string',
        'ticket_quick_replies' => 'nullable|string',
      ]);

      // Normalize Categories: Split by newline or comma, trim, filter empty, join by newline
      if (isset($payload['ticket_categories'])) {
        $cats = preg_split('/[\r\n,]+/', $payload['ticket_categories'], -1, PREG_SPLIT_NO_EMPTY);
        $cats = array_map('trim', $cats);
        $cats = array_unique(array_filter($cats));
        $payload['ticket_categories'] = implode("\n", $cats);
      }

      // Normalize Quick Replies: Split by newline (commas might be part of sentence?), let's stick to newline for quick replies or just newline.
      // The user specifically asked for Categories. But normalizing newlines is good practice.
      // Quick replies usually are sentences, so commas are valid content. 
      // However, previous logic was "One per line".
      // Let's just normalize newlines to be safe, but NOT split by comma for quick replies.
      if (isset($payload['ticket_quick_replies'])) {
        $replies = preg_split('/[\r\n]+/', $payload['ticket_quick_replies'], -1, PREG_SPLIT_NO_EMPTY);
        $replies = array_map('trim', $replies);
        $replies = array_filter($replies);
        $payload['ticket_quick_replies'] = implode("\n", $replies);
      }

      $config = Config::firstOrCreate(['name' => $type], ['value' => []]);

      $config->update([
        'value' => $payload,
      ]);

      Cache::forget('general_settings');

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật cấu hình ticket thành công.'
      ]);
    }
    else if ($type === 'telegram_config') {
      $payload = $request->validate([
        'bot_token' => 'nullable|string',
        'chat_id' => 'nullable|string',
      ]);

      $config = Config::firstOrCreate(['name' => $type], ['value' => $payload]);

      $config->update([
        'value' => $payload,
      ]);

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật thông tin Telegram thành công.'
      ]);
    }
    elseif ($type === 'header_script') {
      $payload = $request->validate([
        'code' => 'nullable|string',
      ]);

      $config = SystemNotice::firstOrCreate(['name' => $type], ['value' => $payload['code']]);

      $config->update([
        'value' => $payload['code'],
      ]);

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật mã script thành công.'
      ]);
    }
    elseif ($type === 'footer_script') {
      $payload = $request->validate([
        'code' => 'nullable|string',
      ]);

      $config = SystemNotice::firstOrCreate(['name' => $type], ['value' => $payload['code']]);

      $config->update([
        'value' => $payload['code'],
      ]);

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật mã script thành công.'
      ]);
    }
    else if ($type === 'get_gift') {
      $payload = $request->validate([
        'min' => 'nullable|integer',
        'max' => 'nullable|integer',
        'width' => 'nullable|integer',
        'hight' => 'nullable|integer',
        'image' => 'nullable|url',
        'status' => 'nullable|boolean',
        'balance' => 'nullable|integer',
        'up_image' => 'nullable|file|mimes:png,jpg,jpeg,gif,svg|max:20000',
      ]);

      if ($request->hasFile('up_image') && !$request->input('image')) {
        $payload['image'] = Helper::uploadFile($request->file('up_image'), 'public');
      }
      elseif ($request->input('delete_image')) {
        $payload['image'] = null;
      }
      else {
        $payload['image'] = $request->input('image');
      }

      unset($payload['up_image']);

      $config = Config::firstOrCreate(['name' => $type], ['value' => []]);

      $config->update([
        'value' => $payload,
      ]);

      Helper::addHistory('Cập nhật hệ thống tặng quà miễn phí cho người mới');

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật thông tin tặng quà thành công.'
      ]);
    }
    elseif ($type === 'social_login') {
      $payload = $request->validate([
        'google_client_id' => 'nullable|string',
        'google_client_secret' => 'nullable|string',
        'google_client_status' => 'nullable|boolean',
        'facebook_client_id' => 'nullable|string',
        'facebook_client_secret' => 'nullable|string',
        'facebook_client_status' => 'nullable|boolean',
        'discord_client_id' => 'nullable|string',
        'discord_client_secret' => 'nullable|string',
        'discord_client_status' => 'nullable|boolean',
      ]);

      $configs = [
        'auth_google' => [
          'client_key' => $payload['google_client_id'],
          'client_secret' => $payload['google_client_secret'],
          'client_status' => $payload['google_client_status'] ?? 0,
        ],
        'auth_facebook' => [
          'client_key' => $payload['facebook_client_id'],
          'client_secret' => $payload['facebook_client_secret'],
          'client_status' => $payload['facebook_client_status'] ?? 0,
        ],
        'auth_discord' => [
          'client_key' => $payload['discord_client_id'],
          'client_secret' => $payload['discord_client_secret'],
          'client_status' => $payload['discord_client_status'] ?? 0,
        ],
      ];

      foreach ($configs as $key => $val) {
        $config = ApiConfig::firstOrCreate(['name' => $key], ['value' => []]);
        $existingValue = $config->value;
        if (is_string($existingValue)) {
          $existingValue = json_decode($existingValue, true) ?: [];
        }
        if (!is_array($existingValue)) {
          $existingValue = [];
        }
        $newValue = array_merge($existingValue, $val);
        $config->update(['value' => $newValue]);
      }

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật cấu hình đăng nhập thành công.'
      ]);
    }

    return redirect()->back();
  }
}
