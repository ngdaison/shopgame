@extends('admin.layouts.master')
@section('title', 'Admin: Cấu hình nạp tiền')
@section('content')
  <div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.banks') }}">Nạp tiền</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.banks') }}">Ngân hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cấu hình</li>
            </ol>
        </nav>
    </div>
  </div>

  <div class="card custom-card shadow-sm">
    <div class="card-header justify-content-between">
      <div class="card-title text-uppercase"><i class="bx bx-cog me-1"></i> CẤU HÌNH NẠP BANK</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.deposit.banks.config.update') }}" method="POST" class="axios-form" data-reload="true">
        @csrf
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select name="status" id="status" class="form-control">
                  <option value="on" @if (($config->value['status'] ?? 0) == 1) selected @endif>ON</option>
                  <option value="off" @if (($config->value['status'] ?? 0) == 0) selected @endif>OFF</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Link Cron Job</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ url('/cron/deposit') }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ url('/cron/deposit') }}')"><i class="fa fa-copy"></i></button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Link Webhook SePay (Cấu hình trên SePay.vn)</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ url('/hooks/sepay-payment') }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ url('/hooks/sepay-payment') }}')"><i class="fa fa-copy"></i></button>
                </div>
                <small class="text-muted">Nhập URL này vào SePay (Định dạng: JSON, Phương thức: POST, Xác thực HMAC-SHA256 Secret Key).</small>
            </div>
            <div class="mb-3">
                <label for="prefix" class="form-label">Prefix</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="prefix" id="prefix" value="{{ $config->value['prefix'] ?? 'KiyoTop' }}" required>
                    <span class="input-group-text">1</span>
                </div>
                <small class="text-muted">Không được để trống Prefix, Prefix là nội dung nạp tiền vào hệ thống.</small>
            </div>
            <div class="mb-3">
                <label for="discount" class="form-label">Khuyến Mãi [+ %]</label>
                <div class="input-group">
                    <input type="number" class="form-control" name="discount" id="discount" value="{{ $config->value['discount'] ?? 0 }}" required>
                    <span class="input-group-text">%</span>
                </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
                <label for="min" class="form-label">Số tiền nạp tối thiểu</label>
                <input type="number" class="form-control" name="min" id="min" value="{{ $config->value['min'] ?? 0 }}" required>
            </div>
            <div class="mb-3">
                <label for="max" class="form-label">Số tiền nạp tối đa</label>
                <input type="number" class="form-control" name="max" id="max" value="{{ $config->value['max'] ?? 1000000000 }}" required>
            </div>
          </div>
        </div>
        <div class="mb-3">
            <label for="note" class="form-label">Lưu ý nạp tiền (Trang nạp tiền qua thẻ / ngân hàng)</label>
            <textarea name="note" id="note" class="form-control ckeditor" rows="5">{{ $notice->value ?? $config->value['note'] ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-start">
            <a href="{{ route('admin.deposit.banks.config') }}" class="btn btn-danger me-2 shadow-sm"><i class="fa fa-rotate-right"></i> Reload</a>
            <button type="submit" class="btn btn-primary shadow-sm" style="background-color: #6f42c1; border-color: #6f42c1;"><i class="fa fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
  
  <div class="row mb-3 mt-3">
      <div class="col-12">
          <div class="card shadow-sm">
              <div class="card-header d-flex justify-content-between align-items-center py-3">
                  <h6 class="m-0 font-weight-bold text-primary" style="padding-left: 10px; border-left: 5px solid #0d6efd;">DANH SÁCH NGÂN HÀNG</h6>
                  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create">
                      <i class="fa fa-plus"></i> Thêm ngân hàng
                  </button>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="display table table-striped table-hover mb-0" id="basic-1">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Thao tác</th>
                                  <th>Ngân hàng</th>
                                  <th>Số tài khoản</th>
                                  <th>Chủ tài khoản</th>
                                  <th>Trạng thái</th>
                                  <th>Ngày Tạo</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($banks as $item)
                                  <tr>
                                      <td>{{ $loop->iteration }}</td>
                                      <td>
                                          <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> Edit</button>
                                          <button class="btn btn-sm btn-danger" onclick="deleteRow('{{ $item->id }}')"><i class="fa fa-trash"></i> Delete</button>
                                      </td>
                                      <td>
                                          <div class="d-flex align-items-center">
                                              {{ $item->bank_name ?? $item->name }}
                                          </div>
                                      </td>
                                      <td>{{ $item->number }}</td>
                                      <td>{{ $item->owner }}</td>
                                      <td>
                                          @if($item->status)
                                              <span class="badge bg-success">Hiển thị</span>
                                          @else
                                              <span class="badge bg-danger">Ẩn</span>
                                          @endif
                                      </td>
                                      <td>{{ isset($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div>

  {{-- Modal Create --}}
  <div class="modal fade" id="modal-create" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Thêm ngân hàng mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.banks.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="row mb-3">
                <div class="col-4"><label class="form-label mt-1">API Provider</label></div>
                <div class="col-8">
                    <select name="provider" class="form-control provider-select" id="provider" required>
                        <option value="">-- Không sử dụng (Manual) --</option>
                        <option value="sepay">SePay</option>
                        <option value="web2m">Web2m (api.web2m.com)</option>
                        <option value="stc">SieuThiCode (api.sieuthicode.net)</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3 api-group d-none" id="group-webhook-url">
                <div class="col-4"><label class="form-label mt-1">URL Webhook</label></div>
                <div class="col-8">
                    <div class="input-group">
                        <input class="form-control" type="text" value="{{ url('/hooks/sepay-payment') }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ url('/hooks/sepay-payment') }}'); toastr.success('Đã sao chép URL Webhook!', 'Thành công');">
                            <i class="fa fa-copy me-1"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted">Copy URL này dán vào mục Webhook URL trên SePay.vn</small>
                </div>
            </div>

            <div class="row mb-3 api-group d-none" id="group-bank-code">
                <div class="col-4"><label class="form-label mt-1">Loại Ngân Hàng (API)</label></div>
                <div class="col-8">
                    <select class="form-control" name="bank_code" id="bank_code">
                        <option value="">-- Chọn ngân hàng --</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3" id="group-name">
                <div class="col-4"><label class="form-label mt-1">Ngân hàng <span class="text-danger">*</span></label></div>
                <div class="col-8"><input class="form-control" type="text" name="name" required placeholder="Nhập tên ngân hàng (Ví dụ: Vietcombank)"></div>
            </div>

            <div class="row mb-3">
                <div class="col-4"><label class="form-label mt-1">Image <span class="text-danger">*</span></label></div>
                <div class="col-8">
                    <input class="form-control" type="file" name="image" required>

                </div>
            </div>

            <div class="row mb-3">
                 <div class="col-4"><label class="form-label mt-1">Chủ tài khoản <span class="text-danger">*</span></label></div>
                 <div class="col-8"><input class="form-control" type="text" name="owner" required placeholder="Nhập tên chủ tài khoản"></div>
            </div>

            <div class="row mb-3">
                <div class="col-4"><label class="form-label mt-1">Số tài khoản <span class="text-danger">*</span></label></div>
                <div class="col-8"><input class="form-control" type="text" name="number" required placeholder="Nhập số tài khoản"></div>
            </div>

            <div class="row mb-3 api-group d-none" id="group-password">
                <div class="col-4"><label class="form-label mt-1">Password IB</label></div>
                <div class="col-8"><input class="form-control" type="text" id="password" name="password" placeholder="Mật khẩu Internet Banking"></div>
            </div>

            <div class="row mb-3 api-group d-none" id="group-token">
                <div class="col-4"><label class="form-label mt-1">Token API</label></div>
                <div class="col-8">
                    <div class="input-group">
                        <input class="form-control" type="text" id="token" name="token" placeholder="Token API">
                        <button class="btn btn-outline-secondary d-none" type="button" id="btn-generate-secret" onclick="generateSePaySecret('token')">
                            <i class="fa fa-key me-1"></i> Tạo Secret Key
                        </button>
                    </div>
                    <small class="text-muted d-none" id="hint-sepay-secret">Bấm "Tạo Secret Key" để hệ thống tự tạo chuỗi <code>whsec_...</code> ngẫu nhiên rồi dán chuỗi này vào SePay.vn</small>
                </div>
            </div>
            


            <div class="mt-4 text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-purple" type="submit"><i class="fa fa-plus"></i> Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Edit --}}
  @foreach ($banks as $value)
    <div class="modal fade" id="modal-edit-{{ $value->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cập nhật ngân hàng #{{ $value->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.banks.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="id" value="{{ $value->id }}">
                <div class="row mb-3">
                  <div class="col-4"><label class="form-label mt-1">API Provider</label></div>
                  <div class="col-8">
                      <select name="provider" class="form-control provider-select" id="edit_provider_{{ $value->id }}" data-id="{{ $value->id }}" required>
                          <option value="" @if(!$value->provider) selected @endif>-- Không sử dụng (Manual) --</option>
                          <option value="sepay" @if($value->provider == 'sepay') selected @endif>SePay</option>
                          <option value="web2m" @if($value->provider == 'web2m') selected @endif>Web2m</option>
                          <option value="stc" @if($value->provider == 'stc') selected @endif>SieuThiCode</option>
                      </select>
                  </div>
                </div>

                <div class="row mb-3 api-group @if($value->provider !== 'sepay') d-none @endif" id="group-webhook-url-{{ $value->id }}">
                    <div class="col-4"><label class="form-label mt-1">URL Webhook</label></div>
                    <div class="col-8">
                        <div class="input-group">
                            <input class="form-control" type="text" value="{{ url('/hooks/sepay-payment') }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ url('/hooks/sepay-payment') }}'); toastr.success('Đã sao chép URL Webhook!', 'Thành công');">
                                <i class="fa fa-copy me-1"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Copy URL này dán vào mục Webhook URL trên SePay.vn</small>
                    </div>
                </div>

                <div class="row mb-3 api-group @if(!$value->provider) d-none @endif" id="group-bank-code-{{ $value->id }}">
                    <div class="col-4"><label class="form-label mt-1">Loại Ngân Hàng (API)</label></div>
                    <div class="col-8">
                        <select class="form-control bank-code-select" name="bank_code" id="edit_bank_code_{{ $value->id }}" data-selected="{{ $value->bank_code }}">
                            <option value="">-- Chọn ngân hàng --</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3" id="group-name-{{ $value->id }}">
                    <div class="col-4"><label class="form-label mt-1">Ngân hàng <span class="text-danger">*</span></label></div>
                    <div class="col-8"><input class="form-control" type="text" name="name" value="{{ $value->bank_name ?? $value->name }}" required placeholder="Nhập tên ngân hàng"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-4"><label class="form-label mt-1">Image</label></div>
                    <div class="col-8">
                        <input class="form-control" type="file" name="image">
    
                    </div>
                </div>

                 <div class="row mb-3">
                    <div class="col-4"><label class="form-label mt-1">Chủ tài khoản <span class="text-danger">*</span></label></div>
                    <div class="col-8"><input class="form-control" type="text" name="owner" value="{{ $value->owner }}" required placeholder="Nhập tên chủ tài khoản"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-4"><label class="form-label mt-1">Số tài khoản <span class="text-danger">*</span></label></div>
                    <div class="col-8"><input class="form-control" type="text" name="number" value="{{ $value->number }}" required placeholder="Nhập số tài khoản"></div>
                </div>

                <div class="row mb-3 api-group @if(!$value->provider) d-none @endif" id="group-password-{{ $value->id }}">
                    <div class="col-4"><label class="form-label mt-1">Password IB</label></div>
                    <div class="col-8"><input class="form-control" type="text" name="password" value="{{ $value->password }}" placeholder="Mật khẩu Internet Banking"></div>
                </div>

                <div class="row mb-3 api-group @if(!$value->provider) d-none @endif" id="group-token-{{ $value->id }}">
                    <div class="col-4"><label class="form-label mt-1">Token API</label></div>
                    <div class="col-8">
                        <div class="input-group">
                            <input class="form-control" type="text" id="token-{{ $value->id }}" name="token" value="{{ $value->token }}" placeholder="Token API">
                            <button class="btn btn-outline-secondary @if($value->provider !== 'sepay') d-none @endif btn-generate-secret-edit" type="button" onclick="generateSePaySecret('token-{{ $value->id }}')">
                                <i class="fa fa-key me-1"></i> Tạo Secret Key
                            </button>
                        </div>
                        <small class="text-muted @if($value->provider !== 'sepay') d-none @endif hint-sepay-edit">Bấm "Tạo Secret Key" để hệ thống tự tạo chuỗi <code>whsec_...</code> ngẫu nhiên rồi dán chuỗi này vào SePay.vn</small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4"><label class="form-label mt-1">Trạng thái</label></div>
                    <div class="col-8">
                        <select class="form-control" name="status">
                            <option value="1" @if($value->status) selected @endif>Hoạt động</option>
                            <option value="0" @if(!$value->status) selected @endif>Tắt</option>
                        </select>
                    </div>
                </div>

              <div class="mt-4 text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-purple" type="submit"><i class="fa fa-save"></i> Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
  <script>
    // Delete Row
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
        const { data: result } = await axios.post('{{ route('admin.banks.delete') }}', { id });
        if (result.status) {
          toastr.success(result.message, 'Thành Công');
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
        sepay: [
            { code: 'Vietcombank', name: 'Vietcombank' },
            { code: 'MBBank', name: 'MBBank' },
            { code: 'Techcombank', name: 'Techcombank' },
            { code: 'ACB', name: 'ACB' },
            { code: 'BIDV', name: 'BIDV' },
            { code: 'TPBank', name: 'TPBank' },
            { code: 'Vietinbank', name: 'Vietinbank' },
            { code: 'VPBank', name: 'VPBank' },
            { code: 'Agribank', name: 'Agribank' },
            { code: 'Sacombank', name: 'Sacombank' },
            { code: 'SHB', name: 'SHB' },
            { code: 'VIB', name: 'VIB' },
            { code: 'MSB', name: 'MSB' },
            { code: 'Seabank', name: 'Seabank' },
            { code: 'LienVietPostBank', name: 'LienVietPostBank' },
            { code: 'Other', name: 'Ngân hàng khác' }
        ],
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

    const generateSePaySecret = function(inputId) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let randomStr = '';
        for (let i = 0; i < 32; i++) {
            randomStr += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const secretKey = 'whsec_' + randomStr;
        $(`#${inputId}`).val(secretKey);
        toastr.success('Đã tạo Secret Key mới: ' + secretKey, 'Thành Công');
    };

    const handleProviderChange = function(element) {
        const provider = $(element).val();
        let bankSelect, passGroup, tokenGroup, webhookGroup, bankGroup, nameGroup;
        
        if (element.id === 'provider') {
             // Create Modal
             bankSelect = $('#bank_code');
             passGroup = $('#group-password');
             tokenGroup = $('#group-token');
             webhookGroup = $('#group-webhook-url');
             bankGroup = $('#group-bank-code');
             nameGroup = $('#group-name');
        } else {
             // Edit Modal
             const id = $(element).data('id');
             bankSelect = $(`#edit_bank_code_${id}`);
             passGroup = $(`#group-password-${id}`);
             tokenGroup = $(`#group-token-${id}`);
             webhookGroup = $(`#group-webhook-url-${id}`);
             bankGroup = $(`#group-bank-code-${id}`);
             nameGroup = $(`#group-name-${id}`);
        }

        if (provider) {
            tokenGroup.removeClass('d-none');
            bankGroup.removeClass('d-none');
            nameGroup.addClass('d-none'); // Hide Name when API is ON

            if (provider === 'sepay') {
                passGroup.addClass('d-none');
                webhookGroup.removeClass('d-none').css('display', 'flex');
                tokenGroup.find('label').text('Secret Key (HMAC)');
                tokenGroup.find('input').attr('placeholder', 'Secret Key cấu hình trên SePay');
                tokenGroup.find('button').removeClass('d-none').css('display', 'inline-block');
                tokenGroup.find('small').removeClass('d-none').css('display', 'block');
            } else {
                webhookGroup.addClass('d-none').css('display', 'none');
                tokenGroup.find('label').text('Token API');
                tokenGroup.find('input').attr('placeholder', 'Token API');
                tokenGroup.find('button').addClass('d-none').css('display', 'none');
                tokenGroup.find('small').addClass('d-none').css('display', 'none');
            }

            // Populate Bank Codes
            bankSelect.empty().append('<option value="">-- Chọn ngân hàng --</option>');
            if (BANK_LISTS[provider]) {
                const currentSelected = bankSelect.data('selected');
                BANK_LISTS[provider].forEach(bank => {
                    const isSelected = currentSelected === bank.code ? 'selected' : '';
                    bankSelect.append(`<option value="${bank.code}" ${isSelected}>${bank.name}</option>`);
                });
            }
            
            // Check if selected bank needs password
            const bankCode = bankSelect.val();
            if (provider === 'sepay' || bankCode === 'TheSieuRe' || bankCode === 'ViettelMoney') {
                passGroup.addClass('d-none');
            } else {
                passGroup.removeClass('d-none');
            }
        } else {
            passGroup.addClass('d-none');
            tokenGroup.addClass('d-none');
            webhookGroup.addClass('d-none').css('display', 'none');
            bankGroup.addClass('d-none');
            nameGroup.removeClass('d-none'); // Show Name when Manual
        }
    };
    
    const handleBankCodeChange = function(element) {
        const selectedText = $(element).find("option:selected").text();
        const value = $(element).val();
        
        let nameInput, passGroup, providerSelect;
        if (element.id === 'bank_code') {
             // Create Modal
             nameInput = $('#modal-create input[name="name"]');
             passGroup = $('#group-password');
             providerSelect = $('#provider');
        } else {
             // Edit Modal
             const form = $(element).closest('form');
             nameInput = form.find('input[name="name"]');
             const id = form.find('select.provider-select').data('id');
             passGroup = $(`#group-password-${id}`);
             providerSelect = $(`#edit_provider_${id}`);
        }
        
        // Auto-fill name
        if (value && nameInput.length && selectedText !== '-- Chọn ngân hàng --') {
            nameInput.val(selectedText);
        }

        // Toggle Password IB
        if (providerSelect.val()) {
            if (providerSelect.val() === 'sepay' || value === 'TheSieuRe' || value === 'ViettelMoney') {
                passGroup.addClass('d-none');
            } else {
                passGroup.removeClass('d-none');
            }
        }
    }


    $(document).ready(function() {
        // DataTable
        if ($.fn.DataTable) {
            $('#basic-1').DataTable({
                order: [[0, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }

        // Provider Change Events (Delegated + Direct)
        $(document).on('change', 'select[name="provider"], #provider, .provider-select', function() {
            handleProviderChange(this);
        });
        
        // Initial Provider Load
        $('select[name="provider"], #provider, .provider-select').each(function() {
             handleProviderChange(this);
        });
        
        // Bank Code Change Events (Delegated for dynamic content or direct bind)
        $(document).on('change', '#bank_code', function() { handleBankCodeChange(this); });
        $(document).on('change', '.bank-code-select', function() { handleBankCodeChange(this); });
        
        if ($("textarea.ckeditor").length > 0) {
            CKEDITOR.replace("note");
        }
    });
  </script>
@endsection

@section('scripts')
<script src="/plugins/ckeditor/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        if ($("textarea.ckeditor").length > 0) {
            CKEDITOR.replace("note", {
                height: 300,
                clipboard_handleImages: false,
                filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
                filebrowserUploadMethod: 'form'
            });
        }
    });
</script>
@endsection
