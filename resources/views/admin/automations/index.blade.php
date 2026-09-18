@extends('admin.layouts.master')
@section('title', 'Quản lý Tác vụ Tự động')
@section('content')

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">Danh sách Tác vụ Tự động (Automations)</div>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fa fa-plus"></i> Thêm Task Mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable1">
                <thead>
                    <tr>
                        <th>Tên công việc</th>
                        <th>Loại công việc</th>
                        <th>Thời gian kiểm tra (Giờ)</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($automations as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>
                                @if($item->type == 'delete_no_deposit_user')
                                    <span class="badge bg-danger">Xóa User không nạp tiền</span>
                                @elseif($item->type == 'delete_logs')
                                    <span class="badge bg-warning">Xóa Logs</span>
                                @elseif($item->type == 'delete_inactive_no_deposit_user')
                                    <span class="badge bg-info">Xóa User chưa nạp tiền không hoạt động</span>
                                @elseif($item->type == 'delete_notifications')
                                    <span class="badge bg-primary">Xóa Thông báo</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->type }}</span>
                                @endif
                            </td>
                            <td>
                                <b>{{ number_format($item->time_in_hours) }}</b> giờ
                                <br>
                                <small class="text-muted">~ {{ round($item->time_in_hours / 24, 1) }} ngày</small>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" onchange="toggleStatus({{ $item->id }}, this.checked)" {{ $item->status ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $item->status ? 'Bật' : 'Tắt' }}</label>
                                </div>
                            </td>
                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editItem({{ json_encode($item) }})">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <a href="javascript:deleteRow({{ $item->id }})" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="automationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('admin.automations.store') }}" method="POST" class="default-form axios-form" data-reload="true">
          @csrf
          <input type="hidden" name="id" id="item_id">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Thêm Tác vụ Mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            
            <div class="mb-3">
                <label class="form-label">Tên công việc <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Ví dụ: Xóa user rác sau 30 ngày" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Loại công việc <span class="text-danger">*</span></label>
                <select class="form-control" name="type" id="type" required onchange="checkType()">
                    <option value="delete_no_deposit_user">Xóa User không nạp tiền</option>
                    <option value="delete_inactive_no_deposit_user">Xóa User chưa nạp tiền không hoạt động</option>
                    <option value="delete_logs">Xóa Logs</option>
                    <option value="delete_notifications">Xóa Thông báo</option>
                </select>
                <small class="text-muted d-block mt-1">Lưu ý: Với loại công việc này, hệ thống chỉ cho phép tồn tại 1 Task duy nhất.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Thời gian kiểm tra (Giờ) <span class="text-danger">*</span></label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control" name="time_in_hours" id="time_in_hours" required min="1">
                    <span class="input-group-text">Giờ</span>
                </div>
                
                <div class="d-flex flex-wrap gap-2" id="quick-select">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(24, this)">1 ngày</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(72, this)">3 ngày</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(168, this)">7 ngày</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(720, this)">30 ngày</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(2160, this)">3 tháng</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(4320, this)">6 tháng</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(8760, this)">1 năm</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTime(17520, this)">2 năm</button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select class="form-control" name="status" id="status">
                    <option value="1">Bật</option>
                    <option value="0">Tắt</option>
                </select>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary">Lưu Task</button>
          </div>
      </form>
    </div>
  </div>
</div>

@section('scripts')
<script>
    function setTime(hours, btn) {
        document.getElementById('time_in_hours').value = hours;
        
        // Remove active class from all buttons
        document.querySelectorAll('#quick-select button').forEach(b => {
            b.classList.remove('btn-primary', 'text-white');
            b.classList.add('btn-outline-secondary');
        });

        // Add active class to clicked button
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary', 'text-white');
    }

    function showAddModal() {
        document.getElementById('item_id').value = '';
        document.getElementById('modalTitle').innerText = 'Thêm Tác vụ Mới';
        
        document.getElementById('name').value = '';
        document.getElementById('type').value = 'delete_no_deposit_user';
        document.getElementById('time_in_hours').value = '';
        document.getElementById('status').value = '1';
        
        // Reset active buttons
        document.querySelectorAll('#quick-select button').forEach(b => {
            b.classList.remove('btn-primary', 'text-white');
            b.classList.add('btn-outline-secondary');
        });
        
        // Update form action
        document.querySelector('form.axios-form').action = '{{ route('admin.automations.store') }}';

        var myModal = new bootstrap.Modal(document.getElementById('automationModal'));
        myModal.show();
    }

    function editItem(item) {
        document.getElementById('item_id').value = item.id;
        document.getElementById('modalTitle').innerText = 'Sửa Tác vụ';
        
        document.getElementById('name').value = item.name;
        document.getElementById('type').value = item.type;
        document.getElementById('time_in_hours').value = item.time_in_hours;
        document.getElementById('status').value = item.status ? '1' : '0';

        // Check matching time button
        document.querySelectorAll('#quick-select button').forEach(b => {
            // Extract hours from onclick attribute or map it if needed. 
            // Simple way: get the hours from the function call in onclick
            // Regex to extract number from onclick="setTime(24, this)"
            const match = b.getAttribute('onclick').match(/setTime\((\d+)/);
            if (match && parseInt(match[1]) == item.time_in_hours) {
                b.classList.remove('btn-outline-secondary');
                b.classList.add('btn-primary', 'text-white');
            } else {
                b.classList.remove('btn-primary', 'text-white');
                b.classList.add('btn-outline-secondary');
            }
        });

        // Update form action to update endpoint
        document.querySelector('form.axios-form').action = '{{ route('admin.automations.update') }}';

        var myModal = new bootstrap.Modal(document.getElementById('automationModal'));
        myModal.show();
    }
    
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

      if (window.pageOverlay) window.pageOverlay.show();

      try {
        const { data: result } = await axios.post('{{ route('admin.automations.delete') }}', {
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
          if (window.pageOverlay) window.pageOverlay.hide();
          toastr.error(result.message, 'Thất Bại');
        }
      } catch (error) {
        if (window.pageOverlay) window.pageOverlay.hide();
        toastr.error($catchMessage(error), 'Thất Bại');
      }
    }

    const toggleStatus = async (id, status) => {
      try {
        const { data: result } = await axios.post('{{ route('admin.automations.status') }}', {
          id,
          status: status ? 1 : 0,
          _token: '{{ csrf_token() }}'
        });
        
        if (result.status) {
             toastr.success(result.message, 'Thành công');
        } else {
             toastr.error(result.message, 'Thất bại');
             // Revert checkbox if failed (optional, simplest is reload or just leave it)
        }
      } catch (error) {
        toastr.error($catchMessage(error), 'Lỗi');
      }
    }
</script>
@endsection

@endsection
