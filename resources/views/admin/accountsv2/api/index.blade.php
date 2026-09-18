@extends('admin.layouts.master')
@section('title', 'Admin: Cấu hình API Shop Clone')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Danh sách cấu hình API</div>
      <div class="d-flex h-100 items-center">
        <button class="btn btn-primary btn-sm" onclick="showCreateModal()"><i class="fa fa-plus-circle"></i> Thêm API mới</button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Tên</th>
              <th>Loại</th>
              <th>Danh mục</th>
              <th>Web</th>
              <th>Mã Giảm giá</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($apis as $api)
              <tr>
                <td>{{ $api->id }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-info-transparent" onclick="editApi({{ json_encode($api) }})"><i class="fa fa-edit"></i> Sửa</button>
                  <button type="button" class="btn btn-sm btn-danger-transparent" onclick="deleteApi({{ $api->id }})"><i class="fa fa-trash"></i> Xóa</button>
                </td>
                <td>{{ $api->name }}</td>
                <td><span class="badge bg-primary-transparent">{{ $api->type }}</span></td>
                <td><span class="badge bg-info-transparent">{{ $api->category ?? 'Chưa xác định' }}</span></td>
                <td>{{ $api->url }}</td>
                <td>{{ $api->coupon }}</td>
                <td>{{ $api->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal API -->
  <div class="modal fade" id="modal-api" tabindex="-1" aria-labelledby="modal-api-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-api-label">Thêm cấu hình API mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="api-form" action="{{ route('admin.accountsv2.api.store') }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <input type="hidden" name="id" id="api-id">
            <div class="mb-3">
              <label class="form-label">Tên cấu hình API <span class="text-danger">*</span></label>
              <input type="text" name="name" id="api-name" class="form-control" placeholder="Ví dụ: ShopClone7 API" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Đường dẫn website (URL) <span class="text-danger">*</span></label>
              <input type="url" name="url" id="api-url" class="form-control" placeholder="https://example.com" required>
              <small class="text-muted">Nhập URL gốc của website nguồn, ví dụ: https://kiyovn.top</small>
            </div>
            <div class="mb-3">
              <label class="form-label">Loại API</label>
              <select name="type" id="api-type" class="form-select" required>
                <option value="ShopClone7">ShopClone7</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Danh mục</label>
              <input type="text" name="category" id="api-category" class="form-control" placeholder="Ví dụ: Nhân vật, Trang bị, v.v...">
              <small class="text-muted">Phân loại sản phẩm từ API này</small>
            </div>
            <div class="mb-3">
              <label class="form-label">API Key <span class="text-danger">*</span></label>
              <input type="text" name="api_key" id="api-key" class="form-control" placeholder="Nhập API Key của bạn" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Mã Giảm giá</label>
              <input type="text" name="coupon" id="api-coupon" class="form-control" placeholder="Nhập mã giảm giá nếu có">
              <small class="text-muted">Mã giảm giá mặc định cho nguồn này</small>
            </div>
            <div class="modal-footer px-0 pb-0">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
              <button type="submit" class="btn btn-primary" id="btn-submit">Lưu lại</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    const apiModal = new bootstrap.Modal(document.getElementById('modal-api'));

    const showCreateModal = () => {
      resetForm();
      apiModal.show();
    }

    const resetForm = () => {
      $('#modal-api-label').text('Thêm cấu hình API mới');
      $('#btn-submit').text('Thêm ngay');
      $('#api-id').val('');
      $('#api-form')[0].reset();
      $('#api-form').attr('action', '{{ route('admin.accountsv2.api.store') }}');
    }

    const editApi = (api) => {
      resetForm();
      $('#modal-api-label').text('Sửa cấu hình API: ' + api.name);
      $('#btn-submit').text('Cập nhật');
      $('#api-form').attr('action', '{{ route('admin.accountsv2.api.update') }}');
      
      $('#api-id').val(api.id);
      $('#api-name').val(api.name);
      $('#api-type').val(api.type);
      $('#api-category').val(api.category);
      $('#api-url').val(api.url);
      $('#api-key').val(api.api_key);
      $('#api-coupon').val(api.coupon);
      
      apiModal.show();
    }

    const deleteApi = async (id) => {
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
        const { data: result } = await axios.post('{{ route('admin.accountsv2.api.delete') }}', {
          id,
          _token: '{{ csrf_token() }}'
        });

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành Công',
            message: result.message
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

    // Tự động điền tên nếu nhập URL (Nhập đến đâu hiện đến đó)
    $('#api-url').on('input', function() {
      const currentName = $('#api-name').val();
      const urlValue = $(this).val().trim();
      
      // Mirror exactly whatever is typed in the URL field to the Name field
      // but only if the user hasn't started typing a custom name yet or if they match.
      // For simplicity, as requested: mirror exactly.
      $('#api-name').val(urlValue);
    });
  </script>
@endsection
