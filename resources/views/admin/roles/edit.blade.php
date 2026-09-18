@extends('admin.layouts.master')
@section('title', 'Admin: Danh Sách Role')
@section('styles')
<style>
    .bg-light-gray { background-color: #f8f9fa; }
    .custom-card { transition: all 0.3s ease; }
    .custom-card:hover { border-color: #7367f0 !important; }
    .group-check:checked + label { color: #7367f0 !important; }
    .form-check-input:checked { background-color: #7367f0; border-color: #7367f0; }
    .permission-check:checked + label { font-weight: 500; color: #333; }
    .card-header { border-bottom: 1px solid rgba(0,0,0,0.05); }
    .bg-soft-primary { background-color: rgba(115, 103, 240, 0.1); }
</style>
@endsection
@section('content')
<div class="card custom-card">
    <div class="card-header justify-content-between align-items-center">
        <div class="card-title">CHỈNH SỬA ROLE</div>
        <div>
            <span class="fs-5 me-2">Tên vai trò <b>'{{ $role->name }}'</b></span>
            <span class="text-muted"><a href="{{ route('admin.role') }}">Roles</a> &raquo; Chỉnh sửa</span>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.role.update', $role->id) }}" method="POST">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold mt-2">Tên vai trò <span class="text-danger">(*)</span></label>
                </div>
                <div class="col-md-9">
                    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold mt-2">Cấp bậc (Số càng lớn, quyền càng cao)</label>
                </div>
                <div class="col-md-9">
                    <input type="number" name="level" class="form-control" value="{{ old('level', $role->level) }}" min="0" required>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold mt-2">Ẩn lịch sử trước ngày</label>
                </div>
                <div class="col-md-9">
                    <input type="date" name="hide_old_history" class="form-control" value="{{ old('hide_old_history', $role->hide_old_history ? $role->hide_old_history->format('Y-m-d') : '') }}">
                    <small class="text-muted">Để trống nếu không ẩn. Mọi dữ liệu (hoạt động, nạp tiền, giao dịch, hóa đơn,...) trước ngày này sẽ bị ẩn đối với vai trò này.</small>
                </div>
            </div>

            <div class="row border-top pt-4 mb-3">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input check-all" type="checkbox" id="checkAll">
                        <label class="form-check-label fw-bold" for="checkAll">Chọn tất cả các quyền</label>
                    </div>
                </div>
            </div>

            @foreach($permissionsMap as $groupName => $content)
            <div class="card custom-card border shadow-none mb-4">
                <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
                    <div class="form-check mb-0">
                        <input class="form-check-input group-check" type="checkbox" id="group_{{ Str::slug($groupName) }}" data-group="{{ Str::slug($groupName) }}">
                        <label class="form-check-label fw-bold text-primary mb-0" for="group_{{ Str::slug($groupName) }}">
                            <i class="ri-shield-user-line me-1"></i> {{ $groupName }}
                        </label>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        @foreach($content as $key => $value)
                            @if(is_array($value))
                                {{-- Level 1 Group --}}
                                <div class="col-md-12 mb-3">
                                    <div class="p-2 bg-light-gray rounded-2 mb-2 d-flex align-items-center justify-content-between">
                                        <h6 class="fw-bold mb-0 text-muted" style="font-size: 0.9rem;">
                                            <i class="ri-arrow-right-s-line"></i> {{ $key }}
                                        </h6>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input subgroup-check" type="checkbox" data-subgroup="{{ Str::slug($groupName . '_' . $key) }}">
                                            <label class="form-check-label text-muted ms-1" style="font-size: 0.7rem;">Chọn tất cả</label>
                                        </div>
                                    </div>
                                    <div class="ps-3">
                                        <div class="row g-2">
                                            @foreach($value as $subKey => $subValue)
                                                @if(is_array($subValue))
                                                    {{-- Level 2 Group --}}
                                                    @php $subGroupId = Str::slug($groupName . '_' . $key . '_' . $subKey); @endphp
                                                    <div class="col-md-12 mb-2 border-start border-2 ps-3 ms-1">
                                                        <div class="mb-1 d-flex align-items-center">
                                                            <span class="badge bg-soft-primary text-primary fw-bold me-2" style="font-size: 0.8rem;">{{ $subKey }}</span>
                                                            <div class="form-check mb-0">
                                                                <input class="form-check-input sub-subgroup-check {{ Str::slug($groupName . '_' . $key) }}-child" type="checkbox" data-subsubgroup="{{ $subGroupId }}">
                                                                <label class="form-check-label text-muted ms-1" style="font-size: 0.65rem;">Chọn tất cả</label>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap">
                                                            @foreach($subValue as $permKey => $permLabel)
                                                                <div class="form-check me-3 mb-1" style="min-width: 140px;">
                                                                    <input class="form-check-input permission-check {{ Str::slug($groupName) }}-check {{ Str::slug($groupName . '_' . $key) }}-item {{ $subGroupId }}-item" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="perm_{{ $permKey }}" 
                                                                        {{ in_array($permKey, is_array($role->permissions) ? $role->permissions : []) ? 'checked' : '' }}>
                                                                    <label class="form-check-label py-0" for="perm_{{ $permKey }}" style="font-size: 0.85rem;">
                                                                        {{ $permLabel }}
                                                                    </label>
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem; opacity: 0.6; margin-top: -2px;">{{ $permKey }}</small>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- Level 2 Perm --}}
                                                    <div class="col-xxl-2 col-xl-3 col-md-4 col-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-check {{ Str::slug($groupName) }}-check {{ Str::slug($groupName . '_' . $key) }}-item" type="checkbox" name="permissions[]" value="{{ $subKey }}" id="perm_{{ $subKey }}" 
                                                                {{ in_array($subKey, is_array($role->permissions) ? $role->permissions : []) ? 'checked' : '' }}>
                                                            <label class="form-check-label py-0" for="perm_{{ $subKey }}" style="font-size: 0.85rem;">
                                                                {{ $subValue }}
                                                            </label>
                                                            <small class="text-muted d-block" style="font-size: 0.65rem; opacity: 0.6; margin-top: -2px;">{{ $subKey }}</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Level 1 Perm --}}
                                <div class="col-xxl-2 col-xl-3 col-md-4 col-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input permission-check {{ Str::slug($groupName) }}-check" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $key }}" 
                                            {{ in_array($key, is_array($role->permissions) ? $role->permissions : []) ? 'checked' : '' }}>
                                        <label class="form-check-label py-0" for="perm_{{ $key }}" style="font-size: 0.85rem;">
                                            {{ $value }}
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.65rem; opacity: 0.6; margin-top: -2px;">{{ $key }}</small>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach

            <div class="d-flex mt-4 border-top pt-4">
                <a href="{{ route('admin.role') }}" class="btn btn-danger me-2"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    // Check All
    $('#checkAll').change(function() {
        $('.permission-check, .group-check, .subgroup-check, .sub-subgroup-check').prop('checked', $(this).prop('checked'));
    });

    // Group Check (Top Level Cards)
    $('.group-check').change(function() {
        const groupClass = '.' + $(this).data('group') + '-check';
        const card = $(this).closest('.card');
        card.find('.permission-check, .subgroup-check, .sub-subgroup-check').prop('checked', $(this).prop('checked'));
        updateCheckAll();
    });

    // Subgroup Check (Section Headers)
    $('.subgroup-check').change(function() {
        const groupSlug = $(this).data('subgroup');
        const section = $(this).closest('.col-md-12');
        section.find('.' + groupSlug + '-item, .' + groupSlug + '-child').prop('checked', $(this).prop('checked'));
        updateParentGroupCheck(this);
        updateCheckAll();
    });

    // Sub-subgroup Check (Badges)
    $('.sub-subgroup-check').change(function() {
        const groupSlug = $(this).data('subsubgroup');
        const container = $(this).closest('.col-md-12');
        container.find('.' + groupSlug + '-item').prop('checked', $(this).prop('checked'));
        updateParentSubgroupCheck(this);
        updateCheckAll();
    });

    // Individual Permission Check
    $('.permission-check').on('change', function() {
        updateAllParentChecks(this);
        updateCheckAll();
    });

    // Standard behavior: auto-check VIEW when action is checked
    $('.permission-check').on('change', function() {
        if ($(this).prop('checked')) {
            const permKey = $(this).val();
            if (permKey.includes('_add') || permKey.includes('_edit') || permKey.includes('_delete') || permKey.includes('_update') || permKey.includes('_run') || permKey.includes('_approve') || permKey.includes('_reply') || permKey.includes('_send') || permKey.includes('_use')) {
                const viewKey = permKey.replace(/(_add|_edit|_delete|_update|_run|_approve|_reply|_send|_use)/, '_view');
                $('#perm_' + viewKey).prop('checked', true);
            }
        }
    });

    // Evaluate on load
    $('.sub-subgroup-check').each(function() { updateParentSubgroupCheck(this); });
    $('.subgroup-check').each(function() { updateParentGroupCheck(this); });
    $('.group-check').each(function() { updateGroupCheckOnLoad(this); });
    updateCheckAll();

    function updateAllParentChecks(element) {
        // Update sub-subgroup
        const container = $(element).closest('.border-start');
        if (container.length) {
            const subSubCheck = container.find('.sub-subgroup-check');
            if (subSubCheck.length) updateParentSubgroupCheck(subSubCheck[0]);
        }
        
        // Update subgroup
        const section = $(element).closest('.col-md-12.mb-3');
        if (section.length) {
            const subCheck = section.find('.subgroup-check');
            if (subCheck.length) updateParentGroupCheck(subCheck[0]);
        }

        // Update main group
        const card = $(element).closest('.card');
        const groupCheck = card.find('.group-check');
        if (groupCheck.length) updateGroupCheckOnLoad(groupCheck[0]);
    }

    function updateParentSubgroupCheck(checkEl) {
        const groupSlug = $(checkEl).data('subsubgroup');
        const allItems = $('.' + groupSlug + '-item');
        const checkedItems = $('.' + groupSlug + '-item:checked');
        $(checkEl).prop('checked', allItems.length > 0 && allItems.length === checkedItems.length);
    }

    function updateParentGroupCheck(checkEl) {
        const groupSlug = $(checkEl).data('subgroup');
        const allItems = $('.' + groupSlug + '-item');
        const checkedItems = $('.' + groupSlug + '-item:checked');
        $(checkEl).prop('checked', allItems.length > 0 && allItems.length === checkedItems.length);
    }

    function updateGroupCheckOnLoad(groupEl) {
        const groupClass = '.' + $(groupEl).data('group') + '-check';
        const allChecked = $(groupClass).length > 0 && $(groupClass).length === $(groupClass + ':checked').length;
        $(groupEl).prop('checked', allChecked);
    }

    function updateCheckAll() {
        const allPlural = $('.permission-check').length === $('.permission-check:checked').length;
        $('#checkAll').prop('checked', allPlural);
    }
});
</script>
@endsection
