@section('title', __t($pageTitle))
<x-app-layout>
  @php
    $noticeContent = Helper::getNotice('page_deposit_crypto');
    $cleanNotice = trim(strip_tags(html_entity_decode($noticeContent ?? '')));
  @endphp
  <div class="grid grid-cols-1 {{ !empty($cleanNotice) ? 'md:grid-cols-2' : '' }} gap-3">
    <div class="card {{ !empty($cleanNotice) ? '' : 'col-span-1 md:col-span-2 max-w-xl mx-auto w-full' }}">
      <div class="card-body flex flex-col p-6">
        <header class="-mx-6 mb-5 flex items-center border-b border-slate-100 px-6 pb-5 dark:border-slate-700">
          <div class="flex-1">
            <div class="card-title text-slate-900 dark:text-white">{{ __t('Nạp Tiền Bằng Crypto') }}</div>
          </div>
        </header>
        <div class="card-text h-full space-y-4">
          <div class="flex justify-center">
            <img src="/images/svg/crypto.svg" style="width: 200px">
          </div>
          <div>
            <form action="/api/accounts/invoices" method="POST" id="form">
              <input type="hidden" name="channel" id="channel" value="fpayment">
              <div class="mb-3">
                <label for="amount" class="form-label" data-key="dp-nhap-so-tien">{{ __t('Nhập số tiền') }}: (USD)</label>
                <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount', 1) }}" required>
              </div>
              <div class="mb-3 text-center">
                <button class="btn btn-primary w-100" type="submit"><i class="fas fa-share"></i> <span data-key="dp-thuc-hien-ngay">{{ __t('Thanh Toán Ngay') }}</span></button>
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
      <account-crypto-list></account-crypto-list>
    </div>
  </div>

  @push('scripts')
    @vite(['resources/js/modules/account/deposit/index.js'])
    <script>
      $(document).ready(() => {
        $("#form").submit(async (e) => {
          e.preventDefault();

          const amount = $("#amount").val(),
            channel = $("#channel").val(),
            button = $(e.target).find("button"),
            action = $(e.target).attr("action");

          if (amount < 1) {
            return $swal("error", "{{ __t('Số tiền nạp tối thiểu là 1 USD') }}");
          }

          $setLoading(button);

          try {
            const {
              data: result
            } = await axios.post(action, {
              amount,
              channel
            });

            $swal("success", result.message).then(() => {
              location.href = result.data.payment_url
            });
          } catch (error) {
            $swal("error", $catchMessage(error));
          } finally {
            $removeLoading(button);
          }
        })
      })
    </script>
  @endpush
</x-app-layout>
