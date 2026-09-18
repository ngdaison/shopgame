@extends('admin.layouts.master')
@section('title', 'Chi Tiết Chiến Dịch: ' . $campaign->name)
@section('content')

<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
        <div class="card overflow-hidden dash1-card border-0">
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between">
                    <div>
                        <p class=" mb-1 fs-12">Tổng Clicks</p>
                        <h2 class="mb-0 fs-20 fw-bold">{{ number_format($campaign->clicks) }}</h2>
                    </div>
                    <span class="text-primary fs-30"><i class="fa fa-mouse-pointer"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
        <div class="card overflow-hidden dash2-card border-0">
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between">
                    <div>
                        <p class=" mb-1 fs-12">Tổng Đăng Ký</p>
                        <h2 class="mb-0 fs-20 fw-bold">{{ number_format($campaign->registrations) }}</h2>
                    </div>
                    <span class="text-success fs-30"><i class="fa fa-user-plus"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
        <div class="card overflow-hidden dash3-card border-0">
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between">
                    <div>
                        <p class=" mb-1 fs-12">Tổng Đơn Hàng</p>
                        <h2 class="mb-0 fs-20 fw-bold">{{ number_format($campaign->orders) }}</h2>
                    </div>
                    <span class="text-warning fs-30"><i class="fa fa-shopping-cart"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
        <div class="card overflow-hidden dash4-card border-0">
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between">
                    <div>
                        <p class=" mb-1 fs-12">Hoa Hồng Phát Sinh</p>
                        <h2 class="mb-0 fs-20 fw-bold text-primary">{{ Helper::formatCurrency($campaign->total_commission) }}</h2>
                    </div>
                    <span class="text-danger fs-30"><i class="fa fa-money-bill-wave"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Lịch Sử Hoạt Động</div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-bordered mb-3">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-clicks">Lượt Click</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-users">Thành Viên</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-commissions">Hoa Hồng</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-clicks">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>IP</th>
                                        <th>User Agent</th>
                                        <th>Thời Gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clicks as $click)
                                        <tr>
                                            <td>{{ $click->ip }}</td>
                                            <td class="small">{{ $click->user_agent }}</td>
                                            <td>{{ $click->created_at }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center">Chưa có dữ liệu</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $clicks->appends(['users_page' => $registrations->currentPage(), 'comms_page' => $commissions->currentPage()])->links() }}
                    </div>
                    <div class="tab-pane fade" id="tab-users">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>IP Đăng Ký</th>
                                        <th>Thời Gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registrations as $user)
                                        <tr>
                                            <td>{{ $user->username }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->register_ip }}</td>
                                            <td>{{ $user->created_at }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">Chưa có dữ liệu</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                         {{ $registrations->appends(['clicks_page' => $clicks->currentPage(), 'comms_page' => $commissions->currentPage()])->links() }}
                    </div>
                    <div class="tab-pane fade" id="tab-commissions">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Đơn Hàng</th>
                                        <th>Số Tiền</th>
                                        <th>Ghi Chú</th>
                                        <th>Thời Gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($commissions as $log)
                                        <tr>
                                            <td><code>{{ $log->order_id }}</code></td>
                                            <td class="fw-bold text-success">+ {{ Helper::formatCurrency($log->amount) }}</td>
                                            <td>{{ $log->user_note }}</td>
                                            <td>{{ $log->created_at }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">Chưa có dữ liệu</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $commissions->appends(['clicks_page' => $clicks->currentPage(), 'users_page' => $registrations->currentPage()])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
