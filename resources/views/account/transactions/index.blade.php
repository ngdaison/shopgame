@section('title', __t($pageTitle))
<x-app-layout>
  <section id="vue-app" class="space-y-6">

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4">
      <div class="relative flex items-center rounded-[6px] bg-cover bg-center bg-no-repeat px-5 py-8" style="background-image: url(/images/all-img/widget-bg-1.png)">
        <div class="flex-1">
          <div class="max-w-[180px]">
            <h4 class="mb-2 text-2xl font-medium text-[#27374D]">
              <span class="block text-sm">{{ __t('Số Tiền Hiện Có,') }}</span>
              <span class="block">{{ $stats['balance'] }}</span>
            </h4>
          </div>
        </div>
        <div class="flex-none">
          <a href="{{ route('account.deposits.index') }}" class="btn-light btn-sm btn bg-white">{{ __t('NẠP THÊM') }}</a>
        </div>
      </div>
      <!--  end Single -->
      <div class="relative flex items-center rounded-[6px] bg-cover bg-center bg-no-repeat px-5 py-8" style="background-image: url(/images/all-img/widget-bg-2.png)">
        <div class="flex-1">
          <div class="max-w-[180px]">
            <h4 class="mb-2 text-2xl font-medium text-white">
              <span class="block text-sm">
                {{ __t('Số Tiền Đã Nạp,') }}
              </span>
              <span class="block">{{ $stats['total_deposit'] }}</span>
            </h4>
          </div>
        </div>
        <div class="flex-none">
          <a href="{{ route('account.deposits.index') }}" class="btn-light btn-sm btn bg-white">{{ __t('NẠP THÊM') }}</a>
        </div>
      </div>
      <!--  end Single -->
      <div class="relative flex items-center rounded-[6px] bg-cover bg-center bg-no-repeat px-5 py-8" style="background-image: url(/images/all-img/widget-bg-3.png)">
        <div class="flex-1">
          <div class="max-w-[180px]">
            <h4 class="mb-2 text-2xl font-medium text-white">
              <span class="block text-sm">
                {{ __t('Số Tiền Đã Tiêu,') }}
              </span>
              <span class="block">{{ $stats['total_spent'] }}</span>
            </h4>
          </div>
        </div>
        <div class="flex-none">
          <a href="{{ route('account.deposits.index') }}" class="btn-light btn-sm btn bg-white">{{ __t('NẠP THÊM') }}</a>
        </div>
      </div>
      <!--  end Single -->
      <div class="relative flex items-center rounded-[6px] bg-cover bg-center bg-no-repeat px-5 py-8" style="background-image: url(/images/all-img/widget-bg-4.png)">
        <div class="flex-1">
          <div class="max-w-[180px]">
            <h4 class="mb-2 text-2xl font-medium text-white">
              <span class="block text-sm">
                {{ __t('Tổng Nạp Tháng') }} {{ date('m/Y') }},
              </span>
              <span class="block">{{ $stats['deposit_in_month'] }}</span>
            </h4>
          </div>
        </div>
        <div class="flex-none">
          <a href="{{ route('account.deposits.index') }}" class="btn-light btn-sm btn bg-white">{{ __t('NẠP THÊM') }}</a>
        </div>
      </div>
    </div>
    <hr class="mb-2 mt-2 h-[10px]" />

    <div class="card">
      <header class="card-header noborder">
        <h4 class="card-title">{{ __t('Lịch sử giao dịch') }}</h4>
      </header>
      <div class="card-body px-6 pb-6" id="vue-transaction-history">
        <account-transaction />
      </div>
    </div>
  </section>

  @push('scripts')
    @vite(['resources/js/modules/account/profile/index.js'])
  @endpush
</x-app-layout>
