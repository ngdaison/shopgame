@extends('admin.layouts.master')
@section('title', 'Admin: Language Translation - ' . $language->name)
@section('content')
  <div class="card custom-card">
    <div class="card-header">
        <div class="card-title">| Thêm nội dung</div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">Default:</label>
            <textarea id="new-key" class="form-control" rows="2" placeholder="Nhập nội dung mặc định"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">{{ $language->name }}:</label>
            <textarea id="new-value" class="form-control" rows="2" placeholder="Nhập nội dung cần dịch"></textarea>
        </div>
        <button type="button" class="btn btn-primary-gradient w-100 fw-bold" onclick="addNewTranslation()">THÊM NGAY</button>
    </div>
  </div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="mb-3">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <h4 class="modal-title fw-bold mb-2">Đang xử lý!</h4>
            <p class="text-muted mb-4">Không được tắt trang này, vui lòng đợi trong giây lát!</p>
            
            <!-- Progress Bar -->
            <div class="progress mb-3" style="height: 25px;">
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%"></div>
            </div>
            <h5 class="text-primary fw-bold mb-4"><span id="progress-percent">0</span>% Hoàn thành</h5>

            <!-- Stats Grid -->
            <div class="row g-3">
                <div class="col-4">
                    <div class="p-3 border rounded border-primary">
                        <i class="fa fa-list text-primary fs-4 mb-2"></i>
                        <h4 class="text-primary fw-bold mb-1" id="stat-total">0</h4>
                        <small class="text-muted">Tổng số</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 border rounded border-success">
                        <i class="fa fa-check text-success fs-4 mb-2"></i>
                        <h4 class="text-success fw-bold mb-1" id="stat-success">0</h4>
                        <small class="text-muted">Thành công</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 border rounded border-danger">
                        <i class="fa fa-times text-danger fs-4 mb-2"></i>
                        <h4 class="text-danger fw-bold mb-1" id="stat-failed">0</h4>
                        <small class="text-muted">Thất bại</small>
                    </div>
                </div>
                <!-- Pending Removed -->
            </div>
        </div>
    </div>
</div>

  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">| Translates <span id="filter-badge" class="badge bg-warning text-dark ms-2" style="display: none; font-size: 10px;">Chưa dịch</span></div>
      <div class="d-flex gap-2">
          <button id="btn-show-all" class="btn btn-info-gradient btn-sm" onclick="showAll()" style="display: none;">
              <i class="fa fa-list"></i> Tất cả
          </button>
          <button id="btn-filter-untranslated" class="btn btn-warning-gradient btn-sm" onclick="filterUnTranslated()">
              <i class="fa fa-filter"></i> Chưa dịch
          </button>
          <button class="btn btn-secondary-gradient btn-sm" onclick="regenerateTranslations()">
              <i class="fa fa-refresh"></i> Tạo lại bản dịch
          </button>
          <button class="btn btn-primary-gradient btn-sm" onclick="bulkTranslate()">
              <i class="fa fa-magic"></i> Dịch tự động
          </button>
          <button id="btn-bulk-delete" class="btn btn-danger-gradient btn-sm" onclick="bulkDelete()" disabled>
              <i class="fa fa-trash"></i> Xoá đã chọn (<span id="checked-count">0</span>)
          </button>
      </div>
    </div>
    <div class="card-body">

      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-striped text-center" id="translation-table">
          <thead class="sticky-top bg-white shadow-sm">
            <tr>
              <th width="40" data-orderable="false"><input type="checkbox" id="check-all"></th>
              <th width="40%">Default</th>
              <th width="40%">{{ $language->name }}</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($translations as $index => $item)
              <tr data-key="{{ $item->key }}">
                <td><input type="checkbox" class="check-item" value="{{ $item->key }}"></td>
                <td class="text-start">
                  <textarea class="form-control bg-light border-0 key-display" rows="1" readonly style="resize: none; overflow: hidden; min-height: 38px;">{{ $item->key }}</textarea>
                </td>
                <td>
                  <textarea class="form-control translation-value" rows="1" style="resize: none; overflow: hidden; min-height: 38px;" oninput="autoResizeRow(this)" onblur="saveTranslation(this, '{{ addslashes($item->key) }}')">{{ $item->value }}</textarea>
                </td>
                <td class="align-middle">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-primary-gradient btn-xs" onclick="autoTranslate(this, '{{ addslashes($item->key) }}')">
                      <i class="fa fa-magic"></i> Dịch tự động
                    </button>
                    <button class="btn btn-danger-gradient btn-xs" onclick="deleteTranslation('{{ addslashes($item->key) }}', this)">
                      <i class="fa fa-trash"></i> Delete
                    </button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>


@endsection

