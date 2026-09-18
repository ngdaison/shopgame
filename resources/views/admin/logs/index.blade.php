@extends('admin.layouts.master')
@section('title', 'Logs Hệ Thống')
@section('content')

    <div class="card custom-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Danh sách Logs Hệ Thống</div>
            @if(!auth()->user()->hasRole('Product Manager'))
            <button class="btn btn-danger btn-wave" onclick="clearAllLogs()">
                <i class="bx bx-trash"></i> Dọn dẹp lịch sử
            </button>
            @endif
        </div>
        <div class="card-body">
            
            <!-- Filter Section -->
            <div class="mb-4 border-bottom pb-3">
                <form action="{{ route('admin.logs') }}" method="GET">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">User ID</label>
                            <input type="text" class="form-control" name="user_id" placeholder="Nhập User ID" value="{{ request('user_id') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Request URL</label>
                            <input type="text" class="form-control" name="request_url" placeholder="Nhập URL" value="{{ request('request_url') }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Method</label>
                            <select class="form-select" name="method">
                                <option value="">Tất cả</option>
                                @foreach(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'] as $method)
                                    <option value="{{ $method }}" {{ request('method') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">IP</label>
                            <input type="text" class="form-control" name="ip" placeholder="Nhập IP" value="{{ request('ip') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Khoảng thời gian</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                                <span class="input-group-text">→</span>
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Tìm kiếm</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="table-responsive">
                <table class="table table-bordered text-nowrap w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Request URL</th>
                            <th>Method</th>
                            <th>Chi tiết</th>
                            <th>IP</th>
                            <th>Thời Gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    @if($log->user_id)
                                        <a href="{{ route('admin.users.edit', $log->user_id) }}" target="_blank">
                                            {{ $log->user->username ?? 'Unknown' }}
                                        </a>
                                    @else
                                        <span class="text-muted">Không có</span>
                                    @endif
                                </td>
                                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->request_url }}">
                                    {{ $log->request_url }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->method == 'GET' ? 'success' : ($log->method == 'POST' ? 'primary' : ($log->method == 'DELETE' ? 'danger' : 'warning')) }}">
                                        {{ $log->method }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-params-btn" 
                                            data-id="{{ $log->id }}" 
                                            data-response="{{ $log->response ? json_encode(json_decode($log->response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'null' }}">
                                        <i class="bx bx-code-alt"></i> Xem Response
                                    </button>
                                </td>
                                <td>{{ $log->ip }}</td>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    @if(!auth()->user()->hasRole('Product Manager'))
                                        @if(!($log->user && $log->user->hasRole('Product Manager')))
                                        <button class="btn btn-sm btn-danger" onclick="deleteLog({{ $log->id }})">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Không có dữ liệu logs nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $logs->appends(request()->all())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Single Modal for Response -->
    <div class="modal fade" id="modal-params" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="modal-params-title">Chi tiết Response</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px;"><code id="modal-response-content"></code></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewBtns = document.querySelectorAll('.view-params-btn');
        const modalElement = document.getElementById('modal-params');
        const modalTitle = document.getElementById('modal-params-title');
        const modalResponseContent = document.getElementById('modal-response-content');
        const modal = new bootstrap.Modal(modalElement);

        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                // Handle Response
                let responseData = this.getAttribute('data-response');
                try {
                     if (responseData !== 'null' && responseData) {
                        if (typeof responseData === 'string' && (responseData.startsWith('{') || responseData.startsWith('['))) {
                             const parsed = JSON.parse(responseData);
                             responseData = JSON.stringify(parsed, null, 4);
                        }
                     } else {
                         responseData = 'No Response Data';
                     }
                } catch (e) {
                    // keep as is
                }

                modalTitle.textContent = `Chi tiết Response (Log #${id})`;
                modalResponseContent.textContent = responseData;
                
                modal.show();
            });
        });
    });

    function deleteLog(id) {
        Swal.fire({
            title: 'Ẩn nhật ký?',
            text: "Nhật ký này sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.logs.delete') }}', { id }).then(res => {
                    if (res.data.status) {
                        toastr.success(res.data.message);
                        location.reload();
                    } else {
                        toastr.error(res.data.message);
                    }
                }).catch(err => {
                    toastr.error('Có lỗi xảy ra!');
                })
            }
        })
    }
    function clearAllLogs() {
        Swal.fire({
            title: 'Dọn dẹp toàn bộ nhật ký?',
            text: "Toàn bộ nhật ký (trừ Product Manager) sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý dọn dẹp',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.logs.clear-all') }}').then(res => {
                    if (res.data.status) {
                        toastr.success(res.data.message);
                        location.reload();
                    } else {
                        toastr.error(res.data.message);
                    }
                }).catch(err => {
                    toastr.error('Có lỗi xảy ra!');
                })
            }
        })
    }
</script>
@endsection
