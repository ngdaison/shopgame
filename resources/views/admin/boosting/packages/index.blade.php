@extends('admin.layouts.master')
@section('title', 'Admin: Boosting Package')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý các gói (Packages) thuộc nhóm "{{ $group->name }}"</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Ưu tiên</th>
              <th>Thao tác</th>
              <th>Tên Gói</th>
              <th>Số sản phẩm</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($group->packages as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>
                  <div class="d-flex justify-content-center">
                    <a href="javascript:void(0)" class="badge bg-primary-gradient text-white me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> sửa</a>
                    <a href="{{ route('admin.boosting.products', ['id' => $item->id]) }}" class="badge bg-success-gradient text-white me-1"><i class="fa fa-eye"></i> xem</a>
                    <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient me-1"><i class="fa fa-trash"></i> xoá</a>
                  </div>
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->products->count() }} sản phẩm</td>
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
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create"><i class="fa fa-plus"></i> Thêm gói mới</button>
      <a href="{{ route('admin.boosting.groups', ['id' => $group->category_id]) }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại danh sách nhóm</a>
    </div>
  </div>

  @foreach ($group->packages as $item)
    <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật gói #{{ $item->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.boosting.packages.update', ['id' => $item->id]) }}" method="POST" class="default-form axios-form" data-reload="true">
              @csrf

              <div class="mb-3">
                <label for="name" class="form-label">Tên gói (Package)</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên gói" value="{{ $item->name }}" required>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status" required>
                  <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Hoạt động</option>
                  <option value="0" {{ $item->status == 0 ? 'selected' : '' }}>Tạm đóng</option>
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

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm gói mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.boosting.packages.store', ['id' => $group->id]) }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label">Tên gói (Package)</label>
              <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Level, Beli,..." required>
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
        } = await axios.post('{{ route('admin.boosting.packages.delete') }}', {
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

    const updatePriority = $debounce(function(id, val) {
        axios.post('{{ route('admin.boosting.packages.update-priority') }}', {
            id: id,
            priority: val,
            _token: '{{ csrf_token() }}'
        }).then(res => {
            if (res.data.status) {
                toastr.success(res.data.message || 'Cập nhật thứ tự thành công!', 'Thành Công');
            } else {
                toastr.error(res.data.message, 'Thất Bại!');
            }
        }).catch(err => {
            toastr.error('Lỗi cập nhật ưu tiên', 'Lỗi!');
        });
    }, 0);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });
  </script>
@endsection

