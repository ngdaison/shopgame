@extends('admin.layouts.master')
@section('title', 'Admin: Cấu hình Branding - ' . $config->domain)
@section('styles')
<style>
  .choices__inner {
      min-height: 44px;
      border-radius: 0.375rem !important;
  }
  
  .choices__list--multiple .choices__item {
      background-color: #7367f0;
      border: 1px solid #7367f0;
      border-radius: 4px;
  }
  
  .choices__list--multiple .choices__item.is-highlighted {
      background-color: #685dd8;
      border: 1px solid #685dd8;
  }
</style>
@endsection
@section('content')
  <div class="row">
    <div class="col-md-12">
        <form action="{{ route('admin.domain.update', ['id' => $config->id]) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Cấu hình Branding & SEO cho: <strong>{{ $config->domain }}</strong></div>
                    <a href="{{ route('admin.domain.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Quay lại</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Branding Section -->
                        <div class="col-md-6 border-end">
                            <div class="mb-3">
                                <label class="form-label">Logo Sáng</label>
                                <input type="file" name="logo_light_file" class="form-control" accept="image/*">
                                @if($config->logo_light)
                                    <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ $config->logo_light }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_light"><i class="fa fa-times"></i></span></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Logo Tối</label>
                                <input type="file" name="logo_dark_file" class="form-control" accept="image/*">
                                @if($config->logo_dark)
                                    <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ $config->logo_dark }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_dark"><i class="fa fa-times"></i></span></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Favicon</label>
                                <input type="file" name="favicon_file" class="form-control" accept="image/*">
                                @if($config->favicon)
                                    <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ $config->favicon }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="favicon"><i class="fa fa-times"></i></span></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Logo Chia sẻ</label>
                                <input type="file" name="logo_share_file" class="form-control" accept="image/*">
                                @if($config->logo_share)
                                    <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ $config->logo_share }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_share"><i class="fa fa-times"></i></span></div>
                                @endif
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Giao diện mặc định</label>
                                    <select name="default_theme" class="form-select" required>
                                        <option value="light" {{ $config->default_theme == 'light' ? 'selected' : '' }}>Light</option>
                                        <option value="dark" {{ $config->default_theme == 'dark' ? 'selected' : '' }}>Dark</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Màu chủ đạo</label>
                                    <input type="color" name="primary_color" class="form-control form-control-color w-100" value="{{ $config->primary_color ?? '#000000' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Banner</label>
                                <input type="file" name="banner_file" class="form-control" accept="image/*">
                                @if($config->banner)
                                    <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ $config->banner }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="banner"><i class="fa fa-times"></i></span></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">YouTube ID</label>
                                <input type="text" name="youtube_id" class="form-control" value="{{ $config->youtube_id }}" placeholder="Ví dụ: dQw4w9WgXcQ">
                                <small class="text-muted">Nếu nhập, video YouTube sẽ hiển thị thay cho ảnh Banner</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh nền Website</label>
                                <input type="file" name="background_image_url_file" class="form-control" accept="image/*">
                                @if($config->background_image_url)
                                    <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ $config->background_image_url }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="background_image_url"><i class="fa fa-times"></i></span></div>
                                @endif
                            </div>

                            {{-- NEW FIELDS --}}
                            <div class="mb-3">
                                <label class="form-label">Font chữ (Google Font Name)</label>
                                <input type="text" name="font" class="form-control" value="{{ $config->font }}" placeholder="VD: Roboto, Open Sans">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Footer Text 1</label>
                                <input type="text" name="footer_text_1" class="form-control" value="{{ $config->footer_text_1 ?? '' }}" placeholder="GIỚI THIỆU">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Footer Text 2</label>
                                <input type="text" name="footer_text_2" class="form-control" value="{{ $config->footer_text_2 ?? '' }}" placeholder="THÔNG TIN CHUNG">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dashboard Text 1</label>
                                <input type="text" name="dashboard_text_1" class="form-control" value="{{ $config->dashboard_text_1 ?? '' }}">
                            </div>





                             <div class="mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="show_banner_top" name="show_banner_top" value="1" {{ $config->show_banner_top ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_banner_top">Hiện Banner và TOP Nạp</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="show_run_notify" name="show_run_notify" value="1" {{ $config->show_run_notify ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_run_notify">Hiện Thông Báo Chạy</label>
                                </div>
                            </div>
                            

                        </div>

                        <!-- SEO & Contact Section -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề Trang / Tên Ứng Dụng</label>
                                <input type="text" name="title" class="form-control" value="{{ $config->title }}" placeholder="VD: KiyoVN, MyShop">
                                <small class="text-muted">Ghi đè cài đặt chung. Dùng cho trang web, email và thông báo. Để trống sẽ dùng cài đặt chung hoặc "KiyoVN".</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả Trang</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Mô tả ngắn về website cho các công cụ tìm kiếm">{{ $config->description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Từ khóa SEO</label>
                                <textarea name="keywords" class="form-control" rows="2" placeholder="Từ khóa, cách nhau bằng dấu phẩy">{{ $config->keywords }}</textarea>
                            </div>

                            <div class="mb-3 pt-3 border-top">
                                <label class="form-label fw-bold">Ngôn ngữ mặc định</label>
                                <select name="language_id" class="form-select">
                                    <option value="">Mặc định</option>
                                    @foreach($languages as $lang)
                                        @if(!$lang->is_default)
                                            <option value="{{ $lang->id }}" {{ $config->language_id == $lang->id ? 'selected' : '' }}>{{ $lang->name }} ({{ strtoupper($lang->iso_code) }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tiền tệ mặc định</label>
                                <select name="currency_id" class="form-select">
                                    <option value="">Mặc định (VND)</option>
                                    @foreach($currencies as $curr)
                                        <option value="{{ $curr->id }}" {{ $config->currency_id == $curr->id ? 'selected' : '' }}>{{ $curr->code }} - {{ $curr->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is-redirect-switch" {{ !empty($pointingDomains) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is-redirect-switch">Chuyển tiếp tên miền</label>
                                </div>
                                
                                <div id="redirect-target-wrapper" class="{{ empty($pointingDomains) ? 'd-none' : '' }}">
                                    <label class="form-label fw-bold">Tên miền được chuyển</label>
                                    <select name="redirect_to[]" id="redirect-to-input" class="form-select" multiple>
                                        @foreach($otherDomains as $d)
                                            <option value="{{ $d }}" {{ in_array($d, $pointingDomains) ? 'selected' : '' }}>{{ $d }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3">Cấu hình đăng nhập mạng xã hội (Chỉ dùng cho tên miền này)</h6>
                            <div class="row">
                                @php
                                    $social_config = $config->social_config ?? [];
                                    $google = $social_config['auth_google'] ?? [];
                                    $facebook = $social_config['auth_facebook'] ?? [];
                                    $discord = $social_config['auth_discord'] ?? [];
                                @endphp

                                <!-- Google -->
                                <div class="col-md-4 mb-3">
                                    <div class="border p-3 rounded h-100">
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
                                            <label class="form-label">Authorized Redirect URI</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="https://{{ $config->domain }}/login/google/callback" readonly>
                                                <button class="btn btn-outline-secondary copy-btn" type="button"><i class="fa fa-copy"></i></button>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Trạng thái</label>
                                            <select class="form-select" name="google_client_status">
                                                <option value="0" {{ ($google['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Sử dụng cấu hình chung</option>
                                                <option value="1" {{ ($google['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật (Ghi đè)</option>
                                                <option value="-1" {{ ($google['client_status'] ?? 0) == -1 ? 'selected' : '' }}>Tắt (Ghi đè)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Facebook -->
                                <div class="col-md-4 mb-3">
                                    <div class="border p-3 rounded h-100">
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
                                            <label class="form-label">Authorized Redirect URI</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="https://{{ $config->domain }}/login/facebook/callback" readonly>
                                                <button class="btn btn-outline-secondary copy-btn" type="button"><i class="fa fa-copy"></i></button>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Trạng thái</label>
                                            <select class="form-select" name="facebook_client_status">
                                                <option value="0" {{ ($facebook['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Sử dụng cấu hình chung</option>
                                                <option value="1" {{ ($facebook['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật (Ghi đè)</option>
                                                <option value="-1" {{ ($facebook['client_status'] ?? 0) == -1 ? 'selected' : '' }}>Tắt (Ghi đè)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Discord -->
                                <div class="col-md-4 mb-3">
                                    <div class="border p-3 rounded h-100">
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
                                            <label class="form-label">Authorized Redirect URI</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="https://{{ $config->domain }}/login/discord/callback" readonly>
                                                <button class="btn btn-outline-secondary copy-btn" type="button"><i class="fa fa-copy"></i></button>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Trạng thái</label>
                                            <select class="form-select" name="discord_client_status">
                                                <option value="0" {{ ($discord['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Sử dụng cấu hình chung</option>
                                                <option value="1" {{ ($discord['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật (Ghi đè)</option>
                                                <option value="-1" {{ ($discord['client_status'] ?? 0) == -1 ? 'selected' : '' }}>Tắt (Ghi đè)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Thông báo | Trang chủ</label>
                                <textarea name="notice_homepage" id="notice_homepage" class="form-control ckeditor" rows="5">{{ $config->notice_homepage }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Thông báo | Nổi ở trang chủ</label>
                                <textarea name="notice_featured_homepage" id="notice_featured_homepage" class="form-control ckeditor" rows="5">{{ $config->notice_featured_homepage }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary-gradient px-5 fs-16">Lưu thay đổi</button>
                </div>
            </div>
        </form>
    </div>
  </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let choices = null;
        if (document.getElementById('redirect-to-input')) {
            choices = new Choices('#redirect-to-input', {
                removeItemButton: true,
                placeholderValue: '-- Chọn tên miền --',
                searchPlaceholderValue: 'Tìm kiếm tên miền...'
            });
        }

        $('#is-redirect-switch').on('change', function() {
            if ($(this).is(':checked')) {
                $('#redirect-target-wrapper').removeClass('d-none');
            } else {
                $('#redirect-target-wrapper').addClass('d-none');
                if (choices) {
                    choices.removeActiveItems();
                }
            }
        });

        $('.copy-btn').on('click', function() {
            let input = $(this).siblings('input');
            input.select();
            document.execCommand('copy');
            
            let btn = $(this);
            let originalHtml = btn.html();
            btn.html('<i class="fa fa-check"></i>');
            setTimeout(function() {
                btn.html(originalHtml);
            }, 2000);
            
            if (typeof toastr !== 'undefined') {
                toastr.success('Đã sao chép đường dẫn!');
            }
        });
    });
</script>
@endsection
