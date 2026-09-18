@extends('admin.layouts.master')
@section('title', 'Admin: Create Accounts Group')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Sửa nhóm "{{ $group->name }}"</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accounts.groups.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <input type="hidden" name="id" value="{{ $group->id }}">
        <div class="mb-3">
          <label for="category" class="form-label">Chuyên mục</label>
          <select name="category_id[]" id="category" class="form-control" multiple>
            @php $selectedCategories = $group->categories->pluck('id')->toArray(); @endphp
            @foreach ($categories as $item)
              <option value="{{ $item->id }}" @if (in_array($item->id, $selectedCategories)) selected @endif>{{ $item->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Ảnh bìa</label>
          <input type="file" id="image" name="image" class="form-control">
        </div>
        <div class="mb-3">
          <label for="game_type" class="form-label">Loại game</label>
          <select name="game_type" id="game_type" class="form-control" required>
            <option value="game-khac">- Game Chung -</option>
            <option value="dot-kich" @if ($group->game_type === 'dot-kich') selected @endif>Đột Kích</option>
            <option value="thue-dot-kich" @if ($group->game_type === 'thue-dot-kich') selected @endif>Thuê Acc Đột Kích</option>
            <option value="lien-minh" @if ($group->game_type === 'lien-minh') selected @endif>Liên Minh Huyền Thoại</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="name" class="form-label">Tên nhóm</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên nhóm" value="{{ $group->name }}" required>
        </div>
        <div class="mb-3">
          <label for="sub_name" class="form-label">Tên nhóm dưới</label>
          <input type="text" class="form-control" id="sub_name" name="sub_name" placeholder="Nhập tên nhóm" value="{{ $group->sub_name }}">
        </div>
        <div class="mb-3">
          <label for="descr" class="form-label">Mô tả</label>
          <textarea class="form-control ckeditor" id="descr" name="descr" rows="3" placeholder="Nhập ghi chú">{{ $group->descr }}</textarea>
        </div>
        <div class="mb-3 row">
          <div class="col-md-6">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="title" name="meta_seo[title]" placeholder="Nhập tiêu đề" value="{{ $group->meta_seo['title'] ?? '' }}">
          </div>
          <div class="col-md-6">
            <label for="keywords" class="form-label">Từ khóa</label>
            <input type="text" class="form-control" id="keywords" name="meta_seo[keywords]" placeholder="Nhập từ khóa" value="{{ $group->meta_seo['keywords'] ?? '' }}">
          </div>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Trạng thái</label>
          <select class="form-control" id="status" name="status" required>
            <option value="1" @if ($group->status == 1) selected @endif>Hoạt động</option>
            <option value="0" @if ($group->status == 0) selected @endif>Tạm đóng</option>
          </select>
        </div>
              <div class="mb-3">
                <label for="priority" class="form-label">Ưu tiên</label>
                <input type="number" class="form-control" id="priority" name="priority" value="{{ $group->priority }}" required>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary w-100" type="submit">Cập nhật</button>
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

        if(document.getElementById('descr')){
            const editor = CKEDITOR.replace('descr', {
                // extraPlugins: 'notification', // Included in Full package
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