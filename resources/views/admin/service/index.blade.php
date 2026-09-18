@extends('admin.layouts.master')
@section('title', 'Quản Lý Dịch Vụ Khác')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Danh sách dịch vụ</div>
      <div class="card-options">
          <button class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#modal-upsert" onclick="prepareCreate()"> Thêm mới</button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Ưu Tiên</th>
              <th>Thao Tác</th>
              <th>Loại</th>
              <th>Tên</th>
              <th>Tên dưới</th>
              <th>Groups</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($items as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>
                  <a href="{{ route('admin.service.edit', ['id' => $item->id]) }}" class="badge bg-primary-gradient border-0">Sửa</a>
                  <button class="badge bg-danger-gradient border-0 btn-delete" data-id="{{ $item->id }}">Xoá</button>
                </td>
                <td>
                    <span class="badge {{ $item->product_type === 'spin' ? 'bg-info' : 'bg-secondary' }}">
                        {{ $item->product_type === 'spin' ? 'Vòng Quay' : 'Chuyên Mục' }}
                    </span>
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->sub_name }}</td>
                <td>
                    @if($item->resolved_groups->count() > 0)
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($item->resolved_groups as $g)
                                <span class="badge bg-info-transparent">
                                    @if($g instanceof \App\Models\Group) [Acc] @elseif($g instanceof \App\Models\GroupV2) [AccV2] @elseif($g instanceof \App\Models\ItemGroup) [Item] @elseif($g instanceof \App\Models\GBGroup) [Boost] @endif
                                    {{ $g->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted small">Chưa có nhóm</span>
                    @endif
                </td>
                <td>
                  <span class="badge {{ $item->status ? 'bg-success' : 'bg-danger' }}">
                    {{ $item->status ? 'Hoạt động' : 'Ẩn' }}
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Unified Create/Edit Modal --}}
  <div class="modal fade" id="modal-upsert" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="form-upsert" action="{{ route('admin.service.store') }}" method="POST" enctype="multipart/form-data" class="axios-form" data-reload="true">
          @csrf
          <input type="hidden" name="id" id="item-id">
          <div class="modal-header">
            <h5 class="modal-title" id="modal-title">Thêm dịch vụ mới</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Tên Dịch Vụ</label>
                <input type="text" class="form-control" name="name" id="name" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="priority" class="form-label">Ưu tiên</label>
                <input type="number" class="form-control" id="priority" name="priority" value="0" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Sub Name</label>
                <input type="text" class="form-control" name="sub_name" id="sub_name">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Loại Dịch Vụ</label>
                <select class="form-control" name="product_type" id="product_type" onchange="toggleFields()">
                  <option value="category">Chuyên Mục (Nhiều Groups)</option>
                  <option value="robux">Dịch Vụ Robux</option>
                  <option value="spin">Vòng Quay May Mắn</option>
                </select>
              </div>

              <div class="col-md-6 mb-3 section-display-mode" style="display:none;">
                <label class="form-label">Kiểu Hiển Thị</label>
                <select class="form-control" name="display_mode" id="display_mode">
                  <option value="list">Danh Sách (Ảnh 2)</option>
                  <option value="grid">Lưới / Card (Ảnh 3)</option>
                </select>
              </div>

               <div class="col-md-6 mb-3 section-robux-only" style="display:none;">
                  <label class="form-label">Thời gian bảo hành (giờ)</label>
                  <input type="number" class="form-control" name="warranty_hours" id="warranty_hours" value="0">
               </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Trạng Thái</label>
                <select class="form-control" name="status" id="status">
                  <option value="1">Hiển Thị</option>
                  <option value="0">Ẩn</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Ảnh Đại Diện</label>
                <input type="file" class="form-control" name="image">
              </div>

              {{-- Category Specific (Groups) --}}
              <div class="col-12 mb-3 section-only-group">
                <label class="form-label">Chọn Nhóm Sản Phẩm (Groups)</label>
                <select class="form-control group-select" name="groups[]" multiple>
                  @foreach($shopGroups as $group)
                    <option value="{{ $group->model }}:{{ $group->id }}">
                      {{ $group->name }}
                    </option>
                  @endforeach
                </select>
              </div>

               {{-- Spin Specific (Category Selection) --}}
               <div class="col-12 mb-3 section-only-category">
                <label class="form-label">Chọn Danh Mục Hiển Thị</label>
                <select class="form-control group-select" name="groups[]" multiple>
                  @foreach($categories as $group)
                    <option value="{{ $group->model }}:{{ $group->id }}">
                      {{ $group->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Đóng</button>
            <button class="btn btn-primary" type="submit">Lưu Lại</button>
          </div>
        </form>
      </div>
    </div>
  </div>



@endsection

@section('scripts')
<script src="/plugins/ckeditor/ckeditor.js"></script>
<script>
    function toggleFields() {
        const type = $('#product_type').val();
        if (type === 'spin') {
            $('.section-spin').show();
            $('.section-only-category').show();
            $('.section-only-group').hide();
            $('.section-display-mode').hide();
            $('.section-robux-only').hide();
        } else if (type === 'robux') {
            $('.section-spin').hide();
            $('.section-only-category').show();
            $('.section-only-group').hide();
            $('.section-display-mode').show();
            $('.section-robux-only').show();
        } else {
            $('.section-spin').hide();
            $('.section-only-category').hide();
            $('.section-only-group').show();
            $('.section-display-mode').hide();
            $('.section-robux-only').hide();
        }
    }

    // Use arrays to store multiple choice instances
    let groupSelects = [];
    function prepareCreate() {
        $('#modal-title').text('Thêm dịch vụ mới');
        $('#form-upsert').attr('action', '{{ route('admin.service.store') }}');
        $('#item-id').val('');
        $('#name').val('');
        $('#sub_name').val('');
        $('#sub_name').val('');
        $('#product_type').val('category');
        $('#display_mode').val('list');

        $('#status').val(1);
        $('#price').val(0);
        
        if (groupSelects.length) {
            groupSelects.forEach(instance => instance.removeActiveItems());
        }

        toggleFields();
    }

    $(document).on('click', '.btn-delete', async function() {
        const id = $(this).data('id');
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
            const { data: res } = await axios.post('{{ route('admin.service.delete') }}', {
                id: id,
                _token: '{{ csrf_token() }}'
            });

            if (res.status) {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: res.message || 'Xóa thành công'
                }));
                location.reload();
            } else {
                $hideLoading();
                toastr.error(res.message, 'Thất Bại');
            }
        } catch (err) {
            $hideLoading();
            toastr.error($catchMessage(err), 'Thất Bại');
        }
    });

    const updatePriority = $debounce(function(id, val) {
        if (typeof pageOverlay !== 'undefined') pageOverlay.show();
        axios.post('{{ route('admin.service.update-priority') }}', {
            id: id,
            priority: val,
            _token: '{{ csrf_token() }}'
        }).then(res => {
            if (res.data.status) {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: res.data.message || 'Cập nhật thứ tự thành công'
                }));
                location.reload();
            } else {
                if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
                toastr.error(res.data.message, 'Thất Bại');
            }
        }).catch(err => {
            if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
            toastr.error('Lỗi cập nhật ưu tiên', 'Thất Bại');
        });
    }, 500);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });

    $(document).ready(function() {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
        toggleFields();

        if ($('.group-select').length) {
             const elements = document.querySelectorAll('.group-select');
             elements.forEach(el => {
                const choice = new Choices(el, {
                    removeItemButton: true,
                    placeholder: true,
                    placeholderValue: '-- Chọn nhóm sản phẩm --',
                    searchPlaceholderValue: 'Tìm kiếm nhóm...',
                });
                groupSelects.push(choice);
             });
        }

        $('.ckeditor').each(function () {
            if(document.getElementById(this.id)) {
                const editor = CKEDITOR.replace(this.id, {
                    // extraPlugins: 'notification', // Included in Full package
                    height: 400,
                    clipboard_handleImages: false,
                    filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
                    filebrowserUploadMethod: 'form'
                });
                
                 editor.on('fileUploadRequest', function(evt) {
                    var xhr = evt.data.fileLoader.xhr;
                    xhr.setRequestHeader('Cache-Control', 'no-cache');
                    if(window.userData && window.userData.access_token) {
                        xhr.setRequestHeader('Authorization', 'Bearer ' + window.userData.access_token);
                    }
                });
            }
        });
    });
</script>
@endsection
