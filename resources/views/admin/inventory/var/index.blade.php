@extends('admin.layouts.master')
@section('title', 'Admin: Inventory Variables Management')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý phần thưởng (unit)</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th data-orderable="false">Thao tác</th>
              <th>Ghi chú</th>
              <th>Đơn vị</th>
              <th data-orderable="false">Hình ảnh</th>
              <th>Sản lượng</th>
              <th>Min / Max</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($vars as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="javscript:void(0)" class="badge bg-primary-gradient text-white me-1" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}">Sửa</a>
                  <a href="javscript:void(0)" class="badge bg-danger-gradient text-white me-1r" onclick="deleteRow({{ $item->id }})">Xóa</a>
                </td>
                <td>
                  <b>{{ $item->name }}</b>
                  @if($item->descr)
                    <br><small class="text-muted">{{ $item->descr }}</small>
                  @endif
                </td>
                <td>{{ $item->unit }}</td>
                <td class="text-center">
                  <img src="{{ $item->image }}" alt="{{ $item->name }}" class="img-fluid" style="width: 30px">
                </td>
                <td>
                  {{ number_format($item->inventories->sum('value')) }} <small class="text-muted">{{ $item->unit }}</small>
                </td>
                <td>
                  {{ $item->min_withdraw }} / {{ $item->max_withdraw }}
                </td>
                <td>
                  @if ($item->is_active)
                    <span class="badge bg-success">Hoạt động</span>
                  @else
                    <span class="badge bg-danger">Không hoạt động</span>
                  @endif
                </td>
                <td>{{ $item->created_at }}</td>
              </tr>
              <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Cập nhật thông tin
                        #{{ $item->id }}</h5>
                      <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('admin.inventories.vars.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <div class="mb-3">
                          <label for="image" class="form-label">Ảnh / Icon</label>
                          <input class="form-control" type="file" id="image" name="image">
                        </div>
                        <div class="mb-3">
                          <label for="name" class="form-label">Tên phần thưởng</label>
                          <input class="form-control" type="text" id="name" name="name" value="{{ $item->name }}" required>
                        </div>
                        <div class="mb-3">
                          <label for="descr" class="form-label">Ghi chú / Mô tả</label>
                          <textarea class="form-control" id="descr" name="descr" rows="2">{{ $item->descr }}</textarea>
                        </div>
                        <div class="mb-3">
                          <label for="unit" class="form-label">Đơn vị</label>
                          <input class="form-control" type="text" id="unit" name="unit" value="{{ $item->unit }}">
                        </div>
                        <div class="mb-3 row">
                          <div class="col-md-6">
                            <label for="min_withdraw" class="form-label">Rút ít nhất</label>
                            <input class="form-control" type="number" id="min_withdraw" name="min_withdraw" value="{{ $item->min_withdraw }}" required>
                          </div>
                          <div class="col-md-6">
                            <label for="max_withdraw" class="form-label">Rút nhiều nhất</label>
                            <input class="form-control" type="number" id="max_withdraw" name="max_withdraw" value="{{ $item->max_withdraw }}" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          @php
                            $inputs = '';
                            if ($item->form_inputs && is_array($item->form_inputs)) {
                                foreach ($item->form_inputs as $input) {
                                    $options = '';
                                    if ($input['type'] === 'select' || $input['type'] === 'radio') {
                                        $options = '|' . implode(',', $input['options']);
                                    }
                                    $inputs .= $input['label'] . '|' . $input['type'] . $options . "\n";
                                }
                            }
                          @endphp
                          <label for="form_inputs" class="form-label">Yêu cầu đầu vào</label>
                          <textarea name="form_inputs" id="form_inputs" class="form-control" rows="4">{{ $inputs }}</textarea>
                          <ul class="mt-2">
                            <li>Input type: label|:type => Tài khoản|text, Mật khẩu|password</li>
                            <li>Input select: label|select|option1,option2,option3</li>
                            <li>Input radio: label|radio|option1,option2,option3</li>
                          </ul>
                        </div>
                        <div class="mb-3">
                          @php
                            $packages = '';
                            if ($item->form_packages && is_array($item->form_packages)) {
                                foreach ($item->form_packages as $key => $package) {
                                    $packages .= $key . "\n";
                                }
                            }
                          @endphp
                          <label for="form_packages" class="form-label">Danh sách gói rút</label>
                          <textarea name="form_packages" id="form_packages" class="form-control" rows="4">{{ $packages }}</textarea>
                        </div>
                        <div class="mb-3">
                          <label for="is_active" class="form-label">Trạng thái</label>
                          <select class="form-control" id="is_active" name="is_active">
                            <option value="1" @if ($item->is_active === 1) selected @endif>Hoạt
                              động
                            </option>
                            <option value="0" @if ($item->is_active === 0) selected @endif>Không
                              hoạt động
                            </option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <button class="btn btn-primary w-100" type="submit">Cập nhật</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create">Thêm mới
      </button>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm thông tin mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.inventories.vars.store') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="image" class="form-label">Ảnh / Icon</label>
              <input class="form-control" type="file" id="image" name="image" required>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Tên phần thưởng</label>
              <input class="form-control" type="text" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="descr" class="form-label">Ghi chú / Mô tả</label>
              <textarea class="form-control" id="descr" name="descr" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <label for="unit" class="form-label">Đơn vị</label>
              <input class="form-control" type="text" id="unit" name="unit" required>
            </div>
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="min_withdraw" class="form-label">Rút ít nhất</label>
                <input class="form-control" type="number" id="min_withdraw" name="min_withdraw" required>
              </div>
              <div class="col-md-6">
                <label for="max_withdraw" class="form-label">Rút nhiều nhất</label>
                <input class="form-control" type="number" id="max_withdraw" name="max_withdraw" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="form_inputs" class="form-label">Yêu cầu đầu vào</label>
              <textarea name="form_inputs" id="form_inputs" class="form-control" rows="4"></textarea>
              <ul class="mt-2">
                <li>Input type: label|:type => Tài khoản|text, Mật khẩu|password</li>
                <li>Input select: label|select|option1,option2,option3</li>
                <li>Input radio: label|radio|option1,option2,option3</li>
              </ul>
            </div>
            <div class="mb-3">
              <label for="form_packages" class="form-label">Danh sách gói rút</label>
              <textarea name="form_packages" id="form_packages" class="form-control" rows="4" placeholder="Mỗi gói là 1 dòng... (10, 20, 30)"></textarea>
            </div>
            <div class="mb-3">
              <label for="is_active" class="form-label">Trạng thái</label>
              <select class="form-control" id="is_active" name="is_active">
                <option value="1">Hoạt động</option>
                <option value="0">Không hoạt động</option>
              </select>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary w-100" type="submit">Thêm mới</button>
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
        } = await axios.post('{{ route('admin.inventories.vars.delete') }}', {
          id
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
  </script>
@endsection
