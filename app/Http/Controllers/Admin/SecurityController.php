<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SecurityGuard;
use App\Http\Controllers\Controller;
use App\Models\SecurityBan;
use App\Models\SecuritySetting;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index()
    {
        // Load settings or defaults
        $settings = SecuritySetting::all()->pluck('setting_value', 'setting_key')->toArray();
        
        // Default structure if not exists
        // SECTION A: Brute Force
        $bruteForceRules = $settings['security_bruteforce_rules'] ?? [];
        
        // SECTION B: Access Control
        $accessControl = $settings['security_access_control'] ?? [];
        
        // SECTION C: Other
        $otherSecurity = $settings['security_other'] ?? [];

        // SECTION D: Captcha
        $captcha = $settings['security_captcha'] ?? [];

        return view('admin.security.index', compact('bruteForceRules', 'accessControl', 'otherSecurity', 'captcha'));
    }

    public function store(Request $request)
    {
        // Save Sections
        // Manually grouping inputs from the form into JSON arrays
        
        $bruteForceData = $request->input('bruteforce', []);
        SecuritySetting::set('security_bruteforce_rules', $bruteForceData);

        $accessControlData = $request->input('access_control', []);
        SecuritySetting::set('security_access_control', $accessControlData);

        $otherData = $request->input('other', []);
        SecuritySetting::set('security_other', $otherData);

        $captchaData = $request->input('captcha', []);
        SecuritySetting::set('security_captcha', $captchaData);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật cài đặt bảo mật thành công.'
        ]);
    }

    public function indexBan(Request $request)
    {
        $bans = SecurityBan::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.security.block', compact('bans'));
    }

    public function storeBan(Request $request)
    {
        $request->validate([
            'type' => 'required|in:user,ip',
            'content' => 'required|string',
            'status' => 'required|in:ban,ban_1_day,ban_2_day,ban_3_day,ban_4_day',
        ]);

        $type = $request->type;
        $content = $request->content;
        $status = $request->status;
        $reason = $request->reason;

        if ($type === 'user') {
            SecurityGuard::banUser($content, $status, $reason);
            
            // Sync status to User
            $userLocked = \App\Models\User::where('username', $content)->first();
            if ($userLocked) {
                $userLocked->status = 'locked';
                $userLocked->save();
            }
        } else {
            SecurityGuard::banIp($content, $status, $reason);
        }

        return response()->json([
            'status' => true,
            'message' => 'Thêm Block thành công.'
        ]);
    }

    public function deleteBan(Request $request)
    {
        $request->validate(['id' => 'required|exists:security_bans,id']);
        SecurityBan::destroy($request->id);
        
        return response()->json([
            'status' => true,
            'message' => 'Xóa Block thành công.'
        ]);
    }
}
