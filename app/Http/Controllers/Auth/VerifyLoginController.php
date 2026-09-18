<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;

class VerifyLoginController extends Controller
{
    public function show()
    {
        if (!session('verify_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.verify-login');
    }

    public function verify(Request $request)
    {
        $userId = session('verify_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        if (session('verify_email')) {
            $otp = $request->input('email_otp');
            $cachedOtp = Cache::get('login_otp_' . $user->id);
            if (!$otp || $otp != $cachedOtp) {
                \App\Helpers\SecurityGuard::recordAttempt('otp', $request->ip());
                return redirect()->back()->withErrors(['email_otp' => 'Mã OTP Email không chính xác hoặc đã hết hạn.']);
            }
        }

        if (session('verify_2fa')) {
            $code = $request->input('google_2fa');
            if (!$code) {
                return redirect()->back()->withErrors(['google_2fa' => 'Vui lòng nhập mã 2FA.']);
            }
            $google2fa = new Google2FA();
            if (!$google2fa->verifyKey($user->google2fa_secret, $code)) {
                \App\Helpers\SecurityGuard::recordAttempt('2fa', $request->ip());
                return redirect()->back()->withErrors(['google_2fa' => 'Mã 2FA không chính xác.']);
            }
        }

        // Verification successful
        Auth::login($user, session('verify_remember', false));
        
        // Regenerate session to prevent session fixation and ensure fresh state
        $request->session()->regenerate();

        // Initialize session and user state (fixes logout issue on /admin)
        $user->postLoginInit($request);

        // Clear session
        session()->forget(['verify_user_id', 'verify_remember', 'verify_email', 'verify_2fa']);
        Cache::forget('login_otp_' . $user->id);

        // Explicitly save session before redirect to home to ensure persistence
        session()->save();

        return redirect()->intended(route('home'));
    }
}
