@extends('admin.layouts.master')
@section('title', 'Lịch sử sử dụng: ' . $coupon->coupon_code)
@section('content')

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">
            Lịch sử sử dụng mã: <span class="text-primary">{{ $coupon->coupon_code }}</span>
        </div>
        <a href="{{ route('admin.coupons') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable1">
                <thead>
                    <tr>
                        <th>Tài khoản</th>
                        <th>Mã đơn</th>
                        <th>Tổng tiền đơn hàng</th>
                        <th>Giảm Giá</th>
                        <th>Tổng tiền đã giảm giá</th>
                        <th>Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        @php
                            $discount = $transaction->extras['discount'] ?? 0;
                            $originalAmount = $transaction->amount + $discount;
                        @endphp
                        <tr>
                            <td>
                                @if($transaction->user)
                                    <a href="{{ route('admin.users.edit', $transaction->user_id) }}">
                                        {{ $transaction->user->username }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A (ID: {{ $transaction->user_id }})</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $transaction->code }}</span>
                            </td>
                            <td>
                                <span class="text-secondary">{{ number_format($originalAmount, 0, '.', ',') }}₫</span>
                            </td>
                            <td>
                                <span class="text-danger">-{{ number_format($discount, 0, '.', ',') }}₫</span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">{{ number_format($transaction->amount, 0, '.', ',') }}₫</span>
                            </td>
                            <td>
                                {{ $transaction->created_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($transactions->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted">Chưa có lịch sử sử dụng cho mã này.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
