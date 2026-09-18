@section('title', __t($pageTitle))
<x-app-layout>
  <section class="mb-3">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3">
      <div class="relative flex items-center rounded-[6px] bg-cover bg-center bg-no-repeat px-5 py-8" style="background-image: url(/images/all-img/widget-bg-6.png)">
        <div class="flex-1">
          <div class="max-w-[180px]">
            <h4 class="mb-2 text-2xl font-medium text-white">
              <span class="block text-sm">{{ __t('Tài Khoản Đã Mua,') }}</span>
              <span class="block">{{ $stats['total'] }} <small>{{ __t('tài khoản') }}</small></span>
            </h4>
          </div>
        </div>
        <div class="flex-none">
          <a href="{{ route('home') }}" class="btn-light btn-sm btn bg-white">{{ __t('MUA THÊM') }}</a>
        </div>
      </div>
      <!--  end Single -->
      <div class="relative flex items-center rounded-[6px] bg-cover bg-center bg-no-repeat px-5 py-8" style="background-image: url(/images/all-img/widget-bg-6.png)">
        <div class="flex-1">
          <div class="max-w-[180px]">
            <h4 class="mb-2 text-2xl font-medium text-white">
              <span class="block text-sm">
                {{ __t('Số Tiền Đã Tiêu,') }}
              </span>
              <span class="block">{{ Helper::formatCurrency($stats['payment']) }}</span>
            </h4>
          </div>
        </div>
        <div class="flex-none">
          <a href="{{ route('home') }}" class="btn-light btn-sm btn bg-white">{{ __t('MUA THÊM') }}</a>
        </div>
      </div>
      <!--  end Single -->
      <div class="relative flex items-center rounded-[6px] bg-cover bg-center bg-no-repeat px-5 py-8" style="background-image: url(/images/all-img/widget-bg-6.png)">
        <div class="flex-1">
          <div class="max-w-[180px]">
            <h4 class="mb-2 text-2xl font-medium text-white">
              <span class="block text-sm">
                {{ __t('Đã Tiêu Trong Tháng,') }}
              </span>
              <span class="block">{{ Helper::formatCurrency($stats['payment_in_month']) }}</span>
            </h4>
          </div>
        </div>
        <div class="flex-none">
          <a href="{{ route('home') }}" class="btn-light btn-sm btn bg-white">{{ __t('MUA THÊM') }}</a>
        </div>
      </div>
    </div>
    <hr class="mb-3 mt-3 h-[10px]" />
  </section>

   <div id="app-account-order-accounts"></div>
   
   @push('scripts')
        @vite(['resources/js/modules/account/order/index.js'])
   @endpush
</x-app-layout>
