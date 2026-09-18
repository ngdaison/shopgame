@extends('admin.layouts.master')
@section('title', 'Admin: Transactions')
@section('content')
  <div class="card custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="card-title">Nhật ký hoạt động</div>
    </div>
    <div class="card-body">
      <div class="mb-2">
        <form id="filter" onsubmit="$('#basic-1').DataTable().ajax.reload(); return false;">
          <div class="mb-3 row">
            <div class="col-md-4">
              <label for="username" class="form-label">Tài khoản</label>
              <input type="text" class="form-control" id="username" name="username">
            </div>
            <div class="col-md-4">
              <label for="type" class="form-label">_</label>
              <div>
                <button class="btn btn-primary">Lọc dữ liệu</button>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-nowrap" id="basic-1">
          <thead>
            <tr>
              <th>#</th>
              <th>Tài khoản</th>
              <th>Nội dung</th>
              <th>Địa chỉ IP</th>
              <th>Thời gian</th>
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
          url: '/api/admin/histories',
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
            payload.type = $('#type').val();
            payload.username = $('#username').val();
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
          data: 'id',
        }, {
          data: 'username',
        }, {
          data: 'content',
        }, {
          data: 'ip_address',
        }, {
          data: 'created_at',
          render: function(data, type, row) {
            return moment(data).format('DD/MM/YYYY HH:mm:ss')
          }
        }, {
          data: 'id',
          render: function(data, type, row) {
              return "";
          }
        }],
        columnDefs: [{
          orderable: false,
          targets: [1]
        }],
      })

    function deleteHistory(id) {
        Swal.fire({
            title: 'Ẩn lịch sử?',
            text: "Lịch sử này sẽ bị ẩn khỏi Admin nhưng vẫn hiển thị với người dùng!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('/api/admin/histories/delete', {
                    ids: [id]
                }, {
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
    function clearAllHistory() {
        Swal.fire({
            title: 'Dọn dẹp toàn bộ lịch sử?',
            text: "Toàn bộ lịch sử hoạt động (trừ Product Manager) sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý dọn dẹp',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.histories.clear-all') }}', {}, {
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
    })
  </script>
@endsection
