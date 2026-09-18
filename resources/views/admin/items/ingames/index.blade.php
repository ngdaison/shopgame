@extends('admin.layouts.master')
@section('title', 'Admin: Items Group')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý tên nhân vật ingame</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>-</th>
              <th>Tên đợt</th>
              <th>Nhân vật</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($ingames as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="javascript:void(0)" class="shadow btn btn-primary btn-xs sharp me-1" data-bs-toggle="modal" data-bs-target="#modal-update-{{ $item->id }}"><i class="fa fa-edit"></i> sửa</a>
                  <a href="javascript:void(0)" class="shadow btn btn-danger btn-xs sharp" onclick="deleteRow({{ $item->id }})"><i class="fa fa-trash"></i> xóa</a>
                </td>
                <td>{{ $item->name }}</td>
                <td>
                  @foreach ($item->content as $name)
                    <span class="badge bg-danger">{{ $name }}</span> <br />
                  @endforeach
                </td>
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
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create"><i class="fa fa-edit"></i> Thêm danh sách mới</button>
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
          <form action="{{ route('admin.items.ingame.store') }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="name" class="form-label">Tên đợt</label>
              <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="content" class="form-label">Nhân vật</label>
              <textarea id="content" name="content" class="form-control" placeholder="Mỗi tên nhân vật là 1 dòng" rows="5" required></textarea>
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
    </div>
  </div>

  @foreach ($ingames as $item)
    <div class="modal fade" id="modal-update-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Thêm thông tin mới</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.items.ingame.update', ['id' => $item->id]) }}" method="POST" class="default-form axios-form" data-reload="true">
              @csrf
              <div class="mb-3">
                <label for="name" class="form-label">Tên đợt</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ $item->name }}" required>
              </div>
              <div class="mb-3">
                <label for="content" class="form-label">Nhân vật</label>
                <textarea id="content" name="content" class="form-control" placeholder="Mỗi tên nhân vật là 1 dòng" rows="5" required>{{ implode("\n", $item->content) }}</textarea>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status" required>
                  <option value="1">Hoạt động</option>
                  <option value="0" @if ($item->status === false) selected @endif>Tạm đóng</option>
                </select>
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
        } = await axios.post('{{ route('admin.items.ingame.delete') }}', {
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
