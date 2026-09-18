@extends('admin.layouts.master')
@section('title', 'Admin: Cấu hình Paypal')
@section('content')
  <div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.paypal') }}">Nạp tiền</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.deposit.paypal') }}">PayPal</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cấu hình</li>
            </ol>
        </nav>
    </div>
  </div>

  <div class="card custom-card shadow-sm">
    <div class="card-header justify-content-between">
      <div class="card-title text-uppercase"><i class="bx bx-cog me-1"></i> CẤU HÌNH NẠP PAYPAL</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.deposit.paypal.config.update') }}" method="POST" class="axios-form" data-reload="true">
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
                <label for="rate" class="form-label">1 USD =</label>
                <div class="input-group">
                    <input type="number" class="form-control" name="rate" id="rate" value="{{ $config['rate'] ?? 23000 }}" required>
                    <span class="input-group-text">VND</span>
                </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
                <label for="client_id" class="form-label">Client ID</label>
                <input type="text" class="form-control" name="client_id" id="client_id" value="{{ $config['client_id'] ?? '' }}" placeholder="Vui lòng nhập clientId">
            </div>
            <div class="mb-3">
                <label for="client_secret" class="form-label">Client Secret</label>
                <input type="text" class="form-control" name="client_secret" id="client_secret" value="{{ $config['client_secret'] ?? '' }}" placeholder="Vui lòng nhập clientSecret">
            </div>
          </div>
        </div>
        <div class="mb-3">
            <label for="note" class="form-label">PayPal Note</label>
            <textarea name="note" id="note" class="form-control ckeditor" rows="5">{{ $notice->value ?? $config['note'] ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
            <button type="submit" class="btn btn-primary w-100 shadow-sm" style="background-color: #6f42c1; border-color: #6f42c1;"><i class="fa fa-save me-1"></i> Save</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row mb-3 mt-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="m-0 font-weight-bold text-primary" style="padding-left: 10px; border-left: 5px solid #0d6efd;">DANH SÁCH PAYPAL</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create">
                    <i class="fa fa-plus"></i> Thêm PayPal
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="display table table-striped table-hover mb-0" id="basic-1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Thao tác</th>
                                <th>Email/Account</th>
                                <th>Trạng thái</th>
                                <th>Ngày Tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $accounts = $paypal->paypal_accounts ?? []; @endphp
                            @foreach ($accounts as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item['id'] }}"><i class="fa fa-edit"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteRow('{{ $item['id'] }}')"><i class="fa fa-trash"></i> Delete</button>
                                    </td>
                                    <td>{{ $item['email'] ?? $item['account'] ?? 'N/A' }}</td>
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
