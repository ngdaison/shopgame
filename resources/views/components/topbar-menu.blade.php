<div class="main-menu">
  <ul class="whitespace-nowrap flex items-center gap-16">
    <!-- Hoạt Động -->
    <li class="menu-item-has-children">
      <a href="javascript:void()" class="transition-all hover:scale-[105%]">
        <div class="flex flex-1 items-center space-x-[6px] rtl:space-x-reverse">
          <span class="icon-box">
            <iconify-icon icon="icon-park-outline:dashboard-car"></iconify-icon>
          </span>
          <div class="text-box"> {{ __t('Hoạt Động') }}</div>
        </div>
        <div class="relative top-1 flex-none text-sm leading-[1] ltr:ml-3 rtl:mr-3">
          <iconify-icon icon="heroicons-outline:chevron-down"></iconify-icon>
        </div>
      </a>
      <ul class="sub-menu">
        <li>
          <a href="{{ route('account.transactions.index') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="mdi:cash-multiple" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Lịch sử Nạp Tiền') }}</span>
            </div>
          </a>
        </li>
        <li>
          <a href="{{ route('account.orders.accounts') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="mdi:account-cash" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Tài Khoản Đã Mua') }}</span>
            </div>
          </a>
        </li>
        <li>
          <a href="{{ route('account.orders.items') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="tabler:lego" class="text-inherit text-base mr-1 align-middle"></iconify-icon>
              <span class="leading-[1]">{{ __t('Lịch Sử Mua Vật Phẩm') }}</span>
            </div>
          </a>
        </li>
        <li>
          <a href="{{ route('account.orders.boosting') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="arcticons:boost" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Lịch Sử Cày Thuê') }}</span>
            </div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Dịch Vụ Khác -->
    <li class="menu-item-has-children">
      <a href="javascript:void()" class="transition-all hover:scale-[105%]">
        <div class="flex flex-1 items-center space-x-[6px] rtl:space-x-reverse">
          <span class="icon-box">
            <iconify-icon icon="material-symbols:other-admission-outline-rounded"></iconify-icon>
          </span>
          <div class="text-box"> {{ __t('Dịch Vụ Khác') }}</div>
        </div>
        <div class="relative top-1 flex-none text-sm leading-[1] ltr:ml-3 rtl:mr-3">
          <iconify-icon icon="heroicons-outline:chevron-down"></iconify-icon>
        </div>
      </a>
      <ul class="sub-menu">
        <li>
          <a href="{{ route('account.tickets.index') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="heroicons-outline:ticket" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Tickets / Hỗ Trợ') }}</span>
            </div>
          </a>
        </li>
        <li>
          <a href="{{ route('pages.affiliates') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="carbon:share-knowledge" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Tiếp Thị Liên Kết') }}</span>
            </div>
          </a>
        </li>
        <li>
          <a href="{{ route('account.withdraws-v2.index') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="tabler:lego" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Rút Thưởng') }}</span>
            </div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Nạp Tiền -->
    <li class="menu-item-has-children">
      <a href="javascript:void()" class="transition-all hover:scale-[105%]">
        <div class="flex flex-1 items-center space-x-[6px] rtl:space-x-reverse">
          <span class="icon-box">
            <iconify-icon icon="gg:credit-card"></iconify-icon>
          </span>
          <div class="text-box"> {{ __t('Nạp Tiền') }}</div>
        </div>
        <div class="relative top-1 flex-none text-sm leading-[1] ltr:ml-3 rtl:mr-3">
          <iconify-icon icon="heroicons-outline:chevron-down"></iconify-icon>
        </div>
      </a>
      <ul class="sub-menu">
        @php $deposit_port = Helper::getConfig('deposit_port'); @endphp
        @if ($deposit_port['cards'] ?? 0)
          <li>
            <a href="{{ route('account.deposits.cards') }}" class="transition-all hover:scale-[105%]">
              <div class="flex items-start space-x-2 rtl:space-x-reverse">
                <iconify-icon icon="ion:card-outline" class="text-base leading-[1]"></iconify-icon>
                <span class="leading-[1]">{{ __t('Thẻ Cào') }}</span>
              </div>
            </a>
          </li>
        @endif
        @if ($deposit_port['bank'] ?? 0)
          <li>
            <a href="{{ route('account.deposits.banking') }}" class="transition-all hover:scale-[105%]">
              <div class="flex items-start space-x-2 rtl:space-x-reverse">
                <iconify-icon icon="clarity:bank-line" class="text-base leading-[1]"></iconify-icon>
                <span class="leading-[1]">{{ __t('Ngân Hàng') }}</span>
              </div>
            </a>
          </li>
        @endif
        @if ($deposit_port['paypal'] ?? 0)
          <li>
            <a href="{{ route('account.deposits.paypal') }}" class="transition-all hover:scale-[105%]">
              <div class="flex items-start space-x-2 rtl:space-x-reverse">
                <iconify-icon icon="simple-line-icons:paypal" class="text-base leading-[1]"></iconify-icon>
                <span class="leading-[1]">{{ __t('Cổng Paypal') }}</span>
              </div>
            </a>
          </li>
        @endif
        @if ($deposit_port['crypto'] ?? 0)
          <li>
            <a href="{{ route('account.deposits.crypto') }}" class="transition-all hover:scale-[105%]">
              <div class="flex items-start space-x-2 rtl:space-x-reverse">
                <iconify-icon icon="arcticons:cryptomator" class="text-base leading-[1]"></iconify-icon>
                <span class="leading-[1]">{{ __t('Tiền Mã Hoá') }}</span>
              </div>
            </a>
          </li>
        @endif
        @if ($deposit_port['perfect_money'] ?? 0)
          <li>
            <a href="{{ route('account.deposits.perfect-money') }}" class="transition-all hover:scale-[105%]">
              <div class="flex items-start space-x-2 rtl:space-x-reverse">
                <iconify-icon icon="arcticons:perfect-ear" class="text-base leading-[1]"></iconify-icon>
                <span class="leading-[1]">{{ __t('Perfect Money') }}</span>
              </div>
            </a>
          </li>
        @endif
      </ul>
    </li>

    <!-- Thông Tin -->
    <li class="menu-item-has-children">
      <a href="javascript:void()" class="transition-all hover:scale-[105%]">
        <div class="flex flex-1 items-center space-x-[6px] rtl:space-x-reverse">
          <span class="icon-box">
            <iconify-icon icon="tabler:news"></iconify-icon>
          </span>
          <div class="text-box"> {{ __t('Thông Tin') }}</div>
        </div>
        <div class="relative top-1 flex-none text-sm leading-[1] ltr:ml-3 rtl:mr-3">
          <iconify-icon icon="heroicons-outline:chevron-down"></iconify-icon>
        </div>
      </a>
      <ul class="sub-menu">

        <li>
          <a href="{{ route('articles.index') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="wpf:news" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Tin Tức Mới') }}</span>
            </div>
          </a>
        </li>
        <li>
          <a href="{{ route('pages.privacy-policy') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="mdi:shield-account" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Chính sách bảo mật') }}</span>
            </div>
          </a>
        </li>
        <li>
          <a href="{{ route('pages.terms-of-service') }}" class="transition-all hover:scale-[105%]">
            <div class="flex items-start space-x-2 rtl:space-x-reverse">
              <iconify-icon icon="mdi:file-document-outline" class="text-base leading-[1]"></iconify-icon>
              <span class="leading-[1]">{{ __t('Điều khoản sử dụng') }}</span>
            </div>
          </a>
        </li>
      </ul>
    </li>
  </ul>
</div>
<!-- end top menu -->
