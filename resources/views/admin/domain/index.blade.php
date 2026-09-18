@extends('admin.layouts.master')
@section('title', 'Admin: Domain Manager')
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
  <div class="row">
    <div class="col-sm-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Quản lý Cấu hình Branding Tên miền</div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-stripped text-center datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Thao tác</th>
                            <th>Tên miền</th>
                            <th>Ngôn ngữ</th>
                            <th>Thời gian tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($configs as $index => $config)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @if(!$config->is_redirect)
                                        <a href="{{ route('admin.domain.edit', ['id' => $config->id]) }}" class="badge bg-primary-gradient text-white">
                                            <i class="fa fa-edit"></i> sửa
                                        </a>
                                        @else
                                        <a href="javascript:void(0)" class="badge bg-primary-gradient text-white edit-domain-trigger" data-config="{{ json_encode($config) }}">
                                            <i class="fa fa-edit"></i> sửa
                                        </a>
                                        @endif
                                        <a href="{{ route('admin.domain.show', ['id' => $config->id]) }}" class="badge bg-info-gradient text-white">
                                            <i class="fa fa-bar-chart"></i> thống kê
                                        </a>
                                        <a href="https://{{ $config->domain }}" target="_blank" class="badge bg-secondary-gradient text-white">
                                            <i class="fa fa-external-link"></i> web
                                        </a>
                                        <a href="javascript:deleteDomain('{{ $config->domain }}', {{ $config->id }})" class="badge bg-danger-gradient text-white">
                                            <i class="fa fa-trash"></i> xoá
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $config->domain }}</div>
                                    @if($config->is_redirect)
                                        <div class="small text-muted">
                                            <i class="fa fa-share"></i> Tên miền được chuyển tiếp 
                                            @php
                                                $targets = (array)$config->redirect_to;
                                            @endphp
                                            <b>{{ implode(', ', $targets) }}</b>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($config->language)
                                        <span class="badge bg-success-gradient">{{ $config->language->name }}</span>
                                    @else
                                        <span class="badge bg-secondary-gradient">Cấu hình chung</span>
                                    @endif
                                </td>
                                <td>{{ $config->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#modal-create">
                    <i class="fa fa-plus-circle"></i> Thêm mới
                </button>
            </div>
        </div>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Thêm thông tin mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>


        <div class="modal-body">
            @if(empty($allowedDomains))
                <div class="alert alert-warning mb-0 border-0">
                    Vui lòng thêm tên miền mới vào <b>Allowed Domains</b> trong <b>Cài đặt chung</b>.
                </div>
            @else
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên miền</label>
                    <select id="new-domain-input" class="form-select py-2">
                        <option value="">Chọn tên miền</option>
                        @foreach ($availableDomains as $domain)
                            <option value="{{ $domain }}">{{ $domain }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is-redirect-switch">
                    <label class="form-check-label fw-semibold" for="is-redirect-switch">Chuyển tiếp tên miền</label>
                </div>

                <div id="redirect-target-wrapper" class="mb-3 d-none">
                    <label class="form-label fw-semibold">Tên miền được chuyển</label>
                    <select id="redirect-to-input" class="form-select py-2" multiple>
                        {{-- Requirement: Show only domains NOT configured (available) --}}
                        @foreach ($availableDomains as $domain)
                            <option value="{{ $domain }}">{{ $domain }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4">
                    <button type="button" class="btn btn-primary-gradient w-100 py-2 fs-15 fw-bold" onclick="addDomain()">Thêm mới</button>
                </div>
            @endif
        </div>
      </div>
    </div>
  </div>
  <!-- Edit Domain Modal -->
  <div class="modal fade" id="editDomainModal" tabindex="-1" aria-labelledby="editDomainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="editDomainModalLabel">Cập nhật thông tin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-config-id">
            <div class="mb-3">
                <label class="form-label fw-semibold">Chuyển tiếp tên miền</label>
                <input type="text" id="edit-domain-display" class="form-control py-2 bg-light" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Tên miền được chuyển</label>
                <select id="edit-redirect-to-input" class="form-select py-2" multiple>
                    @foreach ($allowedDomains as $domain)
                        @php
                            $isRedirectTarget = $configs->where('domain', $domain)->where('is_redirect', true)->first();
                        @endphp
                        @if (!$isRedirectTarget)
                            <option value="{{ $domain }}">{{ $domain }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <button type="button" class="btn btn-primary-gradient w-100 py-2 fs-15 fw-bold" onclick="saveDomainEdit()">Cập nhật</button>
            </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
    let addChoices = null;
    let editChoices = null;

    $(document).ready(function() {
        // Initialize multi-select for Add Modal
        if (document.getElementById('redirect-to-input')) {
            addChoices = new Choices('#redirect-to-input', {
                removeItemButton: true,
                placeholderValue: '-- Chọn tên miền --',
                searchPlaceholderValue: 'Tìm kiếm tên miền...'
            });
        }

        // Initialize multi-select for Edit Modal
        if (document.getElementById('edit-redirect-to-input')) {
            editChoices = new Choices('#edit-redirect-to-input', {
                removeItemButton: true,
                placeholderValue: '-- Chọn tên miền --',
                searchPlaceholderValue: 'Tìm kiếm tên miền...'
            });
        }

        // Data from server
        const allowedDomainsData = @json($allowedDomains);
        const availableDomainsData = @json(array_values($availableDomains));

        // Function to populate Main Domain Dropdown
        function populateMainDomain(isRedirect) {
            const select = $('#new-domain-input');
            const currentVal = select.val();
            select.empty();
            select.append('<option value="">Chọn tên miền</option>');

            // Always show only available (unconfigured) domains for as the target/entry point
            const sourceData = availableDomainsData;

            sourceData.forEach(domain => {
                const isSelected = domain === currentVal ? 'selected' : '';
                select.append(`<option value="${domain}" ${isSelected}>${domain}</option>`);
            });
        }

        // Handle Add Modal Redirect Toggle
        $('#is-redirect-switch').on('change', function() {
            const isChecked = $(this).is(':checked');
            
            // Swap Main Domain Options
            populateMainDomain(isChecked);

            if (isChecked) {
                $('#redirect-target-wrapper').removeClass('d-none');
            } else {
                $('#redirect-target-wrapper').addClass('d-none');
            }
            
            // Trigger change to update the Source list filtering
            $('#new-domain-input').trigger('change');
        });

        // Initial population (Switch is off by default)
        populateMainDomain(false);

        // Delegate listener for edit buttons
        $(document).on('click', '.edit-domain-trigger', function() {
            const config = $(this).data('config');
            editDomain(config);
        });

        // Filter self-selection in Add Modal
        $('#new-domain-input').on('change', function() {
            const selectedDomain = $(this).val();
            
            if (addChoices) {
                // Get all choices
                const allChoices = @json(array_values($availableDomains));
                
                // Clear current choices
                addChoices.clearChoices();
                addChoices.removeActiveItems();
                
                // Re-add choices excluding selected one
                const newChoices = allChoices
                    .filter(domain => domain !== selectedDomain)
                    .map(domain => ({ value: domain, label: domain }));
                    
                addChoices.setChoices(newChoices, 'value', 'label', true);
            }
        });
    });

    function editDomain(config) {
        $('#edit-config-id').val(config.id);
        $('#edit-domain-display').val(config.domain);
        
        // Handle Choices.js value setting
        if (editChoices) {
            editChoices.removeActiveItems();
            const targets = Array.isArray(config.redirect_to) ? config.redirect_to : (config.redirect_to ? [config.redirect_to] : []);
            editChoices.setChoiceByValue(targets);
        }

        const myModal = new bootstrap.Modal(document.getElementById('editDomainModal'));
        myModal.show();
    }

    async function saveDomainEdit() {
        const id = $('#edit-config-id').val();
        const redirectTo = editChoices ? editChoices.getValue(true) : [];

        if (redirectTo.length === 0) {
            toastr.error('Vui lòng chọn ít nhất một tên miền để chuyển tiếp đến');
            return;
        }

        $showLoading();
        try {
            const { data: result } = await axios.post('{{ url("admin/domain/update") }}/' + id, {
                is_redirect: redirectTo.length > 0 ? 1 : 0,
                redirect_to: Array.isArray(redirectTo) ? redirectTo : (redirectTo ? [redirectTo] : []),
                _token: '{{ csrf_token() }}'
            });

            if (result.status) {
                toastr.success(result.message);
                location.reload();
            } else {
                toastr.error(result.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            toastr.error(error.response?.data?.message || 'Không thể lưu thay đổi');
        } finally {
            $hideLoading();
        }
    }

    async function deleteDomain(domain, id) {
        const confirmDelete = await Swal.fire({
            title: 'Bạn có chắc chắn muốn xóa?',
            text: `Xóa cấu hình branding cho tên miền ${domain}?`,
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
            const { data: result } = await axios.post('{{ route("admin.domain.delete") }}', {
                id: id,
                domain: domain,
                _token: '{{ csrf_token() }}'
            });

            if (result.status === true) {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: result.message
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

    async function addDomain() {
        const domain = document.getElementById('new-domain-input').value;
        const isRedirect = document.getElementById('is-redirect-switch')?.checked ? 1 : 0;
        const redirectToVal = addChoices ? addChoices.getValue(true) : [];
        const redirectTo = Array.isArray(redirectToVal) ? redirectToVal : (redirectToVal ? [redirectToVal] : []);

        if (!domain) {
            toastr.error('Vui lòng chọn tên miền');
            return;
        }

        if (isRedirect && redirectTo.length === 0) {
            toastr.error('Vui lòng chọn ít nhất một tên miền để chuyển tiếp đến');
            return;
        }

        $showLoading();

        try {
            const { data: result } = await axios.post('{{ route("admin.domain.store") }}', {
                domain: domain,
                is_redirect: isRedirect,
                redirect_to: redirectTo,
                _token: '{{ csrf_token() }}'
            }, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (result.status === true) {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: result.message
                }));
                location.reload();
            } else {
                $hideLoading();
                toastr.error(result.message || 'Có lỗi xảy ra', 'Thất Bại');
            }
        } catch (error) {
            $hideLoading();
            const msg = error.response?.data?.message || $catchMessage(error);
            toastr.error(msg, 'Thất Bại');
        }
    }
</script>
@endsection
