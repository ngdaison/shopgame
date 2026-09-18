@extends('admin.layouts.master')
@section('title', 'Admin: Items Group')
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
      <div class="card-title">Quản lý nhóm của Dịch vụ vật phẩm</div>
      <div class="d-flex flex-wrap gap-2">
        <form action="{{ route('admin.items.groups') }}" method="GET" class="d-flex gap-2">
          <select name="category_id" class="form-select form-select-sm" style="width: 200px;">
            <option value="">-- Tất cả chuyên mục --</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
        </form>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Ưu tiên</th>
              <th>Thao tác</th>
              <th>Ảnh / Icon</th>
              <th>Tên nhóm</th>
              <th>Chuyên mục</th>
              <th>Tên nhóm dưới</th>
              <th>Sản phẩm</th>
              <th>Trạng thái</th>
              <th>Canh giữa</th>
              <th>Người tạo</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($groups as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>
                  <a href="{{ route('admin.items.packages', ['id' => $item->id]) }}" class="badge bg-primary-gradient text-white me-1"><i class="fa fa-list"></i> Gói</a>
                  <a href="javascript:void(0)" class="badge bg-primary-gradient text-white me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> sửa</a>
                  <a href="{{ route('admin.items.data', ['id' => $item->id]) }}" class="badge bg-primary-gradient text-white me-1"><i class="fa fa-eye"></i> Xem</a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient text-white me-1"><i class="fa fa-trash"></i> Xoá</a>
                </td>
                 <td><img src="{{ $item->image }}" width="40"></td>
                 <td>{{ $item->name }}</td>
                <td>
                  @foreach ($item->categories as $cat)
                    <span class="badge bg-primary-transparent">{{ $cat->name }}</span>
                  @endforeach
                </td>
                <td>{{ $item->sub_name }}</td>
                <td>{{ $item->data()->count() }}</td>
                <td>
                  @if ($item->status == 1)
                    <span class="text-success">Hoạt động</span>
                  @else
                    <span class="text-danger">Tạm đóng</span>
                  @endif
                </td>
                <td>
                  @if ($item->is_center == 1)
                    <span class="text-success">Có</span>
                  @else
                    <span class="text-danger">Không</span>
                  @endif
                </td>
                <td>{{ $item->username }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create"><i class="fa fa-edit"></i> Thêm nhóm mới</button>
      <a href="{{ route('admin.categories') }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại danh sách chuyên mục</a>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm thông tin mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.items.groups.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="category" class="form-label">Chuyên mục</label>
              <select name="category_id[]" id="category" class="form-control category-select" multiple>
                @foreach ($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label for="image" class="form-label">Ảnh bìa</label>
              <input type="file" id="image" name="image" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="image_package" class="form-label">Ảnh gói (Tất cả)</label>
              <input type="file" id="image_package" name="image_package" class="form-control">
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
              <label for="descr-create" class="form-label">Mô tả</label>
              <textarea class="form-control ckeditor" id="descr-create" name="descr" rows="3" placeholder="Nhập ghi chú"></textarea>
            </div>

            <div class="mb-3">
              <label for="status" class="form-label">Trạng thái</label>
              <select class="form-control" id="status" name="status" required>
                <option value="1">Hoạt động</option>
                <option value="0">Tạm đóng</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="is_center" class="form-label">Canh giữa package</label>
              <select class="form-control" id="is_center" name="is_center" required>
                <option value="1">Có</option>
                <option value="0">Không</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="priority" class="form-label">Ưu tiên</label>
              <input type="number" class="form-control" id="priority" name="priority" value="0" required>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary w-100" type="submit">Thêm mới</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  @foreach ($groups as $item)
    <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật nhóm #{{ $item->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.items.groups.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="id" value="{{ $item->id }}">
              <div class="mb-3">
                <label for="category" class="form-label">Chuyên mục</label>
                <select name="category_id[]" id="category-{{ $item->id }}" class="form-control category-select" multiple>
                  @php $selectedCategories = $item->categories->pluck('id')->toArray(); @endphp
                  @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @if (in_array($cat->id, $selectedCategories)) selected @endif>{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label for="image" class="form-label">Ảnh bìa</label>
                <input type="file" id="image" name="image" class="form-control">
              </div>
              <div class="mb-3">
                <label for="image_package" class="form-label">Ảnh gói (Tất cả)</label>
                <input type="file" id="image_package" name="image_package" class="form-control">
                @if ($item->image_package)
                  <img src="{{ $item->image_package }}" width="40" class="mt-2">
                @endif
              </div>
              <div class="mb-3">
                <label for="name" class="form-label">Tên nhóm</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên nhóm" value="{{ $item->name }}" required>
              </div>
              <div class="mb-3">
                <label for="sub_name" class="form-label">Tên nhóm dưới</label>
                <input type="text" class="form-control" id="sub_name" name="sub_name" placeholder="Nhập tên nhóm dưới" value="{{ $item->sub_name }}">
              </div>
              <div class="mb-3">
                <label for="descr-{{ $item->id }}" class="form-label">Mô tả</label>
                <textarea class="form-control ckeditor" id="descr-{{ $item->id }}" name="descr" rows="3" placeholder="Nhập ghi chú">{{ $item->descr }}</textarea>
              </div>

              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status" required>
                  <option value="1" @if ($item->status == 1) selected @endif>Hoạt động</option>
                  <option value="0" @if ($item->status == 0) selected @endif>Tạm đóng</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="is_center" class="form-label">Canh giữa package</label>
                <select class="form-control" id="is_center" name="is_center" required>
                  <option value="1" @if ($item->is_center == 1) selected @endif>Có</option>
                  <option value="0" @if ($item->is_center == 0) selected @endif>Không</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="priority" class="form-label">Ưu tiên</label>
                <input type="number" class="form-control" id="priority" name="priority" value="{{ $item->priority }}" required>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary w-100" type="submit">Cập nhật</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection
@section('scripts')
  <script>
    const deleteRow = async (id) => {
      const confirmDelete = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
      });

      if (!confirmDelete.isConfirmed) return;

      $showLoading();

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.items.groups.delete') }}', {
          id,
          _token: '{{ csrf_token() }}'
        })

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành Công',
            message: result.message || 'Xóa thành công'
          }));
          location.reload();
        } else {
          $hideLoading();
          toastr.error(result.message, 'Thất Bại');
        }
      } catch (error) {
        $hideLoading();
        toastr.error($catchMessage(error), 'Thất Bại');
      }
    }

    const updatePriority = $debounce(function(id, val) {
        if (typeof pageOverlay !== 'undefined') pageOverlay.show();
        axios.post('{{ route('admin.items.groups.update-priority') }}', {
            id: id,
            priority: val,
            _token: '{{ csrf_token() }}'
        }).then(res => {
            if (res.data.status) {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: res.data.message || 'Cập nhật thứ tự thành công'
                }));
                location.reload();
            } else {
                if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
                toastr.error(res.data.message, 'Thất Bại');
            }
        }).catch(err => {
            if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
            toastr.error('Lỗi cập nhật ưu tiên', 'Thất Bại');
        });
    }, 500);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });
  </script>
  <script src="/plugins/ckeditor/ckeditor.js"></script>

  <script>
    $(function() {
      $('.category-select').each(function() {
          new Choices(this, {
              removeItemButton: true,
              placeholder: true,
              placeholderValue: '-- Chọn chuyên mục --'
          });
      });

      $('.ckeditor').each(function () {
          if(document.getElementById(this.id)) {
            const editor = CKEDITOR.replace(this.id, {
                // extraPlugins: 'notification', // Included in Full package
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
      });
    })
  </script>
@endsection
