@extends('admin.layouts.master')
@section('title', 'Quản lý Mã Giảm Giá')
@section('content')

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">Danh sách Mã Giảm Giá</div>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fa fa-plus"></i> Thêm Mã Mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable1">
                <thead>
                    <tr>
                        <th>Mã giảm giá</th>
                        <th>Sản phẩm áp dụng</th>
                        <th>Số lượng / Đã dùng</th>
                        <th>Giảm</th>
                        <th>Thời gian</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $coupon)
                        <tr>
                            <td>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" value="{{ $coupon->coupon_code }}" readonly id="code_{{ $coupon->id }}">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('code_{{ $coupon->id }}')">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                @if(empty($coupon->product_ids))
                                    <span class="badge bg-success">Tất cả sản phẩm</span>
                                @else
                                    <div style="max-width: 200px; overflow-x: auto; white-space: nowrap;">
                                        @foreach($coupon->product_ids as $pid)
                                            <span class="badge bg-info">{{ $pid }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($coupon->quantity == 0)
                                    <span class="badge bg-success">Không giới hạn</span>
                                @else
                                    {{ number_format($coupon->quantity) }}
                                @endif
                                / <span class="text-danger">{{ number_format($coupon->used) }}</span>
                                <br>
                                <small class="text-muted">
                                    Limit/User: {{ $coupon->user_usage_limit == 0 ? 'Unlimited' : $coupon->user_usage_limit }}
                                </small>
                            </td>
                            <td>
                                @if($coupon->discount_type == 'percentage')
                                    <span class="text-primary">{{ (int)$coupon->discount_value }}%</span>
                                @else
                                    <span class="text-success">{{ number_format($coupon->discount_value, 0, '.', ',') }}₫</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    Start: {{ $coupon->start_datetime ? $coupon->start_datetime->format('Y-m-d H:i') : 'Ngay lập tức' }} <br>
                                    End: {{ $coupon->end_datetime ? $coupon->end_datetime->format('Y-m-d H:i') : 'Vĩnh viễn' }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.coupons.show', $coupon->id) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-primary" onclick="editCoupon({{ json_encode($coupon) }})">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <a href="javascript:deleteRow({{ $coupon->id }})" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('admin.coupons.store') }}" method="POST" class="default-form axios-form" data-reload="true">
          @csrf
          <input type="hidden" name="id" id="coupon_id">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Thêm Mã Giảm Giá</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mã giảm giá <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="coupon_code" id="coupon_code" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">Random</button>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Số lượng (0 = Không giới hạn)</label>
                    <input type="number" class="form-control" name="quantity" id="quantity" value="0" min="0" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">GH/Người (0 = Không GH)</label>
                    <input type="number" class="form-control" name="user_usage_limit" id="user_usage_limit" value="0" min="0" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Loại giảm giá</label>
                    <select class="form-control" name="discount_type" id="discount_type">
                        <option value="percentage">Phần trăm (%)</option>
                        <option value="amount">Số tiền cố định</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Giá trị giảm</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="discount_value" id="discount_value" required min="0">
                        <span class="input-group-text">%/₫</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Đơn tối thiểu</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="min_order_value" id="min_order_value" value="0" min="0">
                        <span class="input-group-text">₫</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày bắt đầu</label>
                    <input type="datetime-local" class="form-control" name="start_datetime" id="start_datetime">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày kết thúc</label>
                    <input type="datetime-local" class="form-control" name="end_datetime" id="end_datetime">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Sản phẩm áp dụng (Để trống = Tất cả)</label>
                 <select class="form-control" name="product_ids[]" id="product_ids" multiple>
                    @foreach($products as $product)
                        <option value="{{ $product->uni_id }}">{{ $product->display_name }} ({{ $product->type_label }})</option>
                    @endforeach
                </select>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary">Lưu Mã</button>
          </div>
      </form>
    </div>
  </div>
</div>

@section('scripts')
<script>
    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('coupon_code').value = result;
    }

    let productChoices;

    function initChoices() {
        if (productChoices) {
            productChoices.destroy();
        }
        const element = document.getElementById('product_ids');
        productChoices = new Choices(element, {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: 'Sản phẩm áp dụng (Để trống = Tất cả)'
        });
    }

    function showAddModal() {
        document.getElementById('coupon_id').value = '';
        document.getElementById('modalTitle').innerText = 'Thêm Mã Giảm Giá';
        document.getElementById('coupon_code').value = '';
        document.getElementById('quantity').value = 0;
        document.getElementById('user_usage_limit').value = 0;
        document.getElementById('discount_type').value = 'percentage';
        document.getElementById('discount_value').value = '';
        document.getElementById('min_order_value').value = 0;
        document.getElementById('start_datetime').value = '';
        document.getElementById('end_datetime').value = '';
        
        // Reset and init choices
        $('#product_ids').val([]); 
        initChoices();
        
        var myModal = new bootstrap.Modal(document.getElementById('couponModal'));
        myModal.show();
    }

    function editCoupon(coupon) {
        document.getElementById('coupon_id').value = coupon.id;
        document.getElementById('modalTitle').innerText = 'Sửa Mã Giảm Giá';
        document.getElementById('coupon_code').value = coupon.coupon_code;
        document.getElementById('quantity').value = coupon.quantity;
        document.getElementById('user_usage_limit').value = coupon.user_usage_limit;
        document.getElementById('discount_type').value = coupon.discount_type;
        document.getElementById('discount_value').value = parseFloat(coupon.discount_value);
        document.getElementById('min_order_value').value = parseFloat(coupon.min_order_value);
        
        // Format datetime for input
        if(coupon.start_datetime) document.getElementById('start_datetime').value = coupon.start_datetime.slice(0, 16);
        if(coupon.end_datetime) document.getElementById('end_datetime').value = coupon.end_datetime.slice(0, 16);

        // Set products
        if (coupon.product_ids) {
             $('#product_ids').val(coupon.product_ids);
        } else {
             $('#product_ids').val([]);
        }
        initChoices();

        var myModal = new bootstrap.Modal(document.getElementById('couponModal'));
        myModal.show();
    }
    
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
    }
    
    // Initial call not needed as modal opens trigger it
    const deleteRow = async (id) => {
      const confirmDelete = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
      });

      if (!confirmDelete.isConfirmed) return;

      if (window.pageOverlay) window.pageOverlay.show();

      try {
        const { data: result } = await axios.post('{{ route('admin.coupons.delete') }}', {
          id,
          _token: '{{ csrf_token() }}'
        })

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành Công',
            message: result.message || 'Xóa thành công'
          }));
          location.reload();
        } else {
          if (window.pageOverlay) window.pageOverlay.hide();
          toastr.error(result.message, 'Thất Bại');
        }
      } catch (error) {
        if (window.pageOverlay) window.pageOverlay.hide();
        toastr.error($catchMessage(error), 'Thất Bại');
      }
    }
</script>
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

@endsection
