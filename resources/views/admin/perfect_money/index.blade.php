@extends('admin.layouts.master')
@section('title', 'Phương thức thanh toán Perfect Money')
@section('css')
  <style>
    .card-stats .card-body {
        padding: 15px;
        position: relative;
    }
    .card-stats h3 {
      color: #000;
      font-weight: 700;
      font-size: 20px;
      margin-bottom: 5px;
    }
    .card-stats h5 {
      color: #6c757d;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 0;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
    }
    .bg-orange-red { background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%); }
    .bg-blue { background: linear-gradient(135deg, #56CCF2 0%, #2F80ED 100%); }
    .bg-yellow { background: linear-gradient(135deg, #F2994A 0%, #F2C94C 100%); }
    .bg-purple { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); }
    
    .chart-title {
        font-weight: 700;
        font-size: 16px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
    }
    .chart-title::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 20px;
        background-color: #6f42c1;
        margin-right: 10px;
        border-radius: 2px;
    }
    
    .section-title {
        font-weight: 700;
        font-size: 16px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        margin-bottom: 0;
    }
    .section-title::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 18px;
        background-color: #6f42c1;
        margin-right: 10px;
        border-radius: 2px;
    }

    .btn-purple {
        background-color: #6f42c1;
        color: white;
        border: none;
    }
    .btn-purple:hover {
        background-color: #5e37a6;
        color: white;
    }
    .table thead th {
        font-weight: 600;
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 12px;
    }
    .username-link {
        color: #6f42c1;
        font-weight: 600;
        text-decoration: none;
    }
    .text-green { color: #28a745 !important; }
    .text-red { color: #dc3545 !important; }
  </style>
@endsection
@section('content')
  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Nạp tiền</a></li>
                <li class="breadcrumb-item active" aria-current="page">Perfect Money</li>
            </ol>
        </nav>
        <div>
            <a href="{{ route('admin.deposit.perfect_money.config') }}" class="btn btn-purple btn-sm shadow-sm">
                <i class="fa fa-cog me-1"></i> CẤU HÌNH
            </a>
        </div>
    </div>
  </div>

  <div class="row">
    <!-- Left Stats (4 cards) -->
    <div class="col-md-5">
        <div class="row">
            {{-- Order: Total, Month, Week, Today --}}
            <div class="col-md-6 mb-3">
                <div class="card card-stats h-100 mb-0 shadow-sm">
                    <div class="card-body">
                        <h3>{{ number_format($stats['banks']['total']) }}đ</h3>
                        <h5>Toàn thời gian</h5>
                        <div class="stat-icon bg-orange-red">
                            <i class="fa fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card card-stats h-100 mb-0 shadow-sm">
                    <div class="card-body">
                        <h3>{{ number_format($stats['banks']['month']) }}đ</h3>
                        <h5>Tháng {{ now()->format('m') }}</h5>
                        <div class="stat-icon bg-blue">
                             <i class="fa fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card card-stats h-100 mb-0 shadow-sm">
                    <div class="card-body">
                        <h3>{{ number_format($stats['banks']['week']) }}đ</h3>
                        <h5>Trong tuần</h5>
                        <div class="stat-icon bg-yellow">
                            <i class="fa fa-calendar-week"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card card-stats h-100 mb-0 shadow-sm">
                    <div class="card-body">
                        <h3>{{ number_format($stats['banks']['today']) }}đ</h3>
                        <h5 class="d-flex align-items-center">
                            Hôm nay 
                            @if($stats['banks']['today'] == 0)
                            <small class="text-danger ms-2" style="font-size: 11px;">↘ 100%</small>
                            @endif
                        </h5>
                        <div class="stat-icon bg-purple">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Chart -->
    <div class="col-md-7 mb-3">
        <div class="card h-100 shadow-sm mb-0">
            <div class="card-body">
                <div class="chart-title mb-3">THỐNG KÊ NẠP PERFECT MONEY THÁNG {{ now()->format('m') }}</div>
                <div id="depositChart"></div>
            </div>
        </div>
    </div>
  </div>

   <!-- History Section -->
   <div class="card custom-card shadow-sm">
       <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
         <div class="section-title">LỊCH SỬ NẠP PERFECT MONEY TỰ ĐỘNG</div>
         @if(!auth()->user()->hasRole('Product Manager'))
         <button class="btn btn-danger btn-sm shadow-sm" onclick="clearAllHistory()">
             <i class="fa fa-trash me-1"></i> DỌN DẸP LỊCH SỬ
         </button>
         @endif
       </div>
       <div class="card-body">
         <div class="mb-4">
           <form id="filter" onsubmit="$('#basic-1').DataTable().ajax.reload(); return false;">
             <div class="row g-2">
               <div class="col-md-2">
                 <label for="username" class="form-label">Tài khoản</label>
                 <input type="text" class="form-control" id="username" name="username">
               </div>
               <div class="col-md-2">
                 <label for="code" class="form-label">Mã giao dịch</label>
                 <input type="text" class="form-control" id="code" name="code">
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
                  <label for="domain" class="form-label">Domain</label>
                  <input type="text" class="form-control" id="domain" name="domain" placeholder="Domain...">
                </div>
               <div class="col-md-2 text-center">
                  <label for="amount" class="form-label">Tổng Tiền</label>
                  <input type="text" class="form-control" disabled id="total_amount">
               </div>
             </div>
             <div class="text-center mt-3">
               <button class="btn btn-primary">Lọc dữ liệu</button>
             </div>
           </form>
         </div>
         
         <div class="table-responsive theme-scrollbar">
           <table class="display table table-bordered table-striped text-nowrap" id="basic-1">
             <thead>
               <tr>
                 <th>#</th>
                 <th>Domain</th>
                 <th>Tài khoản</th>
                 <th>Mã Giao Dịch</th>
                 <th>Số dư trước</th>
                 <th>Số tiền</th>
                 <th>Số dư sau</th>
                 <th>Nội dung</th>
                 <th>Trạng thái</th>
                 <th>Thời gian</th>
                 <th>Thao tác</th>
               </tr>
             </thead>
             <tbody></tbody>
           </table>
         </div>
       </div>
   </div>
 @endsection
 
 @section('scripts')
 <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
 <script>
     // Chart
     var options = {
         series: [{
             name: 'Nạp PM',
             data: @json($data_chart ?? [])
         }],
         chart: {
             height: 350,
             type: 'bar',
             toolbar: { show: false }
         },
         colors: ['#6f42c1'],
         plotOptions: {
             bar: {
                 borderRadius: 4,
                 columnWidth: '20px', 
             }
         },
         dataLabels: { enabled: false },
         stroke: { show: true, width: 2, colors: ['transparent'] },
         xaxis: {
             categories: @json($dates ?? []),
             labels: {
                 rotate: -45,
                 style: { fontSize: '10px' }
             }
         },
         yaxis: {
             labels: {
                  formatter: function (value) {
                     return value.toLocaleString('vi-VN');
                  }
             }
         },
         fill: { opacity: 1 },
         grid: {
             borderColor: '#f1f1f1',
         },
         tooltip: {
             enabled: true,
             theme: 'light',
             shared: true,
             intersect: false,
             custom: function({series, seriesIndex, dataPointIndex, w}) {
                 var value = parseInt(series[0][dataPointIndex]).toLocaleString('vi-VN');
                 var label = w.globals.categoryLabels[dataPointIndex] || w.globals.labels[dataPointIndex] || '';
                 return '<div style="padding: 8px 12px; font-size: 13px;">' +
                     '<div style="font-weight: 600; margin-bottom: 4px;">' + label + '</div>' +
                     '<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#6f42c1;margin-right:6px;"></span>Paid: <span style="color:#28a745;font-weight:600;">' + value + ' đ</span></div>' +
                     '</div>';
             }
         }
     };
     var chart = new ApexCharts(document.querySelector("#depositChart"), options);
     chart.render();
 
     $(document).ready(function() {
       // DataTable
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
           url: '{{ route('admin.deposit.perfect_money.api') }}',
           type: 'GET',
           headers: {
             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
              'Authorization': (typeof userData !== 'undefined' && userData && userData.access_token) ? 'Bearer ' + userData.access_token : ''
           },
           data: function(d) {
              d.page = (d.start / d.length) + 1;
              d.limit = d.length;
 
              // Custom filters
              d.type = 'perfect_money'; 
              d.username = $('#username').val();
              d.trans_id = $('#code').val();
              d.domain = $('#domain').val();
              d.start_date = $('#start_date').val();
              d.end_date = $('#end_date').val();
              
              if (d.search && d.search.value) {
                  d.search = d.search.value;
              } else {
                  delete d.search;
              }
              
              if(d.order && d.order[0]) {
                  d.sort_by = d.columns[d.order[0].column].data;
                  d.sort_type = d.order[0].dir;
              }
              
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
           data: 'user_domain',
           render: function(data, type, row) {
               return `<span class="badge badge-info">${data || 'System'}</span>`
           }
         }, {
           data: 'username',
           render: function(data, type, row) {
             return `<a href="/admin/users/edit/${row.user_id}">${data}</a>`
           }
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
            data: 'id',
            className: 'text-center',
            render: function(data, type, row) {
                @if(auth()->user()->hasRole('Product Manager'))
                    return '';
                @endif
                if (row.user_role && row.user_role.includes('Product Manager')) {
                    return '';
                }
                return `<button class="btn btn-sm btn-danger" onclick="deleteTransaction(${data})"><i class="fa fa-trash"></i></button>`;
            }
         }],
         columnDefs: [{
           orderable: false,
           targets: [1]
         }],
       })
     });

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

    function clearAllHistory() {
        Swal.fire({
            title: 'Dọn dẹp toàn bộ lịch sử?',
            text: "Toàn bộ lịch sử nạp Perfect Money (trừ Product Manager) sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý dọn dẹp',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.deposit.perfect_money.clear-all') }}', {}, {
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
