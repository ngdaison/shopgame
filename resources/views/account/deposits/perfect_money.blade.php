@section('title', __t($pageTitle))
<x-app-layout>
  @php
    $noticeContent = Helper::getNotice('page_deposit_pmoney');
    $cleanNotice = trim(strip_tags(html_entity_decode($noticeContent ?? '')));
  @endphp
  <section>
    <div class="grid grid-cols-1 {{ !empty($cleanNotice) ? 'md:grid-cols-2' : '' }} gap-3">
      <div class="card {{ !empty($cleanNotice) ? '' : 'col-span-1 md:col-span-2 max-w-xl mx-auto w-full' }}">
        <div class="card-body flex flex-col p-6">
          <header class="-mx-6 mb-5 flex items-center border-b border-slate-100 px-6 pb-5 dark:border-slate-700">
            <div class="flex-1">
              <div class="card-title text-slate-900 dark:text-white">{{ __t('Nạp Tiền Bằng Perfect Money') }}</div>
            </div>
          </header>
          <div class="card-text h-full space-y-4">
            <div class="flex justify-center mt-5 mb-3">
              <img src="/images/svg/text.svg" style="width: 200px">
            </div>
            <div>
              <form action="{{ $params['API_URL'] }}" method="POST" id="form">
                <input type="hidden" name="SUGGESTED_MEMO" value="<?= $params['SUGGESTED_MEMO'] ?>">
                <input type="hidden" name="PAYMENT_ID" value="<?= $params['PAYMENT_ID'] ?>" />
                <input type="hidden" name="PAYEE_ACCOUNT" value="<?= $params['PAYEE_ACCOUNT'] ?>" />
                <input type="hidden" name="PAYMENT_UNITS" value="<?= $params['PAYMENT_UNITS'] ?>" />
                <input type="hidden" name="PAYEE_NAME" value="<?= $params['PAYEE_NAME'] ?>" />
                <input type="hidden" name="PAYMENT_URL" value="<?= $params['PAYMENT_URL'] ?>" />
                <input type="hidden" name="PAYMENT_URL_METHOD" value="LINK" />
                <input type="hidden" name="NOPAYMENT_URL" value="<?= $params['NOPAYMENT_URL'] ?>" />
                <input type="hidden" name="NOPAYMENT_URL_METHOD" value="LINK" />
                <input type="hidden" name="STATUS_URL" value="<?= $params['STATUS_URL'] ?>" />
                <div class="mb-3">
                  <label for="PAYMENT_AMOUNT" class="form-label" data-key="dp-nhap-so-tien">{{ __t('Nhập Số Tiền: (USD)') }}</label>
                  <input type="number" class="form-control" id="PAYMENT_AMOUNT" name="PAYMENT_AMOUNT" value="{{ old('PAYMENT_AMOUNT', 1) }}" required>
                </div>
                <div class="mb-3 text-center">
                  <button class="btn btn-primary" type="submit"><i class="fas fa-share"></i> <span data-key="dp-thuc-hien-ngay">{{ __t('Thực Hiện Ngay') }}</span></button>
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
        <account-perfect-list></account-perfect-list>
      </div>
    </div>
  </section>
  @push('scripts')
    @vite(['resources/js/modules/account/deposit/index.js'])
    <script>
      $(document).ready(() => {
        $("#form").submit(function() {
          $(this).find(":submit").attr('disabled', 'disabled');
        });
      })
    </script>
  @endpush
</x-app-layout>
