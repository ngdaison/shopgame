@extends('admin.layouts.master')
@section('title', 'Admin: Boosting Products')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thêm sản phẩm cho gói "{{ $package->name }}"</div>
      <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-set-default"><i class="fa fa-star"></i> Set Mặc Định</button>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.boosting.products.store', ['id' => $package->id]) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="name" class="form-label">Tên sản phẩm</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Tên sản phẩm cần bán" required>
          </div>
          <div class="col-md-6">
            <label for="price" class="form-label">Giá sản phẩm</label>
            <input type="text" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
          </div>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Trạng thái</label>
          <select class="form-control" id="status" name="status" required>
            <option value="1">Đang bán</option>
            <option value="0">Chưa bán</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="descr" class="form-label">Mô tả sản phẩm</label>
          <textarea class="form-control" id="descr" name="descr" rows="3">{{ old('descr') }}</textarea>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="priority" class="form-label">Ưu tiên</label>
            <input type="number" class="form-control" id="priority" name="priority" value="0" required>
          </div>
          <div class="col-md-6">
            <label for="warranty_hours" class="form-label">Thời gian bảo hành (giờ)</label>
            <input type="number" class="form-control" id="warranty_hours" name="warranty_hours" value="0" placeholder="0 = không bảo hành">
          </div>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">Thêm sản phẩm</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Danh sách sản phẩm của gói "{{ $package->name }}"</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table-bordered table-stripped text-nowrap datatable table text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Ưu tiên</th>
              <th>Giá bán</th>
              <th>Mã sản phẩm</th>
              <th>Tên Sản Phẩm</th>
              <th>Bảo hành</th>
              <th>Trạng thái</th>
              <th>Ngày thêm</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($package->products as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="javascript:void(0)" class="badge bg-primary-gradient text-white me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> sửa</a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="badge bg-danger-gradient me-1"><i class="fa fa-trash"></i> xoá</a>
                </td>
                <td>
                  <input type="number" class="form-control form-control-sm mx-auto input-priority" value="{{ $item->priority }}" data-id="{{ $item->id }}" style="width: 80px;">
                </td>
                <td>{{ Helper::formatCurrency($item->price) }}</td>
                <td>#{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->warranty_hours }}h</td>
                <td>{!! $item->status === true ? '<span class="text-success">Đang Bán</span>' : '<span class="text-danger">Ngưng Bán</span>' !!}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <a href="{{ route('admin.boosting.packages', ['id' => $package->group_id]) }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại danh sách gói</a>
    </div>
  </div>

  @foreach ($package->products as $item)
    <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cập nhật sản phẩm #{{ $item->id }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.boosting.products.update', ['id' => $item->id]) }}" method="POST" class="default-form axios-form" data-reload="true">
              @csrf
              <div class="mb-3">
                <label for="name" class="form-label">Tên sản phẩm</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên sản phẩm" value="{{ $item->name }}" required>
              </div>
              <div class="mb-3">
                <label for="price" class="form-label">Giá sản phẩm</label>
                <input type="text" class="form-control" id="price" name="price" value="{{ $item->price }}" required>
              </div>
              <div class="mb-3">
                <label for="descr" class="form-label">Mô tả sản phẩm</label>
                <textarea class="form-control" id="descr" name="descr" rows="2">{{ $item->descr }}</textarea>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status" required>
                  <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Đang bán</option>
                  <option value="0" {{ $item->status == 0 ? 'selected' : '' }}>Ngưng bán</option>
                </select>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="priority" class="form-label">Ưu tiên</label>
                  <input type="number" class="form-control" id="priority" name="priority" value="{{ $item->priority }}" required>
                </div>
                <div class="col-md-6">
                  <label for="warranty_hours" class="form-label">Thời gian bảo hành (giờ)</label>
                  <input type="number" class="form-control" id="warranty_hours" name="warranty_hours" value="{{ $item->warranty_hours }}" placeholder="0 = không bảo hành">
                </div>
              </div>
              <div class="mb-3 text-center">
                <button class="btn btn-primary" type="submit">Cập nhật</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach

    <div class="modal fade" id="modal-set-default" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cấu hình mặc định cho nhóm</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.boosting.products.save-default') }}" method="POST" class="default-form axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="id" value="{{ $package->id }}">
              <div class="mb-3">
                <label class="form-label">Tên mặc định</label>
                <input type="text" class="form-control" name="name" placeholder="Tên sản phẩm mặc định">
              </div>
              <div class="mb-3">
                <label class="form-label">Giá mặc định</label>
                <input type="text" class="form-control" name="price" placeholder="Giá mặc định">
              </div>
              <div class="mb-3">
                  <label for="status" class="form-label">Trạng thái</label>
                  <select class="form-control" name="status">
                    <option value="1">Đang bán</option>
                    <option value="0">Chưa bán</option>
                  </select>
                </div>
              <div class="mb-3">
                <label class="form-label">Mô tả sản phẩm</label>
                <textarea class="form-control" name="descr" rows="3" placeholder="Mô tả sản phẩm mặc định"></textarea>
              </div>
              <div class="row mb-3">
                <div class="col-md-12">
                  <label class="form-label">Bảo hành (giờ)</label>
                  <input type="number" class="form-control" name="warranty_hours" placeholder="0">
                </div>
              </div>
              <div class="mb-3 text-center">
                <button class="btn btn-primary" type="submit">Lưu Cấu Hình</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

@endsection
@section('scripts')
  <script>
    const deleteRow = async (id) => {
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
        const {
          data: result
        } = await axios.post('{{ route('admin.boosting.products.delete') }}', {
          id,
          _token: '{{ csrf_token() }}'
        })

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành Công',
            message: result.message || 'Xóa thành công'
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

    const updatePriority = $debounce(function(id, val) {
        axios.post('{{ route('admin.boosting.products.update-priority') }}', {
            id: id,
            priority: val,
            _token: '{{ csrf_token() }}'
        }).then(res => {
            if (res.data.status) {
                toastr.success(res.data.message || 'Cập nhật thứ tự thành công!', 'Thành Công');
            } else {
                toastr.error(res.data.message, 'Thất Bại!');
            }
        }).catch(err => {
            toastr.error('Lỗi cập nhật ưu tiên', 'Lỗi!');
        });
    }, 0);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });

    $(document).ready(function() {
        const defaultProduct = @json(json_decode($defaultProduct ?? '{}'));
        
        // Auto fill modal fields if exists
        if (Object.keys(defaultProduct).length > 0) {
            // Fill Add Form
            if(defaultProduct.name) $('input[name="name"]').val(defaultProduct.name);
            if(defaultProduct.price) $('input[name="price"]').val(defaultProduct.price);
            if(defaultProduct.descr) $('textarea[name="descr"]').val(defaultProduct.descr);
            if(defaultProduct.status !== undefined) $('select[name="status"]').val(defaultProduct.status);
            if(defaultProduct.warranty_hours) $('input[name="warranty_hours"]').val(defaultProduct.warranty_hours);

            // Fill Set Default Modal
            const modal = $('#modal-set-default');
            if(defaultProduct.name) modal.find('input[name="name"]').val(defaultProduct.name);
            if(defaultProduct.price) modal.find('input[name="price"]').val(defaultProduct.price);
            if(defaultProduct.descr) modal.find('textarea[name="descr"]').val(defaultProduct.descr);
            if(defaultProduct.status !== undefined) modal.find('select[name="status"]').val(defaultProduct.status);
            if(defaultProduct.warranty_hours) modal.find('input[name="warranty_hours"]').val(defaultProduct.warranty_hours);
        }
    });
  </script>
@endsection
