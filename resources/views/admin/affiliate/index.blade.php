@extends('admin.layouts.master')
@section('title', 'Admin: Affiliate Management')
@section('content')
  <div class="row">
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-primary rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">Tổng Tiền Giao Dịch</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ Helper::formatCurrency($totalAmount) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-success rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">Giao Dịch Thành Công</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ Helper::formatCurrency($totalAmountCompleted) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-warning rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">Giao Dịch Đang Chờ</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ Helper::formatCurrency($totalAmountPending) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-danger rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">Giao Dịch Thất Bại</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ Helper::formatCurrency($totalAmountCancelled) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Danh Sách Người Được Giới Thiệu - Hiện có <span class="text-danger">{{ $referrals->count() }}</span> Người</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>UID</th>
              <th>Tài Khoản</th>
              <th>Giới Thiệu</th>
              <th>Số Dư</th>
              <th>Tổng Tiền Nạp</th>
              <th>Tổng Hoa Hồng Nhận</th>
              <th>Ngày Giới Thiệu</th>
              <th>Ngày Cập Nhật</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($referrals as $referral)
              <tr>
                <td>{{ $referral['id'] }}</td>
                <td>{{ $referral['username'] }}</td>
                <td>{{ $referral->referrer?->username ?? '-' }}</td>
                <td>{{ number_format($referral['balance']) }} đ</td>
                <td>{{ number_format($referral['total_deposit']) }} đ</td>
                <td>
                    @if($referral->referrer)
                    {{ Helper::formatCurrency(Helper::getTotalComm($referral->referrer->username, $referral->username)) }}
                    @else
                    -
                    @endif
                </td>
                <td>{{ $referral['created_at'] }}</td>
                <td>{{ $referral['updated_at'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    $(document).ready(() => {
      $("#datatable").DataTable({
        responsive: true,
        columnDefs: [{
          orderable: false,
          targets: [0, 1, 2, 3, 4, 5, 6, 7, 8]
        }]
      });
    })
  </script>
@endsection
