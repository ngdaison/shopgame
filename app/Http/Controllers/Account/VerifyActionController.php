<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Log;

class VerifyActionController extends Controller
{
    public function __construct()
    {
    // Remove auth middleware to allow Login Verification (Guest)
    // We will manually check Auth for Action Verification
    }

    public function show()
    {
        // 1. Check for Action Verification (Auth + Cache)
        if (Auth::check()) {
            return $this->showActionVerification();
        }

        if (session('verify_user_id')) {
            return $this->showLoginVerification();
        }

        // 3. Check for Old Input (Retry after failed submit)
        if (old('payload')) {
            try {
                $data = \Illuminate\Support\Facades\Crypt::decrypt(old('payload'));
                if (isset($data['verify_user_id']) || isset($data['user_id'])) {
                    // Restore session for this request only
                    session()->now('verify_user_id', $data['user_id'] ?? $data['verify_user_id']);
                    session()->now('verify_remember', $data['remember'] ?? false);
                    session()->now('verify_email', $data['verify_email'] ?? false);
                    session()->now('verify_2fa', $data['verify_2fa'] ?? false);

                    return $this->showLoginVerification();
                }
            }
            catch (\Exception $e) {
            }
        }

        return abort(404);
    }

    private function showLoginVerification()
    {
        $verifyEmail = session('verify_email');
        $verify2FA = session('verify_2fa');
        $userId = session('verify_user_id');
        $remember = session('verify_remember');

        $verifyMethod = $verify2FA ? 'google2fa' : 'email';

        // Generate stateless payload for the form
        $payload = \Illuminate\Support\Facades\Crypt::encrypt([
            'user_id' => $userId,
            'remember' => $remember,
            'is_login' => true,
            'verify_email' => $verifyEmail,
            'verify_2fa' => $verify2FA
        ]);

        $pageTitle = 'Xác Thực Đăng Nhập';
        $description = 'Vui lòng nhập mã xác thực để tiếp tục đăng nhập.';

        // Automaticaly sending OTP during page load is removed to ensure the page loads instantly.
        // The OTP is now triggered by the client-side JavaScript once the page is fully loaded.

        $otpSentKey = 'otp_sent_' . $userId;
        $expiresAt = Cache::get($otpSentKey);
        $remaining = $expiresAt ? max(0, $expiresAt - now()->timestamp - 1) : 0;

        return view('account.verify-action', compact('verifyMethod', 'pageTitle', 'description', 'payload', 'remaining'));
    }

    private function showActionVerification()
    {
        $userId = Auth::id();

        if (!session('verify_allowed') && !Cache::has('verify_pending_changes_' . $userId) && !Cache::has('verify_is_email_' . $userId) && !Cache::has('verify_method_' . $userId)) {
            return $this->cancel();
        }

        $hasPendingChanges = Cache::has('verify_pending_changes_' . $userId) || session()->has('verify_pending_changes');
        $hasPendingEmail = Cache::has('verify_is_email_' . $userId) || session('verify_is_email');

        if (!$hasPendingChanges && !$hasPendingEmail) {
            return redirect()->route('account.profile.index')->with('error', 'Không tìm thấy thông tin cần xác thực.');
        }

        $pendingEmail = Cache::get('verify_pending_email_' . $userId);
        $verifyMethod = Cache::get('verify_method_' . $userId);
        if (!$verifyMethod)
            $verifyMethod = session('verify_method');

        $qrCodeImage = null;
        $secret = null;

        if ($verifyMethod === 'google2fa_setup') {
            $setupData = Cache::get('verify_2fa_setup_' . $userId);
            if ($setupData) {
                $qrCodeImage = $setupData['qr_code_image'];
                $secret = $setupData['secret'];
            }
        }

        $pendingEmail = Cache::get('verify_pending_email_' . $userId) ?? session('verify_pending_email');

        // Automaticaly sending OTP during page load is removed to ensure the page loads instantly.
        // The OTP is now triggered by the client-side JavaScript once the page is fully loaded.

        $pageTitle = 'Xác Thực Thay Đổi';
        $description = 'Vui lòng nhập mã xác thực để hoàn tất thay đổi thông tin.';

        $otpSentKey = 'otp_sent_' . $userId;
        $expiresAt = Cache::get($otpSentKey);
        $remaining = $expiresAt ? max(0, $expiresAt - now()->timestamp - 1) : 0;

        return view('account.verify-action', compact('verifyMethod', 'pageTitle', 'description', 'qrCodeImage', 'secret', 'remaining'));
    }

