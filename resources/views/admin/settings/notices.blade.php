@extends('admin.layouts.master')
@section('title', 'Admin: Notices Settings')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thông báo | Trang chủ</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'home_dashboard']) }}" method="POST" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <textarea class="form-control ckeditor" name="content" id="home_content" rows="5">{{ $home_dashboard ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">Cập nhật ngay</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thông báo | Chinh sách Bảo mật</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_privacy_policy']) }}" method="POST" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <textarea class="form-control ckeditor" name="content" id="privacy_content" rows="5">{{ $page_privacy_policy ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">Cập nhật ngay</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thông báo | Điều khoản sử dụng</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_tos']) }}" method="POST" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <textarea class="form-control ckeditor" name="content" id="tos_content" rows="5">{{ $page_tos ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">Cập nhật ngay</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thông báo | Nổi ở trang chủ</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'modal_dashboard']) }}" method="POST" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <textarea class="form-control ckeditor" name="content" id="modal_content" rows="5">{{ $modal_dashboard ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">Cập nhật ngay</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thông báo | Trang thông tin tài khoản v1/v2</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_account_info']) }}" method="POST" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <textarea class="form-control ckeditor" name="content" id="account_content" rows="5">{{ $page_account_info ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">Cập nhật ngay</button>
        </div>
      </form>
    </div>
  </div>

@endsection
@section('scripts')
@endsection
