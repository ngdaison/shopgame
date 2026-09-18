@extends('admin.layouts.master')
@section('title', 'Admin: Khuyến Mãi Nạp Tiền')
@section('styles')
<style>
    .choices__inner {
        min-height: 44px;
        border-radius: 0.375rem !important;
    }
    
    .choices__list--multiple .choices__item {
        background-color: #7367f0;
        border: 1px solid #7367f0;
        border-radius: 4px;
    }
    
    .choices__list--multiple .choices__item.is-highlighted {
        background-color: #685dd8;
        border: 1px solid #685dd8;
    }
</style>
@endsection
@section('content')
    <div class="card custom-card">
        <div class="card-header justify-content-between">
            <div class="card-title">Danh sách khuyến mãi</div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create">
                    <i class="fas fa-plus"></i> Thêm khuyến mãi mới
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive theme-scrollbar p-2">
                <table class="table table-bordered text-nowrap w-100 datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                            <th>Nạp Tối Thiểu</th>
                            <th>Nạp Tối Đa</th>
                            <th>Giá Trị</th>
                            <th>Phương Thức</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promotions as $promotion)
                            <tr>
                                <td>{{ $promotion->id }}</td>
                                <td>{{ $promotion->description }}</td>
                                <td>
                                    <a href="javascript:void(0)" class="badge bg-primary-gradient text-white me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $promotion->id }}">
                                        <i class="fa fa-edit"></i> sửa
                                    </a>
                                    <a href="javascript:void(0)" class="badge bg-danger-gradient text-white me-1" onclick="deletePromotion({{ $promotion->id }})">
                                        <i class="fa fa-trash"></i> xoá
                                    </a>
                                </td>
                                <td>{{ number_format($promotion->min_deposit) }}đ</td>
                                <td>{{ number_format($promotion->max_deposit) }}đ</td>
                                <td>
                                    @if ($promotion->bonus_type == 'percentage')
                                        <span class="badge bg-info">{{ $promotion->bonus_value }}%</span>
                                    @else
                                        <span class="badge bg-success">{{ number_format($promotion->bonus_value) }}đ</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse ($promotion->payment_methods as $method)
                                        <span class="badge bg-secondary">{{ \App\Models\Promotion::PAYMENT_METHODS[$method] ?? $method }}</span>
                                    @empty
                                        <span class="badge bg-info">Tất cả</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($promotion->status)
                                        <span class="text-success">Hoạt động</span>
                                    @else
                                        <span class="text-danger">Tạm đóng</span>
                                    @endif
                                </td>
                                <td>{{ $promotion->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modal-create" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm thông tin mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.promotions.store') }}" method="POST" class="default-form axios-form" data-reload="true">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Mô tả (Ghi chú)</label>
                            <input type="text" class="form-control" name="description" placeholder="Ví dụ: Khuyến mãi Tết 2024">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nạp Tối Thiểu</label>
                                <input type="number" class="form-control" name="min_deposit" required min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nạp Tối Đa</label>
                                <input type="number" class="form-control" name="max_deposit" required min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại Khuyến Mãi</label>
                                <select class="form-control" name="bonus_type" required>
                                    <option value="percentage">Phần Trăm (%)</option>
                                    <option value="fixed">Số Tiền Cố Định</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá Trị Khuyến Mãi</label>
                                <input type="number" class="form-control" name="bonus_value" required min="0">
                                <small class="text-muted">Nhập số % (VD: 10) hoặc số tiền (VD: 50000)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Áp dụng cho phương thức nạp (Để trống nếu áp dụng tất cả)</label>
                            <select class="payment-method-select" name="payment_methods[]" multiple>
                                @foreach (\App\Models\Promotion::PAYMENT_METHODS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="1">Hoạt động</option>
                                <option value="0">Tạm đóng</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Thêm mới</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Edit -->
    @foreach ($promotions as $promotion)
        <div class="modal fade" id="modal-edit-{{ $promotion->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cập nhật khuyến mãi #{{ $promotion->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.promotions.update') }}" method="POST" class="default-form axios-form" data-reload="true">
                        @csrf
                        <input type="hidden" name="id" value="{{ $promotion->id }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Mô tả (Ghi chú)</label>
                                <input type="text" class="form-control" name="description" value="{{ $promotion->description }}" placeholder="Ví dụ: Khuyến mãi Tết 2024">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nạp Tối Thiểu</label>
                                    <input type="number" class="form-control" name="min_deposit" value="{{ $promotion->min_deposit }}" required min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nạp Tối Đa</label>
                                    <input type="number" class="form-control" name="max_deposit" value="{{ $promotion->max_deposit }}" required min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại Khuyến Mãi</label>
                                    <select class="form-control" name="bonus_type" required>
                                        <option value="percentage" @if($promotion->bonus_type == 'percentage') selected @endif>Phần Trăm (%)</option>
                                        <option value="fixed" @if($promotion->bonus_type == 'fixed') selected @endif>Số Tiền Cố Định</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá Trị Khuyến Mãi</label>
                                    <input type="number" class="form-control" name="bonus_value" value="{{ $promotion->bonus_value }}" required min="0">
                                    <small class="text-muted">Nhập số % (VD: 10) hoặc số tiền (VD: 50000)</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Áp dụng cho phương thức nạp (Để trống nếu áp dụng tất cả)</label>
                                <select class="payment-method-select" name="payment_methods[]" multiple>
                                    @php $selectedMethods = $promotion->payment_methods ?? []; @endphp
                                    @foreach (\App\Models\Promotion::PAYMENT_METHODS as $key => $label)
                                        <option value="{{ $key }}" @if(in_array($key, $selectedMethods)) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="1" @if($promotion->status == 1) selected @endif>Hoạt động</option>
                                    <option value="0" @if($promotion->status == 0) selected @endif>Tạm đóng</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Choices.js for all Payment Methods selects
            $('.payment-method-select').each(function() {
                if (!this.classList.contains('choices__input')) {
                    new Choices(this, {
                        removeItemButton: true,
                        placeholder: true,
                        placeholderValue: '-- Chọn phương thức --',
                        shouldSort: false,
                        searchEnabled: true,
                        itemSelectText: '',
                        allowHTML: true
                    });
                }
            });
        });

        async function deletePromotion(id) {
            const result = await Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn có chắc chắn muốn xóa khuyến mãi này không?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Xóa ngay',
                cancelButtonText: 'Hủy'
            });

            if (result.isConfirmed) {
                $showLoading();
                try {
                    const { data } = await axios.post('{{ route('admin.promotions.delete') }}', { id: id });
                    if (data.status) {
                        sessionStorage.setItem('pending_notification', JSON.stringify({
                            type: 'success',
                            title: 'Thành Công',
                            message: data.message
                        }));
                        window.location.reload();
                    } else {
                        toastr.error(data.message, 'Thất Bại');
                        $hideLoading();
                    }
                } catch (error) {
                    toastr.error($catchMessage(error), 'Thất Bại');
                    $hideLoading();
                }
            }
        }
    </script>
@endsection
