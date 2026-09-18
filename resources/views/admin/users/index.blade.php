@extends('admin.layouts.master')
@section('title', 'Admin: Users Management')
@section('content')
  <div class="row">
        <div class="col-sm-6 col-md-4 col-lg">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                             <span class="badge bg-primary p-3 rounded-3 text-white">
                                <i class="fa fa-users fs-2"></i>
                             </span>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($total_users) }}</h4>
                            <p class="mb-0 text-secondary">Tổng thành viên</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg">
             <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                             <span class="badge bg-info p-3 rounded-3 text-white">
                                <i class="fa fa-wallet fs-2"></i>
                             </span>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($total_balance) }}đ</h4>
                            <p class="mb-0 text-secondary">Số dư còn lại</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <div class="col-sm-6 col-md-4 col-lg">
             <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                             <span class="badge bg-success p-3 rounded-3 text-white">
                                <i class="fa fa-dollar fs-2"></i>
                             </span>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($total_deposit) }}đ</h4>
                            <p class="mb-0 text-secondary">Tổng nạp</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg">
             <div class="card">
                <div class="card-body">
                     <div class="d-flex align-items-center">
                        <div class="me-3">
                             <span class="badge bg-warning p-3 rounded-3 text-white">
                                <i class="fa fa-user-secret fs-2"></i>
                             </span>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($total_admins) }}</h4>
                            <p class="mb-0 text-secondary">ADMIN</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg">
             <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                             <span class="badge bg-danger p-3 rounded-3 text-white">
                                <i class="fa fa-lock fs-2"></i>
                             </span>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($total_banned) }}</h4>
                            <p class="mb-0 text-secondary">Banned</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý thành viên</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-3">
        <table class="display table table-bordered table-stripped text-nowrap text-center" id="basic-1">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Tài khoản</th>
              <th>Họ và tên</th>
              <th>Email</th>
              <th>Số dư khả dụng</th>
              <th>Tổng nạp</th>
              <th>Cấp bậc</th>
              <th>Loại tài khoản</th>
              <th>Đăng Ký Bằng</th>
              <th>Trạng thái</th>
              <th>Domain</th>
              <th>Online</th>
              <th>Thời gian</th>
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
          url: '/api/admin/users',
          async: true,
          type: 'GET',
          dataType: 'json',

          data: function(data) {
            let payload = {}
            // default params
            payload.username = $('#username').val();
            payload.full_name = $('#full_name').val();
            payload.email = $('#email').val();
            payload.ip_address = $('#ip_address').val();

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
          render: function(data, type, row) {
             let actions = '';
             actions += `<a href="/admin/users/edit/${row.id}" class="btn btn-sm btn-primary me-1" title="Edit"><i class="fa fa-edit me-1"></i> Sửa</a>`;
             actions += `<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-row" data-id="${row.id}" data-url="/api/admin/users/delete/${row.id}" title="Delete"><i class="fa fa-trash me-1"></i> xoá</a>`;
             return actions;
          }
        }, {
          data: 'username',
          render: function(data, type, row) {
              return `<span class="fw-bold text-primary">${data}</span> <br> <small class="text-muted">[ID: ${row.id}]</small>`;
          }
        }, {
          data: 'full_name',
        }, {
          data: 'email',
        }, {
          data: 'balance',
          render: function(data) {
            return $formatCurrency(data)
          }
        }, {
          data: 'total_deposit',
          render: function(data) {
            return $formatCurrency(data)
          }
        }, {
          data: 'max_level',
          render: function(data) {
             return `<span class="badge bg-dark">${data}</span>`;
          }
        }, {
          data: 'role',
          render: function(data) {
            let roles = data ? data.split(',') : ['Member'];
            let html = '';
            roles.forEach(r => {
                let badgeClass = 'bg-secondary';
                if (r.toLowerCase().includes('admin')) badgeClass = 'bg-danger';
                else if (r.toLowerCase().includes('cộng tác viên')) badgeClass = 'bg-info';
                else if (r.toLowerCase().includes('đối tác')) badgeClass = 'bg-primary';
                html += `<span class="badge ${badgeClass} m-1">${r.trim()}</span>`;
            });
            return html;
          }
        }, {
          data: 'register_by',
          render: function(data) {
            if (!data) return '<span class="badge bg-secondary">WEB</span>';
            return `<span class="badge bg-info">${data.toUpperCase()}</span>`;
          }
        }, {
          data: 'status',
          render: function(data) {
            return data === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Banned</span>';
          }
        }, {
           data: 'domain',
           render: function(data) {
               return data ? `<span class="badge bg-secondary">${data}</span>` : '-';
           }
        }, {
           data: 'is_online',
           render: function(data, type, row) {
               if (data === true || data === 1) { 
                   return '<span class="badge bg-success">Online</span>';
               } else {
                   return '<span class="badge bg-secondary">Offline</span>';
               }
           }
        }, {
          data: 'created_at',
          render: function(data) {
            return $formatDate(data)
          }
        }],
        columnDefs: [{
          orderable: false,
          targets: [1]
        }],
      })


      
      // Handle delete-row click
      $(document).on('click', '.delete-row', function() {
        const id = $(this).data('id');
        const url = $(this).data('url');
        
        Swal.fire({
          title: 'Bạn có chắc chắn?',
          text: "Bạn sẽ không thể khôi phục sau khi xóa!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Đồng ý, xóa!',
          cancelButtonText: 'Hủy'
        }).then((result) => {
          if (result.isConfirmed) {
            if (typeof pageOverlay !== 'undefined') pageOverlay.show();
            axios.post(url, {
              _token: '{{ csrf_token() }}'
            })
            .then(res => {
              if (res.data.status === true || res.data.status === 200) {
                sessionStorage.setItem('pending_notification', JSON.stringify({
                    type: 'success',
                    title: 'Thành Công',
                    message: res.data.message || 'Đã xóa thành công'
                }));
                location.reload();
              } else {
                if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
                toastr.error(res.data.message || 'Lỗi không xác định', 'Thất Bại');
              }
            })
            .catch(err => {
              if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
              toastr.error($catchMessage(err), 'Thất Bại');
            });
          }
        });
      });
    })
  </script>
@endsection
