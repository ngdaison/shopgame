@section('title', __t('Nạp Tiền Tài Khoản'))
<x-app-layout>
    <section class="space-y-6">
        <div class="mx-auto max-w-5xl">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
                
                {{-- Ngân hàng --}}
                @if ($depositPort['bank'] ?? false)
                <a href="{{ route('account.deposits.banking') }}" class="block text-center group">
                    <div class="rounded-lg border bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-primary-500">
                        <div class="mb-4 text-4xl text-gray-600 group-hover:text-primary-500">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 group-hover:text-primary-500">
                            {{ __t('Ngân Hàng') }}
                        </h3>
                    </div>
                </a>
                @endif

                {{-- Thẻ cào --}}
                @if ($depositPort['cards'] ?? false)
                <a href="{{ route('account.deposits.cards') }}" class="block text-center group">
                    <div class="rounded-lg border bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-primary-500">
                        <div class="mb-4 text-4xl text-gray-600 group-hover:text-primary-500">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 group-hover:text-primary-500">
                            {{ __t('Thẻ cào') }}
                        </h3>
                    </div>
                </a>
                @endif

                {{-- Cổng Paypal --}}
                @if ($depositPort['paypal'] ?? false)
                <a href="{{ route('account.deposits.paypal') }}" class="block text-center group">
                    <div class="rounded-lg border bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-primary-500">
                        <div class="mb-4 text-4xl text-gray-600 group-hover:text-primary-500">
                            <i class="fa-brands fa-paypal"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 group-hover:text-primary-500">
                            {{ __t('Cổng Paypal') }}
                        </h3>
                    </div>
                </a>
                @endif

                {{-- Tiền Mã Hoá --}}
                @if ($depositPort['crypto'] ?? false)
                <a href="{{ route('account.deposits.crypto') }}" class="block text-center group">
                    <div class="rounded-lg border bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-primary-500">
                        <div class="mb-4 text-4xl text-gray-600 group-hover:text-primary-500">
                            <i class="fa-brands fa-bitcoin"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 group-hover:text-primary-500">
                            {{ __t('Tiền Mã Hoá') }}
                        </h3>
                    </div>
                </a>
                @endif

                 {{-- Perfect Money --}}
                 @if ($depositPort['perfect_money'] ?? false)
                 <a href="{{ route('account.deposits.perfect-money') }}" class="block text-center group">
                    <div class="rounded-lg border bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-primary-500">
                        <div class="mb-4 text-4xl text-gray-600 group-hover:text-primary-500">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 group-hover:text-primary-500">
                            {{ __t('Perfect Money') }}
                        </h3>
                    </div>
                </a>
                @endif

            </div>
        </div>
    </section>
</x-app-layout>
