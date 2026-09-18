@extends('partner.layouts.master')
@section('title', 'Đối tác: Cấu hình Thông báo')
@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('partner.settings.notices.update') }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Cấu hình Thông báo: <strong>{{ $config->domain }}</strong></div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Thông báo | Trang chủ</label>
                        <textarea name="notice_homepage" id="notice_homepage" class="form-control ckeditor" rows="10">{{ $config->notice_homepage }}</textarea>
                        <small class="text-muted">Nội dung này sẽ hiển thị ở trang chủ của website.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Thông báo | Nổi ở trang chủ</label>
                        <textarea name="notice_featured_homepage" id="notice_featured_homepage" class="form-control ckeditor" rows="10">{{ $config->notice_featured_homepage }}</textarea>
                        <small class="text-muted">Nội dung này sẽ hiển thị nổi bật trên trang chủ (thường trong modal hoặc khung đặc biệt).</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Lưu thông báo</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