@section('scripts')
<script>
    // Check all
    document.addEventListener('change', function(e) {
        if (e.target.id === 'check-all') {
            document.querySelectorAll('.check-item').forEach(item => {
                item.checked = e.target.checked;
            });
            updateBulkStatus();
        }
        if (e.target.classList.contains('check-item')) {
            updateBulkStatus();
        }
    });

    function updateBulkStatus() {
        const selected = document.querySelectorAll('.check-item:checked');
        const btn = document.getElementById('btn-bulk-delete');
        const countSpan = document.getElementById('checked-count');
        
        if (selected.length > 0) {
            btn.disabled = false;
            countSpan.textContent = selected.length;
        } else {
            btn.disabled = true;
            countSpan.textContent = "0";
        }
    }

    // Auto-resize textareas
    function autoResizeRow(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }

    function initAutoResize() {
        document.querySelectorAll('textarea.translation-value, textarea.key-display').forEach(textarea => {
            autoResizeRow(textarea);
        });
    }

    // Run on load and after short delay to ensure rendering
    window.addEventListener('load', initAutoResize);
    
    function filterUnTranslated() {
        const table = $('#translation-table').DataTable();
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                const textarea = row.querySelector('.translation-value');
                const keyDisplay = row.querySelector('.key-display');
                if (!textarea || !keyDisplay) return true;
                
                const val = textarea.value.trim();
                const key = keyDisplay.value.trim();
                return val === '' || val === key;
            }
        );
        table.draw();
        
        document.getElementById('filter-badge').style.display = 'inline-block';
        document.getElementById('btn-filter-untranslated').style.display = 'none';
        document.getElementById('btn-show-all').style.display = 'inline-block';
        toastr.info('Đang lọc các mục chưa dịch');
    }

    function showAll() {
        $.fn.dataTable.ext.search.length = 0; // Clear all custom filters
        const table = $('#translation-table').DataTable();
        table.draw();
        
        document.getElementById('filter-badge').style.display = 'none';
        document.getElementById('btn-filter-untranslated').style.display = 'inline-block';
        document.getElementById('btn-show-all').style.display = 'none';
        toastr.info('Đã hiển thị tất cả bản dịch');
    }

    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#translation-table')) {
            $('#translation-table').DataTable().destroy();
        }
        
        $('#translation-table').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": false,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, 500, 1000, 5000, -1], [10, 25, 50, 100, 500, 1000, 5000, "Tất cả"]],
            "language": {
                "search": "Tìm kiếm...",
                "lengthMenu": "Hiển thị _MENU_ mục",
                "info": "Đang hiện _START_ đến _END_ của _TOTAL_ mục",
                "paginate": {
                    "first": "Đầu",
                    "last": "Cuối",
                    "next": "Tiếp",
                    "previous": "Trước"
                }
            },
            "drawCallback": function(settings) {
                initAutoResize();
            }
        });
    });

    setTimeout(initAutoResize, 500);
    


    async function addNewTranslation() {
        const key = document.getElementById('new-key').value;
        const value = document.getElementById('new-value').value;
        if (!key) {
            toastr.error('Vui lòng nhập khoá');
            return;
        }

        $showLoading();
        try {
            const { data: result } = await axios.post('{{ route("admin.language.translation.update", $language->id) }}', {
                key: key,
                value: value,
                _token: '{{ csrf_token() }}'
            });
            
            if (result.status === 'success') {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: 'Đã thêm từ mới'
                }));
                location.reload();
            } else {
                toastr.error(result.message);
            }
        } catch (error) {
            toastr.error('Lỗi khi thêm từ mới');
        } finally {
            $hideLoading();
        }
    }

    async function saveTranslation(el, key) {
        const value = el.value;
        try {
            await axios.post('{{ route("admin.language.translation.update", $language->id) }}', {
                key: key,
                value: value,
                _token: '{{ csrf_token() }}'
            });
            toastr.success('Đã lưu: ' + key, '', {timeOut: 1000});
        } catch (error) {
            toastr.error('Lỗi khi lưu: ' + key);
        }
    }

    async function autoTranslate(btn, text) {
        const row = btn.closest('tr');
        const textarea = row.querySelector('.translation-value');
        
        $showLoading();
        try {
            const { data: result } = await axios.post('{{ route("admin.language.translation.auto-translate", $language->id) }}', {
                text: text,
                _token: '{{ csrf_token() }}'
            });
            
            if (result.status === 'success') {
                textarea.value = result.translated;
                await saveTranslation(textarea, text);
            } else {
                toastr.error(result.message);
            }
        } catch (error) {
            toastr.error('Lỗi dịch tự động');
        } finally {
            $hideLoading();
        }
    }

    async function deleteTranslation(key, btn) {
        const confirmDelete = await Swal.fire({
            title: 'Bạn có chắc chắn muốn xóa?',
            text: "Xóa bản dịch cho: " + key,
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
            await axios.post('{{ route("admin.language.translation.delete", $language->id) }}', {
                key: key,
                _token: '{{ csrf_token() }}'
            });
            btn.closest('tr').remove();
            toastr.success('Đã xóa');
        } catch (error) {
            toastr.error('Lỗi khi xóa');
        } finally {
            $hideLoading();
        }
    }

    async function bulkTranslate() {
        const selected = Array.from(document.querySelectorAll('.check-item:checked')).map(el => el.value);
        if (selected.length === 0) {
            toastr.warning('Vui lòng chọn ít nhất một mục');
            return;
        }

        const confirmBulk = await Swal.fire({
            title: 'Xác nhận dịch hàng loạt?',
            text: "Hệ thống sẽ dịch " + selected.length + " mục.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Dịch ngay',
            cancelButtonText: 'Hủy'
        });
        if (!confirmBulk.isConfirmed) return;

        // Init UI
        const modal = new bootstrap.Modal(document.getElementById('progressModal'));
        modal.show();
        
        const total = selected.length;
        let success = 0;
        let failed = 0;
        let processed = 0;

        const updateUI = () => {
            const percent = Math.round((processed / total) * 100);
            document.getElementById('progress-bar').style.width = percent + '%';
            document.getElementById('progress-percent').textContent = percent;
            
            document.getElementById('stat-total').textContent = total;
            document.getElementById('stat-success').textContent = success;
            document.getElementById('stat-failed').textContent = failed;
        };

        // Concurrency Control: Serial execution (1 worker) with BATCH_SIZE=1 for live updates
        const BATCH_SIZE = 1;
        const CONCURRENCY = 1; 
        
        // Split selected into chunks
        const chunks = [];
        for (let i = 0; i < selected.length; i += BATCH_SIZE) {
            chunks.push(selected.slice(i, i + BATCH_SIZE));
        }
        
        const queue = [...chunks];

        const worker = async () => {
            while (queue.length > 0) {
                const batchKeys = queue.shift();
                updateUI();

                try {
                    const { data: result } = await axios.post('{{ route("admin.language.translation.bulk-auto-translate", $language->id) }}', {
                        keys: batchKeys,
                        _token: '{{ csrf_token() }}'
                    });
                    
                    if (result.status === 'success') {
                        success += result.count;
                        processed += result.count;
                    } else if (result.status === 'warning') {
                         success += result.count;
                         failed += (batchKeys.length - result.count);
                         processed += batchKeys.length;
                    } else {
                        failed += batchKeys.length;
                        processed += batchKeys.length;
                    }
                } catch (error) {
                    console.error('Translation chunk error:', error);
                    failed += batchKeys.length;
                    processed += batchKeys.length;
                } finally {
                    updateUI();
                }
            }
        };

        // Start workers
        const workers = [];
        for (let i = 0; i < CONCURRENCY; i++) {
            workers.push(worker());
        }

        await Promise.all(workers);

        // Done
        setTimeout(() => {
            modal.hide();
            toastr.success(`Dịch tự động hoàn tất!`);
            location.reload();
        }, 1000);
    }

    async function bulkDelete() {
        const selected = Array.from(document.querySelectorAll('.check-item:checked')).map(el => el.value);
        if (selected.length === 0) {
            toastr.warning('Vui lòng chọn ít nhất một mục');
            return;
        }

        const confirmBulk = await Swal.fire({
            title: 'Xác nhận xoá hàng loạt?',
            text: "Hệ thống sẽ xoá " + selected.length + " mục đã chọn.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Xoá ngay',
            cancelButtonText: 'Hủy'
        });
        if (!confirmBulk.isConfirmed) return;

        $showLoading('Đang xoá...');
        try {
            const { data: result } = await axios.post('{{ route("admin.language.translation.bulk-delete", $language->id) }}', {
                keys: selected,
                _token: '{{ csrf_token() }}'
            });
            
            if (result.status === 'success') {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: result.message
                }));
                location.reload();
            } else {
                toastr.error(result.message);
            }
        } catch (error) {
            toastr.error('Lỗi khi xoá hàng loạt');
        } finally {
            $hideLoading();
        }
    }

    async function regenerateTranslations() {
        const confirmRegen = await Swal.fire({
            title: 'Xác nhận tạo lại toàn bộ?',
            text: "Hệ thống sẽ tạo lại toàn bộ từ khoá",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#745af2',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy'
        });
        if (!confirmRegen.isConfirmed) return;
        
        $showLoading();
        try {
            const { data: result } = await axios.post('{{ route("admin.language.translation.regenerate", $language->id) }}', {
                _token: '{{ csrf_token() }}'
            });
            
            if (result.status === 'success') {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: result.message
                }));
                location.reload();
            } else {
                toastr.error(result.message);
            }
        } catch (error) {
            toastr.error('Lỗi khi nạp lại dữ liệu');
        } finally {
            $hideLoading();
        }
    }
</script>
<style>
    .translation-value:focus, .key-display:focus {
        outline: none !important;
        box-shadow: none !important;
        border-color: #dee2e6 !important;
    }
    .translation-value {
        border-color: #dee2e6;
    }
</style>
@endsection
