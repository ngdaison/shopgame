@extends('admin.layouts.master')
@section('title', 'Admin: Accounts Item In Stock')
@section('css')
  <link rel="stylesheet" href="/_admin/libs/dropzone/dropzone.css">
@endsection
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Danh sách tài khoản trong kho - <a href="?sold=1">Đã Bán</a> / <a href="?sold=0">Chưa Bán</a> / <a href="?">Tất Cả</a></div>
    </div>
    <div class="card-body">
      <div>
        <form id="filter" class="default-form">
          <div class="mb-3 row">
            <div class="col-md-3">
              <label for="sold" class="form-label">Trạng thái</label>
              <select name="sold" id="sold" class="form-select">
                <option value="">Tất cả</option>
                <option value="1" @if (request()->input('sold') == 1) selected @endif>Đã bán</option>
                <option value="0" @if (request()->input('sold') == 0) selected @endif>Chưa bán</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="buyer_name" class="form-label">Người mua</label>
              <input type="text" class="form-control" id="buyer_name" name="buyer_name" value="{{ request()->input('buyer_name') }}">
            </div>
            <div class="col-md-3">
              <label for="username" class="form-label">Tài khoản</label>
              <input type="text" class="form-control" id="username" name="username" value="{{ request()->input('username') }}">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3">
              <label for="start_date" class="form-label">Ngày bắt đầu</label>
              <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request()->input('start_date') }}">
            </div>
            <div class="col-md-3">
              <label for="end_date" class="form-label">Ngày kết thúc</label>
              <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request()->input('end_date') }}">
            </div>
            <div class="col-md-3">
              <label for="domain" class="form-label">Tên miền</label>
              <input type="text" class="form-control" id="domain" name="domain" value="{{ request()->input('domain') }}">
            </div>
          </div>
          <div class="text-center mb-3">
            <button type="submit" class="btn btn-primary">Lọc dữ liệu</button>
            <button type="button" class="btn btn-danger ids-action" onclick="deleteList()"><i class="fa fa-trash"></i> Xoá <span class="checked-count">0</span> sản phẩm</button>
          </div>
        </form>
      </div>
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table-bordered table-stripped text-nowrap datatable table text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>Sản phẩm</th>
              <th>Thao tác</th>
              <th>Tài khoản</th>
              <th>Người mua</th>
              <th>Ngày mua</th>
              <th>Thanh toán</th>
              <th>Mã đơn hàng</th>
              <th>Trạng thái</th>
              <th>Ngày thêm</th>
              <th>Tên miền</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($items as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="{{ route('admin.accountsv2.items.show', ['id' => $item->parent?->id ?? '-1']) }}">#{{ $item->parent?->name }}</a>
                </td>
                <td>
                  <a href="{{ route('admin.accountsv2.resources', ['id' => $item->parent?->id]) }}" class="btn btn-primary btn-xs sharp me-1 shadow"><i class="fa fa-pencil"></i> sửa</a>
                  {{-- <a href="javascript:deleteRow({{ $item->id }})" class="btn btn-danger btn-xs sharp me-1 shadow"><i class="fa fa-trash"></i> xoá</a> --}}
                </td>
                <td>{{ $item->username }}</td>
                <td>{{ $item->buyer_name ?? '-' }}</td>
                <td>{{ $item->buyer_date ?? '-' }}</td>
                <td><span class="text-danger">{{ Helper::formatCurrency($item->buyer_paym ?? 0) }}</span></td>
                <td>{{ $item->buyer_code ?? '-' }}</td>
                <td>{!! $item->buyer_name !== null ? '<span class="text-success">Đã bán</span>' : '<span class="text-danger">Chưa bán</span>' !!}</td>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->domain }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
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
        } = await axios.post('{{ route('admin.accountsv2.items.delete') }}', {
          id
        })

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành công',
            message: result.message || 'Xoá thành công'
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
