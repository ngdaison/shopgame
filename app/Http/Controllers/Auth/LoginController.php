<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Account\VerifyActionController;

class LoginController extends Controller
{
  /*
   |--------------------------------------------------------------------------
   | Login Controller
   |--------------------------------------------------------------------------
   |
   | This controller handles authenticating users for the application and
   | redirecting them to your home screen. The controller uses a trait
   | to conveniently provide its functionality to your applications.
   |
   */

  /**
   * Where to redirect users after login.
   *
   * @var string
   */
  protected $redirectTo = RouteServiceProvider::HOME;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest')->except('logout');
  }

  public function showLoginForm()
  {
    return view('auth.login');
  }

  public function login(Request $request)
  {
    $this->validate($request, [
      'username' => 'required|string',
      'password' => 'required',
      'remember' => 'nullable|string',
      'g-recaptcha-response' => 'nullable|string',
    ]);

    if (\App\Models\SecuritySetting::get('security_captcha')['login'] ?? false) {
      $captcha = Helper::verifyCaptcha($request->input('g-recaptcha-response'), 'login_ssr');
      if (!$captcha) {
        return redirect()->back()->withInput($request->only('username', 'remember'))->withErrors([
          'g-recaptcha-response' => 'Xác thực không hợp lệ, vui lòng tải lại trang',
        ]);
      }
    }

    $phase1 = Auth::attempt(['username' => $request->username, 'password' => $request->password], $request->remember === 'on' ? true : false);
    $phase2 = null;

    if (!$phase1) {
      // trying with md5 encrypted password
      $phase2 = Auth::attempt(['username' => $request->username, 'password' => md5($request->password)], $request->remember === 'on' ? true : false);
    }

    if ($phase1 || $phase2) {

      $user = User::find(Auth::id());

      if ($user->status !== 'active') {
        Auth::logout();
        return redirect()->back()->withInput($request->only('username', 'remember'))->withErrors([
          'username' => 'Tài khoản của bạn đã bị khóa',
        ]);
      }

      if ($user->login_verify_email || $user->login_verify_google2fa) {
        $requiresEmail = $user->login_verify_email;
        $requires2fa = $user->login_verify_google2fa;

        // Priority: Google 2FA over Email OTP
        if ($requires2fa) {
          $requiresEmail = false;
        }

        Auth::logout();

        if ($requiresEmail) {
          // No manual OTP send here. VerifyActionController will handle it on page load.
        }

        // Explicitly handle session persistence after logout
        $verifyData = [
          'verify_user_id' => $user->id,
          'verify_remember' => $request->remember === 'on',
          'verify_email' => $requiresEmail,
          'verify_2fa' => $requires2fa,
        ];

        // Use global session helper to ensure we are writing to the fresh session
        VerifyActionController::resetVerification($user->id);
        session()->put($verifyData);
        session()->save();

        return redirect()->route('account.verify');
      }

      // if phase2 => update password
      if ($phase2) {
        $user->update([
          'password' => bcrypt($request->password),
        ]);
      }

      // Initialize post-login session and state
      $user->postLoginInit($request);

      // Notification
      \App\Models\Notification::create([
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'Đăng nhập thành công',
        'content' => 'Phát hiện đăng nhập mới vào lúc ' . now()->format('H:i d/m/Y') . '.',
        'icon' => 'fa fa-sign-in-alt ps-1',
        'is_read' => false
      ]);

      // if successful, then redirect to their intended location
      return redirect()->intended(route('home'));
    }

    // if unsuccessful, then redirect back to the login with the form data
    \App\Helpers\SecurityGuard::recordAttempt('login_ip', $request->ip());
    \App\Helpers\SecurityGuard::recordAttempt('login_acc', null, $request->username);

    return redirect()->back()->withInput($request->only('username', 'remember'))->withErrors([
      'username' => 'Thông tin đăng nhập không chính xác',
    ]);
  }

  public function logout(Request $request)
  {
    if (Auth::check()) {
      \Illuminate\Support\Facades\Cache::forget('user-is-online-' . Auth::id());
    }
    
    Auth::logout();
    
    // Invalidate the session completely
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }
}
