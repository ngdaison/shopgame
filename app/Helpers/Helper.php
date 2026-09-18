<?php
/**
 * @author baodev@cmsnt.co
 *
 * @version 1.0.1
 */

use App\Models\History;
use HTMLPurifier as HTMLPurifier;
use HTMLPurifier_Config as HTMLPurifier_Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

if (!class_exists('Helper')) {
  class Helper
  {
  public static $domainSettings = null;

  public static function getUserRoles($user)
  {
    if (!$user) {
      return [];
    }

    $roleData = [];

    // Check for Admin Permissions
    if ($user->isAdmin()) {
        $roleData[] = [
            'role' => 'admin',
            'label' => __t('Quản trị viên'),
            'route' => route('admin.dashboard'),
            'route_name' => 'admin.dashboard',
            'icon' => 'carbon:share',
            'sidebar_label' => __t('Trang Quản Trị Viên')
        ];
    }

    // Check for Partner Permissions
    if ($user->isPartner()) {
        $roleData[] = [
            'role' => 'partner',
            'label' => __t('Đối tác'),
            'route' => route('partner.dashboard'),
            'route_name' => 'partner.dashboard',
            'icon' => 'carbon:share',
            'sidebar_label' => __t('Trang Đối Tác')
        ];
    }

    // Check for Staff Permissions
    if ($user->isStaff()) {
        $roleData[] = [
            'role' => 'collaborator',
            'label' => __t('Cộng tác viên'),
            'route' => route('staff.dashboard'),
            'route_name' => 'staff.dashboard',
            'icon' => 'carbon:share',
            'sidebar_label' => __t('Trang Cộng Tác Viên')
        ];
    }

    return $roleData;
  }


  public static function getDefaultLocale()
  {
      try {
          $final = 'vi'; // Changed default from 'vn' to 'vi' to match database

          // 1. Check Domain Setting (Highest Priority)
          $domainModel = app()->bound('current_domain_model') ? app('current_domain_model') : null;

          // Fallback: If middleware hasn't run yet, resolve domain manually
          if (!$domainModel) {
              try {
                  $host = request()->getHost();
                  // Use a short cache to avoid repeat queries in the same request if called multiple times before middleware
                  $domainModel = \App\Models\DomainSetting::where('domain', $host)->first();
              } catch (\Exception $e) {
                  // Request context not available, skip domain check
                  $domainModel = null;
              }
          }

          if ($domainModel && $domainModel->language_id) {
              $lang = \App\Models\Language::find($domainModel->language_id);
              if ($lang && $lang->status) {
                  $final = $lang->iso_code;
              }
          }

          if ($final === 'vi' || $final === 'vn') {
              // 2. Check Global General Setting
              try {
                  $global = self::getConfig('general', []);
                  if (isset($global['default_language_id']) && $global['default_language_id']) {
                      $lang = \App\Models\Language::find($global['default_language_id']);
                      if ($lang && $lang->status) {
                          $final = $lang->iso_code;
                      }
                  }
              } catch (\Exception $e) {
                  // Config not available, skip
              }
          }

          if ($final === 'vi' || $final === 'vn') {
              // 3. Fallback to System Default (is_default flag)
              try {
                  $sysDefault = \App\Models\Language::where('is_default', true)->first();
                  if ($sysDefault && $sysDefault->status) {
                      $final = $sysDefault->iso_code;
                  }
              } catch (\Exception $e) {
                  // Database not available, use hardcoded default
                  $final = 'vi';
              }
          }

          try {
              \Illuminate\Support\Facades\Log::info("Helper::getDefaultLocale: Combined Result", ['locale' => $final, 'host' => request()->getHost()]);
          } catch (\Exception $e) {
              // Logging failed, continue silently
          }

          return $final;
      } catch (\Exception $e) {
          // If everything fails, return hardcoded default
          return 'vi';
      }
  }

  // function for laravel models
  public static function getConfig($name, $default = null, $type = 'config')
  {
    switch ($type) {
      case 'config':

        if ($name === 'deposit_info') {
             // Get Bank Config Data
             $bankConfig = \App\Models\BankConfig::first();
             return $bankConfig->config ?? $default;
        }

        $config = \App\Models\Config::where('name', $name)->first();

        if ($config) {
          return $config->value;
        } else {
          \App\Models\Config::create(['name' => $name, 'value' => $default]);
        }

        return $default;
      case 'api':
        $config = \App\Models\ApiConfig::where('name', $name)->first();
        if ($config) {
          return $config->value;
        } else {
          \App\Models\ApiConfig::create(['name' => $name, 'value' => $default]);
        }

        return $default;
      default:
        return $default;
    }
  }

  /**
   * Get domain-specific config with fallback to global theme_custom
   * 
   * @param string $key Config key to retrieve
   * @param mixed $default Default value if not found
   * @return mixed
   */

  /**
   * Get branding config with strict priority:
   * 1. Language-Domain Override
   * 2. Domain Setting
   * 3. Global Setting
   */

  /**
   * Get branding config with strict priority:
   * 1. Language-Domain Override (if exists & has value)
   * 2. Domain Setting (if exists & has value)
   * 3. Global Setting (Fallback)
   * 
   * Global Default must NEVER override 1 or 2.
   */
  public static function branding($key, $default = null, $fallback = true) 
  {
      // 1. Resolve Context
      $domainId = app()->bound('current_domain_id') ? app('current_domain_id') : null;
      // If domain_id is explicitly set to null by middleware (e.g. no match), we only use global.
      
      $locale = app()->getLocale();
      
      $finalValue = null;
      $source = 'unknown';

      // 2. CHECK LANGUAGE OVERRIDE (Highest Priority)
      if ($domainId) {
          // Resolve current Language ID
          // We use static cache to prevent re-querying every time.
          static $currentLangObj = null;
          if ($currentLangObj === null || $currentLangObj->iso_code !== $locale) {
               $currentLangObj = \App\Models\Language::where('iso_code', $locale)->first();
          }
           
          // Check JSON in language
          if ($currentLangObj && !empty($currentLangObj->domain_settings)) {
              if (isset($currentLangObj->domain_settings[$domainId]) && isset($currentLangObj->domain_settings[$domainId][$key])) {
                   $val = $currentLangObj->domain_settings[$domainId][$key];
                   if ($val !== null && $val !== '') {
                       $finalValue = $val;
                       $source = 'language_override';
                   }
              }
          }
      }

      // 3. CHECK DOMAIN SETTING (Middle Priority)
      if ($finalValue === null && $domainId) {
          // Try to get from Helper::$domainSettings IF we trust it, OR use model if bound
          // To be 100% strict as requested, let's use the bound model or query.
          // For performance, we try the valid array first, BUT we must ensure it isn't polluted by global defaults!
          // Helper::$domainSettings was merged with getEffectiveConfig.
          // IF we use Helper::$domainSettings, we are using the merged result. 
          // getEffectiveConfig merges Dom + Lang (Overrides). It DOES NOT merge global.
          // So Helper::$domainSettings[$key] IS SAFE to use as "Domain Level".
          // BUT, to be absolutely sure we don't accidentally get a merged value we don't want?
          // No, merged is fine (Lang > Domain). We just checked Lang above.
          // If Lang didn't have it, Helper::$domainSettings[$key] is the Domain value.
          
          // Wait, if we use Helper::$domainSettings, we might be re-using the logic we just wrote?
          // Let's rely on the RAW Domain Model if available to be explicitly clean.
          $domainModel = app()->bound('current_domain_model') ? app('current_domain_model') : null;
          if ($domainModel) {
               $val = $domainModel->{$key} ?? null;
               if ($val !== null && $val !== '') {
                   $finalValue = $val;
                   $source = 'domain_setting';
               }
          }
          // If raw model fails (e.g. key not in attributes?), try standard domainSettings array as backup
          if ($finalValue === null && isset(Helper::$domainSettings[$key])) {
               $val = Helper::$domainSettings[$key];
               if ($val !== null && $val !== '') {
                   $finalValue = $val;
                   $source = 'domain_middleware_array';
               }
          }
      }
      
      // 4. CHECK GLOBAL SETTING (Lowest Priority)
      if ($finalValue === null && $fallback) {
          // get config 'general'
          $global = Helper::getConfig('general', []);
          if (isset($global[$key]) && $global[$key] !== null && $global[$key] !== '') {
              $finalValue = $global[$key];
              $source = 'global_config';
          }
          
          // NEW: Fallback for notices/shop_info that are split into separate config rows
          if ($finalValue === null) {
              if ($key === 'notice_homepage') {
                  $finalValue = self::getNotice('home_dashboard');
                  $source = 'global_notice_fallback';
              } elseif ($key === 'notice_featured_homepage') {
                  $finalValue = self::getNotice('modal_dashboard');
                  $source = 'global_notice_fallback';
              } else {
                  // Check shop_info for footer/dashboard texts
                  $shopInfo = Helper::getConfig('shop_info', []);
                  if (isset($shopInfo[$key]) && $shopInfo[$key] !== null && $shopInfo[$key] !== '') {
                      $finalValue = $shopInfo[$key];
                      $source = 'global_shop_info';
                  }
              }
          }
      }

      // 5. Default
      if ($finalValue === null) {
          $finalValue = $default;
          $source = 'function_default';
      }

      // Debug Log (Filtered to avoid spam)
      if (request()->routeIs('home') && in_array($key, ['banner', 'logo_light'])) { 
          \Illuminate\Support\Facades\Log::info("Helper::branding($key)", [
              'result' => $finalValue,
              'source' => $source,
              'domain_id' => $domainId,
              'locale' => $locale
          ]);
      }

      return $finalValue;
  }

  /**

   * @param string $key Config key to retrieve
   * @param mixed $default Default value if not found
   * @return mixed
   */
  public static function getDomainConfig($key, $default = null)
  {
    // We allow empty strings because a domain config might intentionally clear a value (e.g. no youtube_id to show banner)
    if (self::$domainSettings && array_key_exists($key, self::$domainSettings) && self::$domainSettings[$key] !== null) {
      return self::$domainSettings[$key];
    }

    $lang = Cache::rememberForever('lang_obj_' . app()->getLocale(), function () {
        return \App\Models\Language::where('iso_code', app()->getLocale())->first();
    });

    if ($lang && isset($lang->{$key}) && !empty($lang->{$key})) {
        return $lang->{$key};
    }
    
    // 3. Fall back to global general config
    $generalConfig = self::getConfig('general', []);
    return $generalConfig[$key] ?? $default;
  }


  public static function getNotice($name, $default = '')
  {
    // Try domain-specific notice first
    $domainModel = app()->bound('current_domain_model') ? app('current_domain_model') : null;
    if ($domainModel) {
        $fieldName = null;
        if ($name === 'home_dashboard') $fieldName = 'notice_homepage';
        elseif ($name === 'modal_dashboard') $fieldName = 'notice_featured_homepage';
        
        if ($fieldName && isset($domainModel->{$fieldName}) && !empty($domainModel->{$fieldName})) {
            return $domainModel->{$fieldName};
        }
    }

    $notice = \App\Models\SystemNotice::where('name', $name)->first();

    if ($notice) {
      return $notice->value;
    } else {
      \App\Models\SystemNotice::create(['name' => $name, 'value' => $default]);
    }

    return $default;
  }

  public static function getApiConfig($name, $default = '')
  {
    // Try domain-specific API config first
    $domainModel = app()->bound('current_domain_model') ? app('current_domain_model') : null;
    if ($domainModel) {
        if (!empty($domainModel->api_configs) && is_array($domainModel->api_configs)) {
            if (isset($domainModel->api_configs[$name])) {
                return $domainModel->api_configs[$name];
            }
        }
    }

    $config = \App\Models\ApiConfig::where('name', $name)->first();
    if ($config) {
      return $config->value;
    } else {
      \App\Models\ApiConfig::create(['name' => $name, 'value' => $default]);
    }

    return $default;
  }

  public static function isSmtpConfigured()
  {
    $smtp = self::getApiConfig('smtp_detail');
    if (!$smtp || !is_array($smtp)) {
      return false;
    }

    return !empty($smtp['host']) && !empty($smtp['port']) && !empty($smtp['user']) && !empty($smtp['pass']);
  }

  public static function addHistory($content, $data = [])
  {
    if (!auth()->check()) {
      return false;
    }

    // Track admin updates for image cache invalidation
    if (auth()->user()->isAdmin()) {
        Cache::forever('admin_last_update_time', time());
    }

    // Get current user and prepare IP tracking
    $user = auth()->user();
    $currentIp = request()->ip();
    $timestamp = now()->toDateTimeString();
    
    // Use the deduplicated update method
    if (method_exists($user, 'updateIpAddress')) {
        $user->updateIpAddress($currentIp);
        $ipHistory = $user->ip_address;
    } else {
        // Fallback for safety or other user types
        $ipHistory = $user->ip_address ?? [];
        $ipHistory[$timestamp] = $currentIp;
        $user->update(['ip_address' => $ipHistory]);
    }
    return History::create([
      'role'       => $user->role,
      'data'       => $data,
      'content'    => $content,
      'user_id'    => $user->id,
      'username'   => $user->username,
      'ip_address' => $currentIp,
    ]);
  }


  // function for string

  public static function formatStatus($status, $type = 'html')
  {
    switch (strtolower($status)) {
      case 'paid':
        return $type == 'html' ? '<span class="fw-bold" style="color: #3D30A2">Đã thanh toán</span>' : 'Đã thanh toán';
      case 'unpaid':
        return $type == 'html' ? '<span class="fw-bold" style="color: #2B2A4C">Chưa thanh toán</span>' : 'Chưa thanh toán';
      case 'pending':
        return $type == 'html' ? '<span class="fw-bold" style="color: #FF9130">Chờ xử lý</span>' : 'Chờ xử lý';
      case 'processing':
        return $type == 'html' ? '<span class="fw-bold" style="color: #FF5B22">Đang xử lý</span>' : 'Đang xử lý';
      case 'completed':
        return $type == 'html' ? '<span class="fw-bold" style="color: #3D30A2">Hoàn thành</span>' : 'Hoàn thành';
      case 'cancelled':
        return $type == 'html' ? '<span class="fw-bold" style="color: #B31312">Đã bị hủy (Hoàn tiền)</span>' : 'Đã bị hủy (Hoàn tiền)';
      case 'declined':
        return $type == 'html' ? '<span class="fw-bold" style="color: #525CE5">Đã bị hủy (Không hoàn tiền)</span>' : 'Đã bị hủy (Không hoàn tiền)';
      case 'destroyed':
        return $type == 'html' ? '<span class="fw-bold" style="color: #B31312">Đã bị hủy</span>' : 'Đã bị hủy';
      case 'active':
        return $type == 'html' ? '<span class="fw-bold" style="color: #2D9596">Đang hoạt động</span>' : 'Đang hoạt động';
      case 'inactive':
        return $type == 'html' ? '<span class="fw-bold" style="color: #B31312">Đã khóa</span>' : 'Đã khóa';
      case 'expired':
        return $type == 'html' ? '<span class="fw-bold" style="color: #B31312">Đã hết hạn</span>' : 'Đã hết hạn';
      case 'error':
        return $type == 'html' ? '<span class="fw-bold" style="color: #B31312">Không hợp lệ</span>' : 'Không hợp lệ';
      case 'assigned':
        return $type == 'html' ? '<span class="fw-bold" style="color: #3D30A2">Đã giao</span>' : 'Đã giao';
      default:
        return $type == 'html' ? '<span class="fw-bold" style="color: #AF2655">Không xác định</span>' : 'Không xác định';
    }
  }

  public static function formatPrice($price, $currency = '$')
  {
    return number_format($price, 0, ',', '.') . ' ' . $currency;
  }

  public static function formatNumber($number)
  {
    return number_format($number, 0, ',', '.');
  }

  public static function formatTime($time, $format = 'd/m/Y H:i:s')
  {
    return date($format, strtotime($time));
  }

  public static function formatDate($time, $format = 'd/m/Y')
  {
    return date($format, strtotime($time));
  }

  public static function formatTimeAgo($time)
  {
    $time = strtotime($time);
    $diff = time() - $time;

    if ($diff < 60) {
      // if zero
      if ($diff < 0) {
        return 'vừa xong';
      } else {
        return $diff . ' giây trước';
      }
    }
    $diff = round($diff / 60);
    if ($diff < 60) {
      return $diff . ' phút trước';
    }
    $diff = round($diff / 60);
    if ($diff < 24) {
      return $diff . ' giờ trước';
    }
    $diff = round($diff / 24);
    if ($diff < 7) {
      return $diff . ' ngày trước';
    }
    $diff = round($diff / 7);
    if ($diff < 4) {
      return $diff . ' tuần trước';
    }

    return date('d/m/Y H:i:s', $time);
  }

  public static function formatTransType($type)
  {
    switch (strtolower($type)) {
      case 'deposit':
        return 'Nạp tiền';
      default:
        return strtoupper($type);
    }
  }

  public static function randomString($length = 10, $uppercase = false)
  {
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $uppercase ? strtoupper($randomString) : $randomString;
  }

  public static function randomNumber($length = 10)
  {
    $characters       = '0123456789';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
  }

  public static function parseOrderId($string, $prefix)
  {
    $re = '/' . $prefix . '\w+/im';
    preg_match_all($re, $string, $matches, PREG_SET_ORDER, 0);
    if (count($matches) == 0) {
      return null;
    }

    // Print the entire match result
    $orderCode    = $matches[0][0];
    $prefixLength = strlen($prefix);
    $orderId      = intval(substr($orderCode, $prefixLength));

    return $orderId;
  }

  public static function parseOrderName($string, $prefix)
  {
    $re = '/' . $prefix . '\w+/im';
    preg_match_all($re, $string, $matches, PREG_SET_ORDER, 0);
    if (count($matches) == 0) {
      return null;
    }

    // Print the entire match result
    $orderCode    = $matches[0][0];
    $prefixLength = strlen($prefix);
    $orderId      = substr($orderCode, $prefixLength);

    return $orderId;
  }

  public static function hideUsername($string, $length = 3)
  {
    if (strlen($string) <= $length) {
      return $string;
    }

    $string = substr($string, 0, $length) . str_repeat('*', strlen($string) - $length);

    return $string;
  }

  public static function hideEmail($string, $length = 3)
  {
    $email = explode('@', $string);
    $email = substr($email[0], 0, $length) . str_repeat('*', strlen($email[0]) - $length) . '@' . $email[1];

    return $email;
  }

  public static function htmlPurifier($dirty_html)
  {
    $config   = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    // Cho phép sử dụng các đường dẫn ảnh dạng data URI (base64)
    $config->set('URI.AllowedSchemes', ['data' => true, 'http' => true, 'https' => true]);

    // Khởi tạo đối tượng HTMLPurifier với cấu hình đã tạo
    $purifier = new HTMLPurifier($config);

    // Sử dụng HTMLPurifier để làm sạch mã HTML
    $clean_html = $purifier->purify($dirty_html);

    return $clean_html;
  }

  // function for datetime
  public static function getRemainingHours($end, $format = '%hh %m %s')
  {
    $end = !strtotime($end) ? date('Y-m-d H:i:s', $end) : $end;

    $startDate = new \DateTime();
    $endDate   = new DateTime($end);

    if ($startDate > $endDate) {
      return sprintf($format, 0, 0, 0);
    }

    $diff    = $endDate->diff($startDate);
    $days    = $diff->days;
    $hours   = $diff->h;
    $minutes = $diff->i;
    $seconds = $diff->s;

    $totalSeconds = $days * 86400 + $hours * 3600 + $minutes * 60 + $seconds;
    $diffDays     = floor($totalSeconds / 86400);
    $diffHours    = floor(($totalSeconds - $diffDays * 86400) / 3600);
    $diffMinutes  = floor(($totalSeconds - $diffDays * 86400 - $diffHours * 3600) / 60);

    return str_replace(['%d', '%h', '%m', '%s'], [$diffDays, $diffHours, $diffMinutes, $seconds], $format);
  }

  public static function getRemainingDays($end, $format = '%dd %hh')
  {
    $end = !strtotime($end) ? date('Y-m-d H:i:s', $end) : $end;

    $startDate = new \DateTime();
    $endDate   = new DateTime($end);

    if ($startDate > $endDate) {
      return sprintf($format, 0, 0, 0);
    }

    $diff    = $endDate->diff($startDate);
    $days    = $diff->days;
    $hours   = $diff->h;
    $minutes = $diff->i;
    $seconds = $diff->s;

    $totalSeconds = $days * 86400 + $hours * 3600 + $minutes * 60 + $seconds;
    $diffDays     = floor($totalSeconds / 86400);
    $diffHours    = floor(($totalSeconds - $diffDays * 86400) / 3600);
    $diffMinutes  = floor(($totalSeconds - $diffDays * 86400 - $diffHours * 3600) / 60);

    return sprintf($format, $diffDays, $diffHours, $diffMinutes);
  }

  public static function getTimeAgo($timestamp)
  {
    $lang = currentLang();

    $time = strtotime($timestamp) ? strtotime($timestamp) : $timestamp;
    // $time  = time() - $time_ago;

    $time_difference = time() - $time;

    if ($time_difference < 1) {
      return $lang === 'vn' ? 'vừa xong' : 'just now';
    }
    $condition = [
      12 * 30 * 24 * 60 * 60 => ($lang === 'vn' ? 'năm' : 'year'),
      30 * 24 * 60 * 60 => ($lang === 'vn' ? 'tháng' : 'month'),
      24 * 60 * 60 => ($lang === 'vn' ? 'ngày' : 'day'),
      60 * 60 => ($lang === 'vn' ? 'giờ' : 'hour'),
      60                     => ($lang === 'vn' ? 'phút' : 'minute'),
      1                      => ($lang === 'vn' ? 'giây' : 'second'),
    ];

    foreach ($condition as $secs => $str) {
      $d = $time_difference / $secs;

      if ($d >= 1) {
        $t = round($d);

        return $t . ' ' . $str . ' ' . ($lang === 'vn' ? 'trước' : 'ago');
      }
    }
  }

  // function convert timezone to new timezone
  public static function convertTimezone($time, $from = 'UTC', $timezone = 'Asia/Ho_Chi_Minh')
  {
    $date = new DateTime($time, new DateTimeZone($from));
    $date->setTimezone(new DateTimeZone($timezone));

    return $date->format('Y-m-d H:i:s');
  }

  // function convert number to currency
  public static function currentCurrency()
  {
    static $memoizedCurrency = null;
    if ($memoizedCurrency) return $memoizedCurrency;

      $code = null;

      // 1. Check URL Parameter (?currency=XXX)
      if (request()->has('currency')) {
          $urlCurrency = strtoupper(request()->get('currency'));
          $curr = \App\Models\Currency::where('code', $urlCurrency)->where('status', true)->first();
          if ($curr) {
              $code = $curr->code;
          }
      }

      // 2. Check Session (both 'currency' and 'currency_code' for compatibility)
      if (!$code && (session()->has('currency') || session()->has('currency_code'))) {
          $sessionCurrency = strtoupper(session('currency') ?? session('currency_code'));
          $curr = \App\Models\Currency::where('code', $sessionCurrency)->where('status', true)->first();
          if ($curr) {
              $code = $curr->code;
          }
      }

      // 3. Check Cookie (both 'currency' and 'currency_code' for compatibility)
      if (!$code && (request()->cookie('currency') || request()->cookie('currency_code'))) {
          $cookieCurrency = strtoupper(request()->cookie('currency') ?? request()->cookie('currency_code'));
          $curr = \App\Models\Currency::where('code', $cookieCurrency)->where('status', true)->first();
          if ($curr) {
              $code = $curr->code;
          }
      }

      // 4. Check Global Setting (default_currency_id)
      if (!$code) {
          $globalSettingId = setting('default_currency_id');
          if ($globalSettingId) {
             $curr = \App\Models\Currency::find($globalSettingId);
             if ($curr && $curr->status) {
                 $code = $curr->code;
             }
          }
      }

      // 5. Check Database Default (is_default flag)
      if (!$code) {
          $default = \App\Models\Currency::where('is_default', true)->first();
          $code = $default ? $default->code : 'VND'; 
      }
      
      $memoizedCurrency = strtoupper($code);
      return $memoizedCurrency;
  }



  public static function formatCurrency($number, $currency = null)
  {
    if ($currency === null) {
        $currency = self::currentCurrency();
    }
    
    $currency = strtoupper($currency);

    // Fetch Currency Model from Cache
    $currencyModel = Cache::remember('currency_' . $currency, 300, function () use ($currency) {
        return \App\Models\Currency::where('code', $currency)->first();
    });

    if ($currencyModel) {
        // DB Stores: 1 Currency = X VND.  (e.g., 1 USD = 25000 VND)
        // System prices are in VND.
        // To show in USD:  Price_in_USD = Price_in_VND / Rate_of_USD
        
        $rate = $currencyModel->rate;
        // Avoid division by zero
        if ($rate <= 0) $rate = 1;

        // If currency is default (VND), rate is 1. number / 1 = number. Correct.
        // If currency is USD (rate 25000), number / 25000 = usd_amount. Wait.
        
        // Let's re-verify the rate logic stored in DB.
        // In Controller store/update: $data['rate'] = 1 / $checkRate;
        // API returns 1 VND = X CODE (e.g. 0.00004 USD).
        // stored rate = 1 / 0.00004 = 25000.
        // So stored rate is Price of 1 Unit in VND. (1 USD = 25000 VND).
        
        // Correct Logic (Standard Rate storage: 1 Unit = X VND):
        // System Amount (VND) / Rate (VND per Unit) = Unit Amount.
        // Example: 50,000 VND / 25,000 (Rate) = 2 USD. 

        $convertedAmount = $number / $rate;

        $symbolLeft = $currencyModel->symbol_left ?? '';
        $symbolRight = $currencyModel->symbol_right ?? '';
        $decimals = $currencyModel->decimals ?? 0;
        
        $separator = $currencyModel->separator ?? '.';
        
        // Force VND specific formatting: 0 decimals, comma decimal separator (so thousands is dot), and space before symbol
        // Force VND specific formatting
        if ($currency === 'VND') {
            $decimals = 0;
            $separator = '.'; 
            if (empty($symbolRight) || trim($symbolRight) === 'đ' || trim($symbolRight) === '₫' || trim($symbolRight) === ',') {
                $symbolRight = ' ₫';
            }
        }


        
        // Custom Request: If value is 0, show no decimals (e.g. 0$ instead of 0.00$)
        if (abs($convertedAmount) < 0.0000000001) {
            $decimals = 0;
            $convertedAmount = 0;
        }

        $thousandSeparator = ($separator == '.') ? ',' : '.';

        return $symbolLeft . number_format($convertedAmount, $decimals, $separator, $thousandSeparator) . $symbolRight;
    }

    // Fallback if model not found
    $fallbacks = [
        'VND' => ['symbol_left' => '', 'symbol_right' => ' ₫', 'decimals' => 0, 'separator' => ','],
        'USD' => ['symbol_left' => '$', 'symbol_right' => '', 'decimals' => 2, 'separator' => '.'],
        'KHR' => ['symbol_left' => '', 'symbol_right' => '៛', 'decimals' => 0, 'separator' => ','],
        'LAK' => ['symbol_left' => '', 'symbol_right' => '₭', 'decimals' => 0, 'separator' => ','],
        'CNY' => ['symbol_left' => '¥', 'symbol_right' => '', 'decimals' => 2, 'separator' => '.'],
        'BRL' => ['symbol_left' => 'R$', 'symbol_right' => '', 'decimals' => 2, 'separator' => ','],
    ];

    if (isset($fallbacks[$currency])) {
        $fb = $fallbacks[$currency];
        $thousandSep = ($fb['separator'] == '.') ? ',' : '.';
        return $fb['symbol_left'] . number_format($number / 1, $fb['decimals'], $fb['separator'], $thousandSep) . $fb['symbol_right'];
    }

    return number_format($number, 0, ',', '.') . ' ' . $currency;
  }

  // function for server - PERSISTENCE_TEST
  public static function getDomain()
  {
    return $_SERVER['HTTP_HOST'] ?? '';
  }

  public static function getHostname()
  {
    return $_SERVER['HTTP_HOST'] ?? '';
  }

  public static function getIp()
  {
    $ip = request()->ip();

    if (request()->header('CF-Connecting-IP')) {
      $ip = request()->header('CF-Connecting-IP');
    }

    return $ip;
  }

  public static function getBrowser()
  {
    return request()->header('User-Agent');
  }

  // function for http request
  public static function curlGet($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
  }

  public static function curlPost($url, $data = [])
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);

    return $server_output;
  }

  public static function getTotalComm($username, $username1)
  {
    // username la nguoi gioi thieu
    // username1 la nguoi duoc gioi thieu

    $total = \App\Models\WalletLog::where('username', $username)
      ->where('sys_note', $username1)
      ->where('type', 'commission')->sum('amount');

    return $total;
  }

  public static function getListBank($code = null)
  {
    try {
      $response = Http::get('https://api.vietqr.io/v2/banks');

      if ($response->failed()) {
        return [];
      }

      $result = $response->json();

      if (isset($result['code']) && $result['code'] != '00') {
        return [];
      }

      $data = collect($result['data']);

      if ($code)
        return $data->where('code', $code)->first();

      return $data;
    } catch (\Throwable $th) {
      return [];
    }
  }

  public static function sendMessageTelegram($message, $parse_mode = 'TEXT')
  {
    $telegram = self::getConfig('telegram_config');

    if (!isset($telegram['bot_token']) || !isset($telegram['chat_id'])) {
      return false;
    }

    $url     = 'https://api.telegram.org/bot' . $telegram['bot_token'] . '/sendMessage';
    $data    = [
      'chat_id' => $telegram['chat_id'],
      'text'    => $message,
      // 'parse_mode' => $parse_mode,
    ];
    $options = [
      'http' => [
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'method'  => 'POST',
        'content' => http_build_query($data),
      ],
    ];
    $context = stream_context_create($options);
    $result  = file_get_contents($url, false, $context);
    if ($result === false) {
      return false;
    } else {
      $json = json_decode($result);
      if ($json->ok) {
        return true;
      } else {
        return false;
      }
    }
  }

  // function for upload
  public static function uploadFile($file, $provider = 'public', $path = null, $name = null)
  {

    $provider = setting('upload_provider', 'public');

    switch ($provider) {
      case 'imgur':
        return self::uploadImgur($file);
      case 'imgbb':
        return self::uploadImgbb($file);
      case 'chevereto':
        return self::uploadChevereto($file);
      case 'public':
        return self::uploadPublic($file, $path, $name);
      case 's3':
        return self::uploadAmazonS3($file, $path);
      case 'do_spaces':
        return self::uploadDOSpaces($file, $path);
      default:
        return null;
    }
  }

  public static function uploadPublic($file, $path = null, $name = null)
  {
    if ($file->isValid()) {
      // Store the image
      $fileExt  = $file->extension();
      $filePath = 'uploads/' . date('d-m-Y');
      $fileName = ($name !== null ? $name : str()->uuid()) . '.' . $fileExt;
      // $fileName = $file->getClientOriginalName();

      if ($path) {
        $filePath = $filePath . '/' . $path;
      }

      $file->move($filePath, $fileName);

      return '/' . ($filePath . '/' . $fileName);
    }

    return null;
  }

  public static function uploadAmazonS3($file, $path = null)
  {
    $config = self::getApiConfig('s3aws');
    if (!isset($config['AWS_ACCESS_KEY_ID']) || !isset($config['AWS_SECRET_ACCESS_KEY']) || !isset($config['AWS_DEFAULT_REGION']) || !isset($config['AWS_BUCKET'])) {
      return self::uploadPublic($file, $path);
    }

    config([
      'filesystems.disks.s3.key'                     => $config['AWS_ACCESS_KEY_ID'],
      'filesystems.disks.s3.secret'                  => $config['AWS_SECRET_ACCESS_KEY'],
      'filesystems.disks.s3.region'                  => $config['AWS_DEFAULT_REGION'],
      'filesystems.disks.s3.bucket'                  => $config['AWS_BUCKET'],
      'filesystems.disks.s3.use_path_style_endpoint' => false,
    ]);

    try {
      $s3 = Storage::disk('s3');
      if ($path) {
        $path = $path . '/' . date('d-m-Y');
      } else {
        $path = 'uploads/' . date('d-m-Y');
      }
      $fileName = str()->uuid() . '.' . $file->extension();
      $filePath = $path . '/' . $fileName;

      $s3->put($filePath, $file->getContent(), 'public');

      return $s3->url($filePath);
    } catch (Exception $e) {
      return self::uploadPublic($file, $path);
    }
  }

  public static function uploadDOSpaces($file, $path = null)
  {
    $config = self::getApiConfig('do_spaces');
    if (!isset($config['DO_SPACES_KEY']) || !isset($config['DO_SPACES_SECRET']) || !isset($config['DO_SPACES_REGION']) || !isset($config['DO_SPACES_BUCKET']) || !isset($config['DO_SPACES_URL'])) {
      return self::uploadPublic($file, $path);
    }

    config([
      'filesystems.disks.do_spaces.key'                     => $config['DO_SPACES_KEY'],
      'filesystems.disks.do_spaces.secret'                  => $config['DO_SPACES_SECRET'],
      'filesystems.disks.do_spaces.region'                  => $config['DO_SPACES_REGION'],
      'filesystems.disks.do_spaces.bucket'                  => $config['DO_SPACES_BUCKET'],
      'filesystems.disks.do_spaces.endpoint'                => $config['DO_SPACES_URL'],
      'filesystems.disks.do_spaces.use_path_style_endpoint' => false,
    ]);

    try {
      $s3 = Storage::disk('do_spaces');
      if ($path) {
        $path = $path . '/' . date('d-m-Y');
      } else {
        $path = 'uploads/' . date('d-m-Y');
      }
      // $fileName = str()->uuid() . '.' . $file->extension();
      $fileName = $file->getClientOriginalName();
      $filePath = $path . '/' . $fileName;

      $s3->put($filePath, $file->getContent(), 'public');

      return $s3->url($filePath);
    } catch (Exception $e) {
      return self::uploadPublic($file, $path);
    }
  }

  public static function uploadChevereto($file)
  {
    $apiKey  = 'chv_DOaR_fef63d594b0a1eae08f2d782b3add38c4dabee527a4146ffd441fabe235a7308603e42aca4803fbcd5575930dad63b2041a51dc27f78b50b18ef2e2d883ee51b';
    $content = $file->getContent();
    $result  = Http::attach('source', base64_encode($content))
      ->withHeaders(['X-API-Key' => $apiKey])
      ->post('https://upanh.org/api/1/upload?title=' . $file->getClientOriginalName())
      ->json();

    if (isset($result['status_code']) && $result['status_code'] === 200) {
      return $result['image']['url'];
    }

    return null;
  }

  public static function uploadImgbb($file)
  {
    $content = $file->getContent();

    $result = Http::attach('image', base64_encode($content))
      ->post('https://api.imgbb.com/1/upload?key=2eca972534568bf1e3bd77c37e0b0266&expiration=30&name=' . $file->getClientOriginalName())
      ->json();

    if (isset($result['status']) && $result['status'] === 200) {
      return $result['data']['url'];
    }

    return $result['error']['message'] ?? null;
  }

  public static function uploadImgur($file)
  {
    $client_id     = '86e171e4f20f914';
    $client_secret = 'cd9540ff7140fe4210350816a44db7b4ab95fd95';

    $result = Http::withHeaders([
      'Authorization' => 'Client-ID ' . $client_id,
    ])
      ->post('https://api.imgur.com/3/image', ['image' => base64_encode($file->getContent())])
      ->json();

    if ($result['success'] === true) {

      return $result['data']['link'];
    }

    return null;
  }

  public static function deleteFile($path)
  {
    try {
      $location = public_path($path);

      if (file_exists($location)) {
        unlink($location);
      }

      return true;
    } catch (\Throwable $th) {
      //throw $th;
      return false;
    }
  }

  // function send mail
  public static function sendMail($data)
  {
    $to          = $data['to'] ?? '';
    $subject     = $data['subject'] ?? '';
    $body        = $data['body'] ?? $data['content'] ?? '';
    $from        = $data['from'] ?? '';
    $fromName    = $data['fromName'] ?? '';
    $cc          = $data['cc'] ?? null;
    $bcc         = $data['bcc'] ?? '';
    $replyTo     = $data['replyTo'] ?? '';
    $replyToName = $data['replyToName'] ?? '';
    $attachments = $data['attachments'] ?? [];
    $headers     = $data['headers'] ?? [];

    try {
      self::sendMailNow($to, $subject, $body, $from, $fromName, $cc, $bcc, $replyTo, $replyToName, $attachments, $headers);

      return true;
    } catch (\Throwable $th) {
      \Illuminate\Support\Facades\Log::error("Email Sending Error: " . $th->getMessage());
      return false;
    }
  }

  private static function sendMailNow($to, $subject, $body, $from = null, $fromName = null, $cc = null, $bcc = null, $replyTo = null, $replyToName = null, $attachments = null, $headers = null)
  {
    $smtp = self::getApiConfig('smtp_detail');

    if ($smtp) {
      config([
        'mail.mailers.smtp.host'       => $smtp['host'],
        'mail.mailers.smtp.port'       => $smtp['port'],
        'mail.mailers.smtp.encryption' => 'tls',
        'mail.mailers.smtp.username'   => $smtp['user'],
        'mail.mailers.smtp.password'   => $smtp['pass'],
        'mail.from.address'            => $smtp['user'],
        'mail.from.name'               => strtoupper(self::getDomain()),
      ]);
      Mail::purge('smtp');
    }

    return Mail::mailer('smtp')->send([], [], function ($message) use ($to, $subject, $body, $from, $fromName, $cc, $bcc, $replyTo, $replyToName, $attachments, $headers) {
      $message->to($to);
      $message->subject($subject);
      $message->html($body);
      // $message->setContent($body);
      // $message->text(strip_tags($body));

      if ($from) {
        $message->from($from, $fromName);
      }
      if ($cc) {
        $message->cc($cc);
      }
      if ($bcc) {
        $message->bcc($bcc);
      }
      if ($replyTo) {
        $message->replyTo($replyTo, $replyToName);
      }
      if ($attachments) {
        foreach ($attachments as $attachment) {
          $message->attach($attachment);
        }
      }
      if ($headers) {
        foreach ($headers as $key => $value) {
          $message->getHeaders()->addTextHeader($key, $value);
        }
      }
    });
  }

  public static function checkLicense()
  {
    $license = env('CLIENT_SECRET_KEY', null);

    if (!$license) {
      die('Vui lòng cấu hình CLIENT_SECRET_KEY trong file .env');
    }

    if (strlen($license) < 26) {
      die('CLIENT_SECRET_KEY không hợp lệ');
    }

    //
  }


  // Optimize Image Load
  public static function getValidImage($path, $default = null)
  {
      $spinner = $default ? asset($default) : asset('/images/svg/spinner.svg');

      if (empty($path)) {
          return $spinner;
      }

      // If path is base64 data URI, return it directly
      if (str_starts_with($path, 'data:image/')) {
          return $path;
      }

      // If path is external URL, return it directly
      if (filter_var($path, FILTER_VALIDATE_URL)) {
          return $path;
      }

      $key = 'img_valid_' . md5($path);
      $lastAdminUpdate = Cache::get('admin_last_update_time', 0);
      $cached = Cache::get($key);

      // Conditions to return cached version:
      // 1. Cache exists
      // 2. Cache was created AFTER the last admin update
      if ($cached && isset($cached['time']) && $cached['time'] >= $lastAdminUpdate) {
          return $cached['valid'] ? asset($path) : $spinner;
      }

      // Check file existence (Re-check)
      $valid = false;
      try {
          // Handling relative paths vs absolute URLs if needed, but usually local paths
          if (file_exists(public_path($path))) {
              $valid = true;
          }
      } catch (\Exception $e) {
          $valid = false;
      }

      // Cache for 1 hour
      Cache::put($key, [
          'valid' => $valid,
          'time' => time()
      ], 3600);

      return $valid ? asset($path) : $spinner;
  }

  public static function verifyCaptcha($response, $action = null)
  {
    $captchaSetting = \App\Models\SecuritySetting::get('security_captcha', []);
    $provider = $captchaSetting['provider'] ?? 'none';

    if ($provider === 'none') {
        return true; 
    }

    // Support both v2 and v3 based on provider (simplified for now, assume secret key is same field 'secret_key')
    $secret = $captchaSetting['secret_key'] ?? null;

    if (!$secret) {
      return false;
    }

    // Google Recaptcha / Turnstile endpoint
    // Note: Turnstile uses a different endpoint but for now let's stick to existing logic or adapt if needed. 
    // The previous code only supported google. Let's keep it compatible.
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    if ($provider === 'turnstile') {
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    }

    $verifyResponse = Http::asForm()->post($url, [
      'secret'   => $secret,
      'response' => $response,
      'remoteip' => request()->ip(),
    ]);

    $result = $verifyResponse->json();

    if ($result['success'] === true && $action === null) {
      return true;
    }

    if ($result['success'] === true && isset($result['action']) && $result['action'] === $action) {
      return true;
    }
    
    // V2 doesn't always return action, so if action is requested but not in result, 
    // check if it's V2 provider which might not support action. 
    // For safety, strict check if action is provided. 
    // But if provider is V2, it might not return action.
    if ($result['success'] === true && $provider === 'recaptcha_v2') {
         return true;
    }

    return false;
  }

  // function for string
  public static function text2array($string)
  {
    $array = explode("\n", $string);
    $array = array_map('trim', $array);
    $array = array_filter($array, function ($value) {
      return $value !== '';
    });

    return $array;
  }

  public static function updateCommission($userId, $amount, $type = 'deposit', $orderCode = null)
  {
    $user = \App\Models\User::find($userId);

    if ($user === null) {
      return;
    }

    // --- Helper::updateCommission ---
    // 1. Existing Referral/Affiliate Logic
    $parent = $user->referrer;
    if ($parent !== null) {
        $config = static::getConfig('affiliate_config');
        $config = is_array($config) ? $config : [];
        $trigger = $config['commission_type'] ?? 'deposit';

        if ($trigger === $type) {
            $canReceive = true;
            // Check Limits
            $limitMode = $config['limit_mode'] ?? 'count';
            $limitCount = (int) ($config['limit_count'] ?? 0);
            $limitDays = (int) ($config['limit_days'] ?? 0);

            if ($limitMode === 'both') {
                if ($limitCount > 0 && $limitDays > 0) {
                    $count = \App\Models\WalletLog::where('user_id', $parent->id)
                      ->where('type', 'commission')
                      ->where('created_at', '>=', now()->subDays($limitDays))
                      ->count();
                    if ($count >= $limitCount) $canReceive = false;
                }
            } elseif ($limitMode === 'count') {
                if ($limitCount > 0) {
                    $count = \App\Models\WalletLog::where('user_id', $parent->id)
                      ->where('type', 'commission')
                      ->where('sys_note', $user->username)
                      ->count();
                    if ($count >= $limitCount) $canReceive = false;
                }
            } elseif ($limitMode === 'days') {
                if ($limitDays > 0) {
                    $days = $user->created_at->diffInDays(now());
                    if ($days > $limitDays) $canReceive = false;
                }
            }

            if ($canReceive) {
                $percent = $config['comm_percent'] ?? 10;
                $commission = ($amount * $percent) / 100;

                if ($commission > 0) {
                    $parent->increment('balance_1', $commission);
                    \App\Models\WalletLog::create([
                        'type'           => 'commission',
                        'amount'         => $commission,
                        'status'         => 'Completed',
                        'user_id'        => $parent->id,
                        'username'       => $parent->username,
                        'order_id'       => $orderCode,
                        'sys_note'       => $user->username,
                        'user_note'      => 'Hoa hồng thành viên (' . $user->username . ')',
                        'user_action'    => 'increment',
                        'ip_address'     => '127.0.0.1',
                        'balance_after'  => $parent->balance_1,
                        'balance_before' => $parent->balance_1 - $commission
                    ]);
                }
            }
        }
    }

    // 2. NEW Campaign Tracking Logic
    $campaign = $user->campaign_id ? \App\Models\Campaign::find($user->campaign_id) : null;
    if ($campaign && $campaign->status) {
        // Check Commission Type (deposit or order)
        if ($campaign->commission_type !== $type) {
            return;
        }

        $canReceiveCampaign = true;

        // Check Limit Mode
        if ($campaign->limit_mode === 'days') {
            if ($campaign->limit_days > 0 && $user->created_at->diffInDays(now()) > $campaign->limit_days) {
                $canReceiveCampaign = false;
            }
        } elseif ($campaign->limit_mode === 'count') {
            if ($campaign->limit_count > 0) {
                $count = \App\Models\WalletLog::where('user_id', $userId)
                    ->where('type', 'campaign_commission')
                    ->where('campaign_id', $campaign->id)
                    ->count();
                if ($count >= $campaign->limit_count) {
                    $canReceiveCampaign = false;
                }
            }
        } elseif ($campaign->limit_mode === 'both') {
            if ($campaign->limit_days > 0 && $user->created_at->diffInDays(now()) > $campaign->limit_days) {
                $canReceiveCampaign = false;
            }
            if ($canReceiveCampaign && $campaign->limit_count > 0) {
                $count = \App\Models\WalletLog::where('user_id', $userId)
                    ->where('type', 'campaign_commission')
                    ->where('campaign_id', $campaign->id)
                    ->count();
                if ($count >= $campaign->limit_count) {
                    $canReceiveCampaign = false;
                }
            }
        }

        if ($canReceiveCampaign) {
            $commission = ($amount * $campaign->comm_percent) / 100;
            if ($commission > 0) {
                $campaign->increment('balance', $commission);
                $campaign->increment('total_commission', $commission);
                $campaign->increment('orders');

                \App\Models\WalletLog::create([
                    'type'           => 'campaign_commission',
                    'amount'         => $commission,
                    'status'         => 'Completed',
                    'user_id'        => $user->id,
                    'campaign_id'    => $campaign->id,
                    'username'       => $user->username,
                    'order_id'       => $orderCode,
                    'sys_note'       => 'Campaign: ' . $campaign->name,
                    'user_note'      => 'Hoa hồng chiến dịch (' . $campaign->name . ')',
                    'user_action'    => 'increment',
                    'ip_address'     => '127.0.0.1',
                    'balance_after'  => $campaign->balance,
                    'balance_before' => $campaign->balance - $commission
                ]);
            }
        }
    }
  }




  public static function getEffectiveConfig($domain_id, $language_id = null)
  {
      // 1. Get Domain Default Config (Base)
      $domainConfig = \App\Models\DomainSetting::find($domain_id);
      
      if (!$domainConfig) {
          return null;
      }

      $configArray = $domainConfig->toArray();

      // 2. If language_id is provided, try to find an override and MERGE it
      if ($language_id) {
          

          /* JSON Config Refactor */
          // Use fresh lookup or reliable cache to get the Language model
          // We must ensure 'domain_settings' attribute is loaded/casted
          $language = \App\Models\Language::find($language_id); 
          
          if ($language) {
              \Illuminate\Support\Facades\Log::info("Helper::getEffectiveConfig - Language Found", [
                  'lang_id' => $language->id,
                  'iso' => $language->iso_code,
                  'has_settings' => !empty($language->domain_settings)
              ]);

              if (!empty($language->domain_settings) && isset($language->domain_settings[$domain_id])) {
                  $overrideJson = $language->domain_settings[$domain_id];
                  
                  \Illuminate\Support\Facades\Log::info("Helper::getEffectiveConfig - Override Found", [
                      'raw_json' => $overrideJson
                  ]);

                  if (!empty($overrideJson)) {
                      // Filter out NULL only? Or empty string too?
                      // User requirement: "If language-domain settings are empty, fallback to domain settings."
                      // If I uploaded a Banner in Admin, it's a string path.
                      // If I didn't, it might be null or empty string depending on Controller.
                      // Code currently filters !is_null && !== ''.
                      $overrideBytes = array_filter($overrideJson, function($value) {
                          return !is_null($value) && $value !== '';
                      });

                      \Illuminate\Support\Facades\Log::info("Helper::getEffectiveConfig - Merging", [
                         'filter_result' => $overrideBytes
                      ]);
                      
                      $configArray = array_merge($configArray, $overrideBytes);
                  }
              } else {
                  \Illuminate\Support\Facades\Log::info("Helper::getEffectiveConfig - No Override for this Domain", ['domain_id' => $domain_id]);
              }
          }
      }

      return (object) $configArray;
  }

  public static function formatMoney($amount, $currencyCode = 'VND')
  {
      $currency = \Illuminate\Support\Facades\Cache::remember('currency_' . $currencyCode, 86400, function () use ($currencyCode) {
          return \App\Models\Currency::where('code', $currencyCode)->first();
      });

      if (!$currency) {
          return number_format($amount);
      }

      $thousands_sep = $currency->separator ?? ',';
      if ($thousands_sep == '.') {
          $thousands_sep = ',';
      }
      $dec_point = ($thousands_sep == '.') ? ',' : '.';
      
      $formatted = number_format($amount, $currency->decimals, $dec_point, $thousands_sep);

      return $currency->symbol_left . $formatted . $currency->symbol_right;
  }

  public static function convertCurrency($amount, $fromCode, $toCode)
  {
      if ($fromCode === $toCode) return $amount;

      $from = \Illuminate\Support\Facades\Cache::remember('currency_' . $fromCode, 86400, function () use ($fromCode) {
          return \App\Models\Currency::where('code', $fromCode)->first();
      });

      $to = \Illuminate\Support\Facades\Cache::remember('currency_' . $toCode, 86400, function () use ($toCode) {
          return \App\Models\Currency::where('code', $toCode)->first();
      });

      if (!$from || !$to) return $amount;

      if ($from->rate == 0) return 0;

      // Rate logic: 1 VND = Rate * Currency
      // Amount(VND) = Amount(From) / Rate(From)
      // Amount(To) = Amount(VND) * Rate(To)

      $amountInVnd = $amount / $from->rate;
      $amountInTarget = $amountInVnd * $to->rate;

      return $amountInTarget;
  }
  public static function sendEmailTemplate($key, $to, $data = [], $cc = [])
  {
      $template = \App\Models\EmailTemplate::where('key', $key)->first();

      if (!$template) {
          // Fallback or Log error?
          // For now, let's log it and maybe try to send a generic one if possible, 
          // but relying on the seeder should be enough if we do it right.
          \Illuminate\Support\Facades\Log::error("Email Template not found: " . $key);
          return false;
      }

      $subject = $template->subject;
      $content = $template->content;

      // Replace variables
      foreach ($data as $k => $v) {
          $subject = str_replace('{' . $k . '}', $v, $subject);
          $content = str_replace('{' . $k . '}', $v, $content);
      }

      return self::sendMail([
          'to' => $to,
          'subject' => $subject,
          'content' => $content,
          'cc' => $cc
      ]);
  }

  public static function getRobuxRate($amount, $config)
  {
      // If config is a simple number, return it
      if (is_numeric($config)) {
          return (float)$config;
      }

      $config = (string)$config;
      if (empty($config)) return 0;

      $parts = explode(',', $config);
      $tiers = [];

      foreach ($parts as $part) {
          $s = trim($part);
          if (empty($s)) continue;

          if (strpos($s, '|') !== false) {
              [$limit, $r] = array_map('intval', explode('|', $s));
              if ($r > 0) {
                  $tiers[] = ['limit' => $limit, 'rate' => $r];
              }
          } else {
              $r = (int)$s;
              if ($r > 0) {
                  $tiers[] = ['limit' => 0, 'rate' => $r];
              }
          }
      }

      // Sort by limit ASC
      usort($tiers, function ($a, $b) {
          return $a['limit'] <=> $b['limit'];
      });

      // Find applicable rate (highest limit <= amount)
      $applicableRate = 0;
      if (count($tiers) > 0) {
          $applicableRate = $tiers[0]['rate']; // Default to lowest tier
          foreach ($tiers as $tier) {
              if ($amount >= $tier['limit']) {
                  $applicableRate = $tier['rate'];
              }
          }
      }

      // If no valid tiers found in string but we had string, try parsing as float
      if ($applicableRate <= 0) {
           return (float)$config;
      }

      return $applicableRate;
  }
  }
}
