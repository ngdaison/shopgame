@extends('admin.layouts.master')
@section('title', 'Admin: Cấu hình nạp thẻ')
@section('content')
  <div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.cards') }}">Nạp tiền</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.cards') }}">Nạp thẻ cào</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cấu hình</li>
            </ol>
        </nav>
    </div>
  </div>

  <div class="card custom-card shadow-sm">
    <div class="card-header justify-content-between">
      <div class="card-title text-uppercase"><i class="bx bx-cog me-1"></i> CẤU HÌNH NẠP THẺ CÀO</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.deposit.cards.config.update') }}" method="POST" class="axios-form" data-reload="true">
        @csrf
        <div class="row mb-3">
             <div class="col-md-6">
                 <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                      <option value="on" @if (($config->value['status'] ?? '') === 'on') selected @endif>ON</option>
                      <option value="off" @if (($config->value['status'] ?? '') === 'off') selected @endif>OFF</option>
                    </select>
                 </div>
                 <div class="mb-3">
                    <label for="api_url" class="form-label">API Url</label>
                    <input type="text" class="form-control" name="api_url" id="api_url" value="{{ $config->value['api_url'] ?? 'https://card24h.com' }}">
                 </div>
                 <div class="mb-3">
                    <label class="form-label">Link Callback (GET/POST)</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ route('cron.deposit.card-callback') }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ route('cron.deposit.card-callback') }}')"><i class="fa fa-copy"></i></button>
                    </div>
                 </div>
                 <div class="mb-3">
                    <label for="partner_id" class="form-label">Partner ID</label>
                    <input type="text" class="form-control" name="partner_id" id="partner_id" value="{{ $config->value['partner_id'] ?? '' }}">
                 </div>
                 <div class="mb-3">
                    <label for="partner_key" class="form-label">Partner Key</label>
                    <input type="text" class="form-control" name="partner_key" id="partner_key" value="{{ $config->value['partner_key'] ?? '' }}">
                 </div>
                 {{-- Global fee removed as per request --}}
                 <input type="hidden" name="fee" value="0">
             </div>
             <div class="col-md-6">
                 <div class="mb-3">
                    <label for="types_list" class="form-label">Loại thẻ</label>
                    <textarea name="types_list" id="types_list" class="form-control" rows="12" placeholder="VIETTEL|VTT|25|10000:10,20000:20
VINAPHONE|VNP|25|10000,20000,50000
MOBIFONE|VMS|25">{{ $config->value['types_list'] ?? '' }}</textarea>
                    <small class="text-muted">
                        Ví dụ: <code>Viettel|VIETTEL|25|10000:10,20000</code><br>
                        - Tên Viettel, Mã API VIETTEL, Phí mặc định 25% (k để cx đc)<br>
                        - Chỉ cho phép nạp 10k (phí riêng 10%) và 20k (phí mặc định 25%)
                    </small>
                 </div>

             </div>
        </div>

        <div class="mb-3">
            <label for="note" class="form-label">Lưu ý nạp tiền (Trang nạp tiền qua thẻ / ngân hàng)</label>
            <textarea name="note" id="note" class="form-control ckeditor" rows="5">{{ $notice->value ?? $config->value['note'] ?? '' }}</textarea>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100 shadow-sm" style="background-color: #6f42c1; border-color: #6f42c1;"><i class="fa fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
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
