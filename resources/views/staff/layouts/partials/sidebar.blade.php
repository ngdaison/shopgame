<aside class="app-sidebar sticky" id="sidebar">

  <!-- Start::main-sidebar-header -->
  <div class="main-sidebar-header">
    <a href="{{ route('staff.dashboard') }}" class="header-logo">
      <img src="/cmsnt/cmsnt_light.png" alt="logo" class="desktop-logo">
      <img src="/_admin/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
      <img src="/cmsnt/cmsnt_dark.png" alt="logo" class="desktop-dark">
      <img src="/_admin/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
      <img src="/cmsnt/cmsnt_light.png" alt="logo" class="desktop-white">
      <img src="/_admin/images/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
    </a>
  </div>
  <!-- End::main-sidebar-header -->

  <!-- Start::main-sidebar -->
  <div class="main-sidebar" id="sidebar-scroll">

    <!-- Start::nav -->
    <nav class="main-menu-container nav nav-pills flex-column sub-open">
      <div class="slide-left" id="slide-left">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
        </svg>
      </div>
      <ul class="main-menu">
        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">Main</span></li>
        <!-- End::slide__category -->

        @if (auth()->user()->hasPermission('staff_dashboard_view'))
        <li class="slide">
          <a href="{{ route('staff.dashboard') }}" class="side-menu__item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
            <i class="bx bx-home side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Bảng Điều Khiển') }}</span>
          </a>
        </li>
        @endif

        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">Staff Menu</span></li>
        <!-- End::slide__category -->

        @if (auth()->user()->hasPermission('staff_orders_items_view'))
          <!-- Start::slide -->
          <li class="slide">
            <a href="{{ route('staff.orders.items.index') }}" class="side-menu__item {{ request()->routeIs('staff.orders.items.index') ? 'active' : '' }}">
              <i class="bx bx-data side-menu__icon"></i>
              <span class="side-menu__label">{{ __t('Đơn hàng vật phẩm') }}</span>
            </a>
          </li>
          <!-- End::slide -->
        @endif
        @if (auth()->user()->hasPermission('staff_orders_boostings_view'))
          <!-- Start::slide -->
          <li class="slide">
            <a href="{{ route('staff.orders.boostings.index') }}" class="side-menu__item {{ request()->routeIs('staff.orders.boostings.index') ? 'active' : '' }}">
              <i class="bx bx-data side-menu__icon"></i>
              <span class="side-menu__label">{{ __t('Đơn hàng cày thuê') }}</span>
            </a>
          </li>
          <!-- End::slide -->
        @endif
        @if (auth()->user()->hasPermission('staff_products_accounts_view'))
          <!-- Start::slide -->
          <li class="slide">
            <a href="{{ route('staff.products.accounts.groups') }}" class="side-menu__item {{ request()->routeIs('staff.products.accounts.groups') ? 'active' : '' }}">
              <i class="bx bx-data side-menu__icon"></i>
              <span class="side-menu__label">{{ __t('Quản lý tài khoản') }}</span>
            </a>
          </li>
          <!-- End::slide -->
        @endif
        @if (auth()->user()->hasPermission('staff_orders_accounts_view'))
          <!-- Start::slide -->
          <li class="slide">
            <a href="{{ route('staff.orders.accounts.index') }}" class="side-menu__item {{ request()->routeIs('staff.orders.accounts.index') ? 'active' : '' }}">
              <i class="bx bx-cart side-menu__icon"></i>
              <span class="side-menu__label">{{ __t('Đơn hàng tài khoản') }}</span>
            </a>
          </li>
          <!-- End::slide -->
        @endif

      </ul>
      <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
        </svg>
      </div>
    </nav>
    <!-- End::nav -->

  </div>
  <!-- End::main-sidebar -->

</aside>
