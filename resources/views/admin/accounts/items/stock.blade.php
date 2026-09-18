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
        <form id="filter" method="GET">
          <div class="mb-3 row">
            <div class="col-md-3">
              <label for="group" class="form-label">Nhóm</label>
              <select name="group" id="group" class="form-select">
                <option value="">Tất cả</option>
                @foreach ($groups as $group)
                  <option value="{{ $group->id }}" @if (request()->input('group') == $group->id) selected @endif>{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
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
            <button type="button" class="btn btn-danger action-ids" onclick="deleteList()"><i class="fa fa-trash"></i> Xoá <span class="checked-ids">0</span> sản phẩm</button>
          </div>
        </form>
      </div>

      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table-bordered table-stripped text-nowrap datatable1_2 table text-center">
          <thead>
            <tr>
              <th data-orderable="false" width="10">
                <input type="checkbox" name="checked_all">
              </th>
              <th>#</th>
              <th>Nhóm</th>
              <th>Thao tác</th>
              <th>Tài khoản</th>
              <th>Mã sản phẩm</th>
              <th>Giá bán</th>
              <th>Giá nhập</th>
              <th>% Giảm giá</th>
              <th>Ảnh sản phẩm</th>
              <th>Người thêm</th>
              <th>Người mua</th>
              <th>Ngày mua</th>
              <th>Thanh toán</th>
              <th>Mã đơn hàng</th>
              <th data-orderable="false">Trạng thái</th>
              <th>Ngày thêm</th>
              <th>Tên miền</th>

              <th>Hoa hồng</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @foreach ([] as $item)
              <tr>
                <td>
                  <input type="checkbox" name="checked_ids[]" value="{{ $value->id }}}">
                </td>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="{{ route('admin.accounts.items', ['id' => $item->group_id]) }}">#{{ $item->group_id }}</a>
                </td>
                <td>
                  <a href="{{ route('admin.accounts.items.show', ['id' => $item->id]) }}" class="badge bg-primary-gradient text-white"><i class="fa fa-pencil"></i> sửa</a>
                  <a href="#" class="badge bg-danger-gradient text-white js-delete-row" data-id="{{ $item->id }}"><i class="fa fa-trash"></i> xoá</a>
                </td>
                <td>{{ $item->username }}</td>
                <td>#{{ $item->code }}</td>
                <td>{{ Helper::formatCurrency($item->price) }}</td>
                <td>{{ Helper::formatCurrency($item->cost) }}</td>
                <td>{{ $item->discount }}%</td>
                <td><img src="{{ Helper::getValidImage($item->image) }}" width="40"></td>
                <td>{{ $item->staff_name ?? '-' }}</td>
                <td>{{ $item->buyer_name ?? '-' }}</td>
                <td>{{ $item->buyer_date ?? '-' }}</td>
                <td><span class="text-danger">{{ Helper::formatCurrency($item->buyer_paym ?? 0) }}</span></td>
                <td>{{ $item->buyer_code ?? '-' }}</td>
                <td>{!! $item->is_sold === true ? '<span class="text-success">Đã bán</span>' : '<span class="text-danger">Chưa bán</span>' !!}</td>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->domain ?? '-' }}</td>

                <td>{{ Helper::formatCurrency($item->staff_payment) }}</td>
                <td>
                  @if ($item->staff_status === 'Completed')
                    <span class="text-success">Đã Duyệt</span>
                  @else
                    <span class="text-danger">Chưa duyệt</span>
                  @endif
                </td>
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
    $("[name=checked_all]").change(function(e) {
      if ($(this).is(":checked")) {
        $("[name='checked_ids[]']").prop("checked", true)
      } else {
        $("[name='checked_ids[]']").prop("checked", false)
      }
    })

    function getIds() {
      let ids = []
      $("[name='checked_ids[]']:checked").each(function() {
        ids.push($(this).val())
      })
      return ids
    }

    // find class actions-ids set disabled with getIds() < 0, and set length checked-ids
    function setActions() {
      let ids = getIds()
      console.log(ids)
      if (ids.length > 0) {
        $(".action-ids").prop("disabled", false)
        $(".checked-ids").text(ids.length)
      } else {
        $(".action-ids").prop("disabled", true)
        $(".checked-ids").text(0)
      }
    }

    $(document)
      .ready(function() {
        setActions();
      })
      .on('change', 'input[name="checked_all"]:enabled', function() {
        setActions();
      })
      .on('change', 'input[name="checked_ids[]"]:enabled', function() {
        setActions();
      })


    const deleteList = async () => {
      const ids = getIds()

      if (ids.length === 0) {
        return toastr.error('Vui lòng chọn ít nhất 1 tài khoản để xóa', 'Thất Bại');
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
        } = await axios.post('{{ route('admin.accounts.items.delete-list') }}', {
          ids: ids
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

    $(document).on('click', '.js-delete-row', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = $(this).data('id');
      if (id) deleteRow(id);
      return false;
    });

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
        } = await axios.post('{{ route('admin.accounts.items.delete') }}', {
          id
        })

        if (result.status) {
          toastr.success(result.message || 'Xoá thành công', 'Thành công');
          try {
            $('.datatable1_2').DataTable().ajax.reload(null, false);
          } catch (e) {}
          try {
            setActions();
          } catch (e) {}
        } else {
          toastr.error(result.message, 'Thất Bại');
        }
      } catch (error) {
        toastr.error($catchMessage(error), 'Thất Bại');
      } finally {
        $hideLoading();
      }
    }

    const refundAccount = async (id) => {
      const confirm = await Swal.fire({
        title: 'Xác nhận hoàn tiền?',
        text: "Bạn có chắc chắn muốn hoàn tiền tài khoản này? Tiền sẽ được cộng lại vào tài khoản người dùng.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
      });

      if (!confirm.isConfirmed) return;

      $showLoading();

      try {
        const { data: result } = await axios.post('{{ route('admin.accounts.items.refund') }}', { id });

        if (result.status) {
          toastr.success(result.message, 'Thành công');
          $('.datatable1_2').DataTable().ajax.reload();
        } else {
          toastr.error(result.message, 'Thất bại');
        }
      } catch (error) {
        toastr.error($catchMessage(error), 'Lỗi');
      } finally {
        $hideLoading();
      }
    }
  </script>

  <script>
    $(document).ready(function() {
      //DataTable
      $(".datatable1_2").DataTable({
        order: [0, 'desc'],
        responsive: false,
        lengthMenu: [
          [10, 50, 100, 200, 500, 1000, 2000, 10000, -1],
          [10, 50, 100, 200, 500, 1000, 2000, 10000, "All"]
        ],
        language: {
          searchPlaceholder: 'Tìm kiếm...',
          sSearch: '',
          lengthMenu: '_MENU_',
        },
        processing: true,
        serverSide: true,
        ajax: {
          url: '/api/admin/data/accounts-v1',
          async: true,
          type: 'GET',
          dataType: 'json',
          headers: {
            'Authorization': 'Bearer ' + userData.access_token,
            'Accept': 'application/json',
          },
          data: function(data) {
            let payload = {}
            // default params
            payload.group = $("#group").val()
            payload.sold = $("#sold").val()
            payload.buyer_name = $("#buyer_name").val()
            payload.username = $("#username").val()
            payload.start_date = $("#start_date").val()
            payload.end_date = $("#end_date").val()
            payload.domain = $("#domain").val()


            // set params
            payload.page = data.start / data.length + 1;
            payload.limit = data.length;
            payload.search = data.search.value;
            payload.sort_by = data.columns[data.order[0].column].data;
            payload.sort_type = data.order[0].dir;
            // return json
            return payload;
          },
          error: function(xhr) {
            toastr.error($catchMessage(xhr), 'Thất Bại');
          },
          dataFilter: function(data) {
            let json = JSON.parse(data);
            if (json.status) {
              json.recordsTotal = json.data.meta.total
              json.recordsFiltered = json.data.meta.total
              json.data = json.data.data
              return JSON.stringify(json); // return JSON string
            } else {
              toastr.error(json.message, 'Thất Bại');
              return JSON.stringify({
                recordsTotal: 0,
                recordsFiltered: 0,
                data: []
              }); // return JSON string
            }
          }
        },
        columns: [{
          data: null,
          sortable: false,
          render: function(data, type, row) {
            return `<input type="checkbox" name="checked_ids[]" value="${data.id}">`
          }
        }, {
          data: 'id'
        }, {
          data: 'group',
          render: function(data) {
            return `<a href="/admin/accounts/items?group=${data.id}">${data.name}</a>`
          }
        }, {
          data: null,
          render: function(data) {
            let actions = `<a href="/admin/accounts/items/edit/${data.id}" class="badge bg-primary-gradient text-white"><i class="fa fa-pencil"></i> sửa</a>
                    <a href="#" class="badge bg-danger-gradient text-white js-delete-row" data-id="${data.id}"><i class="fa fa-trash"></i> xoá</a>`;
            
            // Add refund button if item is sold and not already refunded
            if (data.buyer_name && data.buyer_code !== 'REFUNDED') {
              actions += ` <a href="javascript:refundAccount(${data.id})" class="badge bg-warning-gradient text-white"><i class="fa fa-undo"></i> hoàn tiền</a>`;
            }
            
            return actions;
          }
        }, {
          data: 'username'
        }, {
          data: 'code',
          render: function(data) {
            return `#${data}`
          }
        }, {
          data: 'price',
          render: function(data) {
            return $formatCurrency(data)
          }
        }, {
          data: 'cost',
          render: function(data) {
            return $formatCurrency(data)
          }
        }, {
          data: 'discount',
          render: function(data) {
            return `${data}%`
          }
        }, {
          data: 'image',
          render: function(data) {
            return `<img src="${data}" width="40" />`
          }
        }, {
          data: 'staff_name',
          render: function(data) {
            return data ?? '-'
          }
        }, {
          data: 'buyer_name',
          render: function(data) {
            return data ?? '-'
          }
        }, {
          data: 'buyer_date',
          render: function(data) {
            if (data === null) return '-'
            return $formatDate(data)
          }
        }, {
          data: 'buyer_paym',
          render: function(data) {
            return `<span class="text-danger">${$formatCurrency(data)}</span>`
          }
        }, {
          data: 'buyer_code',
          render: function(data) {
            return data ?? '-'
          }
        }, {
          data: 'is_sold',
          render: function(data) {
            return data === true ? '<span class="text-success">Đã bán</span>' : '<span class="text-danger">Chưa bán</span>'
          }
        }, {
          data: 'created_at',
          render: function(data) {
            return $formatDate(data)
          }
        }, {
          data: 'domain',
          render: function(data) {
            return data ?? '-'
          }
        }, {
          data: 'staff_payment',
          render: function(data) {
            return $formatCurrency(data)
          }
        }, {
          data: 'staff_status',
          render: function(data, type, row) {
            if (row.staff_name === null) return '<span class="text-success">Không cần duyệt</span>'
            return data === 'Completed' ? '<span class="text-success">Đã Duyệt</span>' : '<span class="text-danger">Chưa duyệt</span>'
          }
        }],
        columnDefs: [{
          orderable: false,
          targets: [1]
        }],
      })

      // filter changed
      $("#filter").on('submit', function(e) {
        e.preventDefault();
        $(".datatable1_2").DataTable().ajax.reload();
      })
    })
  </script>
@endsection
