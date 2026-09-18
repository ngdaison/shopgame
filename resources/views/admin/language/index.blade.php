@extends('admin.layouts.master')
@section('title', 'Admin: Language Settings')
@section('styles')
<style>
  .choices__inner {
      min-height: 44px;
      border-radius: 0.375rem !important;
  }
  
  .choices__list--dropdown {
      z-index: 1056 !important;
      background-color: #fff !important;
      color: #333 !important;
      border: 1px solid #ddd;
  }
  
  .choices__list--dropdown .choices__item--selectable.is-highlighted {
      background-color: #7367f0 !important;
      color: #fff !important;
  }

  .modal-content, .modal-body {
      overflow: visible !important;
  }
</style>
@endsection
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý ngôn ngữ</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Ngôn ngữ</th>
              <th>Mã ISO</th>
              <th>Trạng thái</th>
            </tr>
          <tbody>
            @foreach ($languages as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    @php
                        $defaultLocale = Helper::getDefaultLocale();
                        $isThisDefault = ($item->iso_code === $defaultLocale);
                    @endphp
                    @if($isThisDefault)
                        <span class="badge bg-warning">Mặc định</span>
                        <a href="{{ route('admin.language.translation', ['id' => $item->id]) }}" class="badge bg-success-gradient text-white">
                          <i class="fa fa-language"></i> dịch
                        </a>
                    @else
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}" class="badge bg-primary-gradient text-white">
                        <i class="fa fa-edit"></i> sửa
                        </a>
                        <a href="{{ route('admin.language.theme', ['id' => $item->id]) }}" class="badge bg-info-gradient text-white">
                        <i class="fa fa-palette"></i> cấu hình
                        </a>
                        <a href="{{ route('admin.language.translation', ['id' => $item->id]) }}" class="badge bg-success-gradient text-white">
                        <i class="fa fa-language"></i> dịch
                        </a>
                        <a href="javascript:deleteRow('{{ $item->id }}')" class="badge bg-danger-gradient text-white">
                        <i class="fa fa-trash"></i> xoá
                        </a>
                    @endif
                  </div>
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->iso_code }}</td>

                <td>
                    @if($item->status)
                    <span class="badge bg-success">Hoạt động</span>
                    @else
                    <span class="badge bg-secondary">Ẩn</span>
                    @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#modal-create">Thêm ngôn ngữ mới</button>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm ngôn ngữ mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.language.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Tên ngôn ngữ</label>
                <input class="form-control" type="text" id="name" name="name" placeholder="English" required>
            </div>
            <div class="mb-3">
                <label for="iso_code" class="form-label">ISO Code</label>
                <input class="form-control" type="text" id="iso_code" name="iso_code" placeholder="en" required maxlength="5">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status">
                    <option value="1">Kích hoạt</option>
                    <option value="0">Tắt</option>
                </select>
            </div>

            <div class="mb-3">
              <button class="btn btn-primary-gradient w-100" type="submit">Thêm mới</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  @foreach ($languages as $value)
    <div class="modal fade" id="modal-edit-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật #{{ $value->name }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.language.update', $value->id) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
              @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Tên ngôn ngữ</label>
                <input class="form-control" type="text" name="name" value="{{ $value->name }}" required>
            </div>
            <div class="mb-3">
                <label for="iso_code" class="form-label">ISO Code</label>
                <input class="form-control" type="text" name="iso_code" value="{{ $value->iso_code }}" required maxlength="5">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" name="status">
                    <option value="1" @if ($value->status) selected @endif>Kích hoạt</option>
                    <option value="0" @if (!$value->status) selected @endif>Tắt</option>
                </select>
            </div>

              <div class="mb-3">
                <button class="btn btn-primary-gradient w-100" type="submit">Cập nhật</button>
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
        } = await axios.post('{{ route('admin.language.delete') }}', {
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
