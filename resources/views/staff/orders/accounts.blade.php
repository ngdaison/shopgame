@extends('staff.layouts.app')
@section('title', 'Admin: Accounts Item')
@section('css')
  <link rel="stylesheet" type="text/css" href="/_admin/css/vendors/dropzone.css">
@endsection
@section('content')

  @foreach ($groups as $group)
    <div class="card custom-card">
      <div class="card-header justify-content-between">
        <div class="card-title">Quản lý sản phẩm của nhóm "{{ $group->name }}"</div>
      </div>
      <div class="card-body">
        <div class="text-center mb-3">
          <button class="btn btn-danger ids-action" onclick="deleteList()"><i class="fa fa-trash"></i> Xoá <span class="checked-count">0</span> sản phẩm</button>
          <button class="btn btn-primary ids-action" onclick="copyList()"><i class="fa fa-copy"></i> Sao chép <span class="checked-count">0</span> sản phẩm</button>
        </div>
        <div class="table-responsive theme-scrollbar">
          <table class="display table-bordered table-stripped text-nowrap datatable1 table text-center">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" id="check_all" />
                </th>
                <th>#</th>
                <th>Thao tác</th>
                <th>Tài khoản</th>
                <th>Tên sản phẩm</th>
                <th>Mã sản phẩm</th>
                <th>Giá bán</th>
                <th>Giá nhập</th>
                <th>% Giảm giá</th>
                <th>Ảnh sản phẩm</th>
                <th>Người mua</th>
                <th>Hoa hồng</th>
                <th>Mã đơn hàng</th>
                <th>Trạng thái</th>
                <th>Ngày thêm</th>
                <th>-</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($group->items()->where('type', 'account')->where('staff_name', auth()->user()->username)->get() as $item)
                <script>
                  var accountInfo_{{ $item->id }} = {
                    username: '{{ $item->username }}',
                    password: '{{ $item->password }}',
                    extra_data: '{{ $item->auth }}'
                  };
                </script>
                <tr>
                  <td>
                    <input type="checkbox" class="check_item" value="{{ $item->id }}" />
                  </td>
                  <td>{{ $item->id }}</td>
                  <td>
                    @if ($item->is_sold)
                        <a href="javascript:void(0)" class="btn btn-warning btn-xs sharp me-1 shadow" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}"><i class="fa fa-edit"></i> Order</a>
                    @endif
                    <a href="{{ route('staff.products.accounts.items.show', ['id' => $item->id]) }}" class="btn btn-primary btn-xs sharp me-1 shadow"><i class="fa fa-pencil"></i> sửa</a>
                    <a href="javascript:deleteRow({{ $item->id }})" class="btn btn-danger btn-xs sharp me-1 shadow"><i class="fa fa-trash"></i> xoá</a>
                  </td>
                  <td>{{ $item->username }}</td>
                  <td>{{ $item->name }}</td>
                  <td>#{{ $item->code }}</td>
                  <td>{{ Helper::formatCurrency($item->price) }}</td>
                  <td>{{ Helper::formatCurrency($item->cost) }}</td>
                  <td>{{ $item->discount }}%</td>
                  <td><img src="{{ Helper::getValidImage($item->image) }}" width="40"></td>
                  <td>{{ $item->buyer_name ?? '-' }}</td>
                  <td>{{ Helper::formatCurrency($item->staff_payment) }}</td>
                  <td>{{ $item->buyer_code ?? '-' }}</td>
                  <td>{!! $item->is_sold === true ? '<span class="text-success">Đã Bán</span>' : '<span class="text-danger">Chưa Bán</span>' !!}</td>
                  <td>{{ $item->created_at }}</td>
                  <td>
                    @if ($item->staff_status === 'Completed')
                      <span class="text-success">Đã Duyệt</span>
                    @elseif ($item->staff_status === 'WaitPayment')
                      <span class="badge bg-warning">Chờ duyệt</span>
                    @else
                      <span class="text-danger">Chưa duyệt</span>
                    @endif
                  </td>
                </tr>

                @if ($item->is_sold)
                  <!-- Modal Edit Order -->
                  <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Cập nhật đơn hàng #{{ $item->id }}</h5>
                          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="alert alert-danger mb-3">Sau khi hoàn thành đơn, hệ thống sẽ kiểm tra và trả hoa hồng sau 3-5 ngày</div>
                          <form action="{{ route('staff.orders.accounts.update', ['id' => $item->id]) }}" method="POST" class="axios-form" data-reload="true">
                            @csrf
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            <div class="mb-3 row">
                              <div class="col-md-6">
                                <label class="form-label">Người mua</label>
                                <input type="text" class="form-control" value="{{ $item->buyer_name }}" disabled>
                              </div>
                              <div class="col-md-6">
                                <label class="form-label">Thanh toán</label>
                                <input type="text" class="form-control" value="{{ Helper::formatCurrency($item->payment) }}" disabled>
                              </div>
                            </div>
                            <div class="mb-3">
                               <label class="form-label">Có thể nhận</label>
                               <input type="text" class="form-control" value="{{ Helper::formatCurrency((float) (($item->payment * auth()->user()->colla_percent) / 100)) }}" disabled>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Ghi chú đơn hàng</label>
                              <textarea class="form-control" name="order_note" rows="3">{{ $item->order_note }}</textarea>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Trạng thái</label>
                              <select class="form-select" name="status" required>
                                <option value="Assigned" {{ $item->staff_status == 'Assigned' ? 'selected' : '' }}>Đã bán</option>
                                <option value="Processing" {{ $item->staff_status == 'Processing' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="Completed" {{ $item->staff_status == 'Completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="Cancelled" {{ $item->staff_status == 'Cancelled' ? 'selected' : '' }}>Đã hủy / Hoàn tiền</option>
                              </select>
                            </div>
                            <div class="mb-3">
                              <button class="btn btn-primary">Cập nhật</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endforeach
@endsection
@section('scripts')
  <script src="/_admin/libs/dropzone/dropzone-min.js"></script>
  <script src="/plugins/ckeditor/ckeditor.js"></script>

  <script>
    // checkbox table
    $("#check_all").click(function() {
      if ($(this).is(":checked")) {
        $(".check_item").prop("checked", true);
      } else {
        $(".check_item").prop("checked", false);
      }
    });

    $(".check_item").click(function() {
      if ($(".check_item:checked").length == $(".check_item").length) {
        $("#check_all").prop("checked", true);
      } else {
        $("#check_all").prop("checked", false);
      }
    });

    const getIds = () => {
      let ids = [];
      $(".check_item:checked").each(function() {
        ids.push($(this).val());
      });
      return ids;
    }

    // event to class checked-count, checked-ids
    const updateChecked = () => {
      let ids = getIds();
      $(".checked-count").text(ids.length);

      if (ids.length > 0) {
        $(".ids-action").removeClass('disabled btn-disabled')
      } else {
        $(".ids-action").addClass('disabled btn-disabled')
      }
    }

    $(".check_item").click(function() {
      updateChecked();
    });

    $("#check_all").click(function() {
      updateChecked();
    });

    updateChecked();

    //

    $(document).ready(function() {
      $('.datatable1').DataTable({
        language: {
          searchPlaceholder: 'Search...',
          sSearch: '',
        },
        response: false,
        order: [
          [1, 'desc']
        ],
        lengthMenu: [
          [10, 25, 50, 100, 200, -1],
          [10, 25, 50, 100, 200, "Tất cả"]
        ],
        retrieve: true,
        columnDefs: [{
          targets: 0,
          orderable: false,
          searchable: false,
        }],
      })
    })

    const copyList = async () => {
      const ids = getIds()

      if (ids.length === 0) {
        Swal.fire('Thất bại', 'Vui lòng chọn ít nhất 1 tài khoản để xoá', 'error')
        return
      }

      let content = ''

      for (let i = 0; i < ids.length; i++) {
        const id = ids[i]
        const account = window['accountInfo_' + id]

        content += `${account.username}|${account.password}`
        if (account.extra_data) {
          content += `|${account.extra_data}`
        }
        content += '\n'
      }

      // copy to clipboard
      const el = document.createElement('textarea');
      el.value = content;
      document.body.appendChild(el);
      el.select();

      document.execCommand('copy');

      document.body.removeChild(el);

      Swal.fire('Thành công', 'Đã sao chép ' + ids.length + ' tài khoản', 'success')
    }

    const deleteList = async () => {
      const ids = getIds()

      if (ids.length === 0) {
        Swal.fire('Thất bại', 'Vui lòng chọn ít nhất 1 tài khoản để xoá', 'error')
        return
      }

      const confirm = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
      })

      if (!confirm.isConfirmed) return

      $showLoading()

      try {
        const {
          data: result
        } = await axios.post('{{ route('staff.products.accounts.items.delete-list') }}', {
          ids: ids
        })

        Swal.fire('Thành công', result.message, 'success').then(() => {
          window.location.reload();
        })
      } catch (error) {
        Swal.fire('Thất bại', $catchMessage(error), 'error')
      }
    }

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
        } = await axios.post('{{ route('staff.products.accounts.items.delete') }}', {
          id
        })

        Swal.fire('Thành công', result.message, 'success').then(() => {
          window.location.reload();
        })
      } catch (error) {
        Swal.fire('Thất bại', $catchMessage(error), 'error')
      }
    }
  </script>
@endsection
