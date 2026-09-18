@extends('admin.layouts.master')
@section('title', 'Admin: Inventory Variables Management')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý phần thưởng (unit)</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Đơn vị</th>
              <th>Tên gọi</th>
              <th data-orderable="false">Hình ảnh</th>
              <th>Số lượng</th>
              <th>Tài khoản</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
              <th>Cập nhật</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($inventories as $item)
              @php
                if ($item->inventory_var === null && $item->is_active === true) {
                    $item->is_active = false;
                    $item->save();
                }
              @endphp

              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->inventory_var?->unit ?? '-' }}</td>
                <td>{{ $item->inventory_var?->name ?? '-' }}</td>
                <td class="text-center">
                  <img src="{{ $item->inventory_var?->image ?? '' }}" alt="{{ $item->inventory_var?->name ?? '' }}" class="img-fluid" style="width: 30px">
                </td>
                <td>{{ number_format($item->value) }} <small class="text-muted text-danger">{{ $item->inventory_var?->unit ?? '-' }}</small></td>
                <td>{{ $item->username }}</td>
                <td>
                  @if ($item->is_active)
                    <span class="badge bg-success">Hoạt động</span>
                  @else
                    <span class="badge bg-danger">Phong toả</span>
                  @endif
                </td>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->updated_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
