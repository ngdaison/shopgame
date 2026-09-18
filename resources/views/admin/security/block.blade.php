@extends('admin.layouts.master')
@section('title', 'Danh sách Block')
@section('content')

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">Danh sách Block</div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBanModal">
            <i class="fa fa-plus"></i> Thêm Block
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thông tin (Người dùng hoặc IP)</th>
                        <th>Số lần</th>
                        <th>Trạng thái</th>
                        <th>Lý do</th>
                        <th>Thời gian</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bans as $ban)
                        <tr>
                            <td>{{ $ban->id }}</td>
                            <td>
                                @if($ban->type == 'user')
                                    User: <strong>{{ $ban->username }}</strong>
                                @else
                                    IP: <strong>{{ $ban->ip }}</strong>
                                @endif
                            </td>
                            <td>{{ $ban->strikes }}</td>
                            <td>
                                @switch($ban->status)
                                    @case('ban')
                                        <span class="badge bg-danger">Block vĩnh viễn</span>
                                        @break
                                    @case('ban_1_day')
                                        <span class="badge bg-warning">1 Ngày</span>
                                        @break
                                    @case('ban_2_day')
                                        <span class="badge bg-warning">2 Ngày</span>
                                        @break
                                    @case('ban_3_day')
                                        <span class="badge bg-warning">3 Ngày</span>
                                        @break
                                    @case('ban_4_day')
                                        <span class="badge bg-warning">4 Ngày</span>
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $ban->reason ?? '-' }}</td>
                            <td>
                                <div>Ngày tạo: {{ $ban->created_at->format('Y-m-d H:i') }}</div>
                                @if($ban->banned_until)
                                    <div class="text-danger">Đến: {{ $ban->banned_until->format('Y-m-d H:i') }}</div>
                                @else
                                    <div class="text-danger">Vĩnh viễn</div>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.security.block.delete') }}" method="POST" class="default-form axios-form" data-reload="true" data-confirm="Bạn có chắc chắn muốn xóa lượt chặn này không?">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $ban->id }}">
                                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $bans->links() }}
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addBanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.security.block.store') }}" method="POST" class="default-form axios-form" data-reload="true">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Thêm Block Mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Cách chặn</label>
                <select class="form-control" name="type" id="banType">
                    <option value="user">User</option>
                    <option value="ip">IP (Web/WebRTC)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Nội dung (Username hoặc IP)</label>
                <input type="text" class="form-control" name="content" required placeholder="Nhập username hoặc IP">
            </div>
            <div class="mb-3">
                <label class="form-label">Lý do chặn</label>
                <input type="text" class="form-control" name="reason" placeholder="Không bắt buộc">
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select class="form-control" name="status">
                    <option value="ban">Block vĩnh viễn</option>
                    <option value="ban_1_day">Block 1 Ngày</option>
                    <option value="ban_2_day">Block 2 Ngày</option>
                    <option value="ban_3_day">Block 3 Ngày</option>
                    <option value="ban_4_day">Block 4 Ngày</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary">Lưu Block</button>
          </div>
      </form>
    </div>
  </div>
</div>

@endsection
