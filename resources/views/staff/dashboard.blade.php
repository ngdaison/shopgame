@extends('staff.layouts.app')
@section('content')
  <style>
    .card-stats h3 {
      color: #9A3B3B;
      font-size: 36px;
    }

    .card-stats h6 {
      color: #9A3B3B;
      font-size: 18px;
    }
  </style>
  <div>
    <h3>Thống Kê Tài Khoản CTV</h3>
    <div class="row">
      @foreach ($stats['users'] as $key => $value)
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-center card-stats">
                <h3>{{ Helper::formatCurrency($value) }}</h3>
                <h5>{{ $key }}</h5>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <h3>Thống Kê Đơn Tài Khoản</h3>
    <div class="row">
      @foreach ($stats['accounts'] as $key => $value)
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-center card-stats">
                <h3>{{ number_format($value) }}</h3>
                <h5>{{ $key }}</h5>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <h3>Thống Kê Đơn Vật Phẩm</h3>
    <div class="row">
      @foreach ($stats['items'] as $key => $value)
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-center card-stats">
                <h3>{{ number_format($value) }}</h3>
                <h5>{{ $key }}</h5>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <h3>Thống Kê Đơn Cày Thuê</h3>
    <div class="row">
      @foreach ($stats['boostings'] as $key => $value)
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-center card-stats">
                <h3>{{ number_format($value) }}</h3>
                <h5>{{ $key }}</h5>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <h3>Thống Kê Doanh Thu</h3>
    <div class="row">
      @foreach ($stats['transactions'] as $key => $value)
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-center card-stats">
                <h3>{{ Helper::formatCurrency($value) }}</h3>
                <h5>{{ $key }}</h5>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card custom-card">
          <div class="card-header justify-content-between">
            <div class="card-title">Yêu cầu rút tiền</div>
          </div>
          <div class="card-body">
            <form action="{{ route('staff.withdraws.store') }}" method="POST" id="form-withdraw">
              <div class="mb-3">
                <label for="amount" class="form-label">Số tiền rút</label>
                <input type="number" class="form-control" id="amount" name="amount" value="10000" required>
              </div>
              @if($banks->count() > 0)
                <div class="mb-3">
                  <label for="saved_bank" class="form-label">Chọn ngân hàng</label>
                  <select id="saved_bank" name="saved_bank_id" class="form-control" onchange="fillSavedBank(this.value)" required style="appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: none;">
                    <option value="">{{ __t('-- Chọn ngân hàng --') }}</option>
                    @foreach($banks as $bank)
                      <option value="{{ $bank->id }}" data-bank="{{ $bank->bank_code }}" data-number="{{ $bank->account_number }}" data-name="{{ $bank->account_name }}">
                        {{ $bank->bank_name }} - {{ $bank->account_number }} - {{ $bank->account_name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                
                {{-- Hidden fields for form submission --}}
                <input type="hidden" name="bank_name" id="bank_name">
                <input type="hidden" name="account_number" id="account_number">
                <input type="hidden" name="account_name" id="account_name">
                
                <script>
                  function fillSavedBank(id) {
                    if (!id) {
                      document.getElementById('bank_name').value = '';
                      document.getElementById('account_number').value = '';
                      document.getElementById('account_name').value = '';
                      return;
                    }
                    const option = document.querySelector(`#saved_bank option[value="${id}"]`);
                    if (option) {
                      document.getElementById('bank_name').value = option.dataset.bank;
                      document.getElementById('account_number').value = option.dataset.number;
                      document.getElementById('account_name').value = option.dataset.name;
                    }
                  }
                </script>
              @else
                <div class="mb-3">
                  <label for="saved_bank" class="form-label">Chọn ngân hàng</label>
                  <select id="saved_bank" class="form-control" disabled style="appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: none;">
                    <option value="">{{ __t('-- Chọn ngân hàng --') }}</option>
                  </select>
                  <div class="mt-2">
                    <a href="/account/profile?tab=banks" class="text-success" style="font-size: 12px; font-weight: bold;">+ Thêm Ngân Hàng Mới</a>
                  </div>
                </div>
              @endif
              <div class="mb-4">
                <label for="user_note" class="form-label">{{ __t('Ghi Chú') }}</label>
                <textarea name="user_note" id="user_note" class="form-control" rows="3" placeholder="{{ __t('Nhập ghi chú cho admin nếu có') }}">{{ old('user_note') }}</textarea>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary w-full large w-100" type="submit">{{ __t('Rút Tiền Ngay') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card custom-card">
          <div class="card-header justify-content-between">
            <div class="card-title">Lịch sử yêu cầu</div>
          </div>
          <div class="card-body">
            <div class="table-responsive" style="padding: 5px">
              <table class="display table table-bordered table-stripped text-nowrap datatable" id="basic-1">
                <thead>
                  <tr>
                    <td>#</td>
                    <td>Số tiền</td>
                    <td>Ngân hàng</td>
                    <td>Số tài khoản</td>
                    <td>Trạng thái</td>
                    <td>Thời gian</td>
                    <td>Ghi chú</td>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($withdraws as $value)
                    <tr>
                      <td>{{ $value->id }}</td>
                      <td>{{ Helper::formatCurrency($value->amount) }}</td>
                      <td>
                        @php $p = $value->payment_info ?? [] @endphp
                        @if(isset($p['method']) && $p['method'] === 'wallet')
                          Ví Tài Khoản
                        @else
                          {{ $p['bank_name'] ?? '' }}
                        @endif
                      </td>
                      <td>
                        @if(isset($p['method']) && $p['method'] === 'wallet')
                          -
                        @else
                          {{ $p['account_name'] ?? '' }}
                        @endif
                      </td>
                      <td>
                        @if(in_array($value->status, ['Cancelled', 'Declined']))
                          <span class="fw-bold" style="color: #B31312">Đã bị hủy</span>
                        @else
                          {!! Helper::formatStatus($value->status) !!}
                        @endif
                      </td>
                      <td>{{ $value->created_at }}</td>
                      <td>{{ $value->user_note }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div class="card custom-card">
          <div class="card-header justify-content-between">
            <div class="card-title">Danh sách giao dịch</div>
          </div>
          <div class="card-body">
            <div class="table-responsive theme-scrollbar" style="padding: 10px">
              <table class="display table table-bordered table-stripped text-nowrap datatable" id="basic-1">
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
                  </tr>
                </thead>
                <tbody>
                  @foreach ($transactions as $value)
                    <tr>
                      <td>{{ $value->id }}</td>
                      <td>{{ $value->username }}</td>
                      <td>{{ $value->type }}</td>
                      <td>{{ $value->reference }}</td>
                      <td>{{ Helper::formatCurrency($value->balance_before) }}</td>
                      <td>{{ Helper::formatCurrency($value->amount) }}</td>
                      <td>{{ Helper::formatCurrency($value->balance_after) }}</td>
                      <td>{{ $value->description }}</td>
                      <td>
                        @if(in_array($value->status, ['Cancelled', 'Declined']))
                          <span class="fw-bold" style="color: #B31312">Đã bị hủy</span>
                        @else
                          {!! Helper::formatStatus($value->status) !!}
                        @endif
                      </td>
                      <td>{{ $value->created_at }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    $("#form-withdraw").submit(async e => {
      e.preventDefault();

      const action = $(e.target).attr('action'),
        button = $(e.target).find('button[type="submit"]')
      payload = $formDataToPayload(new FormData(e.target));

      const userCollaBalance = parseInt({{ auth()->user()->colla_balance ?? 0 }});

      if (parseInt(payload.amount) > userCollaBalance) {
        Swal.fire('Thất Bại', 'Số dư không đủ để rút.', 'error')
        return
      }

      const confirm = await Swal.fire({
        title: 'Xác Nhận',
        html: `Bạn muốn rút <b>${$formatNumber(payload.amount)} VNĐ</b> về <b>ngân hàng</b> đúng không?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác Nhận',
        cancelButtonText: 'Hủy',
      })

      if (!confirm.isConfirmed) return

      $setLoading(button)

      axios.post(action, payload).then(({
        data: result
      }) => {
        Swal.fire('Thành Công', result.message, 'success').then(() => location.reload())
      }).catch(e => {
        Swal.fire('Thất Bại', $catchMessage(e), 'error')
      }).finally(() => {
        $removeLoading(button)
      })
    })
  </script>
@endsection
