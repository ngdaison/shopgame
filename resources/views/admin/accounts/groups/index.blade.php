@extends('admin.layouts.master')
@section('title', 'Admin: Accounts Group')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý nhóm của Shop Nick</div>
      <div class="d-flex flex-wrap gap-2">
        <form action="{{ route('admin.accounts.groups') }}" method="GET" class="d-flex gap-2">
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
              <th>Tên nhóm dưới</th>
              <th>Đã bán</th>
              <th>Còn lại</th>
              <th>Doanh thu</th>
              <th>Trạng thái</th>
              <th>Chuyên mục</th>
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
                  <a href="{{ route('admin.accounts.groups.edit', ['id' => $item->id]) }}" class="badge bg-primary-gradient text-white me-1"><i class="fa fa-edit"></i> sửa</a>
                  <a href="{{ route('admin.accounts.items', ['id' => $item->id]) }}" class="badge bg-info-gradient text-white me-1"><i class="fa fa-eye"></i> Xem</a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient text-white me-1"><i class="fa fa-trash"></i> Xoá</a>
                </td>
                <td><img src="{{ $item->image }}" width="40"></td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->sub_name }}</td>
                <td><span class="text-primary">{{ $item->sold_count }}</span> nick</td>
                <td><span class="text-danger">{{ $item->in_stock }}</span> nick</td>
                <td>{{ Helper::formatCurrency($item->revenue) }}</td>
                <td>
                  @if ($item->status == 1)
                    <span class="text-success">Hoạt động</span>
                  @else
                    <span class="text-danger">Tạm đóng</span>
                  @endif
                </td>
                <td>
                  @foreach ($item->categories as $cat)
                    <span class="badge bg-primary-transparent">{{ $cat->name }}</span>
                  @endforeach
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
      <a href="{{ route('admin.accounts.groups.create') }}" class="btn btn-primary"><i class="fa fa-edit"></i> Thêm nhóm mới</a>
      <a href="{{ route('admin.categories') }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại danh sách chuyên mục</a>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm thông tin mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

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
        } = await axios.post('{{ route('admin.accounts.groups.delete') }}', {
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
        axios.post('{{ route('admin.accounts.groups.update-priority') }}', {
            id: id,
            priority: val,
            _token: '{{ csrf_token() }}'
        }).then(res => {
            if (res.data.status) {
                toastr.success(res.data.message || 'Cập nhật thứ tự thành công!', 'Thành Công');
            } else {
                toastr.error(res.data.message || 'Đã có lỗi xảy ra', 'Thất Bại!');
            }
        }).catch(err => {
            toastr.error($catchMessage(err), 'Lỗi!');
        });
    }, 0);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });
  </script>
@endsection
