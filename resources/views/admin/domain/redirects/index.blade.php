@extends('admin.layouts.master')
@section('title', 'Admin: Quản lý Chuyển tiếp tên miền')
@section('content')
  <div class="row">
    <div class="col-md-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Danh sách Chuyển tiếp tên miền</div>
                <a href="{{ route('admin.domain.redirects.create') }}" class="btn btn-primary-gradient btn-sm"><i class="fa fa-plus"></i> Thêm mới</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap key-buttons">
                        <thead>
                        <thead>
                            <tr>
                                <th>Chuyển tiếp tên miền (Đích)</th>
                                <th>Chuyển tiếp đến (Nguồn)</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($redirects as $targetId => $group)
                                @php
                                    $target = $group->first()->target;
                                    $firstRedirectId = $group->first()->id;
                                @endphp
                                <tr>
                                    <td class="fw-bold text-primary">{{ $target ? $target->domain : 'N/A' }}</td>
                                    <td>
                                        @foreach($group as $redirect)
                                            <span class="badge bg-secondary mb-1">{{ $redirect->source ? $redirect->source->domain : 'Unknown' }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.domain.redirects.edit', ['id' => $firstRedirectId]) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i> Sửa
                                        </a>
                                        <!-- Actions usually require a form for delete, but here we might want to delete the whole group or individual? 
                                             Let's allow editing to remove/add. Or delete button here to wipe the group?
                                             The controller destroy() deletes ONE redirect. 
                                             Let's keep it simple: Edit to manage. -->
                                    </td>
                                </tr>
                            @endforeach
                            @if($redirects->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Chưa có cấu hình chuyển tiếp nào.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </div>
@endsection
