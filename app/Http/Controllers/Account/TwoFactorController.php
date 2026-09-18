<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Helper;
use App\Http\Controllers\Account\VerifyActionController;

class TwoFactorController extends Controller
{
    public function setup()
    {
        $user = auth()->user();
        if (!$user) {
            auth()->logout();
            return redirect()->route('login');
        }

        VerifyActionController::resetVerification($user->id);

        $google2fa = new Google2FA();

        // Always generate a new temporary secret for setup
        $secret = $google2fa->generateSecretKey(16);
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        
        // Use a public API for QR code generation
        $qrCodeImage = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrCodeUrl);

        // Cache the setup data
        Cache::put('verify_pending_changes_' . $user->id, ['login_verify_google2fa' => true], now()->addMinutes(10)); // Mark as pending change for consistency
        Cache::put('verify_2fa_setup_' . $user->id, [
            'secret' => $secret,
            'qr_code_image' => $qrCodeImage,
            'qr_code_url' => $qrCodeUrl
        ], now()->addMinutes(10));
        
        Cache::put('verify_method_' . $user->id, 'google2fa_setup', now()->addMinutes(10));
        session()->put('verify_method', 'google2fa_setup');

        return redirect()->route('account.verify');
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();
        if (!$user) {
            auth()->logout();
            return response()->json(['status' => 401, 'message' => 'Phiên đăng nhập hết hạn.'], 401);
        }
        $secret = session('temp_2fa_secret');
        
        // If enabling from an existing setup (not first time)
        if (!$secret && $user->google2fa_secret) {
            $secret = $user->google2fa_secret;
        }

        if (!$secret) {
            return response()->json(['status' => 400, 'message' => 'Không tìm thấy mã bí mật. Vui lòng bấm "Thiết lập" lại.'], 400);
        }

        try {
            $google2fa = new Google2FA();
            // Increase window to 8 (4 minutes) to handle clock drift
            $valid = $google2fa->verifyKey($secret, $request->code, 8);
            
            if ($valid) {
                $user->update([
                    'google2fa_secret' => $secret,
                    'login_verify_google2fa' => true
                ]);
                session()->forget('temp_2fa_secret');
                session()->flash('success', 'Thành công|Đã kích hoạt Google Authenticator thành công.');
                return response()->json(['status' => 200, 'message' => 'Đã kích hoạt Google Authenticator thành công.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 400, 'message' => 'Mã xác minh không chính xác.'], 400);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();
        if (!$user) {
           auth()->logout();
           return response()->json(['status' => 401, 'message' => 'Phiên đăng nhập hết hạn.'], 401);
        }
        $secret = $user->google2fa_secret;

        if (!$secret) {
            return response()->json(['status' => 400, 'message' => 'Bạn chưa kích hoạt 2FA.'], 400);
        }

        try {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($secret, $request->code, 8); // 8 windows = 4 minutes

            if ($valid) {
                $user->update([
                    'login_verify_google2fa' => false,
                    'google2fa_secret' => null 
                ]);
        
                session()->flash('success', 'Thành công|Đã hủy kích hoạt Google Authenticator.');
                return response()->json(['status' => 200, 'message' => 'Đã hủy kích hoạt Google Authenticator.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 400, 'message' => 'Mã xác minh không chính xác.'], 400);
    }

    public function sendOtp(Request $request)
    {
        if (!Helper::isSmtpConfigured()) {
            return response()->json(['status' => 400, 'message' => 'Vui lòng cấu hình SMTP Mailer trước khi sử dụng tính năng này.'], 400);
        }

        $user = auth()->user();
        if (!$user) {
           auth()->logout();
           return response()->json(['status' => 401, 'message' => 'Phiên đăng nhập hết hạn.'], 401);
        }
        $otp = rand(100000, 999999);
        
        Cache::put('otp_' . $user->id, $otp, now()->addMinutes(10));

        try {
            Helper::sendEmailTemplate('otp_login', $user->email, [
                'title' => config('app.name'),
                'username' => $user->username,
                'otp' => $otp,
                'time' => now()->format('H:i d/m/Y')
            ]);
            return response()->json(['status' => 200, 'message' => 'Mã OTP đã được gửi đến email của bạn.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'message' => 'Không thể gửi email. Vui lòng thử lại sau.'], 400);
        }
    }
}
