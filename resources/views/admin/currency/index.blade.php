@extends('admin.layouts.master')
@section('title', 'Admin: Quản lý Tiền tệ')
@section('styles')
<style>
  .choices__inner {
      min-height: 44px;
      border-radius: 0.375rem !important;
  }
  
  /* Custom CSS for Choices.js in Modals */
  .choices__list--dropdown {
      z-index: 10000 !important;
      background-color: white !important;
      background: white !important;
      color: #333 !important;
      border: 1px solid #ddd !important;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
      position: absolute !important;
      width: 100% !important;
  }
  
  /* FORCE SCROLLBAR ON ALL LIST ELEMENTS */
  .choices__list--dropdown .choices__list,
  .choices__list--dropdown div[role="listbox"] {
      max-height: 250px !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
      background-color: white !important;
      scrollbar-gutter: stable; /* Keeps space for scrollbar to avoid jumping */
  }
  
  /* Custom Scrollbar */
  .choices__list--dropdown *::-webkit-scrollbar {
      width: 10px !important;
      display: block !important;
  }
  .choices__list--dropdown *::-webkit-scrollbar-track {
      background: #f1f1f1 !important;
  }
  .choices__list--dropdown *::-webkit-scrollbar-thumb {
      background: #7367f0 !important;
      border-radius: 4px;
      border: 2px solid #f1f1f1;
  }
  .choices__list--dropdown *::-webkit-scrollbar-thumb:hover {
      background: #5a4cf5 !important;
  }

  .choices {
      overflow: visible !important;
      margin-bottom: 0 !important;
  }
  
  .modal-body {
      overflow: visible !important;
  }

  .choices__list--dropdown .choices__item--selectable.is-highlighted {
      background-color: #7367f0 !important;
      color: #fff !important;
  }
