@extends('admin.layouts.master')
@section('title', 'Admin: Cấu hình USDT')
@section('content')
  <div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.usdt') }}">Nạp tiền</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.usdt') }}">Crypto USDT</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cấu hình</li>
            </ol>
        </nav>
    </div>
  </div>

  <div class="card custom-card shadow-sm">
    <div class="card-header justify-content-between">
      <div class="card-title text-uppercase"><i class="bx bx-cog me-1"></i> CẤU HÌNH NẠP USDT</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.deposit.usdt.config.update') }}" method="POST" class="axios-form" data-reload="true">
        @csrf
        <div class="row mb-3">
             <div class="col-md-6">
                 <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                      <option value="1" {{ in_array($config['status'] ?? '', ['1', 'on']) ? 'selected' : '' }}>Bật</option>
                      <option value="0" {{ in_array($config['status'] ?? '', ['0', 'off']) ? 'selected' : '' }}>Tắt</option>
                    </select>
                 </div>
                 <div class="mb-3">
                    <label for="type" class="form-label">Loại API</label>
                    <select name="type" id="type" class="form-control">
                        <option value="FPAYMENT.CO | TRC20" @if (($config['type'] ?? '') === 'FPAYMENT.CO | TRC20') selected @endif>FPAYMENT.CO | TRC20</option>
                        <option value="FPAYMENT.NET | TRC20, BEP20, POLYGON, SOLANA" @if (($config['type'] ?? '') === 'FPAYMENT.NET | TRC20, BEP20, POLYGON, SOLANA' || empty($config['type'])) selected @endif>FPAYMENT.NET | TRC20, BEP20, POLYGON, SOLANA</option>
                    </select>
                 </div>
                 <div class="mb-3">
                    <label for="merchant_id" class="form-label">Merchant ID</label>
                    <input type="text" class="form-control" name="merchant_id" id="merchant_id" value="{{ $config['merchant_id'] ?? '' }}">
                 </div>
                 <div class="mb-3">
                    <label for="api_token" class="form-label">Api Key <small class="text-danger fw-bold">(FPAYMENT.NET)</small></label>
                    <input type="text" class="form-control" name="api_token" id="api_token" value="{{ $config['api_token'] ?? '' }}">
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="mb-3">
                    <label for="min" class="form-label">Nạp tối thiểu</label>
                    <input type="number" class="form-control" name="min" id="min" value="{{ $config['min'] ?? 0 }}" required>
                 </div>
                 <div class="mb-3">
                    <label for="max" class="form-label">Nạp tối đa</label>
                    <input type="number" class="form-control" name="max" id="max" value="{{ $config['max'] ?? 100000000 }}" required>
                 </div>
                 <div class="mb-3">
                    <label for="exchange" class="form-label">1 USDT = </label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="exchange" id="exchange" value="{{ $config['exchange'] ?? 24000 }}" required>
                        <span class="input-group-text">VND</span>
                    </div>
                 </div>
                 <div class="mb-3">
                     <p class="mb-0">Hướng dẫn sử dụng: <a href="https://fpayment.net/docs" target="_blank text-primary">https://fpayment.net/docs</a></p>
                 </div>
             </div>
        </div>

        <div class="mb-3">
            <label for="note" class="form-label">Crypto Note</label>
            <textarea name="note" id="note" class="form-control ckeditor" rows="5">{{ $notice->value ?? $config['note'] ?? '' }}</textarea>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100 shadow-sm" style="background-color: #6f42c1; border-color: #6f42c1;"><i class="fa fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row mb-3 mt-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="m-0 font-weight-bold text-primary" style="padding-left: 10px; border-left: 5px solid #0d6efd;">DANH SÁCH USDT</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create">
                    <i class="fa fa-plus"></i> Thêm USDT
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="display table table-striped table-hover mb-0" id="basic-1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Thao tác</th>
                                <th>Wallet/Address</th>
                                <th>Trạng thái</th>
                                <th>Ngày Tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $accounts = $usdt->usdt_accounts ?? []; @endphp
                            @foreach ($accounts as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item['id'] }}"><i class="fa fa-edit"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteRow('{{ $item['id'] }}')"><i class="fa fa-trash"></i> Delete</button>
                                    </td>
                                    <td>{{ $item['address'] ?? $item['account'] ?? 'N/A' }}</td>
                                    <td>
                                        @if($item['status'] ?? false)
                                            <span class="badge bg-success">Hiển thị</span>
                                        @else
                                            <span class="badge bg-danger">Ẩn</span>
                                        @endif
                                    </td>
                                    <td>{{ isset($item['created_at']) ? \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y H:i') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </div>
@endsection
@section('scripts')
<script src="/plugins/ckeditor/ckeditor.js"></script>
<script>
    if ($("textarea.ckeditor").length > 0) {
        CKEDITOR.replace("note", {
            height: 300,
            clipboard_handleImages: false,
            filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
            filebrowserUploadMethod: 'form'
        });
    }
</script>
@endsection
