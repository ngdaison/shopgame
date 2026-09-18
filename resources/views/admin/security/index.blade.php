@extends('admin.layouts.master')
@section('title', 'Cài đặt Bảo mật')
@section('content')

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">Cài đặt Bảo mật</div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.security.update') }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            
            <h5 class="mb-3 text-primary"><i class="fa fa-shield"></i> Bảo vệ chống Brute Force</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 40%">Quy tắc</th>
                            <th style="width: 10%">Bật</th>
                            <th style="width: 15%">Số lần tối đa</th>
                            <th style="width: 15%">Cửa sổ (Phút)</th>
                            <th style="width: 20%">Hành động Chặn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rulesA = [
                                'login_ip' => 'Khóa IP nếu sai mật khẩu quá nhiều lần',
                                'login_acc' => 'Khóa tài khoản nếu sai mật khẩu quá nhiều lần',
                                'api_key' => 'Khóa IP nếu sai API KEY quá nhiều lần',
                                '2fa' => 'Khóa IP nếu sai mã 2FA quá nhiều lần',
                                'otp' => 'Khóa IP nếu sai mã OTP quá nhiều lần (Cửa sổ 30 phút)',
                                'recovery' => 'Khóa IP nếu yêu cầu khôi phục mật khẩu quá nhiều lần (Cửa sổ 30 phút)',
                                'spam_products' => 'Khóa IP nếu spam tải danh sách sản phẩm quá nhiều lần',
                                'cron_key' => 'Khóa IP nếu sai khóa Cron Job quá nhiều lần',
                                'create_acc' => 'Khóa IP nếu cố gắng tạo tài khoản quá nhiều lần (Cửa sổ 30 phút)',
                                'change_gmail' => 'Khóa IP nếu yêu cầu đổi gmail quá nhiều lần (Cửa sổ 30 phút)',
                                'change_gmail_dup' => 'Khóa IP nếu yêu cầu đổi gmail trùng quá nhiều lần trong 30 phút',
                                'tickets' => 'Khóa IP nếu tạo yêu cầu hỗ trợ (Ticket) quá nhiều lần (Cửa sổ 60 phút)',
                                'ticket_msg' => 'Khóa IP nếu gửi quá nhiều tin nhắn ticket (Cửa sổ 1 phút)',
                            ];
                            $defaultsA = [
                                'window' => 10,
                                'max' => 5,
                            ];
                            function getVal($data, $key, $field, $def) {
                                return isset($data[$key][$field]) ? $data[$key][$field] : $def;
                            }
                        @endphp

                        @foreach($rulesA as $key => $label)
                            <tr>
                                <td>
                                    <strong>{{ $label }}</strong>
                                    <input type="hidden" name="bruteforce[{{ $key }}][label]" value="{{ $label }}">
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="bruteforce[{{ $key }}][enable]" value="1" 
                                            {{ getVal($bruteForceRules, $key, 'enable', 0) ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="bruteforce[{{ $key }}][max_attempts]" 
                                        value="{{ getVal($bruteForceRules, $key, 'max_attempts', 5) }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="bruteforce[{{ $key }}][window_minutes]" 
                                        value="{{ getVal($bruteForceRules, $key, 'window_minutes', 10) }}">
                                </td>
                                <td>
                                    <select class="form-control" name="bruteforce[{{ $key }}][ban_action]">
                                        @foreach(['ban' => 'Vĩnh viễn', 'ban_1_day' => '1 Ngày', 'ban_2_day' => '2 Ngày', 'ban_3_day' => '3 Ngày', 'ban_4_day' => '4 Ngày'] as $val => $txt)
                                            <option value="{{ $val }}" {{ getVal($bruteForceRules, $key, 'ban_action', 'ban_1_day') == $val ? 'selected' : '' }}>
                                                {{ $txt }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="alert alert-info mt-2">
                    <strong>Lưu ý:</strong> Nếu hệ thống có thể phát hiện người dùng đã đăng nhập khi có vi phạm xảy ra, thì ĐỒNG THỜI sẽ chặn tài khoản người dùng (loại='user') bên cạnh việc chặn IP.
                </div>
            </div>

            <!-- SECTION B: Access Control -->
            <h5 class="mb-3 text-primary"><i class="fa fa-user-lock"></i> Kiểm soát truy cập</h5>
            <div class="row mb-4">
                <div class="col-12">
                     <div class="card p-3 bg-light">
                        <div class="row align-items-center mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Chặn IP truy cập trái phép vào Trang quản trị trong 15 phút</label>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="access_control[admin_unauth_ban][enable]" value="1"
                                        {{ isset($accessControl['admin_unauth_ban']['enable']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Bật</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">Thời hạn (Phút)</span>
                                    <input type="number" class="form-control" name="access_control[admin_unauth_ban][duration]" 
                                        value="{{ isset($accessControl['admin_unauth_ban']['duration']) ? $accessControl['admin_unauth_ban']['duration'] : 15 }}">
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center mb-2">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Chỉ cho phép Admin đăng nhập từ 1 địa chỉ IP</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="access_control[admin_one_ip][enable]" value="1"
                                        {{ isset($accessControl['admin_one_ip']['enable']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Bật</label>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center mb-2">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Chỉ cho phép Admin đăng nhập từ 1 thiết bị</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="access_control[admin_one_device][enable]" value="1"
                                        {{ isset($accessControl['admin_one_device']['enable']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Bật</label>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Chỉ cho phép Khách hàng đăng nhập từ 1 thiết bị</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="access_control[client_one_device][enable]" value="1"
                                        {{ isset($accessControl['client_one_device']['enable']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Bật</label>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>
            </div>

            <!-- SECTION C: Other -->
            <h5 class="mb-3 text-primary"><i class="fa fa-cogs"></i> Bảo mật khác</h5>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số lượng tài khoản tối đa có thể đăng ký trên mỗi IP</label>
                    <input type="number" class="form-control" name="other[max_acc_per_ip]" 
                        value="{{ $otherSecurity['max_acc_per_ip'] ?? '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Thời gian phiên đăng nhập (phút)</label>
                    <input type="number" class="form-control" name="other[session_duration]" 
                        value="{{ $otherSecurity['session_duration'] ?? '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Khóa bí mật Cron Job</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="other[cron_key]" id="cron_key_field"
                            value="{{ $otherSecurity['cron_key'] ?? '' }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="generateRandomCronKey()">
                            <i class="fa fa-random"></i> Ngẫu nhiên
                        </button>
                    </div>
                    <small class="text-muted">Cách dùng: ?key={KEY}</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">API Key</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="other[api_key]" id="api_key_field"
                            value="{{ $otherSecurity['api_key'] ?? '' }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="generateRandomApiKey()">
                            <i class="fa fa-random"></i> Ngẫu nhiên
                        </button>
                    </div>
                </div>
            </div>

            <h5 class="mb-3 text-primary"><i class="fa fa-robot"></i> Captcha</h5>
            <div class="row mb-4">
                <div class="col-12">
                     <div class="card p-3">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="captcha[login]" value="1"
                                        {{ isset($captcha['login']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Bật khi Đăng nhập</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="captcha[register]" value="1"
                                        {{ isset($captcha['register']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Bật khi Đăng ký</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="captcha[forgot]" value="1"
                                        {{ isset($captcha['forgot']) ? 'checked' : '' }}>
                                    <label class="form-check-label">Bật khi Quên mật khẩu</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nhà cung cấp</label>
                                <select class="form-control" name="captcha[provider]">
                                    <option value="none" {{ ($captcha['provider'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                                    <option value="turnstile" {{ ($captcha['provider'] ?? '') == 'turnstile' ? 'selected' : '' }}>Cloudflare Turnstile</option>
                                    <option value="recaptcha_v2" {{ ($captcha['provider'] ?? '') == 'recaptcha_v2' ? 'selected' : '' }}>ReCaptcha V2</option>
                                    <option value="recaptcha_v3" {{ ($captcha['provider'] ?? '') == 'recaptcha_v3' ? 'selected' : '' }}>ReCaptcha V3</option>
                                </select>
                             </div>
                             <div class="col-md-4 mb-3">
                                <label class="form-label">Site Key</label>
                                <input type="text" class="form-control" name="captcha[site_key]" 
                                    value="{{ $captcha['site_key'] ?? '' }}">
                             </div>
                             <div class="col-md-4 mb-3">
                                <label class="form-label">Secret Key</label>
                                <input type="text" class="form-control" name="captcha[secret_key]" 
                                    value="{{ $captcha['secret_key'] ?? '' }}">
                             </div>
                        </div>
                     </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Lưu Cài đặt</button>
        </form>
    </div>
</div>

<script>
function generateRandomApiKey() {
    document.getElementById('api_key_field').value = $randomString(32);
}
function generateRandomCronKey() {
    document.getElementById('cron_key_field').value = $randomString(32);
}
function $randomString(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}
</script>

@endsection
