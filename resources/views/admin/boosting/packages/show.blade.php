@extends('admin.layouts.master')
@section('title', 'Admin: Boosting Package Edit #' . $package->id)
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Cập nhật sản phẩm vào nhóm</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.boosting.packages.update', ['id' => $package->id]) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="name" class="form-label">Tên sản phẩm</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $package->name) }}" placeholder="Tên sản phẩm cần bán" required>
          </div>
          <div class="col-md-6">
            <label for="price" class="form-label">Giá sản phẩm</label>
            <input type="text" class="form-control" id="price" name="price" value="{{ old('price', $package->price) }}" required>
          </div>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Trạng thái</label>
          <select class="form-control" id="status" name="status" required>
            <option value="1" @if ($package->status === true) selected @endif>Đang bán</option>
            <option value="0" @if ($package->status === false) selected @endif>Chưa bán</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="descr" class="form-label">Mô tả sản phẩm</label>
          <textarea class="form-control ckeditor" id="descr" name="descr" rows="3">{{ old('descr', $package->descr) }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary">Sửa sản phẩm</button>
        </div>
      </form>
    </div>
  </div>
@endsection
