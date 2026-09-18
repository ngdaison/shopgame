@extends('admin.layouts.master')
@section('title', 'Admin: Danh Sách Role')
@section('content')
<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">DANH SÁCH ROLE</div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fa fa-plus me-1"></i> Tạo một role mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-stripped text-nowrap text-center">
                <thead>
                    <tr>
                        <th style="width: 150px;">Thao tác</th>
                        <th>Cấp bậc</th>
                        <th>Vai trò</th>
                        <th>Quyền hạn</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>
                            <a href="{{ route('admin.role.edit', $role->id) }}" class="btn btn-sm btn-primary me-1" title="Sửa"><i class="fa fa-edit"></i></a>
                            <form action="{{ route('admin.role.delete') }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa role này?');">
                                @csrf
                                <input type="hidden" name="id" value="{{ $role->id }}">
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa" {{ $role->name === 'Admin' ? 'disabled' : '' }}><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $role->level }}</span>
                        </td>
                        <td class="fw-bold">{{ $role->name }}</td>
                        <td class="text-start" style="white-space: normal;">
                            @if(is_array($role->permissions))
                                @foreach($role->permissions as $perm)
                                    <span class="badge bg-light text-primary border border-primary m-1">{{ $perm }}</span>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.role.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalLabel">Tạo Role Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên vai trò <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Ví dụ: Admin, Cộng tác viên...">
                    </div>
                    <div class="mb-3">
                        <label for="level" class="form-label">Cấp bậc (Số càng lớn, quyền càng cao)</label>
                        <input type="number" class="form-control" id="level" name="level" value="0" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="hide_old_history" class="form-label font-weight-bold">Ẩn lịch sử trước ngày (Để trống nếu không ẩn)</label>
                        <input type="date" class="form-control" name="hide_old_history" id="hide_old_history">
                        <small class="text-muted">Mọi lịch sử (hoạt động, giao dịch,...) trước ngày này sẽ bị ẩn.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
