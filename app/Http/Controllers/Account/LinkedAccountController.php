<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Helper;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class LinkedAccountController extends Controller
{
    /**
     * Redirect to social provider to link account
     */
    public function link(Request $request, string $provider)
    {
        $allowed = ['google', 'facebook', 'discord'];
        if (!in_array(strtolower($provider), $allowed)) {
            return redirect()->route('account.profile.index', ['tab' => 'linked-account'])
                ->with('error', 'Nhà cung cấp không được hỗ trợ.');
        }

        $config = getSocialConfig($provider);
        if (!$config) {
            return redirect()->route('account.profile.index', ['tab' => 'linked-account'])
                ->with('error', 'Đăng nhập bằng ' . ucfirst($provider) . ' chưa được cấu hình.');
        }

        $clientId = $config['client_key'] ?? $config['client_id'] ?? null;
        $clientSecret = $config['client_secret'] ?? null;

        if (!$clientId || !$clientSecret) {
            return redirect()->route('account.profile.index', ['tab' => 'linked-account'])
                ->with('error', ucfirst($provider) . ' chưa được cấu hình.');
        }

        config([
            'services.' . $provider . '.active' => true,
            'services.' . $provider . '.client_id' => $clientId,
            'services.' . $provider . '.client_secret' => $clientSecret,
            'services.' . $provider . '.redirect' => route('auth.social.callback', ['provider' => $provider]),
        ]);

        // Store the intent so the callback knows to link, not login
        session([
            'social_link_intent'  => true,
            'social_link_user_id' => auth()->id()
        ]);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Unlink a social provider
     */
    public function unlink(Request $request, string $provider)
    {
        $user = auth()->user();
        $allowed = ['google', 'facebook', 'discord'];

        if (!in_array(strtolower($provider), $allowed)) {
            return response()->json(['success' => false, 'message' => 'Nhà cung cấp không được hỗ trợ.'], 422);
        }

        if (!$user->hasSocialLinked($provider)) {
            return response()->json(['success' => false, 'message' => 'Tài khoản ' . ucfirst($provider) . ' chưa được liên kết.'], 422);
        }

        // Security: Must have password or at least one other social linked before unlinking
        $links = $user->social_links ?? [];
        $linkedCount = count(array_filter($links, fn($v) => !empty($v)));

        if (!$user->has_password && $linkedCount <= 1) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đặt mật khẩu trước khi hủy liên kết tài khoản này (đây là phương thức đăng nhập duy nhất).'], 422);
        }

        $user->unlinkSocial($provider);
        Helper::addHistory('Đã hủy liên kết tài khoản ' . ucfirst($provider));

        // Always return JSON (AJAX from profile page)
        return response()->json([
            'success' => true,
            'message' => 'Đã hủy liên kết tài khoản ' . ucfirst($provider) . ' thành công!'
        ]);
    }
}
