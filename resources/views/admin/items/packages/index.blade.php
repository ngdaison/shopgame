@extends('admin.layouts.master')
@section('title', 'Admin: Manage Packages - ' . $group->name)
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý các Gói trong nhóm: {{ $group->name }}</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Ưu tiên</th>
              <th>Thao tác</th>
              <th>Ảnh</th>
              <th>Tên gói</th>
              <th>Sản phẩm</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($packages as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>
                  <a href="javascript:void(0)" class="badge bg-primary-gradient text-white me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> sửa</a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient text-white me-1"><i class="fa fa-trash"></i> Xoá</a>
                </td>
                <td><img src="{{ $item->image }}" width="40"></td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->data()->count() }}</td>
                <td>
                  @if ($item->status == 1)
                    <span class="text-success">Hoạt động</span>
                  @else
                    <span class="text-danger">Tạm đóng</span>
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
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create"><i class="fa fa-edit"></i> Thêm gói mới</button>
      <a href="{{ route('admin.items.groups') }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại danh sách nhóm</a>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm gói mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.items.packages.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <input type="hidden" name="group_id" value="{{ $group->id }}">
            <div class="mb-3">
              <label for="name" class="form-label">Tên gói</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên gói" required>
            </div>
            <div class="mb-3">
              <label for="image" class="form-label">Ảnh</label>
              <input type="file" id="image" name="image" class="form-control">
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Trạng thái</label>
              <select class="form-control" id="status" name="status" required>
                <option value="1">Hoạt động</option>
                <option value="0">Tạm đóng</option>
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

  @foreach ($packages as $item)
    <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật gói #{{ $item->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.items.packages.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="id" value="{{ $item->id }}">
              <div class="mb-3">
                <label for="name" class="form-label">Tên gói</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên gói" value="{{ $item->name }}" required>
              </div>
              <div class="mb-3">
                <label for="image" class="form-label">Ảnh</label>
                <input type="file" id="image" name="image" class="form-control">
                @if ($item->image)
                    <img src="{{ $item->image }}" width="40" class="mt-2 text-center">
                @endif
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status" required>
                  <option value="1" @if ($item->status == 1) selected @endif>Hoạt động</option>
                  <option value="0" @if ($item->status == 0) selected @endif>Tạm đóng</option>
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
        } = await axios.post('{{ route('admin.items.packages.delete') }}', {
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
        axios.post('{{ route('admin.items.packages.update-priority') }}', {
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
@endsection
