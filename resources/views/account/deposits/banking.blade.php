@section('title', __t($pageTitle))
<x-app-layout>
  <section id="vue-app" class="space-y-6">
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-3">
    @php
      $noticeContent = Helper::getNotice('page_deposit_bank');
      $cleanNotice = trim(strip_tags(html_entity_decode($noticeContent ?? '')));
    @endphp
    @if (!empty($cleanNotice))
      <div class="col-span-1 md:col-span-3 card">
        <div class="card-body flex flex-col p-6">
          <div class="card-text h-full space-y-4">
            {!! $noticeContent !!}
          </div>
        </div>
      </div>
    @endif

      @foreach ($banks as $bank)
        <div>
          <div class="rounded-b-none border border-none bg-transparent p-4">
            <img src="{{ Helper::getValidImage($bank->image) }}" alt="{{ $bank->bank_name ?? $bank->name }}" class="mx-auto w-[90px] cursor-pointer object-cover">
          </div>

          <div class="space-y-2 rounded-lg bg-[#002B5B] p-4 text-[18px] font-bold text-white">
            <div class="flex flex-wrap justify-between">
              <span>{{ ucfirst($bank->bank_name ?? $bank->name) }}:</span>
              <span class="copy cursor-pointer" data-clipboard-text="{{ $bank->number }}">{{ $bank->number }}</span>
            </div>

            <div class="flex flex-wrap justify-between">
              <span>{{ __t('Chủ TK') }}</span>
              <span>{{ $bank->owner }}</span>
            </div>

            <div class="flex flex-wrap justify-between">
              <span>{{ __t('Nội Dung') }}</span>
              <span class="copy cursor-pointer" data-clipboard-text="{{ $deposit_prefix }}">{{ $deposit_prefix }}</span>
            </div>

            <div class="text-center">
              {{ __t('Nhập đúng nội dung tiền tự động cộng trong vài phút') }}
            </div>

            <div class="pt-2">
              @if (str_contains(strtolower($bank->bank_name ?? $bank->name), 'momo'))
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=2|99|{{ $bank->number }}|||0|0|0|{{ $deposit_prefix }}|transfer_myqr"
                     alt="QR Momo"
                     class="mx-auto w-full rounded-lg object-fill">
              @elseif (str_contains(strtolower($bank->bank_name ?? $bank->name), 'tsr'))
                <img src="{{ Helper::getValidImage('/images/qrtsr.png') }}"
                class="mx-auto w-full rounded-lg object-fill">
              @else
                <img src="https://api.vietqr.io/{{ strtolower($bank->bank_code ?? $bank->bank_name ?? $bank->name) }}/{{ $bank->number }}/0/{{ $deposit_prefix }}/qronly2.jpg?accountName={{ urlencode($bank->owner) }}&bankName={{ urlencode($bank->bank_name ?? $bank->name) }}"
                     alt="QR VietQR"
                     class="mx-auto w-full rounded-lg object-fill">
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="card">
        <header class="card-header noborder">
            <h4 class="card-title">{{ __t('Lịch sử nạp Bank') }}</h4>
        </header>
        <div class="card-body px-6 pb-6" id="vue-transaction-history">
            <account-bank-list :filters="{ description_like: 'ATM' }" />
        </div>
    </div>
  </section>

  @push('scripts')
    @vite(['resources/js/modules/account/profile/index.js'])
    <script type="module">
    </script>
  @endpush
</x-app-layout>
