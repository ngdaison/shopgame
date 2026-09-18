@extends('admin.layouts.master')
@section('title', 'Admin: Withdraws Management')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý yêu cầu rút tiền cộng tác viên</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Người dùng</th>
              <th>Số tiền</th>
              <th>Ngân hàng</th>
              <th>Số tài khoản</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
              <th>Ghi chú</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($withdraws as $value)
              @php $payment = $value->payment_info; @endphp
              <tr>
                <td>{{ $value->id }}</td>
                <td>
                  <a href="{{ route('admin.users.edit', $value->user_id) }}" class="fw-bold text-primary">{{ $value->username }}</a>
                </td>
                <td>{{ Helper::formatCurrency($value->amount) }}</td>
                <td>
                  @if(isset($payment['method']) && $payment['method'] === 'wallet')
                    Ví Tài Khoản
                  @else
                    {{ $payment['bank_name'] ?? '-' }}
                  @endif
                </td>
                <td>
                  @if(isset($payment['method']) && $payment['method'] === 'wallet')
                    -
                  @else
                    {{ $payment['account_number'] ?? '-' }}<br><small>{{ $payment['account_name'] ?? '' }}</small>
                  @endif
                </td>
                <td>{!! Helper::formatStatus($value->status) !!}</td>
                <td>{{ $value->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $value->user_note }}</td>
                <td class="text-center">
                  <a href="javascript:void(0)" class="badge bg-primary-gradient text-white" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $value->id }}">Chi Tiết</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center">No data available in table</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @foreach ($withdraws as $value)
    @php
      $payment = $value->payment_info;
    @endphp
    <div class="modal fade" id="modal-edit-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật thông tin #{{ $value->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.staff.withdraws.update') }}" method="POST" class="default-form axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="id" value="{{ $value->id }}">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="bank_name" class="form-label">Ngân hàng</label>
                  <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ $payment['bank_name'] ?? '-' }}" disabled>
                </div>
                <div class="col-md-6">
                  <label for="account_number" class="form-label">Số tài khoản</label>
                  <input type="text" class="form-control" id="account_number" name="account_number" value="{{ $payment['account_number'] ?? '-' }}" disabled>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="account_name" class="form-label">Chủ tài khoản</label>
                  <input type="text" class="form-control" id="account_name" name="account_name" value="{{ $payment['account_name'] ?? '-' }}" disabled>
                </div>
                <div class="col-md-6">
                  <label for="amount" class="form-label">Số tiền cần rút</label>
                  <input type="text" class="form-control" id="amount" name="amount" value="{{ Helper::formatCurrency($value->amount) }}" disabled>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="user_note" class="form-label">Ghi Chú</label>
                  <textarea class="form-control" id="user_note" name="user_note" rows="3">{{ $value->user_note }}</textarea>
                </div>
                <div class="col-md-6">
                  <label for="sys_note" class="form-label">Ghi Chú Hệ Thống</label>
                  <textarea class="form-control" id="sys_note" name="sys_note" rows="3" disabled>{{ $value->sys_note }}</textarea>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Hình Thức Rút</label>
                  <input type="text" class="form-control" value="{{ (isset($payment['method']) && $payment['method'] === 'wallet') ? 'Ví Tài Khoản' : 'Ngân Hàng' }}" disabled>
                </div>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng Thái</label>
                <select class="form-control" id="status" name="status">
                  <option value="Pending" @if ($value->status === 'Pending') selected @endif>Đang Chờ</option>
                  <option value="Completed" @if ($value->status === 'Completed') selected @endif>Hoàn Thành</option>
                  <option value="Cancelled" @if ($value->status === 'Cancelled') selected @endif>Đã bị hủy (Hoàn tiền)</option>
                  <option value="Declined" @if ($value->status === 'Declined') selected @endif>Đã bị hủy (Không hoàn tiền)</option>
                </select>
              </div>
              <div class="text-center mb-3">
                @if ($payment && (!isset($payment['method']) || $payment['method'] !== 'wallet'))
                  <img
                    src="https://api.vietqr.io/{{ $payment['bank_name'] }}/{{ $payment['account_number'] }}/{{ $value->amount }}/tt hoa don {{ $value->order_id }}/vietqr_net_2.jpg?accountName={{ $payment['account_name'] }}"
                    width="300" alt="">
                @endif
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
