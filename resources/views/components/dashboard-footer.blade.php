<style>
    /* FIX LỖI FLASH MODAL: Ẩn phần tử cho đến khi Alpine.js được tải */
    [x-cloak] {
        display: none !important;
    }
    
    /* Class .cursor-pointer theo yêu cầu (có thể đã có trong file CSS) */
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush
<div x-data="{ depositModal: false }">


    
    @php
        $shop_info = Helper::getConfig('shop_info') ?? [];
        $contact_info = Helper::getConfig('contact_info') ?? [];
    @endphp
    
    <footer id="footer" class="mb-[60px] md:mb-0 static">
        <div class="py-3 px-3" style="background: #1b1a1a">
            <div class="relative mx-auto mt-2 grid grid-cols-1 md:grid-cols-4 gap-4 w-full max-w-6xl text-white">
                
                <div class="col-span-1 flex flex-col items-start">
                    <a href="{{ route('home') }}">
<img src="{{ setting('logo_dark') ?? '/uploads/01-02-2025/7490de41-d150-4946-a186-55bfec2e30bc.png' }}" alt="{{ setting('title') ?? 'SHYUNRB.COM' }}" class="mb-2 max-w-[170px] h-10 object-contain">
                    </a>
                    <div class="flex flex-col items-start gap-1 mt-2">
                        <a href="{{ route('pages.privacy-policy') }}" class="text-gray-400 hover:text-white text-sm">
                            <i class="fa-solid fa-angle-right me-1"></i> Privacy Policy
                        </a>
                        <a href="{{ route('pages.terms-of-service') }}" class="text-gray-400 hover:text-white text-sm">
                            <i class="fa-solid fa-angle-right me-1"></i> Terms of Service
                        </a>
                    </div>
                </div>

                <div class="col-span-1 text-left">
                    <h6 class="mb-1 text-white uppercase">GIỚI THIỆU</h6>
                    <span class="text-sm">{!! Helper::branding('footer_text_1') !!}</span>
                </div>

                <div class="col-span-1 text-left">
                    <h6 class="mb-1 text-white uppercase">THÔNG TIN CHUNG</h6>
                    <span class="text-sm">{!! Helper::branding('footer_text_2') !!}</span>
                </div>

                <div class="col-span-1">
                    <h6 class="mb-6 text-white text-center">LIÊN HỆ HỖ TRỢ</h6>
                    <div class="grid grid-cols-1 gap-2 text-center">
                        @isset($contact_info['facebook'])
                            <a href="{{ $contact_info['facebook'] ?? '#!' }}" target="_blank" class="btn btn-sm btn-outline-secondary !text-white">
                                <i class="fa-brands fa-square-facebook me-1"></i> Facebook
                            </a>
                        @endisset
                        @isset($contact_info['discord'])
                            <a href="{{ $contact_info['discord'] ?? '#!' }}" target="_blank" class="btn btn-sm btn-outline-secondary !text-white">
                                <i class="fa-brands fa-discord me-1"></i> Discord
                            </a>
                        @endisset
                        @isset($contact_info['telegram'])
                            <a href="{{ $contact_info['telegram'] ?? '#!' }}" target="_blank" class="btn btn-sm btn-outline-secondary !text-white">
                                <i class="fa-brands fa-telegram me-1"></i> Telegram
                            </a>
                        @endisset
                        @isset($contact_info['phone_no'])
                            <a href="tel:{{ $contact_info['phone_no'] ?? '+84123456789' }}" class="btn btn-sm btn-outline-secondary !text-white">
                                <i class="fa-solid fa-phone me-1"></i> {{ $contact_info['phone_no'] ?? '+84123456789' }}
                            </a>
                        @endisset
                        @isset($contact_info['email'])
                            <a href="mailto:{{ $contact_info['email'] ?? 'admin@webmaster.com' }}" class="btn btn-sm btn-outline-secondary !text-white">
                                <i class="fa-solid fa-inbox me-1"></i> {{ $contact_info['email'] ?? 'admin@webmaster.com' }}
                            </a>
                        @endisset
                        @isset($contact_info['instagram'])
                            <a href="{{ $contact_info['instagram'] ?? '#!' }}" target="_blank" class="btn btn-sm btn-outline-secondary !text-white">
                                <i class="fa-brands fa-instagram me-1"></i> Instagram
                            </a>
                        @endisset
                        @isset($contact_info['twitter'])
                            <a href="{{ $contact_info['twitter'] ?? '#!' }}" target="_blank" class="btn btn-sm btn-outline-secondary !text-white">
                                <i class="fa-brands fa-twitter me-1"></i> Twitter
                            </a>
                        @endisset
                    </div>
                </div>
            </div>
        </div>
        <div class="site-footer bg-[#151212] px-6 py-3 text-slate-500 ltr:ml-[248px] rtl:mr-[248px] dark:bg-slate-800 dark:text-slate-300 hidden md:block margin-0">
            <div class="flex justify-center font-medium text-white">
                <p>
                    <a href="https://kiyovn.com" target="_blank" class="text-white">Kiyovn.com</a>, All Rights Reserved
                </p>
            </div>
        </div>
    </footer>
    <div
        class="custom-dropshadow footer-bg bothrefm-0 fixed bottom-0 left-0 z-[990] flex w-full items-center justify-between bg-white bg-no-repeat px-4 py-[12px] backdrop-blur-[40px] backdrop-filter dark:bg-slate-700 md:hidden sm:mt-5">
        
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center flex-1">
            <span class="relative mb-1 flex cursor-pointer flex-col items-center justify-center rounded-full text-[20px] text-slate-900 dark:text-white">
                <i class="fa-solid fa-house"></i>
            </span>
            <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                {{ __t('Trang Chủ') }}
            </span>
        </a>

        @if(Auth::check())
        <a href="{{ route('account.transactions.index') }}" class="flex flex-col items-center justify-center flex-1">
            <span class="relative mb-1 flex cursor-pointer flex-col items-center justify-center rounded-full text-[20px] text-slate-900 dark:text-white">
                <i class="fa-solid fa-wallet"></i>
            </span>
            <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                {{ __t('') }} <span class="text-red-600">{{ Helper::formatCurrency(Auth::user()->balance) }}</span>
            </span>
        </a>
        @endif

        @if (Auth::check())
            <a href="{{ route('account.deposits.index') }}" class="flex flex-col items-center justify-center flex-1">
                <span class="relative mb-1 flex cursor-pointer flex-col items-center justify-center rounded-full text-[20px] text-slate-900 dark:text-white">
                    <i class="fa-solid fa-credit-card"></i>
                </span>
                <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                    {{ __t('Nạp Tiền') }}
                </span>
            </a>
        @else
            <a href="{{ route('account.deposits.index') }}" class="flex flex-col items-center justify-center flex-1">
                <span class="relative mb-1 flex cursor-pointer flex-col items-center justify-center rounded-full text-[20px] text-slate-900 dark:text-white">
                    <i class="fa-solid fa-credit-card"></i>
                </span>
                <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                    {{ __t('Nạp Tiền') }}
                </span>
            </a>
        @endif

        @if (Auth::check())
        <a href="{{ route('account.profile.index') }}" class="flex flex-col items-center justify-center flex-1">
            <span class="relative mb-1 flex cursor-pointer flex-col items-center justify-center rounded-full text-[20px] text-slate-900 dark:text-white">
                <i class="fa-solid fa-user"></i>
            </span>
            <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                {{ __t('Thông Tin') }}
            </span>
        </a>
        @else
        <a href="{{ route('login') }}" class="flex flex-col items-center justify-center flex-1">
            <span class="relative mb-1 flex cursor-pointer flex-col items-center justify-center rounded-full text-[20px] text-slate-900 dark:text-white">
                <i class="fa-solid fa-right-to-bracket"></i>
            </span>
            <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                {{ __t('Đăng Nhập') }}
            </span>
        </a>
        @endif

    </div>
    @php
      $deposit_port = Helper::getConfig('deposit_port') ?? [];
    @endphp
    <div x-show="depositModal"
        x-cloak
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-[2000]"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="depositModal = false" class="bg-white rounded-xl w-full max-w-md shadow-lg p-5 mx-4">
            <h2 class="text-xl font-semibold mb-4 text-center">Chọn phương thức nạp</h2>
            <div class="grid grid-cols-2 gap-4">
                @if ($deposit_port['bank'] ?? 0)
                <a href="{{ route('account.deposits.banking') }}" class="border p-3 rounded-lg flex flex-col items-center hover:bg-gray-100">
                    <i class="fas fa-university text-2xl mb-1 text-primary text-blue-500"></i>
                    <span>Ngân Hàng</span>
                </a>
                @endif
                @if ($deposit_port['cards'] ?? 0)
                <a href="{{ route('account.deposits.cards') }}" class="border p-3 rounded-lg flex flex-col items-center hover:bg-gray-100">
                    <i class="fas fa-credit-card text-2xl mb-1 text-primary text-red-500"></i>
                    <span>Thẻ cào</span>
                </a>
                @endif
                @if ($deposit_port['paypal'] ?? 0)
                <a href="{{ route('account.deposits.paypal') }}" class="border p-3 rounded-lg flex flex-col items-center hover:bg-gray-100">
                    <i class="fab fa-paypal text-2xl mb-1 text-primary text-green-500"></i>
                    <span>Cổng Paypal</span>
                </a>
                @endif
                @if ($deposit_port['crypto'] ?? 0)
                <a href="{{ route('account.deposits.crypto') }}" class="border p-3 rounded-lg flex flex-col items-center hover:bg-gray-100">
                    <i class="fab fa-bitcoin text-2xl mb-1 text-primary text-purple-500"></i>
                    <span>Tiền Mã Hoá</span>
                </a>
                @endif
                @if ($deposit_port['perfect_money'] ?? 0)
                <a href="{{ route('account.deposits.perfect-money') }}" class="border p-3 rounded-lg flex flex-col items-center hover:bg-gray-100">
                    <i class="fas fa-wallet text-2xl mb-1 text-primary text-purple-500"></i>
                    <span>Perfect Money</span>
                </a>
                @endif
            </div>
            <button @click="depositModal = false"
                class="mt-5 w-full py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium">
                Đóng
            </button>
        </div>
    </div>
    </div>
