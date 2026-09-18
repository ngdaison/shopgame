@extends('admin.layouts.master')
@section('title', 'Admin: Posts Management')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý bài viết</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Ưu tiên</th>
              <th>Thao tác</th>
              <th>Ảnh bìa</th>
              <th>Bài viết</th>
              <th>Trạng thái</th>
              <th>Người viết</th>
              <th>Ngày viết</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($posts as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" 
                         value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>
                  <a href="{{ route('admin.posts.show', ['id' => $item->id]) }}" class="badge bg-primary-gradient me-1"><i class="fa fa-pencil"></i> sửa</a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient me-1"><i class="fa fa-trash"></i> xoá</a>
                </td>
                <td>
                  <img src="{{ Helper::getValidImage($item->thumbnail) }}" width="25">
                </td>
                <td>{{ $item->title }}</td>
                <td>
                  @if ($item->status == 1)
                    <span class="text-success">Đang hiện</span>
                  @else
                    <span class="text-danger">Đang ẩn</span>
                  @endif
                </td>
                <td>{{ $item->author_name }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <a href="{{ route('admin.posts.create') }}" class="btn btn-primary-gradient">Tạo bài viết mới</a>
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
        } = await axios.post('{{ route('admin.posts.delete') }}', {
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
        axios.post('{{ route('admin.posts.update-priority') }}', {
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
                toastr.error(res.data.message || 'Đã có lỗi xảy ra', 'Thất Bại');
            }
        }).catch(err => {
            if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
            toastr.error($catchMessage(err), 'Thất Bại');
        });
    }, 500);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });
  </script>
@endsection