</style>
@endsection
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">
        Quản lý Tiền tệ 
        <span class="badge bg-success-gradient text-white fs-12 ms-2">API fxratesapi.com</span>
      </div>
      <div class="d-flex gap-2">
           <a href="{{ route('admin.currency.sync_all') }}" class="btn btn-warning-gradient">
              <i class="fa fa-sync"></i> Refresh Rates
           </a>
           <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#createCurrencyModal">
              <i class="fa fa-plus"></i> Thêm mới
           </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Tỷ giá</th>
              <th>Mã</th>
              <th>Tên</th>
              <th>Mode</th>
              <th>Trạng thái</th>
            </tr>
          <tbody>
            @foreach ($currencies as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                        @if($item->code === 'VND')
                            <span class="badge bg-warning">Mặc định</span>
                        @else
                            <button type="button" class="badge bg-primary-gradient text-white border-0" onclick="editCurrency({{ $item->id }})">
                                <i class="fa fa-edit"></i> sửa
                            </button>
                            <form action="{{ route('admin.currency.delete', $item->id) }}" method="POST" class="d-inline axios-form" data-reload="true">
                                @csrf
                                <button type="submit" class="badge bg-danger-gradient text-white border-0">
                                    <i class="fa fa-trash"></i> xoá
                                </button>
                            </form>
                        @endif
                  </div>
                </td>
                <td>
                  {{ $item->code === 'VND' ? '1' : number_format($item->rate / 1000, 15, ',', '') }}
                </td>
                <td><strong>{{ $item->code }}</strong></td>
                <td>{{ $item->name }}</td>
                <td>
                    @if($item->rate_mode == 'auto')
                        <span class="badge bg-info">Auto</span>
                    @else
                        <span class="badge bg-secondary">Manual</span>
                    @endif
                </td>
                <td>
                    @if($item->status || $item->code === 'VND')
                    <span class="badge bg-success">Active</span>
                    @else
                    <span class="badge bg-danger">Inactive</span>
                    @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Create Modal -->
  <div class="modal fade" id="createCurrencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Thêm Tiền Tệ Mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.currency.store') }}" method="POST" class="default-form axios-form" data-reload="true">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Code</label>
                        <select class="form-select" name="code" id="create_code" required onchange="fillCurrencyName('create')">
                            <option value="">-- Chọn mã tiền tệ --</option>
                            @foreach($supported as $code => $detail)
                                @if(($detail['status'] ?? '') !== 'AVAILABLE') @continue @endif
                                <option value="{{ $code }}" data-name="{{ $detail['currencyName'] }}" data-symbol="{{ $detail['symbol'] ?? '' }}">{{ $code }} - {{ $detail['currencyName'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên Tiền Tệ</label>
                        <input type="text" class="form-control" name="name" id="create_name" required placeholder="United States Dollar">
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ký hiệu trái</label>
                        <input type="text" class="form-control" name="symbol_left" id="create_symbol_left" placeholder="$">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ký hiệu phải</label>
                        <input type="text" class="form-control" name="symbol_right" id="create_symbol_right" placeholder="">
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Số thập phân</label>
                        <input type="number" class="form-control" name="decimals" value="2" min="0" max="8">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dấu phân cách</label>
                        <input type="text" class="form-control" name="separator" placeholder="," value=".">
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chế độ Tỷ giá</label>
                        <select class="form-control" name="rate_mode" id="create_rate_mode" onchange="toggleRateMode('create')">
                            <option value="manual">Thủ công (Manual)</option>
                            <option value="auto">Tự động (Auto API)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tỷ giá</label>
                        <input type="number" step="0.000000000001" class="form-control" name="rate" id="create_rate_input" required value="1">
                        <small class="text-info" id="create_rate_hint"></small>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="create_status" class="form-label">Trạng thái</label>
                        <select class="form-select" id="create_status" name="status">
                            <option value="1" selected>Kích hoạt</option>
                            <option value="0">Tắt</option>
                        </select>
                    </div>
                </div>

            <div class="mb-3">
              <button class="btn btn-primary-gradient w-100" type="submit">Thêm mới</button>
            </div>
              </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editCurrencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cập nhật Tiền Tệ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <!-- Form action set to update route -->
            <form id="editForm" action="{{ route('admin.currency.update') }}" method="POST" class="default-form axios-form" data-reload="true">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="is_default" id="edit_is_default">
                <input type="hidden" name="domain" id="edit_domain">
                
                <div id="editAlert" class="alert alert-info border-info d-none">
                    <i class="fa fa-info-circle"></i> Đây là tiền tệ mặc định. Mã tiền tệ, Tỷ giá và Trạng thái đã bị khóa.
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Code</label>
                        <div id="edit_code_wrapper">
                            <select class="form-select" name="code" id="edit_code" required onchange="fillCurrencyName('edit')">
                                <option value="">-- Chọn mã tiền tệ --</option>
                                @foreach($supported as $code => $detail)
                                    @if(($detail['status'] ?? '') !== 'AVAILABLE') @continue @endif
                                    <option value="{{ $code }}" data-name="{{ $detail['currencyName'] }}" data-symbol="{{ $detail['symbol'] ?? '' }}">{{ $code }} - {{ $detail['currencyName'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="text" class="form-control d-none" id="edit_code_readonly" name="code_readonly" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên Tiền Tệ</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ký hiệu trái</label>
                        <input type="text" class="form-control" name="symbol_left" id="edit_symbol_left">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ký hiệu phải</label>
                        <input type="text" class="form-control" name="symbol_right" id="edit_symbol_right">
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Số thập phân</label>
                        <input type="number" class="form-control" name="decimals" id="edit_decimals" min="0" max="8">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dấu phân cách</label>
                        <input type="text" class="form-control" name="separator" id="edit_separator">
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chế độ Tỷ giá</label>
                        <select class="form-control" name="rate_mode" id="edit_rate_mode" onchange="toggleRateMode('edit')">
                            <option value="manual">Thủ công (Manual)</option>
                            <option value="auto">Tự động (Auto API)</option>
                        </select>
                        <input type="hidden" name="rate_mode" id="edit_rate_mode_hidden" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tỷ giá</label>
                        <input type="number" step="0.000000000001" class="form-control" name="rate" id="edit_rate_input" required>
                        <small class="text-info" id="edit_rate_hint"></small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="edit_status" class="form-label">Trạng thái</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="1">Kích hoạt</option>
                            <option value="0">Tắt</option>
                        </select>
                    </div>
                </div>
    
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-2 fs-15 fw-bold" style="background-color: #7367f0; border-color: #7367f0;">Cập Nhật</button>
                </div>
              </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const currencies = @json($currencies->keyBy('id'));
    let editChoices;

    document.addEventListener('DOMContentLoaded', function() {
        // Init Choices for Create (Manual)
        const createEl = document.getElementById('create_code');
        if (createEl) {
            new Choices(createEl, {
                searchEnabled: true,
                searchChoices: true,
                itemSelectText: '',
                shouldSort: false
            });
        }

        // Init Choices for Edit (Manual)
        const editEl = document.getElementById('edit_code');
        if (editEl) {
            editChoices = new Choices(editEl, {
                searchEnabled: true,
                searchChoices: true,
                itemSelectText: '',
                shouldSort: false
            });
        }
    });

    function editCurrency(id) {
        const data = currencies[id];
        if (!data) return;

        // Populate Form
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_is_default').value = data.is_default ? 1 : 0;
        document.getElementById('edit_domain').value = data.domain ?? '';
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_symbol_left').value = data.symbol_left ?? '';
        document.getElementById('edit_symbol_right').value = data.symbol_right ?? '';
        document.getElementById('edit_decimals').value = data.decimals;
        document.getElementById('edit_separator').value = data.separator ?? '';
        
        // Handle Default Currency Logic
        const isDefault = data.code === 'VND';
        const alertBox = document.getElementById('editAlert');
        const codeWrapper = document.getElementById('edit_code_wrapper');
        const codeReadonly = document.getElementById('edit_code_readonly');
        const rateModeSelect = document.getElementById('edit_rate_mode');
        const rateModeHidden = document.getElementById('edit_rate_mode_hidden');
        const rateInput = document.getElementById('edit_rate_input');
        const statusCheck = document.getElementById('edit_status');
        const domainSelect = document.getElementById('edit_domain');

        if (isDefault) {
            alertBox.classList.remove('d-none');
            
            // Hide Choices, show readonly input
            codeWrapper.classList.add('d-none');
            codeReadonly.classList.remove('d-none');
            codeReadonly.value = data.code;

            // Lock Rate Mode
            rateModeSelect.disabled = true;
            rateModeSelect.value = 'manual';
            rateModeHidden.disabled = false; 
            rateModeHidden.value = 'manual';

            // Lock Rate
            rateInput.value = '1';
            rateInput.readOnly = true;

            // Lock Status
            statusCheck.value = "1";
            statusCheck.disabled = true;

             // Lock Domain & Default Select
             // We generally don't want to change 'Default' status of the default currency directly?
             // Actually, language allows changing it? IDK.
             // But for currency, if it IS default, we probably want to keep it default 
             // unless we set another one.
             // But user might want to assign domain to it.
             // Let's leave them enabled for now or follow specific requirements.
        } else {
            alertBox.classList.add('d-none');
            
            // Show Choices
            codeWrapper.classList.remove('d-none');
            codeReadonly.classList.add('d-none');
            
            // Set Choices Value
            if (editChoices) {
                editChoices.setChoiceByValue(data.code);
            } else {
                document.getElementById('edit_code').value = data.code;
            }

            // Rate Mode
            rateModeSelect.disabled = false;
            rateModeSelect.value = data.rate_mode;
            rateModeHidden.disabled = true;

            // Rate - Display as Rate / 1000, but VND is always 1
            rateInput.readOnly = (data.rate_mode === 'auto');
            if (data.code === 'VND') {
                rateInput.value = '1';
            } else {
                rateInput.value = (parseFloat(data.rate) / 1000).toFixed(15).replace(/\.?0+$/, "");
            }
            
            // Status
            statusCheck.disabled = false;
            statusCheck.value = data.status == 1 ? "1" : "0";
        }

        // Trigger helpers
        toggleRateMode('edit');

        // Show Modal
        var myModal = new bootstrap.Modal(document.getElementById('editCurrencyModal'));
        myModal.show();
    }

    function fillCurrencyName(type) {
        // Updated to not rely on choicesInstance
        const selectId = (type === 'create') ? 'create_code' : 'edit_code';
        const nameId = (type === 'create') ? 'create_name' : 'edit_name';
        const symLeftId = (type === 'create') ? 'create_symbol_left' : 'edit_symbol_left';
        const symRightId = (type === 'create') ? 'create_symbol_right' : 'edit_symbol_right';
        
        const selectEl = document.getElementById(selectId);
        const selectedValue = selectEl.value;

        // Iterate options to find data-name and data-symbol
        let name = '';
        let symbol = '';
        for (let i = 0; i < selectEl.options.length; i++) {
             if (selectEl.options[i].value === selectedValue) {
                 name = selectEl.options[i].getAttribute('data-name');
                 symbol = selectEl.options[i].getAttribute('data-symbol');
                 break;
             }
        }

        if (name) {
            document.getElementById(nameId).value = name;
        }

        if (symbol) {
            // Some heuristics for symbol placement
            const rightSymbols = ['VND', 'KHR', 'LAK'];
            if (rightSymbols.includes(selectedValue)) {
                document.getElementById(symLeftId).value = '';
                document.getElementById(symRightId).value = symbol;
            } else {
                document.getElementById(symLeftId).value = symbol;
                document.getElementById(symRightId).value = '';
            }
        }
    }

    function toggleRateMode(type) {
        const modeId = (type === 'create') ? 'create_rate_mode' : 'edit_rate_mode';
        const inputId = (type === 'create') ? 'create_rate_input' : 'edit_rate_input';
        const hintId = (type === 'create') ? 'create_rate_hint' : 'edit_rate_hint';

        const mode = document.getElementById(modeId).value;
        const rateInput = document.getElementById(inputId);
        const rateHint = document.getElementById(hintId);
        
        // Prevent editing default currency logic issues validation
         if(document.getElementById(modeId).disabled) return; 

        if (mode === 'auto') {
            // Save current manual value to restore later if it's a number
            if(rateInput.value && rateInput.value !== '') {
                rateInput.setAttribute('data-prev-value', rateInput.value);
            }
            
            rateInput.readOnly = true; 
            // In case it was previously disabled by some other logic
            rateInput.disabled = false; 
            // FIX: Do not clear value, let it show previous or current rate
            // rateInput.value = ''; 
            // rateInput.placeholder = "Auto";
            rateHint.innerText = "";
        } else {
            rateInput.disabled = false; // CRITICAL FIX: Re-enable input
            rateInput.readOnly = false;
            
            // Restore previous value if exists
            const prevVal = rateInput.getAttribute('data-prev-value');
            if (prevVal) {
                rateInput.value = prevVal;
            } else {
                 if (type === 'create' && rateInput.value === '') rateInput.value = '1';
            }
            
            rateInput.placeholder = "1";
            rateHint.innerText = "";
        }
    }
  </script>
@endsection
