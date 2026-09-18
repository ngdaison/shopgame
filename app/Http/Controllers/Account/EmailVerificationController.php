<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EmailVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Prepare email verification - Send verification link
     */
    public function sendVerification(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("EmailVerificationController::sendVerification - Start", ['user_id' => Auth::id()]);
        $user = User::find(Auth::id());

        if (!$user->email) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Bạn chưa có email để xác thực']);
            }
            return redirect()->back()->with('error', 'Bạn chưa có email để xác thực');
        }

        if ($user->email_verified_at) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Email của bạn đã được xác thực']);
            }
            return redirect()->back()->with('error', 'Email của bạn đã được xác thực');
        }

        $throttleKey = 'email_verification_sent_' . $user->id;
        if (Cache::has($throttleKey)) {
            $seconds = Cache::get($throttleKey) - time();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Vui lòng chờ ' . $seconds . ' giây trước khi yêu cầu lại.']);
            }
            return redirect()->back()->with('error', 'Vui lòng chờ ' . $seconds . ' giây trước khi yêu cầu lại.');
        }

        // Generate signed URL
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
        ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        // Send email
        \Illuminate\Support\Facades\Log::info("EmailVerificationController::sendVerification - Attempting to send EmailTemplate", ['email' => $user->email]);
        $sent = Helper::sendEmailTemplate('link_verify_email', $user->email, [
            'title' => config('app.name'),
            'username' => $user->username ?? 'bạn',
            'verification_link' => $verificationUrl
        ]);

        \Illuminate\Support\Facades\Log::info("EmailVerificationController::sendVerification - Send Result", ['success' => $sent]);

        if ($sent) {
            Cache::put($throttleKey, time() + 60, now()->addSeconds(60));
            Helper::addHistory('Đã gửi yêu cầu xác thực email');
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Đã gửi email link xác thực thành công']);
            }
            return redirect()->back()->with('success', 'Đã gửi email link xác thực thành công');
        }

        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Không thể gửi email xác thực. Vui lòng thử lại sau.']);
        }
        return redirect()->back()->with('error', 'Không thể gửi email xác thực. Vui lòng thử lại sau.');
    }

    /**
     * Handle verification link click
     */
    public function verifyLink(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if (!hash_equals((string)$request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect()->route('account.profile.index')->with('error', 'Liên kết xác thực không hợp lệ.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.profile.index')->with('success', 'Email của bạn đã được xác thực từ trước.');
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
            Helper::addHistory('Xác thực email thành công qua liên kết');
        }

        return redirect()->route('account.profile.index')->with('success', 'Xác thực email thành công!');
    }

    /**
     * Verify email with OTP (Legacy/Compatibility)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $user = User::find(Auth::id());
        $otp = $request->input('otp');
        $cachedOtp = Cache::get('email_verify_otp_' . $user->id);

        if (!$cachedOtp || $otp != $cachedOtp) {
            return response()->json([
                'status' => 400,
                'message' => 'Mã OTP không chính xác hoặc đã hết hạn'
            ], 400);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        // Clear OTP from cache
        Cache::forget('email_verify_otp_' . $user->id);

        Helper::addHistory('Xác thực email thành công');

        return response()->json([
            'status' => 200,
            'message' => 'Xác thực email thành công!'
        ]);
    }
}
