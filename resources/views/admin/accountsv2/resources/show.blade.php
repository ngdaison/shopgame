@extends('admin.layouts.master')
@section('title', 'Admin: Accounts Resource - Edit')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Chỉnh sửa sản phẩm "{{ $resource->id }}"</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accountsv2.resources.update', ['id' => $resource->id]) }}" id="form-edit" method="POST" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-2">
          <label for="username" class="form-label">Tài Khoản</label>
          <input type="text" class="form-control" id="username" name="username" value="{{ $resource->username }}">
        </div>
        <div class="mb-2">
          <label for="password" class="form-label">Mật khẩu</label>
          <input type="text" class="form-control" id="password" name="password" value="{{ $resource->password }}">
        </div>
        <div class="mb-2">
          <label for="extra_data" class="form-label">Authen/Cookie</label>
          <input type="text" class="form-control" id="extra_data" name="extra_data" value="{{ $resource->extra_data }}">
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="buyer_name" class="form-label">Người Mua</label>
            <input type="text" class="form-control" id="buyer_name" name="buyer_name" value="{{ $resource->buyer_name }}" disabled>
          </div>
          <div class="col-md-6">
            <label for="buyer_code" class="form-label">Mã Đơn</label>
            <input type="text" class="form-control" id="buyer_code" name="buyer_code" value="{{ $resource->buyer_code }}" disabled>
          </div>
        </div>
        <div class="mb-3">
          <button class="btn btn-primary w-100" type="submit">Cập nhật</button>
        </div>
      </form>
    </div>
    @isset($resource->parent)
      <div class="card-footer text-end">
        <a href="{{ route('admin.accountsv2.items', ['id' => $resource->parent->id, 'group' => $resource->parent->id, 'search' => $resource->code]) }}" class="btn btn-danger-gradient"><i class="fas fa-arrow-left"></i> Quay
          lại</a>
      </div>
    @endisset
  </div>
@endsection
