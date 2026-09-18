@extends('admin.layouts.master')
@section('title', 'Admin: Create Accounts Group')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Tạo nhóm mới (Shop Nick)</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accounts.groups.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
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
          <label for="image" class="form-label">Ảnh bìa</label>
          <input type="file" id="image" name="image" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="game_type" class="form-label">Loại game</label>
          <select name="game_type" id="game_type" class="form-control" required>
            <option value="game-khac">- Game Chung -</option>
            <option value="dot-kich">Đột Kích</option>
            <option value="thue-dot-kich">Thuê Acc Đột Kích</option>
            <option value="lien-minh">Liên Minh Huyền Thoại</option>
          </select>
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
    $(function () {
        new Choices('#category', {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: '-- Chọn chuyên mục --'
        });

        if(document.getElementById('descr')) {
            const editor = CKEDITOR.replace('descr', {
                //extraPlugins: 'notification',
                height: 300,
                clipboard_handleImages: false,
                filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
                filebrowserUploadMethod: 'form'
            });

            editor.on('fileUploadRequest', function (evt) {
                var xhr = evt.data.fileLoader.xhr;

                xhr.setRequestHeader('Cache-Control', 'no-cache');

                // CSRF FIX
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                if(window.userData && window.userData.access_token) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + window.userData.access_token);
                }
            });
        }
    })
</script>
@endsection

