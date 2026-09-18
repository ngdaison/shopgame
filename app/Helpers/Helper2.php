<?php



use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

if (!function_exists('setting')) {

  function setting($key, $default = null)
  {
    // 1. Check Domain/Language Context Config (Highest Priority)
    // This uses Helper::$domainSettings which is populated by DomainMiddleware
    // and contains the MERGED result of (Domain Default + Language Override)
    $domainVal = Helper::getDomainConfig($key);
    if ($domainVal !== null && $domainVal !== '') {
      return $domainVal;
    }

    // 2. Fallback to Global Settings (General Config)
    if (Cache::has('general_settings')) {
      $config = Cache::get('general_settings');
    }
    else {
      $config = Helper::getConfig('general', [], 'config');
      Cache::put('general_settings', $config, 60);
    }

    return $config[$key] ?? $default;
  }
}

if (!function_exists('getAppTitle')) {

  function getAppTitle()
  {
    // Get the page title from settings
    return setting('title', '');
  }
}

if (!function_exists('getAppTitleWithFallback')) {

  function getAppTitleWithFallback()
  {
    // Get the page title from settings, fallback to KiyoVN
    $title = setting('title', '');
    return !empty($title) ? $title : 'KiyoVN';
  }
}

if (!function_exists('getEmailAppName')) {

  function getEmailAppName()
  {
    // Try to get the custom email app name from domain config first (falls back to general settings)
    $emailAppName = Helper::getDomainConfig('email_app_name', null);

    // If no custom email app name is set, use default (KiyoVN)
    if (empty($emailAppName)) {
      $emailAppName = 'KiyoVN';
    }

    return $emailAppName;
  }
}

if (!function_exists('theme_config')) {
  function theme_config($key, $default = null)
  {
    if (Cache::has('theme_custom')) {
      $config = Cache::get('theme_custom');
    }
    else {
      $config = Helper::getConfig('theme_custom', []);
      Cache::put('theme_custom', $config, 60);
    }

    return $config[$key] ?? $default;
  }
}

if (!function_exists('currentVersion')) {
  function currentVersion()
  {

    if (env('APP_ENV') == 'local') {
      return 'Local';
    }

    if (env('SERVER_ALLOW_UPDATE') == false) {
      return 'Custom';
    }

    if (Cache::has('current_version')) {
      return Cache::get('current_version');
    }

    $version = Helper::getConfig('version_code', 1000);

    Cache::put('current_version', $version, 120);

    return $version;
  }
}

if (!function_exists('parseItem')) {
  function parseItem($content)
  {
    // Check if content contains | delimiter
    if (strpos($content, '|') !== false) {
      $item = explode('|', $content);
    }
    // Check if content contains : delimiter
    else if (strpos($content, ':') !== false) {
      $item = explode(':', $content);
    }
    // If no valid delimiter found, return empty array
    else {
      $item = [];
    }

    $username = trim($item[0] ?? '');
    $password = trim($item[1] ?? '');

    $extra_data = array_slice($item, 2);
    $extra_data = implode('|', $extra_data);

    return [
      'username' => $username,
      'password' => $password,
      'extra_data' => $extra_data,
    ];
  }
}

function getSettings($key)
{
  return null;
}

function getSelected(): string
{
  if (request()->routeIs('users.*')) {
    return 'tab_two';
  }
  elseif (request()->routeIs('permissions.*')) {
    return 'tab_three';
  }
  elseif (request()->routeIs('roles.*')) {
    return 'tab_three';
  }
  elseif (request()->routeIs('database-backups.*')) {
    return 'tab_four';
  }
  elseif (request()->routeIs('general-settings.*')) {
    return 'tab_five';
  }
  elseif (request()->routeIs('dashboards.*')) {
    return 'tab_one';
  }
  else {
    return 'tab_one';
  }
}

