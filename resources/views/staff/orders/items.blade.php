@extends('staff.layouts.app')

@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Đơn hàng Vật phẩm - Đã nhận</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap text-center datatablez">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Mã đơn</th>
              <th>Dịch vụ</th>
              <th>Thanh toán</th>
              <th>Robux / Rate</th>
              <th>Trạng thái</th>
              <th>Ghi chú</th>
              <th>Thời gian</th>
              <th>Ngày nhận</th>
              <th>Ngày xong</th>
              <th>Tiền nhận</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($claimedOrders as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  @if (in_array($item->status, ['Assigned', 'Processing']))
                    <a href="javascript:void(0)" class="shadow btn btn-primary btn-xs sharp me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> Edit</a>
                  @endif
                </td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ Helper::formatCurrency($item->payment) }}</td>
                <td>{{ $item->robux ? '$R' . $item->robux . ' / ' . $item->rate_robux : '-' }}</td>
                <td>{!! Helper::formatStatus($item->status) !!}</td>
                <td>{{ $item->order_note }}</td>
                <td>{{ $item->created_at }}</td>
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

              @if (in_array($item->status, ['Assigned', 'Completed', 'Processing']))
                <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Cập nhật đơn hàng #{{ $item->id }}</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="alert alert-danger mb-3">Sau khi hoàn thành đơn, hệ thống sẽ kiểm tra và trả hoa hồng sau 3-5 ngày</div>
                        <form action="{{ route('staff.orders.items.update', ['id' => $item->id]) }}" method="POST" enctype="multipart/form-data" class="axios-form" data-reload="true">
                          @csrf
                          <div class="mb-3">
                            <label for="name" class="form-label">Dịch vụ</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ $item->name }}" disabled>
                          </div>
                          <div class="row mb-3">
                            <div class="col-md-4">
                              <label for="code" class="form-label">Mã đơn</label>
                              <input type="text" id="code" name="code" class="form-control" value="{{ $item->code }}" disabled>
                            </div>
                            <div class="col-md-4">
                              <label for="payment" class="form-label">Thanh toán</label>
                              <input type="text" id="payment" name="payment" class="form-control" value="{{ Helper::formatCurrency($item->payment) }}" disabled>
                            </div>
                            <div class="col-md-4">
                              <label for="assigned_payment" class="form-label">Có thể nhận</label>
                              <input type="text" id="assigned_payment" name="assigned_payment" class="form-control" value="{{ Helper::formatCurrency((float) (($item->payment * $user->colla_percent) / 100)) }}"
                                disabled>
                            </div>
                          </div>
                          @if (in_array($item->status, ['Processing', 'Completed', 'Cancelled']))
                            @if ($item->type === 'gamepass' || $item->type === 'robux')
                              <div class="mb-3">
                                <label for="input_user" class="form-label">Pass ID / Link Game Pass</label>
                                <input type="text" id="input_user" name="input_user" class="form-control" value="{{ $item->input_user ?? '-KHÔNG CÓ-' }}" disabled>
                              </div>
                            @elseif ($item->type === 'addfriend')
                              <div class="mb-3">
                                <label for="input_user" class="form-label">Tài khoản nhận</label>
                                <input type="text" id="input_user" name="input_user" class="form-control" value="{{ $item->input_user ?? '-KHÔNG CÓ-' }}" disabled>
                              </div>
                            @else
                              <div class="mb-3 row">
                                <div class="col-md-4">
                                  <label for="input_user" class="form-label">Tài khoản</label>
                                  <input type="text" id="input_user" name="input_user" class="form-control" value="{{ $item->input_user ?? '-KHÔNG CÓ-' }}" disabled>
                                </div>
                                <div class="col-md-4">
                                  <label for="input_pass" class="form-label">Mật khẩu</label>
                                  <input type="text" id="input_pass" name="input_pass" class="form-control" value="{{ $item->input_pass ?? '-KHÔNG CÓ-' }}" disabled>
                                </div>
                                <div class="col-md-4">
                                  <label for="input_auth" class="form-label">Đăng nhập</label>
                                  <input type="text" id="input_auth" name="input_auth" class="form-control" value="{{ $item->input_auth ?? '-KHÔNG CÓ-' }}" disabled>
                                </div>
                              </div>
                            @endif
                          @endif
                          @if (in_array($item->status, ['Processing', 'Completed', 'Cancelled']))
                            <div class="mb-3">
                              <label for="admin_note" class="form-label">Ghi chú admin</label>
                              <textarea class="form-control" id="admin_note" name="admin_note" rows="3">{{ $item->admin_note }}</textarea>
                            </div>
                            <div class="mb-3">
                              <label for="order_note" class="form-label">Ghi chú khách</label>
                              <textarea class="form-control" id="order_note" name="order_note" rows="3">{{ $item->order_note }}</textarea>
                            </div>
                          @else
                            <input type="hidden" name="admin_note" value="{{ $item->admin_note }}">
                            <input type="hidden" name="order_note" value="{{ $item->order_note }}">
                          @endif
                          <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status" required>
                              @if ($item->status === 'Assigned')
                                <option value="Assigned" selected>Đã nhận đơn</option>
                                <option value="Processing">Đang xử lý</option>
                              @else
                                <option value="Assigned" disabled>Đã nhận đơn</option>
                                <option value="Processing" {{ $item->status === 'Processing' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="Completed" {{ $item->status === 'Completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="Cancelled" {{ $item->status === 'Cancelled' ? 'selected' : '' }}>Đã hủy / Hoàn</option>
                              @endif
                            </select>
                          </div>
                          <div class="mb-3">
                            <button class="btn btn-primary">Cập nhật</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <div class="d-flex justify-content-center">
        {{ $claimedOrders->links() }}
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h4>Quản lý đơn hàng vật phẩm - chưa nhận</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap text-center datatablez">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Mã đơn</th>
              <th>Dịch vụ</th>
              <th>Thanh toán</th>
              <th>Robux / Rate</th>
              <th>Trạng thái</th>
              <th>Ghi chú</th>
              <th>Thời gian</th>
              <th>Ngày nhận</th>
              <th>Ngày xong</th>
              <th>Tiền nhận</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pendingOrders as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  @if ($item->status === 'Pending')
                    <a href="javascript:void(0)" class="shadow btn btn-danger btn-xs sharp me-1" onclick="claimOrder({{ $item->id }})"><i class="fa fa-database"></i> Claim</a>
                  @endif
                </td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ Helper::formatCurrency($item->payment) }}</td>
                <td>{{ $item->robux ? '$R' . $item->robux . ' / ' . $item->rate_robux : '-' }}</td>
                <td>{!! Helper::formatStatus($item->status) !!}</td>
                <td>{{ $item->order_note }}</td>
                <td>{{ $item->created_at }}</td>
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
    <div class="card-footer">
      <div class="d-flex justify-content-center">
        {{ $pendingOrders->links() }}
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    const claimOrder = async (id) => {
      const confirm = await Swal.fire({
        title: 'Bạn có chắc chắn chứ?',
        text: "Bạn không được tự ý huỷ đơn này khi đã nhận!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Nhận',
        cancelButtonText: 'Hủy'
      })

      if (!confirm.isConfirmed) return;

      $showLoading();

      try {
        const {
          data: result
        } = await axios.post('{{ route('staff.orders.items.claim') }}', {
          id
        });

        sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành Công',
            message: result.message
        }));
        window.location.reload();
      } catch (error) {
        if (typeof toastr !== 'undefined') {
            toastr.error($catchMessage(error), 'Thất Bại');
        } else {
            Swal.fire('Thất bại', $catchMessage(error), 'error');
        }
      }
    }
  </script>
@endsection