    public function resend()
    {
        $userId = Auth::id() ?? session('verify_user_id');
        if (!$userId) {
            return response()->json(['status' => 400, 'message' => 'Phiên xác thực không hợp lệ.'], 400);
        }

        $verifyMethod = Cache::get('verify_method_' . $userId);
        $isLoginVerification = !Auth::check() && session()->has('verify_user_id');

        // Session Fallback
        if (!$verifyMethod) {
            $verifyMethod = session('verify_method');
        }

        if (!$verifyMethod && $isLoginVerification) {
            $verifyMethod = session('verify_2fa') ? 'google2fa' : 'email';
        }

        // Cache Intent Fallback
        if (!$verifyMethod) {
            $pending = Cache::get('verify_pending_changes_' . $userId) ?? session('verify_pending_changes');
            if ($pending && isset($pending['intent']) && $pending['intent'] === '2fa_setup_flow') {
                $verifyMethod = 'email';
            }
        }

        if ($verifyMethod === 'email' || Cache::has('verify_is_email_' . $userId) || session('verify_is_email') || ($isLoginVerification && session('verify_email'))) {
            $pendingEmail = Cache::get('verify_pending_email_' . $userId) ?? session('verify_pending_email');
            $isEmailVerification = Cache::has('verify_is_email_' . $userId) || session('verify_is_email') || $isLoginVerification;

            // The throttle check is already done inside sendActionOtp

            $otpSentKey = 'otp_sent_' . $userId;
            $expiresAt = Cache::get($otpSentKey);
            if ($expiresAt) {
                $remaining = $expiresAt - now()->timestamp;
                if ($remaining > 0) {
                    return response()->json([
                        'status' => 429, 
                        'message' => "Vui lòng chờ {$remaining} giây trước khi yêu cầu mã mới.",
                        'remaining' => $remaining
                    ], 429);
                }
            }

            $sent = $this->sendActionOtp($userId, $pendingEmail, $isEmailVerification);

            if (!$sent) {
                return response()->json(['status' => 500, 'message' => 'Gửi OTP thất bại. Vui lòng thử lại hoặc liên hệ Admin kiểm tra cấu hình Mail.'], 500);
            }

            return response()->json(['status' => 200, 'message' => 'Đã gửi mã OTP mới thành công.']);
        }

        return response()->json(['status' => 400, 'message' => 'Phương thức xác thực không hỗ trợ gửi lại (' . ($verifyMethod ?? 'NULL') . ') - UID: ' . ($userId ?? 'NULL')], 400);
    }

