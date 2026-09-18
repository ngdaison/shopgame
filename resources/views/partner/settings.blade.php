@extends('partner.layouts.master')
@section('title', 'Đối tác: Cấu hình chung')
@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('partner.settings.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Cấu hình tên miền: <strong>{{ $config->domain }}</strong></div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Hình ảnh & Giao diện</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Logo Sáng</label>
                                <input type="file" name="logo_light_file" class="form-control" accept="image/*">
                                @if($config->logo_light)
                                    <div class="mt-2 text-center p-2 border rounded bg-light"><img src="{{ $config->logo_light }}" class="img-fluid" style="height: 80px;"></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Logo Tối</label>
                                <input type="file" name="logo_dark_file" class="form-control" accept="image/*">
                                @if($config->logo_dark)
                                    <div class="mt-2 text-center p-2 border rounded bg-light"><img src="{{ $config->logo_dark }}" class="img-fluid" style="height: 80px;"></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Favicon</label>
                                <input type="file" name="favicon_file" class="form-control" accept="image/*">
                                @if($config->favicon)
                                    <div class="mt-2 text-center p-2 border rounded bg-light"><img src="{{ $config->favicon }}" class="img-fluid" style="height: 40px;"></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Logo Chia sẻ</label>
                                <input type="file" name="logo_share_file" class="form-control" accept="image/*">
                                @if($config->logo_share)
                                    <div class="mt-2 text-center p-2 border rounded bg-light"><img src="{{ $config->logo_share }}" class="img-fluid" style="height: 40px;"></div>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Banner</label>
                                <input type="file" name="banner_file" class="form-control" accept="image/*">
                                @if($config->banner)
                                    <div class="mt-2 text-center p-2 border rounded bg-light"><img src="{{ $config->banner }}" class="img-fluid" style="height: 100px;"></div>
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
                                    <div class="mt-2 text-center p-2 border rounded bg-light"><img src="{{ $config->background_image_url }}" class="img-fluid" style="height: 100px;"></div>
                                @endif
                            </div>

                             <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Màu chủ đạo</label>
                                    <input type="color" name="primary_color" class="form-control form-control-color w-100" value="{{ $config->primary_color ?? '#000000' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Giao diện mặc định</label>
                                    <select name="default_theme" class="form-select">
                                        <option value="light" {{ $config->default_theme == 'light' ? 'selected' : '' }}>Light</option>
                                        <option value="dark" {{ $config->default_theme == 'dark' ? 'selected' : '' }}>Dark</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Nội dung & Tùy chỉnh</h6>

                            <div class="mb-3">
                                <label class="form-label">Tiêu đề Trang / Tên Ứng Dụng</label>
                                <input type="text" name="title" class="form-control" value="{{ $config->title }}" placeholder="VD: KiyoVN, MyShop">
                                <small class="text-muted">Ghi đè cài đặt chung. Dùng cho trang web, email và thông báo. Để trống sẽ dùng cài đặt chung hoặc "KiyoVN".</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả (Description)</label>
                                <textarea name="description" class="form-control" rows="2">{{ $config->description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Từ khóa (Keywords)</label>
                                <textarea name="keywords" class="form-control" rows="2">{{ $config->keywords }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Font chữ (Google Font)</label>
                                <input type="text" name="font" class="form-control" value="{{ $config->font }}" placeholder="VD: Roboto">
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
                    </div>


                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
