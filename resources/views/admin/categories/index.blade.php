@extends('admin.layouts.master')
@section('title', 'Admin: Quản lý Chuyên mục')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý chuyên mục</div>
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
              <th>Tên chuyên mục</th>
              <th>Tên chuyên mục dưới</th>
              <th>Số nhóm con</th>
              <th>Người tạo</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($categories as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>
                  <div class="d-flex justify-content-center">
                    <a href="javascript:void(0)" class="badge bg-primary-gradient text-white me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> sửa</a>
                    <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient me-1"><i class="fa fa-trash"></i> xoá</a>
                  </div>
                </td>
                <td>
                  @if(!empty($item->image))
                    <img src="{{ $item->image }}" alt="" style="width: 25px; height: 25px; border-radius: 5px; object-fit: cover;">
                  @else
                    <i class="fa fa-image text-muted" style="font-size: 18px;"></i>
                  @endif
                </td>
                <td>{{ $item->name }}</td>
                <td>
                  @if(!empty($item->sub_name))
                    <span>{{ $item->sub_name }}</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex flex-wrap justify-content-center gap-1">
                    {{-- Boosting Groups --}}
                    @foreach ($item->gbGroups()->where('status', true)->get() as $g)
                      <span class="badge bg-primary-transparent">[Boost] {{ $g->name }}</span>
                    @endforeach
                    
                    {{-- Item Groups --}}
                    @foreach ($item->itemGroups()->where('status', true)->get() as $g)
                      <span class="badge bg-info-transparent">[Item] {{ $g->name }}</span>
                    @endforeach

                    {{-- Account Groups --}}
                    @foreach ($item->accountGroups()->where('status', true)->get() as $g)
                      <span class="badge bg-success-transparent">[Acc] {{ $g->name }}</span>
                    @endforeach

                    {{-- AccountV2 Groups --}}
                    @foreach ($item->accountV2Groups()->where('status', true)->get() as $g)
                      <span class="badge bg-warning-transparent">[AccV2] {{ $g->name }}</span>
                    @endforeach

                    @if($item->gbGroups()->count() === 0 && $item->itemGroups()->count() === 0 && $item->accountGroups()->count() === 0 && $item->accountV2Groups()->count() === 0)
                      <span class="text-muted small">Chưa có nhóm</span>
                    @endif
                  </div>
                </td>
                <td>{{ $item->username }}</td>
                <td>
                  @if ($item->status == 'active')
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
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create"><i class="fa fa-edit"></i> Thêm chuyên mục mới</button>
    </div>
  </div>

  @foreach ($categories as $item)
    <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật chuyên mục #{{ $item->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.categories.update') }}" method="POST" class="default-form axios-form" data-reload="true" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="id" value="{{ $item->id }}">
              <div class="mb-3">
                <label for="image" class="form-label">Hình ảnh / Icon</label>
                <input type="file" class="form-control" id="image" name="image">
                @if ($item->image)
                  <img src="{{ $item->image }}" alt="" style="width: 100px; height: 100px; border-radius: 5px; margin-top: 10px;">
                @endif
              </div>

              <div class="mb-3">
                  <label for="priority" class="form-label">Ưu tiên</label>
                  <input type="number" class="form-control" id="priority" name="priority" value="{{ $item->priority }}" required>
                </div>
              <div class="mb-3">
                <label for="name" class="form-label">Tên chuyên mục</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên chuyên mục" value="{{ $item->name }}" required>
              </div>
              <div class="mb-3">
                <label for="sub_name" class="form-label">Tên chuyên mục dưới</label>
                <input type="text" class="form-control" id="sub_name" name="sub_name" placeholder="Nhập tên phụ (tùy chọn)" value="{{ $item->sub_name }}">
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status" required>
                  <option value="active" {{ $item->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                  <option value="draft" {{ $item->status == 'draft' ? 'selected' : '' }}>Tạm đóng</option>
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

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm thông tin mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.categories.store') }}" method="POST" class="default-form axios-form" data-reload="true" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="image" class="form-label">Hình ảnh / Icon</label>
              <input type="file" class="form-control" id="image" name="image">
            </div>

            <div class="mb-3">
              <label for="priority" class="form-label">Ưu tiên</label>
              <input type="number" class="form-control" id="priority" name="priority" value="0" required>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Tên chuyên mục</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên chuyên mục" required>
            </div>
            <div class="mb-3">
              <label for="sub_name" class="form-label">Tên chuyên mục dưới</label>
              <input type="text" class="form-control" id="sub_name" name="sub_name" placeholder="Nhập tên phụ (tùy chọn)">
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Trạng thái</label>
              <select class="form-control" id="status" name="status" required>
                <option value="active">Hoạt động</option>
                <option value="draft">Tạm đóng</option>
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
        } = await axios.post('{{ route('admin.categories.delete') }}', {
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
        axios.post('{{ route('admin.categories.update-priority') }}', {
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