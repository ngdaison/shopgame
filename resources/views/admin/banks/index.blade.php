@extends('admin.layouts.master')
@section('title', 'Admin: Banks Accounts Management')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý tài khoản ngân hàng</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Ảnh / Icon</th>
              <th>Tên ngân hàng</th>
              <th>Chủ tài khoản</th>
              <th>Số tài khoản</th>
              <th>Chi nhánh</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($banks as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}" class="badge bg-primary-gradient me-1"><i class="fa fa-edit"></i> sửa</a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient me-1"><i class="fa fa-trash"></i> xoá</a>
                </td>
                <td>
                  <img src="{{ $item->image }}" width="30" alt="">
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->owner }}</td>
                <td>{{ $item->number }}</td>
                <td>{{ $item->branch }}</td>
                <td>{{ $item->status === true ? 'Đang hiện' : 'Đang ẩn' }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#modal-create">Thêm tài khoản mới</button>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm thông tin mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.banks.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="image" class="form-label">Ảnh / Icon</label>
              <input class="form-control" type="file" id="image" name="image" required>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Tên ngân hàng</label>
              <input class="form-control" type="text" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="number" class="form-label">Số tài khoản</label>
              <input class="form-control" type="text" id="number" name="number" required>
            </div>
            <div class="mb-3">
              <label for="owner" class="form-label">Chủ tài khoản</label>
              <input class="form-control" type="text" id="owner" name="owner" required>
            </div>
            <div class="mb-3">
              <label for="provider" class="form-label">Chọn API Cung Cấp</label>
              <select class="form-control provider-select" name="provider" id="provider" required>
                <option value="">Không sử dụng API (Chạy thủ công)</option>
                <option value="web2m">Web2m (api.web2m.com)</option>
                <option value="stc">SieuThiCode (api.sieuthicode.net)</option>
              </select>
            </div>
            
            <div class="mb-3 api-group d-none" id="group-bank-code">
              <label for="bank_code" class="form-label">Chọn Loại Ngân Hàng (API)</label>
              <select class="form-control" name="bank_code" id="bank_code">
                <option value="">-- Chọn ngân hàng --</option>
              </select>
            </div>

            <div class="mb-3 api-group d-none" id="group-password">
              <label for="password" class="form-label">Password Internet Banking</label>
              <input class="form-control" type="text" id="password" name="password" placeholder="Mật khẩu đăng nhập Internet Banking">
            </div>
            <div class="mb-3 api-group d-none" id="group-token">
              <label for="token" class="form-label">Token API</label>
              <input class="form-control" type="text" id="token" name="token" placeholder="Token lấy từ hệ thống API">
            </div>
            {{-- <div class="mb-3">
              <label for="branch" class="form-label">Chi nhánh</label>
              <input class="form-control" type="text" id="branch" name="branch">
            </div> --}}
            <div class="mb-3">
              <label for="status" class="form-label">Trạng thái</label>
              <select class="form-control" id="status" name="status">
                <option value="1">Hoạt động</option>
                <option value="0">Không hoạt động</option>
              </select>
            </div>
            <div class="mb-3">
              <button class="btn btn-danger-gradient w-100" type="submit">Thêm mới</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  @foreach ($banks as $value)
    <div class="modal fade" id="modal-edit-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật thông tin #{{ $value->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.banks.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="id" value="{{ $value->id }}">
              <div class="mb-3">
                <label for="image" class="form-label">Ảnh / Icon</label>
                <input class="form-control" type="file" id="image" name="image">
              </div>
              <div class="mb-3">
                <label for="name" class="form-label">Tên ngân hàng</label>
                <input class="form-control" type="text" id="name" name="name" value="{{ $value->name }}" required>
              </div>
              <div class="mb-3">
                <label for="number" class="form-label">Số tài khoản</label>
                <input class="form-control" type="text" id="number" name="number" value="{{ $value->number }}" required>
              </div>
              <div class="mb-3">
                <label for="owner" class="form-label">Chủ tài khoản</label>
                <input class="form-control" type="text" id="owner" name="owner" value="{{ $value->owner }}" required>
              </div>
              <div class="mb-3">
                <label for="edit_provider_{{ $value->id }}" class="form-label">Chọn API Cung Cấp</label>
                <select class="form-control provider-select" name="provider" id="edit_provider_{{ $value->id }}" data-id="{{ $value->id }}" required>
                  <option value="" @if(!$value->provider) selected @endif>Không sử dụng API (Chạy thủ công)</option>
                  <option value="web2m" @if($value->provider == 'web2m') selected @endif>Web2m (api.web2m.com)</option>
                  <option value="stc" @if($value->provider == 'stc') selected @endif>SieuThiCode (api.sieuthicode.net)</option>
                </select>
              </div>
              
              <div class="mb-3 api-group @if(!$value->provider) d-none @endif" id="group-bank-code-{{ $value->id }}">
                <label for="edit_bank_code_{{ $value->id }}" class="form-label">Chọn Loại Ngân Hàng (API)</label>
                <select class="form-control bank-code-select" name="bank_code" id="edit_bank_code_{{ $value->id }}" data-selected="{{ $value->bank_code }}">
                  <option value="">-- Chọn ngân hàng --</option>
                </select>
              </div>

              <div class="mb-3 api-group @if(!$value->provider) d-none @endif" id="group-password-{{ $value->id }}">
                <label for="password" class="form-label">Password Internet Banking</label>
                <input class="form-control" type="text" name="password" value="{{ $value->password }}" placeholder="Mật khẩu đăng nhập Internet Banking">
              </div>
              <div class="mb-3 api-group @if(!$value->provider) d-none @endif" id="group-token-{{ $value->id }}">
                <label for="token" class="form-label">Token API</label>
                <input class="form-control" type="text" name="token" value="{{ $value->token }}" placeholder="Token lấy từ hệ thống API">
              </div>
              {{-- <div class="mb-3">
                <label for="branch" class="form-label">Chi nhánh</label>
                <input class="form-control" type="text" id="branch" name="branch" value="{{ $value->branch }}">
              </div> --}}
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status">
                  <option value="1" @if ($value->status === true) selected @endif>Hoạt động</option>
                  <option value="0" @if ($value->status === false) selected @endif>Không hoạt động</option>
                </select>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary w-100" type="submit">Cập nhật</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection
@section('scripts')
  <script>
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
        } = await axios.post('{{ route('admin.banks.delete') }}', {
          id
        })

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành Công',
            message: result.message || 'Xóa thành công'
          }));
          location.reload();
        } else {
          $hideLoading();
          toastr.error(result.message, 'Thất Bại');
        }
      } catch (error) {
        $hideLoading();
        toastr.error($catchMessage(error), 'Thất Bại');
      }
    }

    // API Providers Configuration
    const BANK_LISTS = {
        web2m: [
            { code: 'Vietcombank', name: 'Vietcombank' },
            { code: 'MBBank', name: 'MBBank' },
            { code: 'MBBank_OpenAPI', name: 'MBBank OpenAPI' },
            { code: 'Techcombank', name: 'Techcombank' },
            { code: 'BIDV', name: 'BIDV' },
            { code: 'BIDV_OpenAPI', name: 'BIDV OpenAPI' },
            { code: 'ACB', name: 'ACB' },
            { code: 'ACB_OpenAPI', name: 'ACB OpenAPI' },
            { code: 'TPBank', name: 'TPBank' },
            { code: 'Vietinbank', name: 'Vietinbank' },
            { code: 'Seabank', name: 'Seabank' },
            { code: 'TheSieuRe', name: 'TheSieuRe' }
        ],
        stc: [
            { code: 'Vietcombank', name: 'Vietcombank' },
            { code: 'MBBank', name: 'MBBank' },
            { code: 'Techcombank', name: 'Techcombank' },
            { code: 'ACB', name: 'ACB' },
            { code: 'BIDV', name: 'BIDV' },
            { code: 'TPBank', name: 'TPBank' },
            { code: 'Vietinbank', name: 'Vietinbank' },
            { code: 'Seabank', name: 'Seabank' },
            { code: 'VPBank', name: 'VPBank' },
            { code: 'ViettelMoney', name: 'Viettel Money' },
            { code: 'TheSieuRe', name: 'TheSieuRe' }
        ]
    };

    const handleProviderChange = function(element) {
        const provider = $(element).val();
        // Determine context (create or edit) based on element ID or location
        // Create modal uses simple IDs, Edit uses suffix
        let bankSelect, passGroup, tokenGroup, bankGroup;
        
        if (element.id === 'provider') {
             // Create Modal
             bankSelect = $('#bank_code');
             passGroup = $('#group-password');
             tokenGroup = $('#group-token');
             bankGroup = $('#group-bank-code');
        } else {
             // Edit Modal
             const id = $(element).data('id');
             bankSelect = $(`#edit_bank_code_${id}`);
             passGroup = $(`#group-password-${id}`);
             tokenGroup = $(`#group-token-${id}`);
             bankGroup = $(`#group-bank-code-${id}`);
        }

        if (provider) {
            passGroup.removeClass('d-none');
            tokenGroup.removeClass('d-none');
            bankGroup.removeClass('d-none');

            // Populate Bank Codes
            bankSelect.empty().append('<option value="">-- Chọn ngân hàng --</option>');
            if (BANK_LISTS[provider]) {
                const currentSelected = bankSelect.data('selected');
                BANK_LISTS[provider].forEach(bank => {
                    const isSelected = currentSelected === bank.code ? 'selected' : '';
                    bankSelect.append(`<option value="${bank.code}" ${isSelected}>${bank.name}</option>`);
                });
            }
        } else {
            passGroup.addClass('d-none');
            tokenGroup.addClass('d-none');
            bankGroup.addClass('d-none');
        }
    };


    $(document).ready(function() {
        // Initialize for Create Modal
        $('#provider').change(function() { handleProviderChange(this); });

        // Initialize for Edit Modals (using event delegation or loop if needed, but simple selector works too)
        $('.provider-select').change(function() { handleProviderChange(this); });
        
        // Trigger change for existing edit modals to populate lists based on generic `data-selected` logic? 
        // We need to trigger it but preserve the selected value properly.
        // Iterate over all provider selects to init state
        $('.provider-select').each(function() {
             if ($(this).val()) {
                 handleProviderChange(this);
             }
        });
    });
  </script>
@endsection
