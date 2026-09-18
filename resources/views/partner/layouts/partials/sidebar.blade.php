<aside class="app-sidebar sticky" id="sidebar">

  <!-- Start::main-sidebar-header -->
  <div class="main-sidebar-header">
    <a href="{{ route('partner.dashboard') }}" class="header-logo">
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
        <li class="slide__category"><span class="category-name">Trang chủ</span></li>
        <!-- End::slide__category -->

        @if (auth()->user()->hasPermission('partner_dashboard_view'))
        <li class="slide">
          <a href="{{ route('partner.dashboard') }}" class="side-menu__item {{ request()->routeIs('partner.dashboard') ? 'active' : '' }}">
            <i class="bx bx-home side-menu__icon"></i>
            <span class="side-menu__label">Tổng quan</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">Quản lý</span></li>
        <!-- End::slide__category -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('partner_settings_view'))
        <li class="slide">
          <a href="{{ route('partner.settings.index') }}" class="side-menu__item {{ request()->routeIs('partner.settings.index') ? 'active' : '' }}">
            <i class="bx bx-cog side-menu__icon"></i>
            <span class="side-menu__label">Cấu hình chung</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('partner_notices_view'))
        <li class="slide">
          <a href="{{ route('partner.settings.notices') }}" class="side-menu__item {{ request()->routeIs('partner.settings.notices') ? 'active' : '' }}">
            <i class="bx bx-bell side-menu__icon"></i>
            <span class="side-menu__label">Cấu hình Thông báo</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->
        
        <!-- Future Modules can be added here -->

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
