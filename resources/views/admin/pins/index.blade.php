@extends('admin.layouts.master')
@section('title', 'Admin: Pin Groups')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý danh sách được ghim</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Tên / Ảnh</th>
              <th>Mở bằng</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pin_groups as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <button class="badge bg-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}">Sửa</button>
                  <button class="badge bg-danger" onclick="deleteRow({{ $item->id }})">Xóa</button>
                </td>
                <td>
                  <a href="{{ $item->image }}" target="_blank">{{ $item->name }}</a>
                </td>
                <td>{{ $item->open_type }}</td>
                <td>
                  @if ($item->status === true)
                    <span class="badge bg-success">Hoạt động</span>
                  @else
                    <span class="badge bg-danger">Không hoạt động</span>
                  @endif
                </td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#modal-create">Thêm thông tin</button>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm thông tin</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.pin-groups.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="image" class="form-label">Ảnh / Icon</label>
              <input class="form-control" type="file" id="image" name="image" required>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Tiêu đề</label>
              <input class="form-control" type="text" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="link" class="form-label">Liên kết</label>
              <input class="form-control" type="text" id="link" name="link" placeholder="/cay-thue/cay-thue-blox-fruits-9726 hoặc https://example.com" required>
            </div>
            <div class="mb-3">
              <label for="open_type" class="form-label">Mở bằng</label>
              <select class="form-control" id="open_type" name="open_type" required>
                <option value="_blank">Mở trong cửa sổ mới</option>
                <option value="_self">Mở trong cùng cửa sổ</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Trạng thái</label>
              <select class="form-control" id="status" name="status">
                <option value="1">Hoạt động</option>
                <option value="0">Không hoạt động</option>
              </select>
            </div>
            <div class="mb-3">
              <button class="btn btn-danger-gradient w-100" type="submit">Thêm mới</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  @foreach ($pin_groups as $value)
    <div class="modal fade" id="modal-edit-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật thông tin #{{ $value->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.pin-groups.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="id" value="{{ $value->id }}">
              <div class="mb-3">
                <label for="image" class="form-label">Ảnh / Icon</label>
                <input class="form-control" type="file" id="image" name="image">
              </div>
              <div class="mb-3">
                <label for="name" class="form-label">Tiêu đề</label>
                <input class="form-control" type="text" id="name" name="name" value="{{ $value->name }}" required>
              </div>
              <div class="mb-3">
                <label for="link" class="form-label">Liên kết</label>
                <input class="form-control" type="text" id="link" name="link" value="{{ $value->link }}" placeholder="/cay-thue/cay-thue-blox-fruits-9726 hoặc https://example.com" required>
              </div>
              <div class="mb-3">
                <label for="open_type" class="form-label">Mở bằng</label>
                <select class="form-control" id="open_type" name="open_type" required>
                  <option value="_blank" @if ($value->open_type === '_blank') selected @endif>Mở trong cửa sổ mới</option>
                  <option value="_self" @if ($value->open_type === '_self') selected @endif>Mở trong cùng cửa sổ</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status">
                  <option value="1" @if ($value->status) selected @endif>Hoạt động</option>
                  <option value="0" @if (!$value->status) selected @endif>Không hoạt động</option>
                </select>
              </div>
              <div class="mb-3">
                <button class="btn btn-danger-gradient w-100" type="submit">Thêm mới</button>
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
        } = await axios.post('{{ route('admin.pin-groups.delete') }}', {
          id
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
  </script>
@endsection