    private function sendActionOtp($userId, $pendingEmail, $isEmailVerification, $skipCooldown = false)
    {
        $user = User::find($userId);
        $otpSentKey = 'otp_sent_' . $userId;

        // Skip check if it's an auto-send that doesn't care about cooldown
        if ($skipCooldown || !Cache::has($otpSentKey)) {
            $isLogin = !Auth::check() && (string)session('verify_user_id') === (string)$userId;
            $cacheKey = $isLogin ? 'login_otp_' . $userId : 'otp_' . $userId;
            
            // Only generate new OTP if one doesn't exist OR we're forcing a resend
            $otp = rand(100000, 999999);
            Cache::put($cacheKey, $otp, now()->addMinutes(10));

            if (!$skipCooldown) {
                Cache::put($otpSentKey, now()->addSeconds(60)->timestamp, now()->addSeconds(60));
            }

            $targetEmail = $pendingEmail ?? $user->email;

            try {
                $templateKey = $isEmailVerification ? 'otp_verify_email' : 'otp_profile_change';
                Helper::sendEmailTemplate($templateKey, $targetEmail, [
                    'title' => config('app.name'),
                    'username' => $user->username,
                    'otp' => $otp,
                    'time' => now()->format('H:i d/m/Y')
                ]);
                return true;
            }
            catch (\Exception $e) {
                if (!$skipCooldown) Cache::forget($otpSentKey);
                Log::error('OTP Action Error: ' . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    public function cancel()
    {
        session()->forget(['verify_user_id', 'verify_remember', 'verify_email', 'verify_2fa', 'verify_method', 'verify_is_email', 'verify_pending_email', 'verify_pending_changes', 'verify_allowed']);

        if (Auth::check()) {
            $userId = Auth::id();
            $this->clearActionCache($userId);
            return redirect()->route('account.profile.index')->with('success', 'Đã hủy yêu cầu xác thực.');
        }

        session()->forget(['verify_user_id', 'verify_remember', 'verify_email', 'verify_2fa', 'verify_method', 'verify_is_email', 'verify_pending_email', 'verify_pending_changes']);
        return redirect()->route('login');
    }

    public function verify(Request $request)
    {
        // 1. Check for Login Payload
        if ($request->has('payload')) {
            try {
                $data = \Illuminate\Support\Facades\Crypt::decrypt($request->input('payload'));
                if (isset($data['is_login']) && $data['is_login']) {
                    return $this->verifyLogin($request, $data);
                }
            }
            catch (\Exception $e) {
                return redirect()->route('login')->with('error', 'Phiên xác thực không hợp lệ or đã hết hạn.');
            }
        }

        // 2. Action Verification
        if (Auth::check()) {
            return $this->verifyAction($request);
        }

        return abort(404);
    }

    private function verifyLogin(Request $request, $data)
    {
        $userId = $data['user_id'];
        $user = User::findOrFail($userId);
        $otp = $request->input('otp');

        if (!empty($data['verify_2fa'])) {
            $google2fa = new Google2FA();
            if (!$otp || !$google2fa->verifyKey($user->google2fa_secret, $otp)) {
                if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Mã Google 2FA không chính xác.'], 422);
                return redirect()->back()->withInput()->withErrors(['otp' => 'Mã Google 2FA không chính xác.']);
            }
        }
        elseif (!empty($data['verify_email'])) {
            $cachedOtp = Cache::get('login_otp_' . $user->id);
            if (!$otp || $otp != $cachedOtp) {
                if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Mã OTP Email không chính xác hoặc đã hết hạn.'], 422);
                return redirect()->back()->withInput()->withErrors(['otp' => 'Mã OTP Email không chính xác hoặc đã hết hạn.']);
            }
        }

        Auth::login($user, $data['remember'] ?? false);
        
        // Regenerate session to prevent session fixation and ensure fresh state
        $request->session()->regenerate();

        $user->postLoginInit($request);

        Cache::forget('login_otp_' . $user->id);
        session()->forget(['verify_user_id', 'verify_remember', 'verify_email', 'verify_2fa']);

        // Explicitly save session before redirect to home to ensure persistence
        session()->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'redirect_url' => route('home')
            ]);
        }

        return redirect()->intended(route('home'));
    }

    private function verifyAction(Request $request)
    {
        $userId = Auth::id();
        $user = User::find($userId);

        $verifyMethod = Cache::get('verify_method_' . $userId);
        if (!$verifyMethod)
            $verifyMethod = session('verify_method');

        $otp = $request->input('otp');

        // Special case for 2FA Setup
        if ($verifyMethod === 'google2fa_setup') {
            $setupData = Cache::get('verify_2fa_setup_' . $userId);
            if (!$setupData) {
                return redirect()->route('account.profile.index')->with('error', 'Phiên thiết lập đã hết hạn. Vui lòng thử lại.');
            }

            $google2fa = new Google2FA();
            if (!$otp || !$google2fa->verifyKey($setupData['secret'], $otp)) {
                session()->put('verify_allowed', true);
                if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Mã xác minh không chính xác.'], 422);
                return redirect()->back()->withErrors(['otp' => 'Mã xác minh không chính xác.']);
            }

            // Enable 2FA
            $user->update([
                'google2fa_secret' => $setupData['secret'],
                'login_verify_google2fa' => true
            ]);

            $this->clearActionCache($userId);
            Cache::forget('verify_2fa_setup_' . $userId);

            Helper::addHistory('Kích hoạt Google Authenticator thành công');
            return redirect()->route('account.profile.index')->with('success', 'Đã kích hoạt Google Authenticator thành công!');
        }

        if ($verifyMethod === 'google2fa') {
            $google2fa = new Google2FA();
            if (!$otp || !$google2fa->verifyKey($user->google2fa_secret, $otp)) {
                session()->put('verify_allowed', true);
                if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Mã Google 2FA không chính xác.'], 422);
                return redirect()->back()->withErrors(['otp' => 'Mã Google 2FA không chính xác.']);
            }
        }
        elseif ($verifyMethod === 'email') {
            $cached = Cache::get('otp_' . $userId) ?? Cache::get('action_otp_' . $userId);
            if (!$otp || $otp != $cached) {
                session()->put('verify_allowed', true);
                if ($request->ajax()) return response()->json(['success' => false, 'message' => 'Mã OTP Email không chính xác hoặc đã hết hạn.'], 422);
                return redirect()->back()->withErrors(['otp' => 'Mã OTP Email không chính xác hoặc đã hết hạn.']);
            }
            Cache::forget('otp_' . $userId);
            Cache::forget('action_otp_' . $userId);

            // Check for bridge intent (Email -> 2FA Setup)
            $intent = Cache::get('verify_intent_' . $userId) ?? session('verify_intent');
            if ($intent === 'enable_2fa_setup') {
                Cache::forget('verify_intent_' . $userId);
                session()->forget('verify_intent');
                Cache::forget('otp_sent_' . $userId); // Force allow fresh OTP for setup
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Xác thực thành công!',
                        'redirect_url' => route('account.security.2fa.setup')
                    ]);
                }
                return redirect()->route('account.security.2fa.setup');
            }
        }

        // ... Existing Action Logic ...
        // Handle email verification
        if (Cache::has('verify_is_email_' . $userId) || session('verify_is_email')) {
            $user->email_verified_at = now();
            $user->email_changed_at = now();
            $user->save();

            $this->clearActionCache($userId);
            session()->forget(['verify_method', 'verify_is_email', 'verify_pending_email']);
            Helper::addHistory('Xác thực email thành công');

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xác thực email thành công!',
                    'redirect_url' => route('account.profile.index')
                ]);
            }

            return redirect()->route('account.profile.index')->with('success', 'Thành công|Xác thực email thành công!');
        }

        // Handle profile changes
        $changes = Cache::get('verify_pending_changes_' . $userId) ?? session('verify_pending_changes');
        if ($changes) {
            // === Handle change_password intent ===
            if (isset($changes['intent']) && $changes['intent'] === 'change_password') {
                // OTP verified above for all email methods in the main flow

                if (isset($changes['new_password'])) {
                    $user->password = $changes['new_password']; // Already hashed
                    $user->has_password = $changes['has_password'] ?? true;
                    $user->save();
                }

                $this->clearActionCache($userId);
                Cache::forget('verify_intent_' . $userId);
                session()->forget(['verify_pending_changes', 'verify_method', 'verify_intent']);
                Helper::addHistory('Thay đổi mật khẩu thành công qua xác thực');

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Cập nhật mật khẩu thành công!',
                        'redirect_url' => route('account.profile.index', ['tab' => 'change-password'])
                    ]);
                }

                return redirect()->route('account.profile.index', ['tab' => 'change-password'])
                    ->with('success', 'Cập nhật mật khẩu thành công!');
            }

            // Handle keypass actions
            if (isset($changes['username'])) {
                $user->username = $changes['username'];
                $user->username_changed_at = now();
            }
            if (isset($changes['email'])) {
                $user->email = $changes['email'];
                $user->email_verified_at = null;
            }
            if (isset($changes['phone'])) {
                $user->phone = $changes['phone'];
                $user->phone_changed_at = now();
            }
            // Handling disabling 2FA
            if (isset($changes['login_verify_google2fa']) && $changes['login_verify_google2fa'] === false) {
                $user->login_verify_google2fa = false;
                $user->google2fa_secret = null;
            }

            $user->save();
            $this->clearActionCache($userId);
            session()->forget('verify_pending_changes');
            Helper::addHistory('Cập nhật thông tin tài khoản thành công qua xác thực');

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công!',
                    'redirect_url' => route('account.profile.index')
                ]);
            }

            return redirect()->route('account.profile.index')->with('success', 'Cập nhật thông tin thành công!');
        }

        return redirect()->route('account.profile.index')->with('error', 'Trạng thái xác thực không hợp lệ. Vui lòng thử lại.');
    }

    public static function resetVerification($userId)
    {
        // Cache
        Cache::forget('verify_pending_changes_' . $userId);
        Cache::forget('verify_method_' . $userId);
        Cache::forget('verify_pending_email_' . $userId);
        Cache::forget('verify_is_email_' . $userId);
        Cache::forget('verify_intent_' . $userId);
        Cache::forget('otp_' . $userId);
        Cache::forget('otp_sent_' . $userId);
        Cache::forget('action_otp_' . $userId);
        
        // Session
        session()->forget([
            'verify_user_id', 'verify_remember', 'verify_email', 'verify_2fa', 
            'verify_method', 'verify_is_email', 'verify_pending_email', 
            'verify_pending_changes', 'verify_allowed', 'verify_intent'
        ]);
    }

    private function clearActionCache($userId)
    {
        self::resetVerification($userId);
    }
}
