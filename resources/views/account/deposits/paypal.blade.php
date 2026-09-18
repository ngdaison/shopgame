@section('title', __t($pageTitle))
<x-app-layout>
  @php
    $noticeContent = Helper::getNotice('page_deposit_paypal');
    $cleanNotice = trim(strip_tags(html_entity_decode($noticeContent ?? '')));
  @endphp
  <div class="grid grid-cols-1 {{ !empty($cleanNotice) ? 'md:grid-cols-2' : '' }} gap-3">
    <div class="card {{ !empty($cleanNotice) ? '' : 'col-span-1 md:col-span-2 max-w-xl mx-auto w-full' }}">
      <div class="card-body flex flex-col p-6">
        <header class="-mx-6 mb-5 flex items-center border-b border-slate-100 px-6 pb-5 dark:border-slate-700">
          <div class="flex-1">
            <div class="card-title text-slate-900 dark:text-white">{{ __t('Nạp Tiền Bằng Paypal') }}</div>
          </div>
        </header>
        <div class="card-text h-full space-y-4">
          <div class="flex justify-center">
            <img src="/images/svg/paypal.svg" style="width: 200px">
          </div>
          <div>
            <form action="/api/accounts/invoices" method="POST" id="form">
              <input type="hidden" name="channel" id="channel" value="fpayment">
              <div class="mb-3">
                <label for="amount" class="form-label" data-key="dp-nhap-so-tien">{{ __t('Nhập số tiền') }}: (USD)</label>
                <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount', 1) }}" required>
              </div>
              <div class="mb-3 text-center">
                <script src="https://www.paypal.com/sdk/js?client-id={{ $config['client_id'] ?? '' }}&currency=USD"></script>

                <div id="paypal-button-container"></div>
                {{-- <button class="btn btn-primary w-100" type="submit"><i class="fas fa-share"></i> <span data-key="dp-thuc-hien-ngay">{{ __t('Thanh Toán Ngay') }}</span></button> --}}
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    @if (!empty($cleanNotice))
    <div class="card">
      <div class="card-body flex flex-col p-6">
        <header class="-mx-6 mb-5 flex items-center border-b border-slate-100 px-6 pb-5 dark:border-slate-700">
          <div class="flex-1">
            <div class="card-title text-slate-900 dark:text-white">{{ __t('Lưu Ý Khi Nạp') }}</div>
          </div>
        </header>
        <div class="card-text h-full space-y-4">
          {!! $noticeContent !!}
        </div>
      </div>
    </div>
    @endif
    <div id="app" class="col-span-1 md:col-span-2">
      <account-paypal-list></account-paypal-list>
    </div>
  </div>
    @push('scripts')
      @vite(['resources/js/modules/account/deposit/index.js'])
      <script>
        (function($) {
          paypal.Buttons({

            // Sets up the transaction when a payment button is clicked
            createOrder: function(data, actions) {
              return actions.order.create({
                purchase_units: [{
                  amount: {
                    value: $('#amount')
                      .val() // Can reference variables or functions. Example: `value: document.getElementById('...').value`
                  }
                }]
              });
            },

            // Finalize the transaction after payer approval
            onApprove: function(data, actions) {
              return actions.order.capture().then(function(orderData) {
                axios.post('/api/deposit/paypal-confirm', orderData).then(({
                  data: result
                }) => {

                  Swal.fire('Thành công', result.message, 'success').then(() => {
                    window.location.reload();
                  })
                }).catch(error => {
                  Swal.fire('Thất bại', $catchMessage(error), 'error')
                })
              });
            }
          }).render('#paypal-button-container');
        })(jQuery)
      </script>
    @endpush
</x-app-layout>
