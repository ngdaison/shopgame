@extends('admin.layouts.master')
@section('title', 'Admin: General Settings')
@section('css')
  <link rel="stylesheet" href="{{ asset('/plugins/codemirror/codemirror.css') }}">
  <link rel="stylesheet" href="{{ asset('/plugins/codemirror/theme/monokai.css') }}">
@endsection
@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Cài Đặt Hệ Thống</div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.general.update', ['type' => 'general']) }}" method="POST" class="default-form axios-form" data-reload="true" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <!-- 1. Tên miền được phép | Tiêu đề Trang -->
              <div class="col-md-6 mb-2">
                <label class="form-label">Tên miền được phép</label>
                <input type="text" class="form-control" name="allowed_domains" value="{{ setting('allowed_domains') }}" placeholder="example.com, example.net">
                <small class="text-muted">Chỉ có tên miền ở trong đây mới được sử dụng website</small>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Tiêu đề Trang / Tên Ứng Dụng</label>
                <input type="text" class="form-control" name="title" value="{{ setting('title') }}" placeholder="VD: KiyoVN, MyApp">
                <small class="text-muted">Dùng cho trang web, email và thông báo. Nếu để trống sẽ dùng "KiyoVN". Tên miền có thể ghi đè giá trị này.</small>
              </div>
              <div class="col-md-6"></div>


              <!-- 2. Mô tả Trang | Từ khóa SEO -->
              <div class="col-md-6 mb-2">
                <label class="form-label">Mô tả Trang</label>
                <textarea class="form-control" name="description" rows="2">{{ setting('description') }}</textarea>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Từ khóa SEO</label>
                <textarea class="form-control" name="keywords" rows="2">{{ setting('keywords') }}</textarea>
              </div>

              <!-- 3. Giao diện | Màu chủ đạo (+ Giao diện mặc định check) -->
              <div class="col-md-6 mb-2">
                <label class="form-label">Giao diện</label>
                <select class="form-select" name="default_theme">
                  <option value="light" {{ setting('default_theme') == 'light' ? 'selected' : '' }}>Sáng</option>
                  <option value="dark" {{ setting('default_theme') == 'dark' ? 'selected' : '' }}>Tối</option>
                </select>
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" name="delete_primary_color" id="delete_primary_color" {{ setting('primary_color') ? '' : 'checked' }}>
                  <label class="form-check-label fw-bold" for="delete_primary_color">
                    Giao diện mặc định
                  </label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Màu chủ đạo</label>
                <div class="input-group">
                  <input type="color" class="form-control form-control-color w-100" name="primary_color" id="primary_color" value="{{ setting('primary_color', '#000000') }}" title="Chọn màu" {{ setting('primary_color') ? '' : 'disabled' }}>
                </div>
                <div class="mt-2">
                  <label class="form-label">Font chữ</label>
                  <input type="text" class="form-control" name="font_family" value="{{ setting('font_family', 'Signika') }}" placeholder="Font chữ mặc định (Nhập 0 để tắt)">
                </div>
              </div>
              <script>
                document.getElementById('delete_primary_color').addEventListener('change', function() {
                  const colorInput = document.getElementById('primary_color');
                  if (this.checked) {
                    colorInput.setAttribute('disabled', 'disabled');
                  } else {
                    colorInput.removeAttribute('disabled');
                  }
                });
              </script>

              <!-- 4. Nơi Lưu Trữ Ảnh | Thời gian chờ nạp (giây) -->
              <div class="col-md-6 mb-2">
                <label class="form-label">Nơi Lưu Trữ Ảnh</label>
                <select class="form-select" name="upload_provider">
                  <option value="public" {{ setting('upload_provider') == 'public' ? 'selected' : '' }}>Local Storage (Public)</option>
                  <option value="imgbb" {{ setting('upload_provider') == 'imgbb' ? 'selected' : '' }}>ImgBB API</option>
                </select>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Thời gian chờ nạp (giây)</label>
                <input type="number" class="form-control" name="time_wait_free" value="{{ setting('time_wait_free') }}">
              </div>

              <!-- 5. Tỷ giá Robux | Ngôn ngữ mặc định -->
              <div class="col-md-6 mb-2">
                <label class="form-label">Tỷ giá Robux (Cố định hoặc Mốc|Rate)</label>
                <input type="text" class="form-control" name="rate_robux" value="{{ setting('rate_robux') }}" placeholder="VD: 10 hoặc 100|150, 500|140">
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Ngôn ngữ mặc định</label>
                <select class="form-select" name="default_language_id">
                  <option value="">Mặc định</option>
                  @foreach($languages as $lang)
                    <option value="{{ $lang->id }}" {{ setting('default_language_id') == $lang->id ? 'selected' : '' }}>{{ $lang->name }} ({{ strtoupper($lang->iso_code) }})</option>
                  @endforeach
                </select>
              </div>

              <!-- 6. Tiền tệ mặc định -->
              <div class="col-md-6 mb-2">
                <label class="form-label">Tiền tệ mặc định</label>
                <select class="form-select" name="default_currency_id">
                  <option value="">Mặc định (VND)</option>
                  @foreach($currencies as $curr)
                    <option value="{{ $curr->id }}" {{ setting('default_currency_id') == $curr->id ? 'selected' : '' }}>{{ $curr->code }} - {{ $curr->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">Chuyển hướng về trang chủ khi lỗi 404</label>
                <select class="form-select" name="redirect_404_to_home">
                  <option value="0" {{ setting('redirect_404_to_home') == '0' ? 'selected' : '' }}>Tắt</option>
                  <option value="1" {{ setting('redirect_404_to_home') == '1' ? 'selected' : '' }}>Bật</option>
                </select>
              </div>



              <!-- 7. Logo Sáng | Logo Tối | Favicon | Logo Chia sẻ -->
              <div class="col-md-3 mb-2">
                <label class="form-label">Logo Sáng</label>
                <input type="file" class="form-control" name="logo_light_file" accept="image/*">
                @if(setting('logo_light'))
                  <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ setting('logo_light') }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_light"><i class="fa fa-times"></i></span></div>
                @endif
              </div>
              <div class="col-md-3 mb-2">
                <label class="form-label">Logo Tối</label>
                <input type="file" class="form-control" name="logo_dark_file" accept="image/*">
                @if(setting('logo_dark'))
                  <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ setting('logo_dark') }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_dark"><i class="fa fa-times"></i></span></div>
                @endif
              </div>
              <div class="col-md-3 mb-2">
                <label class="form-label">Favicon</label>
                <input type="file" class="form-control" name="favicon_file" accept="image/*">
                @if(setting('favicon'))
                  <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ setting('favicon') }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="favicon"><i class="fa fa-times"></i></span></div>
                @endif
              </div>
              <div class="col-md-3 mb-2">
                <label class="form-label">Logo Chia sẻ</label>
                <input type="file" class="form-control" name="logo_share_file" accept="image/*">
                @if(setting('logo_share'))
                  <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ setting('logo_share') }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_share"><i class="fa fa-times"></i></span></div>
                @endif
              </div>

              <!-- 8. Banner | YouTube ID -->
              <div class="col-md-6 mb-2">
                <label class="form-label">Banner</label>
                <input type="file" class="form-control" name="banner_file" accept="image/*">
                @if(setting('banner'))
                  <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ setting('banner') }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="banner"><i class="fa fa-times"></i></span></div>
                @endif
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label">YouTube ID</label>
                <input type="text" class="form-control" name="youtube_id" value="{{ setting('youtube_id') }}">
                <small class="text-muted">Nếu nhập, video YouTube sẽ hiển thị thay cho ảnh Banner</small>
              </div>

              <!-- 9. Ảnh nền Website -->
              <div class="col-md-12 mb-2">
                <label class="form-label">Ảnh nền Website</label>
                <input type="file" class="form-control" name="background_image_url_file" accept="image/*">
                @if(setting('background_image_url'))
                  <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ setting('background_image_url') }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="background_image_url"><i class="fa fa-times"></i></span></div>
                @endif
              </div>

              <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary-gradient px-5">Lưu Cài Đặt</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Cấu hình đăng nhập mạng xã hội</div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.general.update', ['type' => 'social_login']) }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="row">
              @php
                $google = Helper::getApiConfig('auth_google');
                $facebook = Helper::getApiConfig('auth_facebook');
                $discord = Helper::getApiConfig('auth_discord');
              @endphp
              
              <!-- Google -->
              <div class="col-md-4 mb-3">
                <div class="border p-3 rounded">
                  <h6 class="fw-bold mb-3"><i class="fab fa-google text-danger me-2"></i>Google Login</h6>
                  <div class="mb-2">
                    <label class="form-label">Client ID</label>
                    <input type="text" class="form-control" name="google_client_id" value="{{ $google['client_key'] ?? '' }}">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Client Secret</label>
                    <input type="text" class="form-control" name="google_client_secret" value="{{ $google['client_secret'] ?? '' }}">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="google_client_status">
                      <option value="1" {{ ($google['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật</option>
                      <option value="0" {{ ($google['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Tắt</option>
                    </select>
                  </div>
                  <div class="mb-0">
                    <label class="form-label">Redirect URL</label>
                    <input type="text" class="form-control bg-light" value="{{ route('auth.social.callback', ['provider' => 'google']) }}" readonly>
                  </div>
                </div>
              </div>

              <!-- Facebook -->
              <div class="col-md-4 mb-3">
                <div class="border p-3 rounded">
                  <h6 class="fw-bold mb-3"><i class="fab fa-facebook text-primary me-2"></i>Facebook Login</h6>
                  <div class="mb-2">
                    <label class="form-label">Client ID</label>
                    <input type="text" class="form-control" name="facebook_client_id" value="{{ $facebook['client_key'] ?? '' }}">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Client Secret</label>
                    <input type="text" class="form-control" name="facebook_client_secret" value="{{ $facebook['client_secret'] ?? '' }}">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="facebook_client_status">
                      <option value="1" {{ ($facebook['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật</option>
                      <option value="0" {{ ($facebook['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Tắt</option>
                    </select>
                  </div>
                  <div class="mb-0">
                    <label class="form-label">Redirect URL</label>
                    <input type="text" class="form-control bg-light" value="{{ route('auth.social.callback', ['provider' => 'facebook']) }}" readonly>
                  </div>
                </div>
              </div>

              <!-- Discord -->
              <div class="col-md-4 mb-3">
                <div class="border p-3 rounded">
                  <h6 class="fw-bold mb-3"><i class="fab fa-discord text-info me-2"></i>Discord Login</h6>
                  <div class="mb-2">
                    <label class="form-label">Client ID</label>
                    <input type="text" class="form-control" name="discord_client_id" value="{{ $discord['client_key'] ?? '' }}">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Client Secret</label>
                    <input type="text" class="form-control" name="discord_client_secret" value="{{ $discord['client_secret'] ?? '' }}">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="discord_client_status">
                      <option value="1" {{ ($discord['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật</option>
                      <option value="0" {{ ($discord['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Tắt</option>
                    </select>
                  </div>
                  <div class="mb-0">
                    <label class="form-label">Redirect URL</label>
                    <input type="text" class="form-control bg-light" value="{{ route('auth.social.callback', ['provider' => 'discord']) }}" readonly>
                  </div>
                </div>
              </div>
              
              <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary-gradient px-5">Lưu cấu hình</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Thông Tin Giới Thiệu</div>
        </div>
        <div class="card-body">
            @php $shop_info = Helper::getConfig('shop_info'); @endphp
            <form action="{{ route('admin.settings.general.update', ['type' => 'shop_info']) }}" method="POST" class="default-form axios-form" data-reload="true">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Footer Text 1</label>
                    <input type="text" class="form-control" name="footer_text_1" value="{{ $shop_info['footer_text_1'] ?? '' }}" placeholder="GIỚI THIỆU">
                </div>
                <div class="mb-3">
                    <label class="form-label">Footer Text 2</label>
                    <input type="text" class="form-control" name="footer_text_2" value="{{ $shop_info['footer_text_2'] ?? '' }}" placeholder="THÔNG TIN CHUNG">
                </div>
                <div class="mb-3">
                    <label class="form-label">Dashboard Text 1</label>
                    <input type="text" class="form-control" name="dashboard_text_1" value="{{ $shop_info['dashboard_text_1'] ?? '' }}">
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary-gradient px-5">Cập nhật ngay</button>
                </div>
            </form>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Rút Thưởng Miễn Phí Vật Phẩm Trong Trò Chơi [GỐC TRÁI WEBSITE]</div>
        </div>
        <div class="card-body">
          <div class="alert alert-danger">* Nếu có 4 vòng quay (4 vật phẩm) thì khách sẽ nhận được random 4 vật phẩm (theo số lượng vòng quay hiện tại)</div>
          @php $get_gift = Helper::getConfig('get_gift'); @endphp
          <form action="{{ route('admin.settings.general.update', ['type' => 'get_gift']) }}" method="POST" class="default-form axios-form" data-reload="true" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="status" class="form-label">Trạng Thái</label>
                <select name="status" id="status" class="form-control">
                  <option value="1" {{ ($get_gift['status'] ?? 0) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($get_gift['status'] ?? 0) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="balance" class="form-label">Yêu Cầu Số Dư</label>
                <input type="number" class="form-control" id="balance" name="balance" value="{{ $get_gift['balance'] ?? 0 }}" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="min" class="form-label">Tối Thiểu</label>
                <input type="number" class="form-control" id="min" name="min" value="{{ $get_gift['min'] ?? 0 }}" required>
              </div>
              <div class="col-md-6">
                <label for="max" class="form-label">Tối Đa</label>
                <input type="number" class="form-control" id="max" name="max" value="{{ $get_gift['max'] ?? 0 }}" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="width" class="form-label">Width: Ảnh</label>
                <input type="number" class="form-control" id="width" name="width" value="{{ $get_gift['width'] ?? 0 }}" required>
              </div>
              <div class="col-md-6">
                <label for="height" class="form-label">Height: Ảnh</label>
                <input type="number" class="form-control" id="height" name="height" value="{{ $get_gift['height'] ?? 0 }}" required>
              </div>
            </div>
            <div class="mb-">
              <label for="up_image" class="form-label">Hình Ảnh</label>
              <input type="file" class="form-control" id="up_image" name="up_image">
              <input type="url" name="image" class="form-control mt-2" placeholder="Nhập link hoặc chọn ảnh để upload" value="{{ $get_gift['image'] ?? '' }}">
                <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ Helper::getValidImage($get_gift['image'] ?? '') }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="image"><i class="fa fa-times"></i></span></div>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>



    <div class="col-md-6">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Cấu hình Affiliate Program</div>
        </div>
        <div class="card-body">
          @php $affiliate_config = Helper::getConfig('affiliate_config'); @endphp
          <form action="{{ route('admin.settings.general.update', ['type' => 'affiliate_config']) }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="row mb-2">
              <div class="col-md-4 mb-2">
                <label for="min_withdraw" class="form-label">Tối Thiểu Rút</label>
                <input type="number" class="form-control" id="min_withdraw" name="min_withdraw" value="{{ $affiliate_config['min_withdraw'] ?? '' }}">
              </div>
              <div class="col-md-4 mb-2">
                <label for="max_withdraw" class="form-label">Tối Đa Rút</label>
                <input type="number" class="form-control" id="max_withdraw" name="max_withdraw" value="{{ $affiliate_config['max_withdraw'] ?? '' }}">
              </div>
              <div class="col-md-4 mb-2">
                <label for="withdraw_status" class="form-label">Trạng Thái Rút</label>
                <select class="form-control" id="withdraw_status" name="withdraw_status">
                  <option value="1" {{ ($affiliate_config['withdraw_status'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($affiliate_config['withdraw_status'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-md-12 mb-2">
                <label for="comm_percent" class="form-label">Phần trăm hoa hồng chuyển khoản</label>
                <input type="number" class="form-control" id="comm_percent" name="comm_percent" value="{{ $affiliate_config['comm_percent'] ?? 0 }}">
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-md-6 mb-2">
                <label for="commission_type" class="form-label">Tính Hoa Hồng</label>
                <select class="form-control" id="commission_type" name="commission_type">
                  <option value="deposit" {{ ($affiliate_config['commission_type'] ?? 'deposit') == 'deposit' ? 'selected' : '' }}>Nạp Tiền</option>
                  <option value="order" {{ ($affiliate_config['commission_type'] ?? 'deposit') == 'order' ? 'selected' : '' }}>Đơn Hàng</option>
                </select>
              </div>
              <div class="col-md-6 mb-2">
                <label for="limit_mode" class="form-label">Giới Hạn Nhận</label>
                <select class="form-control" id="limit_mode" name="limit_mode">
                  <option value="count" {{ ($affiliate_config['limit_mode'] ?? 'count') == 'count' ? 'selected' : '' }}>Số Lần</option>
                  <option value="days" {{ ($affiliate_config['limit_mode'] ?? 'count') == 'days' ? 'selected' : '' }}>Số Ngày</option>
                  <option value="both" {{ ($affiliate_config['limit_mode'] ?? 'count') == 'both' ? 'selected' : '' }}>Số Ngày + Số Lần</option>
                </select>
              </div>
            </div>
            
            <div class="row mb-2" id="group_limit_days">
              <div class="col-md-12 mb-2">
                <label for="limit_days" class="form-label">Giới Hạn Số Ngày</label>
                <input type="number" class="form-control" id="limit_days" name="limit_days" value="{{ $affiliate_config['limit_days'] ?? 0 }}">
                <small class="text-muted" id="help_limit_days">Số ngày kể từ khi thành viên đăng ký được phép nhận hoa hồng.</small>
              </div>
            </div>

            <div class="row mb-2" id="group_limit_count">
              <div class="col-md-12 mb-2">
                <label for="limit_count" class="form-label">Giới Hạn Số Lần</label>
                <input type="number" class="form-control" id="limit_count" name="limit_count" value="{{ $affiliate_config['limit_count'] ?? 0 }}">
                <small class="text-muted" id="help_limit_count">Số lần tối đa được nhận hoa hồng từ mỗi thành viên.</small>
              </div>
            </div>

            <script>
              document.addEventListener("DOMContentLoaded", function() {
                const limitMode = document.getElementById('limit_mode');
                const groupCount = document.getElementById('group_limit_count');
                const groupDays = document.getElementById('group_limit_days');
                const helpDays = document.getElementById('help_limit_days');
                const helpCount = document.getElementById('help_limit_count');

                function updateVisibility() {
                  const val = limitMode.value;
                  if (val === 'count') {
                    groupCount.style.display = 'block';
                    groupDays.style.display = 'none';
                    helpCount.innerText = 'Số lần tối đa được nhận hoa hồng từ mỗi thành viên';
                  } else if (val === 'days') {
                    groupCount.style.display = 'none';
                    groupDays.style.display = 'block';
                    helpDays.innerText = 'Số ngày kể từ khi thành viên đăng ký được phép nhận hoa hồng';
                  } else if (val === 'both') {
                    groupCount.style.display = 'block';
                    groupDays.style.display = 'block';
                    helpDays.innerText = 'Số lần tối đa được nhận hoa hồng từ mỗi thành viên';
                    helpCount.innerText = 'Số lần tối đa trong khoảng thời gian trên';
                  }
                }

                limitMode.addEventListener('change', updateVisibility);
                updateVisibility();
              });
            </script>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập Nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card custom-card" id="ticket-settings">
        <div class="card-header justify-content-between">
          <div class="card-title">Cấu hình Ticket</div>
        </div>
        <div class="card-body">
          @php $ticket_config = Helper::getConfig('ticket_config'); @endphp
          <form action="{{ route('admin.settings.general.update', ['type' => 'ticket_config']) }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="row">
              <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Chủ đề Ticket</label>
                <input type="text" class="form-control" name="ticket_categories" placeholder="Hỗ trợ nạp tiền, Hỗ trợ lỗi game, Hỗ trợ khác" value="{{ isset($ticket_config['ticket_categories']) ? str_replace(["\r\n", "\r", "\n"], ", ", $ticket_config['ticket_categories']) : '' }}">
                <small class="text-muted">Nhập các chủ đề hỗ trợ, phân cách bằng dấu phẩy.</small>
              </div>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-primary-gradient px-5">Lưu Cấu Hình</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Tuỳ chỉnh giao diện</div>
        </div>
        <div class="card-body">
          @php $bconfig = Helper::getConfig('theme_custom'); @endphp
          <form action="{{ route('admin.settings.general.update', ['type' => 'theme_custom']) }}" method="POST" class="default-form axios-form" data-reload="true" enctype="multipart/form-data">
            @csrf
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="card_stats" class="form-label">Thẻ Thống Kê</label>
                <select class="form-select" id="card_stats" name="card_stats">
                  <option value="1" {{ ($bconfig['card_stats'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($bconfig['card_stats'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="product_info_type" class="form-label">Kiểu hiển thị thông tin sản phẩm</label>
                <select class="form-select" id="product_info_type" name="product_info_type">
                  <option value="0" {{ ($bconfig['product_info_type'] ?? null) == 0 ? 'selected' : '' }}>Chỉ hiện nick còn lại</option>
                  <option value="1" {{ ($bconfig['product_info_type'] ?? null) == 1 ? 'selected' : '' }}>Hiện đã bán và Nick còn lại</option>
                </select>
              </div>
            </div>
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="buy_button_img" class="form-label">Link ảnh nút mua</label>
                <input type="text" class="form-control" id="buy_button_img" name="buy_button_img" value="{{ $bconfig['buy_button_img'] ?? '_assets/images/stores/view-all.gif' }}">
              </div>
              <div class="col-md-6">
                <label for="enable_custom_theme" class="form-label">Cho Phép Tuỳ Chỉnh Theme</label>
                <select class="form-select" id="enable_custom_theme" name="enable_custom_theme">
                  <option value="1" {{ ($bconfig['enable_custom_theme'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($bconfig['enable_custom_theme'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
            </div>
             <div class="mb-3 row">
              <div class="col-md-6">
                <label for="show_thongbao" class="form-label">Hiện Thông Báo Chạy</label>
                <select class="form-select" id="show_thongbao" name="show_thongbao">
                  <option value="1" {{ ($bconfig['show_thongbao'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($bconfig['show_thongbao'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="show_lsmua" class="form-label">Hiện Lịch Sử Mua Nick</label>
                <select class="form-select" id="show_lsmua" name="show_lsmua">
                  <option value="1" {{ ($bconfig['show_lsmua'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($bconfig['show_lsmua'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="show_banner" class="form-label">Hiện Banner và TOP Nạp</label>
                <select class="form-select" id="show_banner" name="show_banner">
                  <option value="1" {{ ($bconfig['show_banner'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($bconfig['show_banner'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="show_all_account_img" class="form-label">Tắt Slide ảnh sản phẩm (Account Info)</label>
                <select class="form-select" id="show_all_account_img" name="show_all_account_img">
                  <option value="1" {{ ($bconfig['show_all_account_img'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($bconfig['show_all_account_img'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
            </div>
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="minigame_show_value" class="form-label">Hiển thị chi tiết phần thưởng</label>
                <select class="form-select" id="minigame_show_value" name="minigame_show_value">
                  <option value="1" {{ ($bconfig['minigame_show_value'] ?? null) == 1 ? 'selected' : '' }}>Bật</option>
                  <option value="0" {{ ($bconfig['minigame_show_value'] ?? null) == 0 ? 'selected' : '' }}>Tắt</option>
                </select>
              </div>
              <div class="col-md-6">
                 <label for="pin_type" class="form-label">Kiểu hiển thị nhóm GHIM</label>
                <select class="form-select" id="pin_type" name="pin_type">
                  <option value="slide" {{ ($bconfig['pin_type'] ?? null) == 'slide' ? 'selected' : '' }}>Slide</option>
                  <option value="grid" {{ ($bconfig['pin_type'] ?? null) == 'grid' ? 'selected' : '' }}>Grid</option>
                </select>
              </div>
              <div class="mb-3 row">
                  <div class="col-md-12">
                    <label class="form-label">Top nạp tháng (Fake)</label>
                    <textarea class="form-control" name="fake_top_deposit" rows="5" placeholder="Tên (nếu có &quot;*&quot; sau cùng thì nó hiện 3 chữ đầu)|Số tiền nạp">{{ theme_config('fake_top_deposit') }}</textarea>
                    <small class="text-muted">Mỗi dòng 1 người. VD: NguyenVanA*|500000 (Hiện: Ngu****** - 500.000) hoặc TranVanB|200000 (Hiện: TranVanB - 200.000)</small>
                  </div>
              </div>
              <div class="mb-3 row">
                  <div class="col-md-12">
                      <label class="form-label text-danger fw-bold">Không lưu log các đường dẫn (admin/logs)</label>
                      <textarea class="form-control" name="excluded_log_paths" rows="3" placeholder="VD: /account/heartbeat, heartbeat, auth/login">{{ theme_config('excluded_log_paths') }}</textarea>
                      <small class="text-muted">Nhập các đường dẫn không muốn lưu log, cách nhau bằng dấu phẩy hoặc xuống dòng. Nhập <b>/</b> để dừng lưu toàn bộ log hệ thống.</small>
                  </div>
              </div>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Thông tin liên hệ</div>
        </div>
        <div class="card-body">
          @php $contact = Helper::getConfig('contact_info'); @endphp
          <form action="{{ route('admin.settings.general.update', ['type' => 'contact_info']) }}" method="POST" class="default-form axios-form" data-reload="true" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="facebook" class="form-label">Facebook</label>
                <input type="text" class="form-control" id="facebook" name="facebook" value="{{ $contact['facebook'] ?? '' }}">
              </div>
              <div class="col-md-4">
                <label for="telegram" class="form-label">Telegram</label>
                <input type="text" class="form-control" id="telegram" name="telegram" value="{{ $contact['telegram'] ?? '' }}">
              </div>
              <div class="col-md-4">
                <label for="twitter" class="form-label">Twitter</label>
                <input type="text" class="form-control" id="twitter" name="twitter" value="{{ $contact['twitter'] ?? '' }}">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="phone_no" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="phone_no" name="phone_no" value="{{ $contact['phone_no'] ?? '' }}">
              </div>
              <div class="col-md-4">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email" value="{{ $contact['email'] ?? '' }}">
              </div>
              <div class="col-md-4">
                <label for="discord" class="form-label">Discord</label>
                <input type="text" class="form-control" id="discord" name="discord" value="{{ $contact['discord'] ?? '' }}">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="instagram" class="form-label">Instagram</label>
                <input type="text" class="form-control" id="instagram" name="instagram" value="{{ $contact['instagram'] ?? '' }}">
              </div>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Header Code</div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.general.update', ['type' => 'header_script']) }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="code" class="form-label">Code</label>
              <textarea class="form-control" name="code" id="editor1" rows="10">{{ Helper::getNotice('header_script') }}</textarea>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Footer Code</div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.general.update', ['type' => 'footer_script']) }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="code" class="form-label">Code</label>
              <textarea class="form-control" name="code" id="editor2" rows="10">{{ Helper::getNotice('footer_script') }}</textarea>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
@endsection
@section('scripts')
  <script src="{{ asset('/plugins/codemirror/codemirror.js') }}"></script>
  <script src="{{ asset('/plugins/codemirror/mode/css/css.js') }}"></script>
  <script src="{{ asset('/plugins/codemirror/mode/xml/xml.js') }}"></script>
  <script src="{{ asset('/plugins/codemirror/mode/htmlmixed/htmlmixed.js') }}"></script>

  <script src="/plugins/ckeditor/ckeditor.js"></script>
  <script>
    $(function() {
      // CodeMirror
      CodeMirror.fromTextArea(document.getElementById("editor1"), {
        mode: "htmlmixed",
        theme: "monokai"
      });
      CodeMirror.fromTextArea(document.getElementById("editor2"), {
        mode: "htmlmixed",
        theme: "monokai"
      });

      if(document.getElementById('notice_homepage')) {
          CKEDITOR.replace('notice_homepage', {
            height: 300,
            clipboard_handleImages: false,
            filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
            filebrowserUploadMethod: 'form'
          });
      }
      if(document.getElementById('notice_featured_homepage')) {
          CKEDITOR.replace('notice_featured_homepage', {
            height: 300,
            clipboard_handleImages: false,
            filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
            filebrowserUploadMethod: 'form'
          });
      }
    })
  </script>
@endsection
