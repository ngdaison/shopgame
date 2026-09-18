@extends('admin.layouts.master')
@section('title', 'Admin: Create Accounts Group')
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
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Tạo nhóm mới (Shop Nick V2)</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accountsv2.groups.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <label for="category" class="form-label">Chuyên mục</label>
          <select name="category_id[]" id="category" class="form-control" multiple>
            @foreach ($categories as $item)
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label for="priority" class="form-label">Ưu tiên</label>
          <input type="number" id="priority" name="priority" class="form-control" value="0" required>
          <i>Số ưu tiên lớn thì nó hiện ở đầu</i>
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Ảnh bìa</label>
          <input type="file" id="image" name="image" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="name" class="form-label">Tên nhóm</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên nhóm" required>
        </div>
        <div class="mb-3">
          <label for="sub_name" class="form-label">Tên nhóm dưới</label>
          <input type="text" class="form-control" id="sub_name" name="sub_name" placeholder="Nhập tên nhóm dưới">
        </div>
        <div class="mb-3">
          <label for="descr" class="form-label">Mô tả</label>
          <textarea class="form-control ckeditor" id="descr" name="descr" rows="3" placeholder="Nhập ghi chú"></textarea>
        </div>
        <div class="mb-3 row">
          <div class="col-md-6">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="title" name="meta_seo[title]" placeholder="Nhập tiêu đề">
          </div>
          <div class="col-md-6">
            <label for="keywords" class="form-label">Từ khóa</label>
            <input type="text" class="form-control" id="keywords" name="meta_seo[keywords]" placeholder="Nhập từ khóa">
          </div>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Trạng thái</label>
          <select class="form-control" id="status" name="status" required>
            <option value="1">Hoạt động</option>
            <option value="0">Tạm đóng</option>
          </select>
        </div>
        <div class="mb-3">
          <button class="btn btn-primary w-100" type="submit">Thêm mới</button>
        </div>
      </form>
    </div>
  </div>
@endsection
@section('scripts')
  <script src="/plugins/ckeditor/ckeditor.js"></script>

  <script>
    $(document).ready(function() {
      new Choices('#category', {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: '-- Chọn chuyên mục --'
      });

      if(document.getElementById('descr')){
          const editor = CKEDITOR.replace('descr', {
            // extraPlugins: 'notification',
            height: 300,
            clipboard_handleImages: false,
            filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
            filebrowserUploadMethod: 'form'
          });

          editor.on('fileUploadRequest', function(evt) {
            var xhr = evt.data.fileLoader.xhr;

            xhr.setRequestHeader('Cache-Control', 'no-cache');
            if(window.userData && window.userData.access_token) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + window.userData.access_token);
            }
          })
      }
    })
  </script>
@endsection
