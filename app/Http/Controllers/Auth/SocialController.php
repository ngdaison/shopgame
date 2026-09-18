<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Account\VerifyActionController;

class SocialController extends Controller
{
  public function redirectToProvider($provider)
  {
    $config = getSocialConfig($provider);

    if (!$config) {
      return redirect('login')->with('error', 'Đăng nhập bằng ' . ucfirst($provider) . ' chưa được cấu hình');
    }

    if (isset($config['client_status']) && (int)$config['client_status'] === -1) {
      return redirect('login')->with('error', 'Đăng nhập bằng ' . ucfirst($provider) . ' đã bị vô hiệu hóa');
    }

    $clientId = $config['client_key'] ?? $config['client_id'] ?? null;
    $clientSecret = $config['client_secret'] ?? null;

    if (!$clientId || !$clientSecret) {
      return redirect('login')->with('error', 'Đăng nhập bằng ' . ucfirst($provider) . ' chưa được cấu hình');
    }

    if ($config) {
      config([
        'services.' . $provider . '.active' => true,
        'services.' . $provider . '.client_id' => $clientId,
        'services.' . $provider . '.client_secret' => $clientSecret,
        'services.' . $provider . '.redirect' => route('auth.social.callback', ['provider' => $provider]),
      ]);
    }

    // If user is already logged in, store popup flag if coming from popup
    if (Auth::check()) {
      session(['social_link_provider' => $provider]);
      // Popup flag is set by LinkedAccountController::link()
    }

    return Socialite::driver($provider)->redirect();
  }

  public function handleProviderCallback($provider)
  {
    $config = getSocialConfig($provider);

    if ($config) {
      $clientId = $config['client_key'] ?? $config['client_id'] ?? null;
      $clientSecret = $config['client_secret'] ?? null;

      config([
        'services.' . $provider . '.active' => true,
        'services.' . $provider . '.client_id' => $clientId,
        'services.' . $provider . '.client_secret' => $clientSecret,
        'services.' . $provider . '.redirect' => route('auth.social.callback', ['provider' => $provider]),
      ]);
    }

    $socialUser = Socialite::driver($provider);

    try {
      $socialUser = $socialUser->user();
    }
    catch (\Exception $e) {
      return redirect()->route('login')->with('error', 'Đăng nhập bằng ' . $provider . ' thất bại. Lỗi: ' . $e->getMessage());
    }

    // Check if there was an intent to link, but Auth was somehow lost
    if (!Auth::check() && session('social_link_intent') && session('social_link_user_id')) {
        Auth::loginUsingId(session('social_link_user_id'));
    }

    // === CASE: User is already logged in -> Link social account ===
    if (Auth::check()) {
      $currentUser = Auth::user();

      // Security: Check if this social ID is already linked to ANOTHER account
      $allUsers = User::whereNotNull('social_links')->where('id', '!=', $currentUser->id)->get();
      foreach ($allUsers as $u) {
        $links = $u->social_links ?? [];
        if (isset($links[strtolower($provider)]) && $links[strtolower($provider)] == $socialUser->id) {
          $errorMsg = 'Tài khoản ' . ucfirst($provider) . ' này đã được liên kết với một tài khoản khác.';
          if (session('social_link_popup')) {
            session()->forget('social_link_popup');
            return $this->popupResultPage(false, $provider, $errorMsg);
          }
          return redirect()->route('account.profile.index', ['tab' => 'linked-account'])
            ->with('error', $errorMsg);
        }
      }

      // Link the social account
      $currentUser->linkSocial($provider, $socialUser->id);

      $successMessage = 'Đã liên kết tài khoản ' . ucfirst($provider) . ' thành công!';
      $autoVerifiedEmail = false;

      // Auto-verify email if social email matches current user's email
      if ($socialUser->email && $socialUser->email === $currentUser->email && !$currentUser->email_verified_at) {
        $currentUser->markEmailAsVerified();
        $currentUser->email_changed_at = now();
        $currentUser->save();
        $successMessage .= ' Email đã được xác thực tự động.';
        $autoVerifiedEmail = true;
        Helper::addHistory('Liên kết ' . ucfirst($provider) . ' + Email tự động xác thực');
      } else {
        Helper::addHistory('Đã liên kết tài khoản ' . ucfirst($provider));
      }

      // If called from popup, close popup and notify parent
      if (session('social_link_popup')) {
        session()->forget('social_link_popup');
        return $this->popupResultPage(true, $provider, $successMessage, $autoVerifiedEmail);
      }

      return redirect()->route('account.profile.index', ['tab' => 'linked-account'])
        ->with('success', $successMessage);
    }

    // === CASE: Find user by social_links JSON field ===
    $allUsers = User::whereNotNull('social_links')->get();
    $userBySocialId = null;
    foreach ($allUsers as $u) {
      $links = $u->social_links ?? [];
      if (isset($links[strtolower($provider)]) && $links[strtolower($provider)] == $socialUser->id) {
        $userBySocialId = $u;
        break;
      }
    }

    if ($userBySocialId) {
      return $this->loginUser($userBySocialId, $provider, $socialUser->id, true);
    }

    // === CASE: Find user by username (old method) ===
    $username = $socialUser->id;
    if ($socialUser->email) {
      $username = explode('@', $socialUser->email)[0];
    }

    $userByUsername = User::where('username', $username)->first();
    if ($userByUsername) {
      // Auto-link social account
      $userByUsername->linkSocial($provider, $socialUser->id);
      return $this->loginUser($userByUsername, $provider, $socialUser->id, true);
    }

    // Find by old provider ID username
    $userById = User::where('username', $socialUser->id)->first();
    if ($userById) {
      $userById->linkSocial($provider, $socialUser->id);
      return $this->loginUser($userById, $provider, $socialUser->id, true);
    }

    // === CASE: Find user by matching email ===
    if ($socialUser->email) {
      $userByEmail = User::where('email', $socialUser->email)->first();
      if ($userByEmail) {
        // Auto-link this social provider
        $userByEmail->linkSocial($provider, $socialUser->id);
        return $this->loginUser($userByEmail, $provider, $socialUser->id, true);
      }
    }

    // === CASE: Create new user ===
    $newUser = User::create([
      'role' => 'user',
      'email' => $socialUser->email ?? ($socialUser->id . '@' . $provider . '.com'),
      'avatar' => $socialUser->avatar,
      'username' => $username,
      'filename' => $socialUser->name,
      'fullname' => $socialUser->name,
      'password' => bcrypt($socialUser->id) . '_baoinc' . mt_rand(400, 500),
      'has_password' => false,
      'ip' => request()->ip(),
      'register_ip' => request()->ip(),
      'user_agent' => request()->userAgent(),
      'register_by' => strtoupper($provider),
      'referral_code' => str()->random(12),
      'domain' => Helper::getDomain(),
      'social_links' => [strtolower($provider) => $socialUser->id],
    ]);

    $newUser->postLoginInit(request());
    $newUser->markEmailAsVerified();
    Auth::login($newUser);

    Helper::addHistory('Đăng ký tài khoản bằng ' . $provider . ' thành công (Email đã tự động xác thực)');

    if (session('social_link_intent')) {
        session()->forget('social_link_intent');
        return redirect()->route('account.profile.index', ['tab' => 'linked-account']);
    }

    return redirect()->intended(route('home'));
  }

