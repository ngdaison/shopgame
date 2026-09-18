@extends('admin.layouts.master')
@section('title', 'Admin: Accounts V2 Orders')
@section('content')
  <div class="card custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="card-title">Quản lý đơn hàng Accounts V2</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar p-2">
        <table class="display table table-bordered table-stripped text-center datatable1_2">
          <thead>
            <tr>
              <th width="30">#</th>
              <th>Thao tác</th>
              <th>Username</th>
              <th>Tên miền</th>
              <th>Đơn hàng</th>
              <th>Trạng thái</th>
              <th>Thanh toán</th>
              <th>Sản phẩm</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer"></div>
  </div>

  <!-- Edit Order Modal Template -->
  <div class="modal fade" id="modal-edit-template" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cập nhật đơn hàng <span id="modal-order-code"></span></h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="order-update-form" class="default-form axios-form" data-reload="true" method="POST">
            @csrf
            <input type="hidden" id="order-buyer-code" name="buyer_code">
            
            <div class="mb-3">
              <label class="form-label">Sản phẩm</label>
              <input type="text" id="order-product" class="form-control" disabled>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Mã đơn hàng</label>
                <input type="text" id="order-code-display" class="form-control" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">Thanh toán</label>
                <input type="text" id="order-payment" class="form-control" disabled>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Số lượng</label>
                <input type="text" id="order-quantity" class="form-control" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">Người mua</label>
                <input type="text" id="order-buyer" class="form-control" disabled>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Ghi chú khách</label>
              <textarea class="form-control" id="customer_note" rows="2" disabled></textarea>
            </div>

            <div class="mb-3">
              <label for="admin_note" class="form-label">Ghi chú admin</label>
              <textarea class="form-control" id="admin_note" name="admin_note" rows="2"></textarea>
            </div>

            <div class="mb-3">
              <label for="status" class="form-label">Trạng thái</label>
              <select class="form-select" id="status" name="status" required>
                <option value="Completed">Hoàn thành</option>
                <option value="Processing">Đang xử lý</option>
                <option value="Cancelled">Hủy đơn (Hoàn tiền)</option>
              </select>
            </div>

            <div id="refund-section" style="display: none;">
              <h6>Hoàn tiền một phần</h6>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="refund_quantity" class="form-label">Số lượng cần hoàn *</label>
                  <input type="number" class="form-control" id="refund_quantity" name="refund_quantity" min="0" max="1" value="0">
                  <small class="text-muted">Không vượt quá tổng số lượng còn lại</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tổng số tiền hoàn</label>
                  <input type="text" id="refund_amount_display" class="form-control" disabled value="0đ">
                </div>
              </div>
            </div>

            <div class="mb-3">
              <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    // Store order data globally for modal access
    let ordersData = {};

    $(document).ready(function() {
      console.log('Document ready, initializing DataTable...');
      
      // Check if table exists
      const table = $('.datatable1_2');
      console.log('Table found:', table.length, 'elements');
      
      if (table.length === 0) {
        console.error('Table with class .datatable1_2 not found!');
        alert('ERROR: Table not found in DOM');
        return;
      }
      
      // DataTable
      const dt = table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '/api/admin/data/accounts-v2',
          type: 'GET',
          headers: {
            'Authorization': 'Bearer ' + userData.access_token,
            'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: function(d) {
            d.sold = 1; // Filter for sold items only
            d.unique_orders = 1; // Group by order
            d.table = 'resource_v2_o'; // Explicitly request orders table
            d.limit = d.length;
            d.page = (d.start / d.length) + 1;
            d.search = d.search.value;
            
            // Map columns for sorting
            const colMap = {
              0: 'id',
              2: 'buyer_name',
              3: 'domain',
              4: 'buyer_code',
              5: 'order_status',
              6: 'total_payment',
              8: 'buyer_date',
            };
            
            // Set sort params safely
            if (d.order && d.order.length > 0 && d.order[0]) {
              d.sort_by = colMap[d.order[0].column] || 'id';
              d.sort_type = d.order[0].dir || 'desc';
            } else {
              d.sort_by = 'id';
              d.sort_type = 'desc';
            }
          },
          dataFilter: function(data) {
            try {
              let json = JSON.parse(data);
              console.log('DataFilter received:', json);
              
              if (json.status === 200 && json.data && json.data.data) {
                json.recordsTotal = json.data.meta ? json.data.meta.total : 0;
                json.recordsFiltered = json.data.meta ? json.data.meta.total : 0;
                
                console.log('Number of records:', json.data.data.length);
                
                // Store order data globally and create modals
                if (Array.isArray(json.data.data)) {
                  json.data.data.forEach((order, idx) => {
                    ordersData[order.buyer_code] = order;
                    console.log('Stored order ' + idx + ':', order.buyer_code);
                    
                    // Create modal if it doesn't exist
                    if (!$('#modal-edit-' + order.buyer_code).length) {
                      const modalHtml = $('#modal-edit-template').clone()
                        .attr('id', 'modal-edit-' + order.buyer_code)
                        .removeClass('d-none');
                      $('body').append(modalHtml);
                      console.log('Created modal for:', order.buyer_code);
                    }
                  });
                }
                
                console.log('ordersData keys:', Object.keys(ordersData));
                json.data = json.data.data;
                console.log('DataFilter returning:', json.data.length, 'records');
                return JSON.stringify(json);
              } else {
                console.error('API Error Response:', json);
                toastr.error(json.message || 'Không tải được dữ liệu');
                return JSON.stringify({
                  recordsTotal: 0,
                  recordsFiltered: 0,
                  data: []
                });
              }
            } catch(e) {
              console.error('DataFilter Error:', e, 'Data:', data);
              toastr.error('Lỗi xử lý dữ liệu: ' + e.message);
              return JSON.stringify({
                recordsTotal: 0,
                recordsFiltered: 0,
                data: []
              });
            }
          },
          error: function(xhr, status, error) {
            console.error('DataTable AJAX Error:', {
              status: status,
              error: error,
              statusCode: xhr.status,
              response: xhr.responseText
            });
            
            let errorMsg = 'Lỗi tải dữ liệu';
            if (xhr.status === 401) {
              errorMsg = 'Không xác thực. Vui lòng đăng nhập lại.';
            } else if (xhr.status === 403) {
              errorMsg = 'Không có quyền truy cập.';
            } else if (xhr.status === 500) {
              try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || 'Lỗi server';
                console.error('Server error details:', response);
              } catch(e) {
                errorMsg = 'Lỗi server (500)';
              }
            }
            
            console.error('Final error message:', errorMsg);
            if (typeof toastr !== 'undefined') {
              toastr.error(errorMsg);
            } else {
              alert('AJAX Error: ' + errorMsg);
            }
          }
        },
        columns: [
          { data: 'id' },
          {
            data: 'buyer_code',
            orderable: false,
            render: function(data, type, row) {
              let actions = `
                <a href="javascript:void(0)" class="badge bg-success-gradient" data-bs-toggle="modal" data-bs-target="#modal-edit-${row.buyer_code}">
                  <i class="fa fa-edit"></i> sửa
                </a>
              `;
              
              return actions;
            }
          },
          {
            data: 'buyer_name',
            render: function(data, type, row) {
              if (row.buyer_name) {
                return `<a href="/admin/users?username=${row.buyer_name}" target="_blank" class="text-primary font-weight-bold">${row.buyer_name}</a>`;
              }
              return '-';
            }
          },
          {
            data: 'domain',
            render: function(data, type, row) {
              if (row.domain_display) {
                return `<span class="badge bg-info-gradient">${row.domain_display}</span>`;
              }
              return `<span class="badge bg-info-gradient">N/A</span>`;
            }
          },
          {
            data: 'buyer_code',
            render: function(data, type, row) {
              return `
                <div class="text-start">
                  <div>Mã đơn hàng: <span class="fw-bold">#${row.buyer_code}</span></div>
                  <div>Mã đơn API: -</div>
                  <div>Server API: -</div>
                </div>
              `;
            }
          },
          {
            data: 'order_status',
            render: function(data, type, row) {
              const status = row.order_status || 'Completed';
              let badgeClass = 'bg-success';
              if (status === 'Cancelled') badgeClass = 'bg-danger';
              else if (status === 'Processing') badgeClass = 'bg-warning';
              return `<span class="badge ${badgeClass}">${status}</span>`;
            }
          },
          {
            data: 'total_payment',
            render: function(data, type, row) {
              const payment = row.total_payment || row.buyer_paym || 0;
              const quantity = row.quantity || 1;
              const cost = (row.parent ? (row.parent.cost || 0) : 0) * quantity;
              const profit = payment - cost;
              const profitClass = profit > 0 ? 'text-success' : 'text-danger';
              
              const formattedPayment = typeof $formatCurrency !== 'undefined' ? $formatCurrency(payment) : payment;
              const formattedCost = typeof $formatCurrency !== 'undefined' ? $formatCurrency(cost) : cost;
              const formattedProfit = typeof $formatCurrency !== 'undefined' ? $formatCurrency(profit) : profit;

              return `
                <div class="text-start">
                  <div>Số lượng: <b class="text-warning">${quantity}</b></div>
                  <div>Thanh toán: <span class="text-danger fw-bold">${formattedPayment}</span></div>
                  <div>Giá vốn: ${formattedCost} - Lãi: <span class="${profitClass}">${formattedProfit}</span></div>
                </div>
              `;
            }
          },
          {
            data: 'code',
            render: function(data, type, row) {
              // Use product_name which is loaded in the API response
              let name = row.product_name || (row.parent ? row.parent.name : row.code);
              let groupName = (row.parent && row.parent.group) ? row.parent.group.name : '-';
              
              return `
                  <div class="text-start">
                      <div class="text-primary fw-bold">${name}</div>
                      <div class="text-muted fs-11">${groupName}</div>
                  </div>
              `;
            }
          },
          {
            data: 'buyer_date',
            render: function(data, type, row) {
              return `<span class="fw-bold">${typeof $formatDate !== 'undefined' ? $formatDate(data) : data}</span>`;
            }
          }
        ],
        order: [[0, 'desc']], // Default sort by id desc
        language: {
             searchPlaceholder: "Tìm kiếm...",
             sSearch: "",
             lengthMenu: "_MENU_ ",
        },
      });
      
      console.log('DataTable initialized successfully');
    });


    // Populate modal when edit button is clicked
    $(document).on('show.bs.modal', '[id^="modal-edit-"]', function(e) {
      const $modal = $(this);
      const buyerCode = $modal.attr('id').replace('modal-edit-', '');
      console.log('Modal show event:', buyerCode);
      console.log('Available buyer codes:', Object.keys(ordersData));
      
      const orderData = ordersData[buyerCode];
      
      if (!orderData) {
        console.error('Order data not found for buyer code:', buyerCode);
        console.error('ordersData:', ordersData);
        toastr.error('Không tìm thấy dữ liệu đơn hàng!');
        return;
      }

      console.log('Loading order data:', orderData);
      
      const productName = orderData.product_name || (orderData.parent ? orderData.parent.name : orderData.code);
      
      // Use scoped selectors to find elements within this modal
      $modal.find('#modal-order-code').text('#' + buyerCode);
      $modal.find('#order-buyer-code').val(buyerCode);
      $modal.find('#order-product').val(productName);
      $modal.find('#order-code-display').val(buyerCode);
      $modal.find('#order-payment').val($formatCurrency(orderData.total_payment || orderData.buyer_paym || 0));
      $modal.find('#order-quantity').val(orderData.quantity || 1);
      $modal.find('#order-buyer').val(orderData.buyer_name);
      $modal.find('#admin_note').val(orderData.admin_note || '');
      $modal.find('#customer_note').val(orderData.buyer_note || orderData.customer_note || orderData.order_note || '');

      // Set current status
      const currentStatus = orderData.order_status || 'Completed';
      $modal.find('#status').val(currentStatus);

      // If order is already Cancelled, disable the cancel option and hide refund section
      const $statusSelect = $modal.find('#status');
      const $cancelledOption = $statusSelect.find('option[value="Cancelled"]');
      
      if (currentStatus === 'Cancelled') {
        // Already refunded - disable changing to cancelled again
        $cancelledOption.prop('disabled', true).text('Hủy đơn (Đã hoàn tiền)');
        $modal.find('#refund-section').hide();
      } else {
        // Enable cancelled option
        $cancelledOption.prop('disabled', false).text('Hủy đơn (Hoàn tiền)');
        $modal.find('#refund-section').hide(); // Hidden by default
      }

      $modal.find('#refund_quantity').attr('max', orderData.quantity || 1).val(0);
      $modal.find('#refund_amount_display').val('0đ');

      console.log('Modal populated successfully');
      
      // Calculate refund amount on quantity change
      $modal.find('#refund_quantity').off('input').on('input', function() {
        const qty = parseInt($(this).val()) || 0;
        const maxQty = parseInt($(this).attr('max'));
        const totalPayment = orderData.total_payment || orderData.buyer_paym || 0;
        const orderQty = orderData.quantity || 1;
        const perItemPrice = totalPayment / orderQty;
        const refundAmount = qty * perItemPrice;
        
        if (qty > maxQty) {
          $(this).val(maxQty);
          $modal.find('#refund_amount_display').val($formatCurrency(maxQty * perItemPrice));
        } else {
          $modal.find('#refund_amount_display').val($formatCurrency(refundAmount));
        }
      });

      // Set form action using scoped selector
      $modal.find('#order-update-form').attr('action', '{{ route("admin.accountsv2.orders.update") }}');

      // Handle status change to toggle refund section
      $modal.find('#status').off('change').on('change', function() {
        if ($(this).val() === 'Cancelled' && currentStatus !== 'Cancelled') {
          $modal.find('#refund-section').show();
        } else {
          $modal.find('#refund-section').hide();
        }
      });
    });

    function deleteOrder(buyer_code) {
        Swal.fire({
            title: 'Ẩn đơn hàng?',
            text: "Đơn hàng này sẽ bị ẩn khỏi Admin nhưng vẫn hiển thị ở lịch sử người dùng!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.accountsv2.orders.delete') }}', {
                    buyer_code
                }).then(res => {
                    if (res.data.status) {
                        toastr.success(res.data.message);
                        $('.datatable1_2').DataTable().ajax.reload();
                    } else {
                        toastr.error(res.data.message);
                    }
                }).catch(err => {
                    toastr.error('Có lỗi xảy ra!');
                })
            }
        })
    }

    function clearAllOrders() {
        Swal.fire({
            title: 'Dọn dẹp toàn bộ đơn hàng?',
            text: "Toàn bộ đơn hàng Accounts V2 (trừ Product Manager) sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý dọn dẹp',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route('admin.accountsv2.orders.clear-all') }}').then(res => {
                    if (res.data.status) {
                        toastr.success(res.data.message);
                        $('.datatable1_2').DataTable().ajax.reload();
                    } else {
                        toastr.error(res.data.message);
                    }
                }).catch(err => {
                    toastr.error('Có lỗi xảy ra!');
                })
            }
        })
    }

  </script>
@endsection
