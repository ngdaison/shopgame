@extends('staff.layouts.app')
@section('title', 'Admin: Accounts Group')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý nhóm sản phẩm</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Ưu tiên</th>
              <th>Thao tác</th>
              <th>Ảnh / Icon</th>
              <th>Tên nhóm</th>
              <th>Doanh thu</th>
              <th>Trạng thái</th>
              <th>Người tạo</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($groups as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->priority }}</td>
                <td>
                  <a href="{{ route('staff.products.accounts.items', ['id' => $item->id]) }}" class="badge bg-danger-gradient text-white me-1"><i class="fa fa-eye"></i> Xem</a>
                </td>
                <td>
                  {{-- <img src="{{ $item->image }}" width="40"> --}}
                  <a href="{{ $item->image }}" target="_blank">Xem Ảnh</a>
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ Helper::formatCurrency($item->items()->where('staff_name', auth()->user()->username)->where('buyer_name', '!=', null)->where('buyer_code', '!=', null)->sum('staff_payment') ?? 0) }}</td>
                <td>
                  @if ($item->status == 1)
                    <span class="text-success">Hoạt động</span>
                  @else
                    <span class="text-danger">Tạm đóng</span>
                  @endif
                </td>
                <td>{{ $item->username }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