  /**
   * Login a found user, with optional 2FA/OTP check.
   */
  private function loginUser(User $user, string $provider, string $socialId, bool $autoVerifyEmail = false)
  {
    if ($user->status !== 'active') {
      return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị khóa.');
    }

    if ($autoVerifyEmail && !$user->hasVerifiedEmail()) {
      $user->markEmailAsVerified();
    }

    // Check 2FA / OTP requirement
    if ($user->login_verify_email || $user->login_verify_google2fa) {
      $requiresEmail = $user->login_verify_email;
      $requires2fa = $user->login_verify_google2fa;

      // Priority: Google 2FA
      if ($requires2fa) {
        $requiresEmail = false;
      }

      if ($requiresEmail) {
        // No manual OTP send here. VerifyActionController will handle it on page load.
      }

      $verifyData = [
        'verify_user_id' => $user->id,
        'verify_remember' => false,
        'verify_email' => $requiresEmail,
        'verify_2fa' => $requires2fa,
      ];

      VerifyActionController::resetVerification($user->id);
      session()->put($verifyData);
      session()->save();

      return redirect()->route('account.verify');
    }

    $user->postLoginInit(request());
    Auth::login($user);
    Helper::addHistory('Đăng nhập bằng ' . $provider . ' thành công');

    if (session('social_link_intent')) {
        session()->forget('social_link_intent');
        return redirect()->route('account.profile.index', ['tab' => 'linked-account']);
    }

    return redirect()->intended(route('home'));
  }

  /**
   * Return a self-closing HTML page that notifies the opener via postMessage, then closes.
   * Used when OAuth is triggered from a popup window.
   */
  private function popupResultPage(bool $success, string $provider, string $message, bool $emailVerified = false): \Illuminate\Http\Response
  {
    $payload = json_encode([
      'type'         => 'social_link_result',
      'success'      => $success,
      'provider'     => strtolower($provider),
      'message'      => $message,
      'emailVerified'=> $emailVerified,
    ]);

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Đang xử lý...</title>
<style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background:#f8fafc;color:#64748b;font-size:14px;}</style>
</head>
<body>
  <div style="text-align:center">
    <div style="font-size:32px;margin-bottom:8px">{$icon}</div>
    <p>{$message}</p>
    <p style="font-size:12px;color:#94a3b8">Cửa sổ này sẽ tự đóng...</p>
  </div>
  <script>
    try {
      window.opener && window.opener.postMessage({$payload}, '*');
    } catch(e) {}
    setTimeout(function(){ window.close(); }, 1200);
  <\/script>
</body>
</html>
HTML;

    // Replace placeholder icon
    $icon = $success ? '✅' : '❌';
    $html = str_replace('{$icon}', $icon, $html);

    return response($html, 200)->header('Content-Type', 'text/html');
  }
}
