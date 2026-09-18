@extends('admin.layouts.master')
@section('title', 'Admin: Edit Account Item')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Chỉnh Sửa Sản Phẩm #{{ $item->code }} - {{ $item->name }}</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accountsv2.items.update') }}" method="POST" enctype="multipart/form-data" class="default-form" id="update-form">
        @csrf
        <input type="hidden" name="id" value="{{ $item->id }}">
        
        <div class="mb-3">
          <label for="name" class="form-label">Tên sản phẩm</label>
          <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $item->name) }}" placeholder="Tên sản phẩm cần bán" required>
        </div>
        
        <div class="mb-3">
          <label for="code" class="form-label">Mã sản phẩm</label>
          <input type="number" class="form-control" id="code" name="code" value="{{ old('code', $item->code) }}">
        </div>
        
        <div class="mb-3">
          <label for="image" class="form-label">Ảnh sản phẩm</label>
          @if($item->image)
            <div class="mb-2">
              <img src="{{ asset($item->image) }}" alt="Current Image" style="max-width: 100px; max-height: 100px;">
            </div>
          @endif
          <input type="file" class="form-control" id="image" name="image">
          <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="price" class="form-label">Giá bán</label>
            <input type="text" class="form-control" id="price" name="price" value="{{ old('price', $item->price) }}" required>
          </div>
          <div class="col-md-4">
            <label for="cost" class="form-label">Giá nhập</label>
            <input type="text" class="form-control" id="cost" name="cost" value="{{ old('cost', $item->cost) }}" required>
          </div>
          <div class="col-md-4">
            <label for="discount" class="form-label">% Giảm giá</label>
            <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', $item->discount) }}" required>
          </div>
        </div>
        
        <div class="mb-3 row">
          <div class="col-md-4">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-control" id="status" name="status" required>
              <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Đang bán</option>
              <option value="0" {{ $item->status == 0 ? 'selected' : '' }}>Ngưng bán</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="type" class="form-label">Loại sản phẩm</label>
            <select name="type" id="type" class="form-control" required>
              <option value="account">Mặc Định</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="is_bulk" class="form-label">Số lượng mua / lần</label>
            <input type="number" class="form-control" id="is_bulk" name="is_bulk" value="{{ old('is_bulk', $item->is_bulk) }}" required>
          </div>
          <div class="col-md-4 mt-3">
            <label for="allow_preorder" class="form-label">Cho phép đặt trước</label>
            <select class="form-control" id="allow_preorder" name="allow_preorder">
              <option value="0" {{ $item->allow_preorder == 0 ? 'selected' : '' }}>Tắt</option>
              <option value="1" {{ $item->allow_preorder == 1 ? 'selected' : '' }}>Bật</option>
            </select>
            <i class="text-muted" style="font-size: 11px;">Nếu bật, khi hết hàng khách vẫn có thể mua (dạng đặt trước)</i>
          </div>
          <div class="col-md-12 mt-3">
            <label for="warranty_hours" class="form-label">Thời gian bảo hành (giờ)</label>
            <input type="number" class="form-control" id="warranty_hours" name="warranty_hours" value="{{ old('warranty_hours', $item->warranty_hours) }}" placeholder="Nhập số giờ bảo hành (0 = không bảo hành)">
          </div>
        </div>

        <div class="mb-3">
          <label for="client_type" class="form-label">Cấu hình nguồn</label>
          <select name="client_type" id="client_type" class="form-control" onchange="toggleApiConfigFields(this.value)">
            <option value="normal" {{ $item->client_type == 'normal' ? 'selected' : '' }}>Mặc Định (Kho trong hệ thống)</option>
            <option value="api" {{ $item->client_type == 'api' ? 'selected' : '' }}>API (Kết nối từ website khác)</option>
          </select>
        </div>
        
        <div id="api-config" class="mb-3 row" style="display: {{ $item->client_type == 'api' ? 'flex' : 'none' }};">
          <div class="col-md-4">
            <label for="api_config_id" class="form-label">Chọn nguồn API <span class="text-danger">*</span></label>
            <select name="api_config_id" id="api_config_id" class="form-control" onchange="fetchApiProducts(this.value, '{{ $item->api_id }}')">
              <option value="">-- Chọn nguồn API --</option>
              @foreach ($apis as $api)
                <option value="{{ $api->id }}" {{ $item->api_config_id == $api->id ? 'selected' : '' }}>{{ $api->name }} ({{ $api->url }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-5">
            <label for="api_id" class="form-label">Chọn sản phẩm <span class="text-danger">*</span> (Có thể chọn nhiều)</label>
            <select name="api_id[]" id="api_id" class="form-control" multiple>
              <option value="">-- Trước tiên hãy chọn nguồn API --</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="api_coupon" class="form-label">Mã giảm giá (Coupon)</label>
            <input type="text" class="form-control" id="api_coupon" name="api_coupon" value="{{ old('api_coupon', $item->api_coupon) }}" placeholder="Mã giảm giá riêng (nếu có)">
          </div>
          <div style="display: none;">
            <input type="hidden" name="api_domain" value="">
            <input type="hidden" name="api_key" value="">
          </div>
        </div>
        
        <div class="mb-3">
          <label for="highlights" class="form-label">Chi tiết sản phẩm</label>
          <textarea class="form-control" id="highlights" name="highlights" rows="3">{{ old('highlights', is_array($item->highlights) ? implode("\n", array_map(function($h) { return is_array($h) ? ($h['name'] . ':' . $h['value']) : $h; }, $item->highlights)) : $item->highlights) }}</textarea>
          <i>Chi tiết nhập như sau: NAME:VALUE => Cấp độ:20, hoặc => Cấp độ 20</i>
        </div>
        
        <div class="mb-3">
          <label for="priority" class="form-label">Ưu tiên</label>
          <input type="number" id="priority" name="priority" class="form-control" value="{{ old('priority', $item->priority) }}" required>
        </div>
        
        <div class="mb-3 text-center">
          <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
          <a href="{{ route('admin.accountsv2.items', ['id' => $item->group_id]) }}" class="btn btn-secondary">Quay lại</a>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    let productChoices;
    const apiProducts = @json($apiProducts ?? []);
    const currentApiId = '{{ $item->api_id }}';

    window.toggleApiConfigFields = function(value) {
      if (value === 'api') {
        $('#api-config').show();
      } else {
        $('#api-config').hide();
      }
    };

    window.fetchApiProducts = function(api_config_id, current_api_id = null) {
        console.group('fetchApiProducts (Show)');
        console.log('API Source ID:', api_config_id, 'Current API ID:', current_api_id);
        
        // Handle comma separated IDs
        const currentIds = current_api_id ? String(current_api_id).split(',').map(id => id.trim()) : [];
        
        let products = [];
        if (api_config_id && apiProducts) {
            products = apiProducts[api_config_id] || [];
        }

        let choices = [];
        if (!api_config_id) {
            choices.push({
                value: '',
                label: '-- Trước tiên hãy chọn nguồn API --',
                selected: true,
                disabled: true
            });
        } else {
            choices.push({
                value: '',
                label: '-- Chọn sản phẩm --',
                selected: !current_api_id
            });

            if (products && (Array.isArray(products) || typeof products === 'object')) {
                const productArray = Array.isArray(products) ? products : Object.values(products);
                productArray.forEach(product => {
                    const pId = String(product.external_id || product.id);
                    const label = `${product.name} (Tồn: ${product.amount} - Giá: ${product.price})`;
                    const isSelected = currentIds.includes(pId);
                    
                    choices.push({
                        value: pId,
                        label: label,
                        selected: isSelected,
                        customProperties: {
                            name: product.name,
                            price: product.price
                        }
                    });
                });
            }
        }

        if (productChoices) {
            productChoices.destroy();
        }

        const selectEl = document.getElementById('api_id');
        if (selectEl) {
            let html = '';
            choices.forEach(c => {
                html += `<option value="${c.value}" ${c.selected ? 'selected' : ''} data-name="${c.customProperties?.name || ''}" data-price="${c.customProperties?.price || ''}">${c.label}</option>`;
            });
            selectEl.innerHTML = html;

            if (typeof Choices !== 'undefined') {
                productChoices = new Choices(selectEl, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: '-- Chọn sản phẩm --',
                    removeItemButton: true,
                    allowHTML: true
                });
            }
        }
        console.groupEnd();
    };

    $(document).ready(function() {
        $('#api_config_id').on('change', function() {
            window.fetchApiProducts($(this).val());
        });

        $('#api_id').on('change', function() {
            const selectedChoice = productChoices.getValue();
            if (selectedChoice && selectedChoice.customProperties) {
                const name = selectedChoice.customProperties.name;
                const price = selectedChoice.customProperties.price;
                
                if (name && $('#name').val().trim() === '') {
                    $('#name').val(name);
                }
                if (price && ($('#price').val() == '' || $('#price').val() == '0')) {
                    $('#price').val(price);
                }
            }
        });

        // Init visibility
        const clientTypeSelect = document.getElementById('client_type');
        if (clientTypeSelect) {
            window.toggleApiConfigFields(clientTypeSelect.value);
        }

        // Always initialize Choices on load with a small delay
        setTimeout(() => {
            window.fetchApiProducts($('#api_config_id').val(), currentApiId);
        }, 200);

        // AJAX Update Form Submission
        $('#update-form').on('submit', async function(e) {
            e.preventDefault();
            $showLoading();
            
            try {
                const url = $(this).attr('action');
                const formData = new FormData(this);
                const { data } = await axios.post(url, formData);
                
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: data.message || 'Cập nhật thành công'
                }));
                window.location.reload();
            } catch (error) {
                toastr.error($catchMessage(error));
                $hideLoading();
            }
        });
    });
  </script>
@endsection
