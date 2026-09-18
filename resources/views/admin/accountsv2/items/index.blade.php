@extends('admin.layouts.master')
@section('title', 'Admin: Accounts Item')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản Lý Sản Phẩm Của Nhóm "{{ $group->name }}"</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table-bordered table-stripped table datatable-items">
          <thead>
            <tr>
              <th width="30">#</th>
              <th>Ưu Tiên</th>
              <th>Thao tác</th>
              <th>Tên sản phẩm</th>
              <th>Mã sản phẩm</th>
              <th>Giá bán</th>
              <th>Giá nhập</th>
              <th>% Giảm giá</th>
              <th>Ảnh sản phẩm</th>
              <th>Cấu hình nguồn</th>
              <th>Trạng thái</th>
              <th>Doanh thu</th>
              <th>Ngày thêm</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($group->items as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>
                  <a href="{{ route('admin.accountsv2.items.show', ['id' => $item->id]) }}" class="badge bg-primary-gradient text-white me-1"><i class="fa fa-pencil"></i> Sửa</a>
                  @if($item->client_type == 'normal' || ($item->client_type == 'api' && !$item->api_config_id))
                    <a href="{{ route('admin.accountsv2.resources', ['id' => $item->id]) }}" class="badge bg-info-gradient text-white me-1"><i class="fa fa-database"></i> Data</a>
                  @endif
                  <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient text-white me-1"><i class="fa fa-trash"></i> xoá</a>
                </td>
                <td>{{ $item->name }}</td>
                <td>#{{ $item->code }}</td>
                <td>{{ Helper::formatCurrency($item->price) }}</td>
                <td>{{ Helper::formatCurrency($item->cost) }}</td>
                <td>{{ $item->discount }}%</td>
                <td>
                  <a href="{{ asset($item->image) }}" target="_blank">
                    <img src="{{ asset($item->image) }}" width="22" height="22">
                  </a>
                </td>
                <td>
                  @if ($item->client_type === 'api' && $item->api_config_id)
                    <span class="badge bg-info-gradient text-white">API: {{ $item->apiConfig->name ?? 'N/A' }}</span>
                  @else
                    <span class="badge bg-success-gradient text-white">Mặc Định</span>
                  @endif
                </td>
                <td>
                  @if ($item->client_type === 'api' && $item->api_config_id)
                    {{-- Sản phẩm API: lấy số lượng từ API --}}
                    <span class="badge bg-info-gradient text-white">{{ $item->amount ?? 0 }} sản phẩm</span>
                  @else
                    {{-- Sản phẩm kho: lấy số lượng từ resources --}}
                    <span class="badge bg-success-gradient text-white">{{ $item->resources->where('buyer_name', null)->count() }} nick</span>
                  @endif
                </td>
                <td>{{ Helper::formatCurrency($item->revenue) }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <a href="{{ route('admin.accountsv2.groups', ['id' => $group->category_id]) }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại danh sách nhóm</a>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thêm sản phẩm vào nhóm</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accountsv2.items.store', ['id' => $group->id]) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label">Tên sản phẩm</label>
          <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Tên sản phẩm cần bán" required>
        </div>
        <div class="mb-3">
          <label for="code" class="form-label">Mã sản phẩm</label>
          <input type="number" class="form-control" id="code" name="code" value="{{ old('code') }}" placeholder="Để trống để tự động random 10 số hoặc nhập mã của bạn">
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Ảnh sản phẩm</label>
          <input type="file" class="form-control" id="image" name="image" required>
        </div>
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="price" class="form-label">Giá bán</label>
            <input type="text" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
          </div>
          <div class="col-md-4">
            <label for="cost" class="form-label">Giá nhập</label>
            <input type="text" class="form-control" id="cost" name="cost" value="{{ old('cost') }}" required>
          </div>
          <div class="col-md-4">
            <label for="discount" class="form-label">% Giảm giá</label>
            <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', 0) }}" required>
          </div>
        </div>
        <div class="mb-3 row">
          <div class="col-md-4">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-control" id="status" name="status" required>
              <option value="1">Đang bán</option>
              <option value="0">Ngưng bán</option>
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
            <input type="number" class="form-control" id="is_bulk" name="is_bulk" value="{{ old('is_bulk', 1) }}" required>
          </div>
          <div class="col-md-4 mt-3">
            <label for="allow_preorder" class="form-label">Cho phép đặt trước</label>
            <select class="form-control" id="allow_preorder" name="allow_preorder">
              <option value="0">Tắt</option>
              <option value="1">Bật</option>
            </select>
            <i class="text-muted" style="font-size: 11px;">Nếu bật, khi hết hàng khách vẫn có thể mua (dạng đặt trước)</i>
          </div>
          <div class="col-md-12 mt-3">
              <label for="warranty_hours" class="form-label">Thời gian bảo hành (giờ)</label>
              <input type="number" class="form-control" id="warranty_hours" name="warranty_hours" value="{{ old('warranty_hours', 0) }}" placeholder="Nhập số giờ bảo hành (0 = không bảo hành)">
          </div>
        </div>

        <div class="mb-3">
          <label for="client_type" class="form-label">Cấu hình nguồn</label>
          <select name="client_type" id="client_type" class="form-control" onchange="toggleApiConfigFields(this.value)">
            <option value="normal">Mặc Định (Kho trong hệ thống)</option>
            <option value="api">API (Kết nối từ website khác)</option>
          </select>
        </div>
        
        <div id="api-config" class="mb-3 row" style="display: none;">
          <div class="col-md-4">
            <label for="api_config_id" class="form-label">Chọn nguồn API <span class="text-danger">*</span></label>
            <select name="api_config_id" id="api_config_id" class="form-control" onchange="fetchApiProducts(this.value)">
              <option value="">-- Chọn nguồn API --</option>
              @foreach ($apis as $api)
                <option value="{{ $api->id }}">{{ $api->name }} ({{ $api->url }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-5">
            <label for="api_id" class="form-label">Chọn sản phẩm <span class="text-danger">*</span></label>
            <select name="api_id" id="api_id" class="form-control">
              <option value="">-- Trước tiên hãy chọn nguồn API --</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="api_coupon" class="form-label">Mã giảm giá (Coupon)</label>
            <input type="text" class="form-control" id="api_coupon" name="api_coupon" value="" placeholder="Mã giảm giá riêng (nếu có)">
          </div>
          <div style="display: none;">
              <input type="hidden" name="api_domain" value="">
              <input type="hidden" name="api_key" value="">
          </div>
        </div>

        <div class="mb-3">
          <label for="highlights" class="form-label">Chi tiết sản phẩm</label>
          <textarea class="form-control" id="highlights" name="highlights" rows="3">{{ old('highlights') }}</textarea>
          <i>Chi tiết nhập như sau: NAME:VALUE => Cấp độ:20, hoặc => Cấp độ 20</i>
        </div>
        {{-- <div class="mb-3">
          <label for="description" class="form-label">Mô tả sản phẩm</label>
          <textarea class="form-control ckeditor" id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div> --}}
        <div class="mb-3">
          <label for="priority" class="form-label">Ưu tiên</label>
          <input type="number" id="priority" name="priority" class="form-control" value="0" required>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary">Tạo sản phẩm</button>
        </div>
      </form>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    let productChoices;
    const apiProducts = @json($apiProducts ?? []);

    window.toggleApiConfigFields = function(value) {
      if (value === 'api') {
        $('#api-config').show();
        $('#local-stock-config').hide();
      } else {
        $('#api-config').hide();
        $('#local-stock-config').show();
      }
    };

    window.fetchApiProducts = function(api_config_id, current_api_id = null) {
        console.group('fetchApiProducts');
        console.log('API Source ID (input):', api_config_id);
        console.log('Available API IDs in data:', Object.keys(apiProducts || {}));
        
        let products = [];
        if (api_config_id && apiProducts) {
            products = apiProducts[api_config_id] || [];
        }
        console.log('Found products:', products);

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
                selected: true
            });

            if (products && (Array.isArray(products) || typeof products === 'object')) {
                const productArray = Array.isArray(products) ? products : Object.values(products);
                productArray.forEach(product => {
                    const label = `${product.name} (Tồn: ${product.amount} - Giá: ${product.price})`;
                    choices.push({
                        value: String(product.external_id),
                        label: label,
                        selected: false,
                        customProperties: {
                            name: product.name,
                            price: product.price
                        }
                    });
                });
            }
        }

        console.log('Built choices:', choices);

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

      $('.datatable-items').DataTable({
        language: {
          searchPlaceholder: 'Search...',
          sSearch: '',
          lengthMenu: 'Show _MENU_ entries',
        },
        order: [[11, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']]
      });

      // Init visibility based on current selection
      const clientTypeSelect = document.getElementById('client_type');
      if (clientTypeSelect) {
        window.toggleApiConfigFields(clientTypeSelect.value);
      }

      // Always initialize Choices on load with a small delay
      setTimeout(() => {
        window.fetchApiProducts($('#api_config_id').val());
      }, 200);
    });

    const updatePriority = $debounce(function(id, val) {
        axios.post('{{ route('admin.accountsv2.items.update-priority') }}', {
            id: id,
            priority: val,
            _token: '{{ csrf_token() }}'
        }).then(res => {
            if (res.data.status) {
                toastr.success(res.data.message || 'Cập nhật thứ tự thành công!', 'Thành Công');
            } else {
                toastr.error(res.data.message, 'Thất Bại!');
            }
        }).catch(err => {
            toastr.error('Lỗi cập nhật ưu tiên', 'Lỗi!');
        });
    }, 0);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });

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

      $showLoading();

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.accountsv2.items.delete') }}', {
          id,
           _token: '{{ csrf_token() }}'
        })

        if (result.status === 200 || result.status === true) {
            sessionStorage.setItem('pending_notification', JSON.stringify({
                type: 'success',
                title: 'Thành Công',
                message: result.message || 'Xóa thành công'
            }));
            window.location.reload();
        } else {
            $hideLoading();
            toastr.error(result.message || 'Có lỗi xảy ra', 'Thất Bại');
        }
      } catch (error) {
        $hideLoading();
        toastr.error($catchMessage(error), 'Thất Bại');
      }
    }
  </script>
@endsection