function CMSNT_check_license($licensekey, $localkey = '')
{
  try {
    $whmcsurl = 'https://client.cmsnt.co/';
    $licensing_secret_key = 'SHOPNICK3';
    $localkeydays = 15;
    $allowcheckfaildays = 5;
    $check_token = time() . md5(mt_rand(100000000, mt_getrandmax()) . $licensekey);
    $checkdate = date("Ymd");
    $domain = $_SERVER['SERVER_NAME'];
    $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ($_SERVER['LOCAL_ADDR'] ?? $_SERVER['REMOTE_ADDR']);
    $dirpath = dirname(__FILE__);
    $verifyfilepath = 'modules/servers/licensing/verify.php';
    $localkeyvalid = false;
    $originalcheckdate = $localkeydays ? date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y"))) : '';
    if ($localkey) {
      $localkey = str_replace("\n", '', $localkey); # Remove the line breaks
      $localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
      $md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
      if ($md5hash == md5($localdata . $licensing_secret_key)) {
        $localdata = strrev($localdata); # Reverse the string
        $md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
        $localdata = substr($localdata, 32); # Extract License Data
        $localdata = base64_decode($localdata);
        $localkeyresults = json_decode($localdata, true);
        $originalcheckdate = $localkeyresults['checkdate'];
        if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
          $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
          if ($originalcheckdate > $localexpiry) {
            $localkeyvalid = true;
            $results = $localkeyresults;
            $validdomains = explode(',', $results['validdomain']);
            if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
              $localkeyvalid = false;
              $localkeyresults['status'] = "Invalid";
              $results = array();
            }
            $validips = explode(',', $results['validip']);
            if (!in_array($usersip, $validips)) {
              $localkeyvalid = false;
              $localkeyresults['status'] = "Invalid";
              $results = array();
            }
            $validdirs = explode(',', $results['validdirectory']);
            if (!in_array($dirpath, $validdirs)) {
              $localkeyvalid = false;
              $localkeyresults['status'] = "Invalid";
              $results = array();
            }
          }
        }
      }
    }
    if (!$localkeyvalid) {
      $responseCode = 0;
      $postfields = array(
        'licensekey' => $licensekey,
        'domain' => $domain,
        'ip' => $usersip,
        'dir' => $dirpath,
      );
      if ($check_token)
        $postfields['check_token'] = $check_token;
      $query_string = '';
      foreach ($postfields as $k => $v) {
        $query_string .= $k . '=' . urlencode($v) . '&';
      }
      if (function_exists('curl_exec')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
      }
      else {
        $responseCodePattern = '/^HTTP\/\d+\.\d+\s+(\d+)/';
        $fp = @fsockopen($whmcsurl, 80, $errno, $errstr, 5);
        if ($fp) {
          $newlinefeed = "\r\n";
          $header = "POST " . $whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
          $header .= "Host: " . $whmcsurl . $newlinefeed;
          $header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
          $header .= "Content-length: " . @strlen($query_string) . $newlinefeed;
          $header .= "Connection: close" . $newlinefeed . $newlinefeed;
          $header .= $query_string;
          $data = $line = '';
          @stream_set_timeout($fp, 20);
          @fputs($fp, $header);
          $status = @socket_get_status($fp);
          while (!@feof($fp) && $status) {
            $line = @fgets($fp, 1024);
            $patternMatches = array();
            if (
            !$responseCode
            && preg_match($responseCodePattern, trim($line), $patternMatches)
            ) {
              $responseCode = (empty($patternMatches[1])) ? 0 : $patternMatches[1];
            }
            $data .= $line;
            $status = @socket_get_status($fp);
          }
          @fclose($fp);
        }
      }
      if ($responseCode != 200) {
        $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
        if (($originalcheckdate) > $localexpiry) {
          $results = $localkeyresults;
        }
        else {
          $results = array();
          $results['status'] = "Invalid";
          $results['description'] = "Remote Check Failed";
          return $results;
        }
      }
      else {
        preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
        $results = array();
        foreach ($matches[1] as $k => $v) {
          $results[$v] = $matches[2][$k];
        }
      }
      if (!is_array($results)) {
        die("Invalid License Server Response");
      }
      if (isset($results['md5hash'])) {
        if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
          $results['status'] = "Invalid";
          $results['description'] = "MD5 Checksum Verification Failed";
          return $results;
        }
      }
      if ($results['status'] == "Active") {
        $results['checkdate'] = $checkdate;
        $data_encoded = json_encode($results);
        $data_encoded = base64_encode($data_encoded);
        $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
        $data_encoded = strrev($data_encoded);
        $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
        $data_encoded = wordwrap($data_encoded, 80, "\n", true);
        $results['localkey'] = $data_encoded;
      }
      $results['remotecheck'] = true;
    }
    unset($postfields, $data, $matches, $whmcsurl, $licensing_secret_key, $checkdate, $usersip, $localkeydays, $allowcheckfaildays, $md5hash);
    return $results;
  }
  catch (\Exception $e) {
    $results['status'] = "Invalid";
    $results['description'] = $e->getMessage();
    return $results;
  }
}

