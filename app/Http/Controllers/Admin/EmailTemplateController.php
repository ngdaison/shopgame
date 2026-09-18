<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $this->seedDefaults(); // Ensure all defaults exist
        $templates = EmailTemplate::all();
        $telegram = setting('telegram_config', []);
        $discord = setting('discord_config', []);
        return view('admin.template.index', compact('templates', 'telegram', 'discord'));
    }

    public function updateConfig(Request $request)
    {
        $type = $request->input('type');

        if ($type == 'telegram') {
            $data = $request->validate([
                'bot_token' => 'nullable|string',
                'chat_id_deposit' => 'nullable|string',
                'chat_id_order' => 'nullable|string',
            ]);
            setting(['telegram_config' => $data])->save();
        }
        elseif ($type == 'discord') {
            $data = $request->validate([
                'webhook_deposit' => 'nullable|url',
                'webhook_order' => 'nullable|url',
            ]);
            setting(['discord_config' => $data])->save();
        }

        return back()->with('success', 'Cập nhật cấu hình thành công!');
    }

    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return view('admin.template.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template = EmailTemplate::findOrFail($id);
        $template->update([
            'subject' => $request->subject,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.template.index')->with('success', 'Cập nhật mẫu mail thành công');
    }

    private function seedDefaults()
    {
        $defaults = [
            [
                'key' => 'order_success',
                'subject' => 'Thông tin đơn hàng - {title}',
                'content' => '<h1>Thông tin đơn hàng</h1><p>Xin chào {username},</p><p>Cảm ơn bạn đã mua hàng.</p>',
                'description' => 'Gửi khi khách hàng mua hàng thành công',
                'variables' => ['{domain}', '{title}', '{username}', '{product}', '{amount}', '{trans_id}', '{pay}', '{time}']
            ],
            [
                'key' => 'otp_verification',
                'subject' => 'Mã xác thực OTP - {title}',
                'content' => '<p>Mã OTP của bạn là: <strong>{otp}</strong></p>',
                'description' => 'Gửi mã OTP xác thực',
                'variables' => ['{domain}', '{title}', '{username}', '{otp}', '{time}']
            ],
            [
                'key' => 'welcome',
                'subject' => 'Chào mừng đến với {title}',
                'content' => '<p>Xin chào {username}, chào mừng bạn gia nhập cộng đồng của chúng tôi.</p>',
                'description' => 'Gửi khi đăng ký tài khoản thành công',
                'variables' => ['{domain}', '{title}', '{username}', '{time}']
            ],
            [
                'key' => 'reset_password',
                'subject' => 'Khôi phục mật khẩu - {title}',
                'content' => '<p>Xin chào {username},</p><p>Bạn vừa yêu cầu khôi phục mật khẩu. Vui lòng nhấn vào link bên dưới để đặt lại mật khẩu:</p><p><a href="{link}">Đặt lại mật khẩu</a></p>',
                'description' => 'Gửi khi người dùng yêu cầu đặt lại mật khẩu',
                'variables' => ['{domain}', '{title}', '{username}', '{link}', '{time}']
            ],
            [
                'key' => 'otp_verify_email',
                'subject' => '[{title}] Xác thực địa chỉ Email',
                'content' => '<p>Mã xác thực email của bạn là: <strong>{otp}</strong>. Mã có hiệu lực trong 10 phút.</p>',
                'description' => 'Gửi mã OTP để xác thực email mới',
                'variables' => ['{domain}', '{title}', '{username}', '{otp}', '{time}']
            ],
            [
                'key' => 'otp_profile_change',
                'subject' => '[{title}] Mã xác minh thay đổi thông tin',
                'content' => '<p>Mã xác minh của bạn là: <strong>{otp}</strong>. Vui lòng không cung cấp mã này cho bất kỳ ai.</p>',
                'description' => 'Gửi mã OTP khi thay đổi thông tin cá nhân',
                'variables' => ['{domain}', '{title}', '{username}', '{otp}', '{time}']
            ],
            [
                'key' => 'otp_login',
                'subject' => '[{title}] Mã xác minh OTP',
                'content' => '<p>Mã xác minh của bạn là: <strong>{otp}</strong>. Mã này có hiệu lực trong 10 phút.</p>',
                'description' => 'Gửi mã OTP khi đăng nhập 2FA',
                'variables' => ['{domain}', '{title}', '{username}', '{otp}', '{time}']
            ],
            [
                'key' => 'order_item_created',
                'subject' => 'Đơn hàng vật phẩm {code} của bạn đã được tạo',
                'content' => '<p>Xin chào, <strong>{username}</strong></p><p>Dịch vụ: <strong>{name}</strong></p><p>Đơn hàng: <strong>{code}</strong> của bạn đã được tạo thành công.</p><p>Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.</p><p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>',
                'description' => 'Gửi khi tạo đơn vật phẩm thành công',
                'variables' => ['{domain}', '{title}', '{username}', '{code}', '{name}', '{payment}', '{note}', '{time}']
            ],
            [
                'key' => 'link_verify_email',
                'subject' => 'Xác thực địa chỉ Email BẰNG LINK',
                'content' => '<p>Xin chào {username},</p><p>Vui lòng nhấn vào liên kết bên dưới để xác thực địa chỉ email của bạn:</p><p><a href="{verification_link}" style="display:inline-block;padding:10px 20px;background-color:#4CAF50;color:white;text-decoration:none;border-radius:5px;">Xác thực ngay</a></p><p>Hoặc sao chép liên kết này vào trình duyệt: {verification_link}</p><p>Liên kết này có hiệu lực trong 60 phút.</p>',
                'description' => 'Gửi liên kết xác thực email',
                'variables' => ['{domain}', '{title}', '{username}', '{verification_link}', '{time}']
            ]
        ];

        foreach ($defaults as $data) {
            EmailTemplate::firstOrCreate(
            ['key' => $data['key']],
                $data
            );
        }
    }
}
