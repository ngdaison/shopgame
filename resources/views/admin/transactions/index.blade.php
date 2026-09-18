@extends('admin.layouts.master')
@section('title', 'Admin: Transactions')
@section('content')
  <div class="card custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="card-title">Danh sách giao dịch</div>
    </div>
    <div class="card-body">
      <div class="mb-2">
        <form id="filter" onsubmit="$('#basic-1').DataTable().ajax.reload(); return false;">
          <div class="mb-3 row">
            <div class="col-md-2">
              <label for="type" class="form-label">Loại giao dịch</label>
              <select name="type" id="type" class="form-select">
                <option value="">Tất cả</option>
                <option value="account-buy">Mua Nick V1</option>
                <option value="account-buy-v2">Mua Nick V2</option>
                <option value="item-buy">Mua Vật Phẩm</option>
                <option value="boosting-buy">Cày thuê game</option>
                <option value="deposit-card">Nạp thẻ</option>
                <option value="deposit-bank">Nạp tiền</option>
                <option value="admin-change">Admin Change</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="username" class="form-label">Tài khoản</label>
              <input type="text" class="form-control" id="username" name="username">
            </div>
            <div class="col-md-2">
              <label for="start_date" class="form-label">Ngày bắt đầu</label>
              <input type="date" class="form-control" id="start_date" name="start_date">
            </div>
            <div class="col-md-2">
              <label for="end_date" class="form-label">Ngày kết thúc</label>
              <input type="date" class="form-control" id="end_date" name="end_date">
            </div>
            <div class="col-md-2">
              <label for="domain" class="form-label">Tên miền</label>
              <input type="text" class="form-control" id="domain" name="domain">
            </div>
            <div class="col-md-2 text-center">
              <label for="amount" class="form-label">Tổng Tiền</label>
              <input type="text" class="form-control" disabled id="total_amount">
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-primary">Lọc dữ liệu</button>
          </div>
        </form>
      </div>
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-nowrap" id="basic-1">
          <thead>
            <tr>
              <th>#</th>
              <th>Tài khoản</th>
              <th>Giao dịch</th>
              <th>Mã giao dịch</th>
              <th>Số dư trước</th>
              <th>Số tiền</th>
              <th>Số dư sau</th>
              <th>Nội dung</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
              <th>Tên miền</th>
              <th>Thao tác</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    $(document).ready(function() {
      //DataTable
      $("#basic-1").DataTable({
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
          url: '{{ route('admin.transactions.api') }}',
          type: 'GET',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             'Authorization': (typeof userData !== 'undefined' && userData && userData.access_token) ? 'Bearer ' + userData.access_token : ''
          },
          data: function(d) {
             // Map DataTable parameters to API parameters
             d.page = (d.start / d.length) + 1;
             d.limit = d.length;

             // Custom filters
             d.type = $('#type').val();
             d.username = $('#username').val();
             d.start_date = $('#start_date').val();
             d.end_date = $('#end_date').val();
             d.domain = $('#domain').val();
             
             // Handle search
             if (d.search && d.search.value) {
                 d.search = d.search.value;
             } else {
                 delete d.search;
             }
             
             // Handle sorting
             if(d.order && d.order[0]) {
                 d.sort_by = d.columns[d.order[0].column].data;
                 d.sort_type = d.order[0].dir;
             }
             
             // Cleanup
             delete d.columns;
             delete d.order;

             return d;
          },
          error: function(xhr) {
            console.error(xhr);
            toastr.error('Lỗi tải dữ liệu: ' + (xhr.statusText || 'Unknown Error'), 'Thất Bại');
          },
          dataFilter: function(data) {
            var json = JSON.parse(data);
            var dtData = {
                draw: json.draw || 0,
                recordsTotal: 0,
                recordsFiltered: 0,
                data: []
            };

            if (json.data) {
                if (json.data.meta) {
                    dtData.recordsTotal = json.data.meta.total;
                    dtData.recordsFiltered = json.data.meta.total;
                }
                if (json.data.data) {
                    dtData.data = json.data.data;
                     // Update Total Amount if element exists
                    if($('#total_amount').length > 0) {
                        var total = dtData.data.reduce((a, b) => parseFloat(a) + (parseFloat(b.amount) || 0), 0);
                        $('#total_amount').val($formatCurrency(total));
                    }
                }
            }
            
            return JSON.stringify(dtData);
          }
        },
        columns: [{
          data: 'id',
        }, {
          data: 'username',
          render: function(data, type, row) {
            return `<a href="/admin/users/edit/${row.user_id}">${data}</a>`
          }
        }, {
          data: 'type',
        }, {
          data: 'code',
        }, {
          data: 'balance_before',
          render: function(data, type, row) {
            return $formatCurrency(data)
          }
        }, {
          data: 'amount',
          render: function(data, type, row) {
            return row.prefix + ' ' + $formatCurrency(data)
          }
        }, {
          data: 'balance_after',
          render: function(data, type, row) {
            return $formatCurrency(data)
          }
        }, {
          data: 'content',
        }, {
          data: 'status',
          className: 'text-center',
          render: function(data, type, row) {
            return `<span class="text-primary">${data?.toUpperCase()}</span>`
          }
        }, {
          data: 'created_at',
          render: function(data, type, row) {
            return $formatDate(data)
          }
        }, {
          data: 'user_domain',
          render: function(data, type, row) {
            if (row.user_domain) {
              return `<span class="badge bg-info-gradient">${row.user_domain}</span>`;
            } else {
              return `<span class="badge bg-info-gradient">N/A</span>`;
            }
          }
        }, {
          data: 'id',
          className: 'text-center',
            render: function(data, type, row) {
                return '';
            }
        }],
        columnDefs: [{
          orderable: false,
          targets: [1]
        }],
      })
    })

    function deleteTransaction(id) {
        Swal.fire({
            title: 'Ẩn giao dịch?',
            text: "Giao dịch này sẽ bị ẩn khỏi Admin nhưng vẫn hiển thị với người dùng!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.transactions.delete.api') }}', {
                    ids: [id]
                }, {
                    headers: {
                        'Authorization': (typeof userData !== 'undefined' && userData && userData.access_token) ? 'Bearer ' + userData.access_token : ''
                    }
                }).then(res => {
                    toastr.success(res.data.message);
                    $('#basic-1').DataTable().ajax.reload();
                }).catch(err => {
                    toastr.error($catchMessage(err));
                })
            }
        })
    }

    function clearAllTransactions() {
        Swal.fire({
            title: 'Dọn dẹp toàn bộ giao dịch?',
            text: "Toàn bộ giao dịch (trừ Product Manager) sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý dọn dẹp',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.transactions.clear-all') }}', {}, {
                    headers: {
                        'Authorization': 'Bearer ' + userData.access_token
                    }
                }).then(res => {
                    toastr.success(res.data.message);
                    $('#basic-1').DataTable().ajax.reload();
                }).catch(err => {
                    toastr.error($catchMessage(err));
                })
            }
        })
    }
  </script>
@endsection
