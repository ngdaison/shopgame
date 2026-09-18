@extends('admin.layouts.master')
@section('title', 'Admin: Invoices Management')
@section('content')
  <div class="mb-3 alert alert-danger">
    Để sử dụng chức năng nạp tiền bằng hóa đơn (qua ngân hàng) vui lòng CRON link này: <a href="{{ route('cron.deposit.check', ['dp_type' => 'invoice', 'type' => 'BANK_KEY']) }}"
      target="_blank">{{ route('cron.deposit.check', ['dp_type' => 'invoice', 'type' => 'BANK_KEY']) }} [BANK_KEY=acb, vietcombank... tương tự trong trang cài đặt apis]</a>
    | 2 phút / lần
  </div>
  <div class="card custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="card-title">Quản lý hoá đơn</div>
      @if(!auth()->user()->hasRole('Product Manager'))
      <button class="btn btn-danger btn-wave" onclick="clearAllInvoices()">
          <i class="bx bx-trash"></i> Dọn dẹp lịch sử
      </button>
      @endif
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Thao tác</th>
              <th>Mã hoá đơn</th>
              <th>Domain</th>
              <th>Số tiền</th>
              <th>Loại tiền tệ</th>
              <th>Loại giao dịch</th>
              <th>Khách hàng</th>
              <th>Trạng thái</th>
              <th>Ngân hàng</th>
              <th>Còn lại</th>
              <th>Ghi chú</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($invoices as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  @if ($item->status === 'processing')
                    <a href="javascript:setStatus({{ $item->id }}, 'paid')" class="shadow text-white badge bg-success-gradient"><i class="fas fa-thumbs-up"></i></a>
                    <a href="javascript:setStatus({{ $item->id }}, 'cancelled')" class="shadow text-white badge bg-danger-gradient"><i class="fas fa-trash"></i></a>
                  @endif
                </td>
                <td>{{ $item->code }}</td>
                <td><span class="badge bg-info">{{ $item->domain_display }}</span></td>
                <td>{{ Helper::formatCurrency($item->amount) }}</td>
                <td>{{ $item->currency }}</td>
                <td>{{ Helper::formatTransType($item->type) }}</td>
                <td>{{ $item->username }}</td>
                <td>{!! Helper::formatStatus($item->status) !!}</td>
                <td>
                  @php $paymentDetail = ($item->payment_details) @endphp

                  @if ($paymentDetail)
                    <span class="text-danger">{{ $paymentDetail['name'] ?? 'N/A' }}|{{ $paymentDetail['number'] ?? 'N/A' }}</span>
                  @else
                    <span class="text-danger">-</span>
                  @endif

                </td>
                <td>
                  @if ($item->is_expired)
                    <span class="text-danger">-</span>
                  @else
                    {{ $item->expired_str }}
                  @endif
                </td>

                <td>{{ $item->description }}</td>
                <td>{{ $item->created_at }}</td>
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
    const setStatus = async (id, type) => {
      let simpleText = type === 'paid' ? 'đã thanh toán' : 'đã hủy'

      const confirm = await Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: `Chuyển trạng thái hoá đơn này sang ${simpleText}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy',
      })

      if (confirm.isConfirmed !== true)
        return

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.invoices.update') }}', {
          id,
          type
        })

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành Công',
            message: result.message || 'Cập nhật trạng thái thành công'
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
    const clearAllInvoices = async () => {
        const confirm = await Swal.fire({
            title: 'Dọn dẹp toàn bộ hoá đơn?',
            text: "Toàn bộ hoá đơn (trừ Product Manager) sẽ bị ẩn khỏi Admin!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý dọn dẹp',
            cancelButtonText: 'Hủy'
        });

        if (confirm.isConfirmed !== true) return;

        try {
            const { data: result } = await axios.post('{{ route('admin.invoices.clear-all') }}');

            if (result.status) {
                toastr.success(result.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(result.message, 'Thất Bại');
            }
        } catch (error) {
            toastr.error($catchMessage(error), 'Thất Bại');
        }
    }
  </script>
@endsection
