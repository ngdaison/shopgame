@extends('admin.layouts.master')
@section('title', 'Admin: Cập nhật Chuyển tiếp tên miền')
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
  <div class="row justify-content-center">
    <div class="col-md-8">
        <form action="{{ route('admin.domain.redirects.update', ['id' => $redirect->id]) }}" method="POST" class="default-form" data-reload="true">
            @csrf
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Cập nhật thông tin</div>
                    <a href="{{ route('admin.domain.redirects.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Quay lại</a>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i> Bạn đang chỉnh sửa nhóm chuyển hướng cho tên miền đích bên dưới.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Chuyển tiếp tên miền (Đích)</label>
                        <select name="target_domain_id" id="target_domain_id" class="form-select py-2 choices-single">
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" {{ $targetId == $domain->id ? 'selected' : '' }}>{{ $domain->domain }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Đây là tên miền chính mà người dùng sẽ truy cập vào.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Chuyển tiếp đến (Chọn nhiều)</label>
                        <select name="source_domain_ids[]" id="source_domain_ids" class="form-select py-2 choices-multiple" multiple>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" {{ in_array($domain->id, $currentSourceIds) ? 'selected' : '' }}>{{ $domain->domain }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Các tên miền này sẽ chuyển hướng về "Chuyển tiếp tên miền" ở trên.</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary-gradient px-4">Cập nhật cấu hình</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
  </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const targetSelect = document.getElementById('target_domain_id');
        const sourceSelect = document.getElementById('source_domain_ids');

        const targetChoices = new Choices(targetSelect, {
            searchEnabled: true,
            itemSelectText: '',
            placeholderValue: '-- Chọn tên miền đích --',
        });

        const sourceChoices = new Choices(sourceSelect, {
            removeItemButton: true,
            searchEnabled: true,
            placeholderValue: '-- Chọn các tên miền nguồn --',
            noResultsText: 'Không tìm thấy kết quả',
            noChoicesText: 'Hết lựa chọn để thay thế',
        });
    });
</script>
@endsection