function checkLicenseKey($licensekey)
{
  // Decoupled from legacy license server
  return [
    'status' => true,
    'msg' => 'Giấy phép hợp lệ (Local)',
    'message' => 'Giấy phép hợp lệ (Local)'
  ];
}

if (!function_exists('currentLang')) {
  function currentLang()
  {
    return app()->getLocale();
  }
}

if (!function_exists('usdRate')) {
  function usdRate()
  {
    return 24000;
  }
}

if (!function_exists('getLangJson')) {
  function getLangJson($lang = null)
  {
    if ($lang === null) {
      $lang = currentLang();
    }
    return [];
  }
}

if (!function_exists('__t')) {
  function __t($str)
  {
    $locale = currentLang();

    // Default language (Vietnamese) returns source text
    if ($locale === 'vn' || $locale === 'vi') {
      return $str;
    }

    $key = 'trans_' . $locale . '_' . md5($str);

    return Cache::remember($key, 3600, function () use ($locale, $str) {
      $language = \App\Models\Language::where('iso_code', $locale)->where('status', true)->first();
      if (!$language || !$language->translations)
        return $str;

      return $language->translations[$str] ?? $str;
    });
  }
}

if (!function_exists('domain')) {
  function domain()
  {
    return $_SERVER['SERVER_NAME'];
  }
}

if (!function_exists('get_change_logs')) {
  function get_change_logs()
  {
    return [];
  }
}

if (!function_exists('is_valid_2fa_secret')) {
  function is_valid_2fa_secret($secret)
  {
    if (!$secret)
      return false;
    return preg_match('/^[A-Z2-7]+=*$/', strtoupper($secret)) && strlen($secret) >= 16;
  }
}

if (!function_exists('generate_code_2fa')) {
  function generate_code_2fa($secret)
  {
    if (!$secret)
      return '';
    try {
      $google2fa = new \PragmaRX\Google2FA\Google2FA();
      return $google2fa->getCurrentOtp($secret);
    }
    catch (\Exception $e) {
      return '';
    }
  }
}

if (!function_exists('feature_enabled')) {
  function feature_enabled($feature)
  {
    return (bool)setting('feature_' . $feature, false);
  }
}

if (!function_exists('getSocialConfig')) {
  function getSocialConfig($provider)
  {
    $socialConfigs = Helper::branding('social_config', []);
    $providerKey = 'auth_' . strtolower($provider);

    // 1. Get Domain Config (if exists)
    $domainConfig = isset($socialConfigs[$providerKey]) ? $socialConfigs[$providerKey] : null;

    // 2. Check if we should use Global Config
    // Case A: No domain config found -> Use Global
    // Case B: Domain config exists but status is 0 (Use General) -> Use Global
    $useGlobal = false;
    if (!$domainConfig) {
      $useGlobal = true;
    }
    elseif (isset($domainConfig['client_status']) && (int)$domainConfig['client_status'] === 0) {
      $useGlobal = true;
    }

    if ($useGlobal) {
      // Fetch Global Config (ApiConfig)
      $globalConfig = Helper::getApiConfig($providerKey);
      // Map Global structure to expected structure if needed
      // ApiConfig stores it as ['client_key' => ..., 'client_secret' => ..., 'client_status' => ...] usually
      return $globalConfig;
    }

    return $domainConfig;
  }
}
