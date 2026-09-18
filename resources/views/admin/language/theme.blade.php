@extends('admin.layouts.master')
@section('title', 'Cấu hình giao diện: ' . $language->name)
@section('content')
  <div class="row">
    <div class="col-sm-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">
            Cấu hình <span class="text-primary">{{ $language->name }}</span>
            @if(isset($activeConfig))
                <span class="text-muted mx-2">/</span>
                <span class="badge bg-info">{{ $activeConfig->domain }}</span>
            @endif
          </div>
          <a href="{{ route('admin.language.theme', ['id' => $language->id]) }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Quay lại
          </a>
        </div>
        <div class="card-body">
            @if(isset($activeConfig))
                {{-- EDIT MODE FORM --}}

                 <form action="{{ route('admin.language.theme.update', ['id' => $language->id, 'domain_id' => $activeConfig->domain_id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tiêu đề Trang</label>
                            <input type="text" name="title" class="form-control" value="{{ $activeConfig->title }}" placeholder="Nhập tiêu đề trang...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Màu chủ đạo</label>
                            <input type="text" name="primary_color" class="form-control" value="{{ $activeConfig->primary_color }}" placeholder="Ví dụ: #745af2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Giao diện mặc định</label>
                            <select class="form-select" name="default_theme">
                                <option value="default" {{ $activeConfig->default_theme == 'default' ? 'selected' : '' }}>Mặc định</option>
                                <option value="auto" {{ $activeConfig->default_theme == 'auto' ? 'selected' : '' }}>Tự động (Theo thiết bị)</option>
                                <option value="light" {{ $activeConfig->default_theme == 'light' ? 'selected' : '' }}>Sáng</option>
                                <option value="dark" {{ $activeConfig->default_theme == 'dark' ? 'selected' : '' }}>Tối</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mô tả Trang (SEO)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả trang...">{{ $activeConfig->description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Từ khóa SEO</label>
                            <textarea name="keywords" class="form-control" rows="3" placeholder="Nhập từ khóa SEO...">{{ $activeConfig->keywords }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Logo Sáng</label>
                            <input type="file" name="logo_light_file" class="form-control mb-2">
                            @if($activeConfig->logo_light)
                                <div class="mt-2 text-center p-2 border rounded image-preview-wrapper"><img src="{{ asset($activeConfig->logo_light) }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_light"><i class="fa fa-times"></i></span></div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Logo Tối</label>
                            <input type="file" name="logo_dark_file" class="form-control mb-2">
                            @if($activeConfig->logo_dark)
                                <div class="mt-2 text-center p-2 border rounded image-preview-wrapper"><img src="{{ asset($activeConfig->logo_dark) }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_dark"><i class="fa fa-times"></i></span></div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Favicon</label>
                            <input type="file" name="favicon_file" class="form-control mb-2">
                            @if($activeConfig->favicon)
                                <div class="mt-2 text-center p-2 border rounded image-preview-wrapper"><img src="{{ asset($activeConfig->favicon) }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="favicon"><i class="fa fa-times"></i></span></div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Logo Chia sẻ</label>
                            <input type="file" name="logo_share_file" class="form-control mb-2">
                            @if($activeConfig->logo_share)
                                <div class="mt-2 text-center p-2 border rounded image-preview-wrapper"><img src="{{ asset($activeConfig->logo_share) }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="logo_share"><i class="fa fa-times"></i></span></div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Banner</label>
                            <input type="file" name="banner_file" class="form-control mb-2">
                            @if($activeConfig->banner)
                                <div class="mt-2 text-center p-2 border rounded image-preview-wrapper"><img src="{{ asset($activeConfig->banner) }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="banner"><i class="fa fa-times"></i></span></div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email Admin</label>
                            <input type="email" name="admin_email" class="form-control" value="{{ $activeConfig->admin_email }}" placeholder="admin@example.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">YouTube Video ID</label>
                            <input type="text" name="youtube_id" class="form-control" value="{{ $activeConfig->youtube_id }}" placeholder="Ví dụ: dQw4w9WgXcQ">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Ảnh nền Website</label>
                            <input type="file" name="background_image_url_file" class="form-control mb-2">
                            @if($activeConfig->background_image_url)
                                <div class="mt-2 text-center p-2 border rounded bg-light image-preview-wrapper"><img src="{{ asset($activeConfig->background_image_url) }}" class="img-fluid" style="height: 120px; object-fit: contain;"><span class="delete-image-btn" data-field="background_image_url"><i class="fa fa-times"></i></span></div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary-gradient fw-bold px-4">Lưu Cài Đặt</button>
                    </div>
                </form>

            @else
                {{-- LIST MODE --}}
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead>
                            <tr>
                                <th>Tên Miền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($configs as $config)
                                <tr>
                                    <td class="fw-bold">{{ $config->domain }}</td>
                                    <td>
                                        @if($config->has_override)
                                            <span class="badge bg-success">Đã ghi đè</span>
                                        @else
                                            <span class="badge bg-secondary">Mặc định</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.language.theme', ['id' => $language->id, 'domain_id' => $config->id]) }}" class="badge bg-primary-gradient">
                                            <i class="fa fa-edit"></i> Sửa
                                        </a>
                                        @if($config->has_override)
                                            <button onclick="deleteOverride({{ $language->id }}, {{ $config->id }})" 
                                                    class="btn btn-sm btn-danger-gradient">
                                                <i class="fa fa-trash"></i> Xoá ghi đè
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.delete-image-btn').on('click', function() {
            var field = $(this).data('field');
            var form = $(this).closest('form');
            form.append('<input type="hidden" name="delete_' + field + '" value="1">');
            $(this).closest('.image-preview-wrapper').remove();
        });
    });

    async function deleteOverride(langId, domainId) {
        if (!confirm('Bạn có chắc chắn muốn xoá cấu hình ghi đè này? Nó sẽ quay về sử dụng cấu hình mặc định của tên miền.')) return;
        
        try {
            const { data: result } = await axios.post('{{ route("admin.language.theme.delete") }}', {
                language_id: langId,
                domain_id: domainId,
                _token: '{{ csrf_token() }}'
            });
            
            if (result.status) {
                toastr.success(result.message);
                location.reload();
            } else {
                toastr.error(result.message);
            }
        } catch (e) {
            toastr.error('Đã có lỗi xảy ra.');
        }
    }
</script>
@endsection
