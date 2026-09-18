@extends('admin.layouts.master')
@section('title', 'Admin: Items Orders')
@section('content')
  <div class="card custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="card-title">Quản lý đơn hàng vật phẩm</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Mã đơn</th>
              <th>Domain</th>

              <th>Dịch vụ</th>
              <th>Người mua</th>
              <th>Thanh toán</th>
              <th>Robux</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
              <th>Cập nhật</th>

              <th>CTV Nhận</th>
              <th>Ngày nhận</th>
              <th>Ngày xong</th>
              <th>Tiền nhận</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($orders as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                   <a href="javascript:void(0)" class="badge bg-success-gradient me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> sửa</a>
                </td>
                <td>{{ $item->code }}</td>
                <td><span class="badge bg-info">{{ $item->domain_display }}</span></td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->username }}</td>
                <td>{{ Helper::formatCurrency($item->payment) }}</td>
                <td>{{ $item->robux ? '$R' . $item->robux : '-' }}</td>
                <td>{!! Helper::formatStatus($item->status) !!}</td>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->updated_at }}</td>

                <td>{{ $item->assigned_to ?? '0 có' }}</td>
                <td>{{ $item->assigned_at }}</td>
                <td>{{ $item->assigned_completed ?? '-' }}</td>
                <td>{{ $item->assigned_payment > 0 ? Helper::formatCurrency($item->assigned_payment) : '-' }}</td>
                <td>
                  @if ($item->assigned_status === 'Completed')
                    <span class="badge bg-success">Đã nhận</span>
                  @elseif ($item->assigned_status === 'WaitPayment')
                    <span class="badge bg-warning">Chờ duyệt</span>
                  @else
                    <span class="badge bg-danger">Chưa nhận</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer"></div>
  </div>

  @foreach ($orders as $item)
    <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật nhóm #{{ $item->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.items.orders.update', ['id' => $item->id]) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
              @csrf
              <div class="mb-3">
                <label for="name" class="form-label">Dịch vụ</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ $item->name }}" disabled>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="code" class="form-label">Mã đơn</label>
                  <input type="text" id="code" name="code" class="form-control" value="{{ $item->code }}" disabled>
                </div>
                <div class="col-md-6">
                  <label for="payment" class="form-label">Thanh toán</label>
                  <input type="text" id="payment" name="payment" class="form-control" value="{{ Helper::formatCurrency($item->payment) }}" disabled>
                </div>
              </div>
              @if ($item->type === 'gamepass')
                <div class="mb-3">
                  <label for="input_user" class="form-label">Link GamePass</label>
                  <input type="text" id="input_user" name="input_user" class="form-control" value="{{ $item->input_user ?? '-KHÔNG CÓ-' }}" readonly>
                </div>
              @elseif ($item->type === 'user')
                @php
                  $ingame = '';
                  foreach ($item->input_ingame as $ig) {
                      $ingame .= $ig . "\n";
                  }
                @endphp
                <div class="mb-3">
                  <label for="input_user" class="form-label">Tài khoản nhận</label>
                  <input type="text" id="input_user" name="input_user" class="form-control" value="{{ $item->input_user ?? '-KHÔNG CÓ-' }}" readonly>
                </div>
              @else
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="input_user" class="form-label">Tài khoản</label>
                    <input type="text" id="input_user" name="input_user" class="form-control" value="{{ $item->input_user ?? '-KHÔNG CÓ-' }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label for="input_pass" class="form-label">Mật khẩu</label>
                    <input type="text" id="input_pass" name="input_pass" class="form-control" value="{{ $item->input_pass ?? '-KHÔNG CÓ-' }}" readonly>
                  </div>
                </div>
              @endif
              <div class="mb-3">
                <label for="input_contact" class="form-label">Liên hệ</label>
                <input type="text" id="input_contact" name="input_contact" class="form-control" value="{{ $item->input_contact ?? '-KHÔNG CÓ-' }}" readonly>
              </div>
              <div class="mb-3">
                <label for="order_note" class="form-label">Ghi chú khách</label>
                <textarea class="form-control" id="order_note" name="order_note" rows="3" readonly>{{ $item->order_note }}</textarea>
              </div>
              <div class="mb-3">
                <label for="admin_note" class="form-label">Ghi chú admin</label>
                <textarea class="form-control" id="admin_note" name="admin_note" rows="3">{{ $item->admin_note }}</textarea>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-select" id="status" name="status" required>
                  <option value="Pending" {{ $item->status === 'Pending' ? 'selected' : '' }}>Chờ xử lý</option>
                  <option value="Processing" {{ $item->status === 'Processing' ? 'selected' : '' }}>Đang xử lý</option>
                  <option value="Completed" {{ $item->status === 'Completed' ? 'selected' : '' }}>Hoàn thành</option>
                  <option value="Cancelled" {{ $item->status === 'Cancelled' ? 'selected' : '' }}>Đã bị hủy (Hoàn tiền)</option>
                  <option value="Destroyed" {{ $item->status === 'Destroyed' ? 'selected' : '' }}>Đã hủy (Không hoàn tiền)</option>
                </select>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary w-100">Cập nhật</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach

  <script>
    const refundOrder = async (id) => {
        const confirm = await Swal.fire({
            title: 'Xác nhận hoàn tiền?',
            text: "Bạn có chắc chắn muốn hoàn tiền đơn hàng này? Tiền sẽ được cộng lại vào tài khoản người dùng.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        });

        if (!confirm.isConfirmed) return;

        try {
            const { data: result } = await axios.post('{{ route('admin.items.orders.refund') }}', { id });

            if (result.status) {
                toastr.success(result.message, 'Thành công');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(result.message, 'Thất bại');
            }
        } catch (error) {
            toastr.error('Có lỗi xảy ra', 'Lỗi');
        }
    }

    const deleteOrder = async (id) => {
        const confirm = await Swal.fire({
            title: 'Ẩn đơn hàng?',
            text: "Đơn hàng này sẽ bị ẩn khỏi Admin nhưng vẫn hiển thị ở lịch sử người dùng!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        });

        if (!confirm.isConfirmed) return;

        try {
            const { data: result } = await axios.post('{{ route('admin.items.orders.delete') }}', { id });

            if (result.status) {
                toastr.success(result.message, 'Thành công');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(result.message, 'Thất bại');
            }
        } catch (error) {
            toastr.error('Có lỗi xảy ra', 'Lỗi');
        }
    }
    const clearAllOrders = async () => {
        const confirm = await Swal.fire({
            title: 'Dọn dẹp toàn bộ đơn hàng?',
            text: "Toàn bộ đơn hàng (trừ Product Manager) sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý dọn dẹp',
            cancelButtonText: 'Hủy'
        });

        if (!confirm.isConfirmed) return;

        try {
            const { data: result } = await axios.post('{{ route('admin.items.orders.clear-all') }}');

            if (result.status) {
                toastr.success(result.message, 'Thành công');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(result.message, 'Thất bại');
            }
        } catch (error) {
            toastr.error('Có lỗi xảy ra', 'Lỗi');
        }
    }
  </script>
@endsection
