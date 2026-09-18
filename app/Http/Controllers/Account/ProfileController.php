<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Account\VerifyActionController;

class ProfileController extends Controller
{
    public function updateWebRtcIp(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);

        $user = auth()->user();

        // Update only if IP is different from current WebRTC IP
        if ($user->webrtc_ip !== $request->ip) {
            $user->update([
                'webrtc_ip' => $request->ip,
                'webrtc_updated_at' => now()
            ]);
        }

        return response()->json(['status' => true]);
    }

    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn hoặc tài khoản không tồn tại.');
        }

        return view('account.profile.index', [
            'pageTitle' => 'Thông tin tài khoản',
        ], compact('user'));
    }

    public function transactions()
    {
        $user = auth()->user();

        if (!$user) {
            auth()->logout();
            return redirect()->route('login');
        }

        $stats = [
            'balance' => Helper::formatCurrency($user->balance),
            'total_spent' => Helper::formatCurrency($user->total_deposit - $user->balance),
            'total_deposit' => Helper::formatCurrency($user->total_deposit),
            'deposit_in_month' => Helper::formatCurrency(Transaction::where('user_id', $user->id)->where('type', 'deposit')->whereMonth('created_at', date('m'))->sum('amount')),
        ];

        return view('account.transactions.index', [
            'pageTitle' => 'Lịch Sử Giao Dịch',
        ], compact('stats'));
    }

    public function updatePassword(Request $request)
    {
        if (env('APP_DEMO', false)) {
            return redirect()->back()->with('error', 'Chức năng này không khả dụng trong chế độ demo');
        }

        $user = auth()->user();

        if (!$user) {
            auth()->logout();
            return redirect()->route('login');
        }

        $hasPassword = $user->has_password ?? true;

        // Build validation rules based on whether user has a password
        $rules = [
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6',
        ];

        if ($hasPassword) {
            $rules['old_password'] = 'required|string|min:6';
        }

        $payload = $request->validate($rules);

        // Verify old password for users who have one
        if ($hasPassword) {
            if (!password_verify($payload['old_password'], $user->password)) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Mật khẩu cũ không chính xác']);
                }
                return redirect()->back()->withErrors(['old_password' => 'Mật khẩu cũ không chính xác']);
            }
        }

        if ($payload['new_password'] !== $payload['confirm_password']) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Mật khẩu xác nhận không chính xác']);
            }
            return redirect()->back()->withErrors(['confirm_password' => 'Mật khẩu xác nhận không chính xác']);
        }

        // Determine verification method based on user security settings
        $verifyMethod = null;
        if ($user->login_verify_google2fa) {
            $verifyMethod = 'google2fa';
        } elseif ($user->login_verify_email && $user->email && $user->email_verified_at) {
            $verifyMethod = 'email';
        }

        if ($verifyMethod) {
            // Store pending password change in cache
            $userId = $user->id;
            VerifyActionController::resetVerification($userId);
            
            $pendingChanges = [
                'new_password' => bcrypt($payload['new_password']),
                'has_password' => true,
                'intent' => 'change_password',
            ];

            \Illuminate\Support\Facades\Cache::put('verify_pending_changes_' . $userId, $pendingChanges, now()->addMinutes(30));
            \Illuminate\Support\Facades\Cache::put('verify_method_' . $userId, $verifyMethod, now()->addMinutes(30));
            \Illuminate\Support\Facades\Cache::put('verify_intent_' . $userId, 'change_password', now()->addMinutes(30));

            session()->put('verify_pending_changes', $pendingChanges);
            session()->put('verify_method', $verifyMethod);
            session()->put('verify_intent', 'change_password');
            session()->put('verify_allowed', true);


            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vui lòng xác minh hành động này.',
                    'redirect_url' => route('account.verify')
                ]);
            }
            return redirect()->route('account.verify');
        }

        // No verification needed - apply directly
        $user->password = bcrypt($payload['new_password']);
        $user->has_password = true;
        $user->save();

        Helper::addHistory('Thay đổi mật khẩu thành công');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Cập nhật mật khẩu thành công']);
        }

        return redirect()->back()->with('success', 'Cập nhật mật khẩu thành công');
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            auth()->logout();
            return redirect()->route('login');
        }
        $updateType = $request->input('update_type', 'all'); // 'personal_info', 'security_settings', or 'all'

        $validate = [];

        // Validation Layout
        if ($updateType == 'personal_info' || $updateType == 'all') {
            $validate = array_merge($validate, [
                'full_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:male,female,lesbian,gay,bisexual,transgender',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            ]);

            if ($request->has('username') && $request->username !== $user->username) {
                $validate['username'] = 'required|string|min:4|unique:users,username,' . $user->id;
            }
            if ($request->has('email') && $request->email !== $user->email) {
                $validate['email'] = 'required|email|unique:users,email,' . $user->id;
            }
        }

        if ($updateType == 'security_settings' || $updateType == 'all') {
            $validate = array_merge($validate, [
                'login_verify_email' => 'nullable|boolean',
                'login_verify_google2fa' => 'nullable|boolean',
                'secure_order_view' => 'nullable|boolean',
                'notify_login_success' => 'nullable|boolean',
            ]);
        }

        $data = $request->validate($validate);

        $pendingChanges = [];
        $requiresVerification = false;
        $verifyMethod = null;

        // --- HANDLE PERSONAL INFO ---
        if ($updateType == 'personal_info' || $updateType == 'all') {
            // Avatar
            if ($request->filled('avatar_code')) {
                $avatar_code = $request->input('avatar_code');
                // Basic check for data URI format
                if (str_starts_with($avatar_code, 'data:image/')) {
                    $user->avatar = $avatar_code;
                }
                else {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Dữ liệu ảnh không hợp lệ (Base64 expected)']);
                    }
                    return redirect()->back()->with('error', 'Dữ liệu ảnh không hợp lệ (Base64 expected)');
                }
            }
            elseif ($request->hasFile('avatar')) {
                try {
                    $file = $request->file('avatar');
                    $base64Data = base64_encode(file_get_contents($file->getRealPath()));
                    $mimeType = $file->getMimeType();
                    $user->avatar = 'data:' . $mimeType . ';base64,' . $base64Data;
                }
                catch (\Exception $e) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Lỗi chuyển đổi ảnh sang base64: ' . $e->getMessage()]);
                    }
                    return redirect()->back()->with('error', 'Lỗi chuyển đổi ảnh sang base64: ' . $e->getMessage());
                }
            }

            // Fullname
            if (array_key_exists('full_name', $data)) {
                $user->full_name = $data['full_name'];
            }

            // Gender
            if (array_key_exists('gender', $data)) {
                $user->gender = $data['gender'];
                if (empty($user->gender))
                    $user->gender = 'gay';
            }

            // Sensitive: Email
            if (isset($data['email']) && $data['email'] !== $user->email) {
                if (in_array($user->register_by, ['GOOGLE', 'FACEBOOK', 'DISCORD'])) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Tài khoản đăng ký qua Mạng xã hội không thể thay đổi Email.']);
                    }
                    return redirect()->back()->with('error', 'Tài khoản đăng ký qua Mạng xã hội không thể thay đổi Email.');
                }

                if (empty($user->email) || !$user->email_verified_at) {
                    $user->email = $data['email'];
                    $user->email_verified_at = null;
                }
                else {
                    // Verified Email Change
                    if ($user->email_changed_at && \Carbon\Carbon::parse($user->email_changed_at)->addMonths(6)->isFuture()) {
                        $nextDate = \Carbon\Carbon::parse($user->email_changed_at)->addMonths(6)->format('d/m/Y');
                        if ($request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể đổi Email 1 lần mỗi 6 tháng. Vui lòng đợi đến ngày ' . $nextDate]);
                        }
                        return redirect()->back()->with('error', 'Bạn chỉ có thể đổi Email 1 lần mỗi 6 tháng. Vui lòng đợi đến ngày ' . $nextDate);
                    }
                    $pendingChanges['email'] = $data['email'];
                    $requiresVerification = true;
                    if ($verifyMethod !== 'google2fa')
                        $verifyMethod = 'email';
                }
            }

            // Sensitive: Username & Phone
            $usernameChanged = (isset($data['username']) && $data['username'] !== $user->username);
            $phoneChanged = (array_key_exists('phone', $data) && $data['phone'] != $user->phone);

            if ($usernameChanged || $phoneChanged) {
                if ($usernameChanged) {
                    if ($user->username_changed_at && \Carbon\Carbon::parse($user->username_changed_at)->addMonth()->isFuture()) {
                        $nextDate = \Carbon\Carbon::parse($user->username_changed_at)->addMonth()->format('d/m/Y');
                        if ($request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể đổi tên đăng nhập 1 lần mỗi tháng. Vui lòng đợi đến ngày ' . $nextDate]);
                        }
                        return redirect()->back()->with('error', 'Bạn chỉ có thể đổi tên đăng nhập 1 lần mỗi tháng. Vui lòng đợi đến ngày ' . $nextDate);
                    }
                    $pendingChanges['username'] = $data['username'];
                }

                if ($phoneChanged) {
                    if ($user->phone_changed_at && \Carbon\Carbon::parse($user->phone_changed_at)->addMonths(6)->isFuture()) {
                        $nextDate = \Carbon\Carbon::parse($user->phone_changed_at)->addMonths(6)->format('d/m/Y');
                        if ($request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể đổi Số điện thoại 1 lần mỗi 6 tháng. Vui lòng đợi đến ngày ' . $nextDate]);
                        }
                        return redirect()->back()->with('error', 'Bạn chỉ có thể đổi Số điện thoại 1 lần mỗi 6 tháng. Vui lòng đợi đến ngày ' . $nextDate);
                    }
                    $pendingChanges['phone'] = $data['phone'];
                }

                if (!$requiresVerification) {
                    if ($user->login_verify_google2fa) {
                        $verifyMethod = 'google2fa';
                    }
                    else {
                        $verifyMethod = 'email';
                    }
                    $requiresVerification = true;
                }
            }
        }


        // --- HANDLE SECURITY SETTINGS ---
        if ($updateType == 'security_settings' || $updateType == 'all') {
            // SMTP Check
            if (($request->has('login_verify_email') || $request->has('notify_login_success')) && !Helper::isSmtpConfigured()) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Vui lòng cấu hình SMTP Mailer trước khi bật các tính năng bảo mật qua Email.']);
                }
                return redirect()->back()->with('error', 'Vui lòng cấu hình SMTP Mailer trước khi bật các tính năng bảo mật qua Email.');
            }

            $user->login_verify_email = $request->has('login_verify_email');
            // login_verify_google2fa is handled separately (Enable via API, Disable via Verification)
            // $user->login_verify_google2fa = $request->has('login_verify_google2fa'); 
            $user->secure_order_view = $request->has('secure_order_view');
            $user->notify_login_success = $request->has('notify_login_success');

            // Check if Disabling 2FA
            // Only check if we are in security settings context (checkbox existence implies intent)
            if ($user->login_verify_google2fa && !$request->has('login_verify_google2fa')) {
                $requiresVerification = true;
                $verifyMethod = 'google2fa';
                $pendingChanges['login_verify_google2fa'] = false;
            }

            // Enabling 2FA
            if ($request->has('login_verify_google2fa') && !$user->login_verify_google2fa) {
                if ($user->email_verified_at) {
                    $user->save(); // Save any parallel changes

                    $userId = $user->id;
                    VerifyActionController::resetVerification($userId);

                    Cache::put('verify_method_' . $userId, 'email', now()->addMinutes(10));

                    // Fix: Add pending changes to satisfy VerifyActionController validation
                    Cache::put('verify_pending_changes_' . $userId, ['intent' => '2fa_setup_flow'], now()->addMinutes(10));
                    Cache::put('verify_intent_' . $userId, 'enable_2fa_setup', now()->addMinutes(10));

                    // Use Session for robustness
                    session()->put('verify_method', 'email');
                    session()->put('verify_pending_changes', ['intent' => '2fa_setup_flow']);
                    session()->put('verify_intent', 'enable_2fa_setup');
                    session()->put('verify_allowed', true);

                    if ($request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Vui lòng xác minh hành động này.',
                            'redirect_url' => route('account.verify')
                        ]);
                    }
                    return redirect()->route('account.verify');
                }

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Vui lòng thực hiện xác minh để bật 2FA.',
                        'redirect_url' => route('account.security.2fa.setup')
                    ]);
                }
                return redirect()->route('account.security.2fa.setup');
            }
        }


        if ($requiresVerification) {
            $user->save(); // Save non-sensitive changes

            $userId = $user->id;
            VerifyActionController::resetVerification($userId);

            Cache::put('verify_pending_changes_' . $userId, $pendingChanges, now()->addMinutes(30));
            Cache::put('verify_method_' . $userId, $verifyMethod, now()->addMinutes(30));

            session()->put('verify_pending_changes', $pendingChanges);
            session()->put('verify_method', $verifyMethod);
            session()->put('verify_allowed', true);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vui lòng xác minh hành động này.',
                    'redirect_url' => route('account.verify')
                ]);
            }
            return redirect()->route('account.verify');
        }

        $user->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);
        }
        return redirect()->back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function addBank(Request $request)
    {
        $payload = $request->validate([
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $bankInfo = Helper::getListBank($payload['bank_code']);

        $user = auth()->user();
        if (!$user) {
            auth()->logout();
            return redirect()->route('login');
        }

        $user->banks()->create([
            'bank_code' => $payload['bank_code'],
            'bank_name' => $bankInfo['shortName'] ?? $payload['bank_code'],
            'account_number' => $payload['account_number'],
            'account_name' => $payload['account_name'],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Thêm thẻ ngân hàng thành công!'
            ]);
        }

        return redirect()->back()->with('success', 'Thêm thẻ ngân hàng thành công!');
    }

    public function deleteBank($id)
    {
        $user = auth()->user();
        if (!$user) {
            auth()->logout();
            return redirect()->route('login');
        }
        $bank = $user->banks()->findOrFail($id);
        $bank->delete();

        return redirect()->back()->with('success', 'Xóa thẻ ngân hàng thành công!');
    }

    public function toggleBankStatus($id)
    {
        $user = auth()->user();
        if (!$user) {
            auth()->logout();
            return redirect()->route('login');
        }
        $bank = $user->banks()->findOrFail($id);
        $bank->status = !$bank->status;
        $bank->save();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $bank->status,
                'message' => 'Cập nhật trạng thái ngân hàng thành công!'
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái ngân hàng thành công!');
    }
}