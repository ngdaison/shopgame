<!-- BEGIN: Sidebar -->
<div class="sidebar-wrapper group h-screen bg-white shadow-none" style="z-index: 1201 !important; background-color: white !important; box-shadow: none !important;">
  <div id="bodyOverlay" class="fixed top-0 z-[1005] hidden h-screen w-screen bg-slate-900 bg-opacity-50 backdrop-blur-sm">
  </div>
  <div class="logo-segment flex items-center justify-between px-4 py-6 bg-white dark:bg-slate-800 sticky top-0" style="z-index: 1010 !important; background-color: white !important;">
    <x-application-logo />
    <button class="sidebarCloseIcon text-2xl xl:hidden">
      <iconify-icon class="text-slate-900 dark:text-slate-200" icon="line-md:close"></iconify-icon>
    </button>
  </div>
  <div class="sidebar-menus z-[1006] bg-white px-6 py-2 dark:bg-slate-800 min-h-screen shadow-none" id="sidebar_menus" style="background-color: white !important;">
    <ul class="sidebar-menu">
      <li class="sidebar-menu-title">{{ __t('MENU') }}</li>
      <li>
        <a href="{{ route('home') }}" class="navItem {{ request()->routeIs('home') ? 'active' : '' }}">
          <span class="flex items-center">
            <iconify-icon class="nav-icon" icon="material-symbols:dashboard-outline"></iconify-icon>
            <span>{{ __t('Trang Chủ') }}</span>
          </span>
        </a>
      </li>
      @if (!in_array(domain(), ['acctruykich.com']))
        <li class="">
          <a href="javascript:void(0)" class="navItem">
            <span class="flex items-center">
              <iconify-icon class=" nav-icon" icon="humbleicons:cart"></iconify-icon>
              <span>{{ __t('Lịch Sử Hoạt Động') }}</span>
            </span>
            <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
          </a>
          <ul class="sidebar-submenu">
            <li>
              <a href="{{ route('account.orders.accounts') }}" class="{{ request()->routeIs('account.orders.accounts') ? 'active' : '' }}">{{ __t('Tài Khoản Đã Mua') }}</a>
            </li>
            <li>
              <a href="{{ route('account.orders.items') }}" class="{{ request()->routeIs('account.orders.items') ? 'active' : '' }}">{{ __t('Lịch Sử Mua Vật Phẩm') }}</a>
            </li>
            <li>
              <a href="{{ route('account.orders.boosting') }}" class="{{ request()->routeIs('account.orders.boosting') ? 'active' : '' }}">{{ __t('Lịch Sử Cày Thuê') }}</a>
            </li>
            <li>
              <a href="{{ route('account.transactions.index') }}" class="{{ request()->routeIs('account.transactions.index') ? 'active' : '' }}">{{ __t('Lịch sử Nạp Tiền') }}</a>
            </li>
            @if (feature_enabled('bulk-orders'))
            @endif
          </ul>
        </li>
      @else
        <li>
          <a href="{{ route('account.orders.accounts') }}" class="navItem {{ request()->routeIs('account.orders.accounts') ? 'active' : '' }}">
            <span class="flex items-center">
              <iconify-icon class="nav-icon" icon="heroicons-outline:chevron-right"></iconify-icon>
              <span>{{ __t('Lịch Sử Mua Nick') }}</span>
            </span>
          </a>
        </li>
      @endif
      <li class="">
        <a href="javascript:void(0)" class="navItem">
          <span class="flex items-center">
            <iconify-icon class=" nav-icon" icon="tabler:lego"></iconify-icon>
            <span>{{ __t('Đơn Dịch Vụ Khác') }}</span>
          </span>
          <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{ route('account.tickets.index') }}" class="{{ request()->routeIs('account.tickets.index') ? 'active' : '' }}">{{ __t('Hỗ trợ') }}</a>
          </li>
          <li>
            <a href="{{ route('pages.affiliates') }}" class="{{ request()->routeIs('pages.affiliates') ? 'active' : '' }}">{{ __t('Tiếp Thị Liên Kết') }}</a>
          </li>
          <li>
            <a href="{{ route('account.withdraws-v2.index') }}" class="{{ request()->routeIs('account.withdraws-v2.index') ? 'active' : '' }}">{{ __t('Rút Thưởng') }}</a>
          </li>

        </ul>
      </li>

      </li>
      <li class="">
        <a href="javascript:void(0)" class="navItem">
          <span class="flex items-center">
            <iconify-icon class="nav-icon" icon="gg:credit-card"></iconify-icon>
            <span>{{ __t('Nạp Tiền') }}</span>
          </span>
          <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
        </a>
        <ul class="sidebar-submenu">
          @php
            $deposit_port = Helper::getConfig('deposit_port');
          @endphp


          @if ($deposit_port['cards'] ?? 0)
            <li>
              <a href="{{ route('account.deposits.cards') }}" class="{{ request()->routeIs('account.deposits.cards') ? 'active' : '' }}">{{ __t('Thẻ Cào') }}</a>
            </li>
          @endif



          @if ($deposit_port['bank'] ?? 0)
            <li>
              <a href="{{ route('account.deposits.banking') }}" class="{{ request()->routeIs('account.deposits.bank') ? 'active' : '' }}">{{ __t('Ngân Hàng') }}</a>
            </li>
          @endif

          @if ($deposit_port['paypal'] ?? 0)
            <li>
              <a href="{{ route('account.deposits.paypal') }}" class="{{ request()->routeIs('account.deposits.paypal') ? 'active' : '' }}">{{ __t('Cổng Paypal') }}</a>
            </li>
          @endif

          @if ($deposit_port['crypto'] ?? 0)
            <li>
              <a href="{{ route('account.deposits.crypto') }}" class="{{ request()->routeIs('account.deposits.crypto') ? 'active' : '' }}">{{ __t('Tiền Mã Hoá') }}</a>
            </li>
          @endif

          @if ($deposit_port['perfect_money'] ?? 0)
            <li>
              <a href="{{ route('account.deposits.perfect-money') }}" class="{{ request()->routeIs('account.deposits.perfect-money') ? 'active' : '' }}">{{ __t('Perfect Money') }}</a>
            </li>
          @endif
        </ul>
      </li>
      <li class="">
        <a href="javascript:void(0)" class="navItem">
          <span class="flex items-center">
            <iconify-icon class=" nav-icon" icon="tabler:news"></iconify-icon>
            <span>{{ __t('Thông Tin') }}</span>
          </span>
          <iconify-icon class="icon-arrow" icon="heroicons-outline:chevron-right"></iconify-icon>
        </a>
        <ul class="sidebar-submenu">
          <li>
            <a href="{{ route('articles.index') }}" class="{{ request()->routeIs('articles.index') ? 'active' : '' }}">{{ __t('Tin Tức Mới') }}</a>
          </li>

          <li>
            <a href="{{ route('pages.privacy-policy') }}" class="{{ request()->routeIs('pages.privacy-policy') ? 'active' : '' }}">{{ __t('Chính sách bảo mật') }}</a>
          </li>
          <li>
            <a href="{{ route('pages.terms-of-service') }}" class="{{ request()->routeIs('pages.terms-of-service') ? 'active' : '' }}">{{ __t('Điều khoản sử dụng') }}</a>
          </li>
        </ul>
      </li>

      @if (Auth::check())
        @foreach (Helper::getUserRoles(Auth::user()) as $appRole)
          <li>
            <a href="{{ $appRole['route'] }}" class="navItem {{ request()->routeIs($appRole['route_name']) ? 'active' : '' }}">
              <span class="flex items-center">
                <iconify-icon class="nav-icon" icon="{{ $appRole['icon'] }}"></iconify-icon>
                <span>{{ $appRole['sidebar_label'] }}</span>
              </span>
            </a>
          </li>
        @endforeach
      @endif
      </li>
      <li>
        <a href="{{ route('account.profile.index') }}" class="navItem {{ request()->routeIs('account.profile.index') ? 'active' : '' }}">
          <span class="flex items-center">
            <iconify-icon class="nav-icon" icon="mdi:account"></iconify-icon>
            <span>{{ __t('Thông Tin Tài Khoản') }}</span>
          </span>
        </a>
      </li>
      <li>
        <a href="javascript:$logout()" class="navItem">
          <span class="flex items-center">
            <iconify-icon class="nav-icon" icon="line-md:logout"></iconify-icon>
            <span>{{ __t('Đăng Xuất Tài Khoản') }}</span>
          </span>
        </a>
      </li>
    </ul>
  </div>
</div>
<!-- End: Sidebar -->
