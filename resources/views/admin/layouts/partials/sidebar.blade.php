<aside class="app-sidebar sticky" id="sidebar">

  <!-- Start::main-sidebar-header -->
  <div class="main-sidebar-header">
    <a href="{{ route('admin.dashboard') }}" class="header-logo">
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

        @if (auth()->user()->hasPermission('admin_dashboard_view'))
        <li class="slide">
          <a href="{{ route('admin.dashboard') }}" class="side-menu__item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bx bx-home side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Bảng Điều Khiển') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_kiyoai_use'))
        <li class="slide">
          <a href="{{ route('admin.kiyoai') }}" class="side-menu__item {{ request()->routeIs('admin.kiyoai*') ? 'active' : '' }}">
            <i class="bx bx-bot side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('KiyoAI Assistant') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">System Settings</span></li>
        <!-- End::slide__category -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_security_settings_view') || auth()->user()->hasPermission('admin_security_block_view'))
        <li class="slide has-sub {{ request()->routeIs('admin.security*') ? 'open' : '' }}">
          <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.security*') ? 'active' : '' }}">
            <i class="bx bx-shield-quarter side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Bảo Mật Hệ Thống') }}</span>
             <i class="fe fe-chevron-right side-menu__angle"></i>
          </a>
          <ul class="slide-menu child1">
            @if (auth()->user()->hasPermission('admin_security_settings_view'))
            <li class="slide">
              <a href="{{ route('admin.security') }}" class="side-menu__item {{ request()->routeIs('admin.security') ? 'active' : '' }}">{{ __t('Cài Đặt Bảo Mật') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_security_block_view'))
             <li class="slide">
              <a href="{{ route('admin.security.block') }}" class="side-menu__item {{ request()->routeIs('admin.security.block') ? 'active' : '' }}">{{ __t('Danh Sách Block') }}</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        <!-- End::slide -->
         
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_settings_general_view') || auth()->user()->hasPermission('admin_domain_view') || auth()->user()->hasPermission('admin_settings_apis_view') || auth()->user()->hasPermission('admin_settings_notices_view') || auth()->user()->hasPermission('admin_pin_groups_view') || auth()->user()->hasPermission('admin_currency_view') || auth()->user()->hasPermission('admin_language_view'))
        <li class="slide has-sub {{ request()->routeIs('admin.settings.general','admin.settings.apis','admin.settings.notices','admin.pin-groups','admin.domain.*','admin.currency.*','admin.language*') ? 'open' : '' }}">
          <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.settings.general','admin.settings.apis','admin.settings.notices','admin.pin-groups','admin.domain.*','admin.currency.*','admin.language*') ? 'active' : '' }}">
            <i class="bx bx-cog side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Cài Đặt Hệ Thống') }}</span>
            <i class="fe fe-chevron-right side-menu__angle"></i>
          </a>
          <ul class="slide-menu child1">
            @if (auth()->user()->hasPermission('admin_settings_general_view'))
            <li class="slide">
              <a href="{{ route('admin.settings.general') }}" class="side-menu__item {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">{{ __t('Cài đặt chung') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_domain_view'))
            <li class="slide">
              <a href="{{ route('admin.domain.index') }}" class="side-menu__item {{ request()->routeIs('admin.domain.*') ? 'active' : '' }}">{{ __t('Quản lý Tên miền') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_settings_apis_view'))
            <li class="slide">
              <a href="{{ route('admin.settings.apis') }}" class="side-menu__item {{ request()->routeIs('admin.settings.apis') ? 'active' : '' }}">{{ __t('Cấu Hình API Keys') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_settings_notices_view'))
            <li class="slide">
              <a href="{{ route('admin.settings.notices') }}" class="side-menu__item {{ request()->routeIs('admin.settings.notices') ? 'active' : '' }}">{{ __t('Cài Đặt Thông Báo') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_pin_groups_view'))
            <li class="slide">
              <a href="{{ route('admin.pin-groups') }}" class="side-menu__item {{ request()->routeIs('admin.pin-groups') ? 'active' : '' }}">{{ __t('Danh Sách Ghim') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_currency_view'))
            <li class="slide">
              <a href="{{ route('admin.currency.index') }}" class="side-menu__item {{ request()->routeIs('admin.currency.*') ? 'active' : '' }}">{{ __t('Quản Lý Tiền Tệ') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_language_view'))
            <li class="slide">
              <a href="{{ route('admin.language') }}" class="side-menu__item {{ request()->routeIs('admin.language*') ? 'active' : '' }}">{{ __t('Quản lý ngôn ngữ') }}</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
            @if (auth()->user()->hasPermission('admin_automations_view'))
            <li class="slide">
              <a href="{{ route('admin.automations') }}" class="side-menu__item {{ request()->routeIs('admin.automations') ? 'active' : '' }}">
                <i class="bx bx-task side-menu__icon"></i>
                <span class="side-menu__label">{{ __t('Tác Vụ Tự Động') }}</span>
              </a>
            </li>
            @endif
        <!-- End::slide -->

        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">Users Manager</span></li>
        <!-- End::slide__category -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_statistical_view'))
        <li class="slide">
          <a href="{{ route('admin.statistical') }}" class="side-menu__item {{ request()->routeIs('admin.statistical') ? 'active' : '' }}">
            <i class="bx bx-bar-chart-alt-2 side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Thống Kê Chiến Dịch') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_users_view'))
        <li class="slide">
          <a href="{{ route('admin.users') }}" class="side-menu__item {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.edit') ? 'active' : '' }}">
            <i class="bx bx-user side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Thành Viên') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        @if (auth()->user()->hasPermission('admin_role_view'))
        <li class="slide">
          <a href="{{ route('admin.role') }}" class="side-menu__item {{ request()->routeIs('admin.role*') ? 'active' : '' }}">
            <i class="bx bx-user-pin side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Role') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_transactions_view'))
        <li class="slide">
          <a href="{{ route('admin.transactions') }}" class="side-menu__item {{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
            <i class="bx bx-data side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Lịch Sử Giao Dịch') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_histories_view'))
        <li class="slide">
          <a href="{{ route('admin.histories') }}" class="side-menu__item {{ request()->routeIs('admin.histories') ? 'active' : '' }}">
            <i class="bx bx-server side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Lịch Sử Hoạt Động') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_logs_view'))
        <li class="slide">
          <a href="{{ route('admin.logs') }}" class="side-menu__item {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
            <i class="bx bx-history side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Logs Hệ Thống') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_deposit_banks_view') || auth()->user()->hasPermission('admin_deposit_cards_view') || auth()->user()->hasPermission('admin_deposit_usdt_view') || auth()->user()->hasPermission('admin_deposit_paypal_view') || auth()->user()->hasPermission('admin_deposit_perfect_money_view'))
        <li class="slide has-sub {{ request()->routeIs('admin.deposit.banks', 'admin.deposit.cards', 'admin.deposit.usdt', 'admin.deposit.paypal', 'admin.deposit.perfect_money') ? 'open' : '' }}">
          <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.deposit.banks', 'admin.deposit.cards', 'admin.deposit.usdt', 'admin.deposit.paypal', 'admin.deposit.perfect_money') ? 'active' : '' }}">
            <i class="bx bx-wallet side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Lịch sử nạp') }}</span>
            <i class="fe fe-chevron-right side-menu__angle"></i>
          </a>
          <ul class="slide-menu child1">
            @if (auth()->user()->hasPermission('admin_deposit_banks_view'))
            <li class="slide">
              <a href="{{ route('admin.deposit.banks') }}" class="side-menu__item {{ request()->routeIs('admin.deposit.banks') ? 'active' : '' }}">{{ __t('Ngân hàng') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_deposit_cards_view'))
            <li class="slide">
              <a href="{{ route('admin.deposit.cards') }}" class="side-menu__item {{ request()->routeIs('admin.deposit.cards') ? 'active' : '' }}">{{ __t('Nạp thẻ cào') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_deposit_usdt_view'))
            <li class="slide">
              <a href="{{ route('admin.deposit.usdt') }}" class="side-menu__item {{ request()->routeIs('admin.deposit.usdt') ? 'active' : '' }}">{{ __t('Crypto USDT') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_deposit_paypal_view'))
            <li class="slide">
              <a href="{{ route('admin.deposit.paypal') }}" class="side-menu__item {{ request()->routeIs('admin.deposit.paypal') ? 'active' : '' }}">{{ __t('Paypal') }}</a>
            </li>
            @endif
            @if (auth()->user()->hasPermission('admin_deposit_perfect_money_view'))
            <li class="slide">
              <a href="{{ route('admin.deposit.perfect_money') }}" class="side-menu__item {{ request()->routeIs('admin.deposit.perfect_money*') ? 'active' : '' }}">{{ __t('Perfect Money') }}</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        <!-- End::slide -->

         
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_invoices_view'))
        <li class="slide">
          <a href="{{ route('admin.invoices') }}" class="side-menu__item {{ request()->routeIs('admin.invoices') ? 'active' : '' }}">
            <i class="bx bx-dollar side-menu__icon"></i>
            <span class="side-menu__label">Quản Lý Hoá Đơn<span class="badge bg-warning-transparent ms-2"></span>
          </a>
        </li>
        @endif
        <!-- End::slide -->
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_template_view'))
        <li class="slide">
          <a href="{{ route('admin.template.index') }}" class="side-menu__item {{ request()->routeIs('admin.template.*') ? 'active' : '' }}">
            <i class="bx bx-envelope side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Mail Template') }}</span>
          </a>
        </li>
        @endif
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_tickets_view'))
        <li class="slide">
          <a href="{{ route('admin.tickets') }}" class="side-menu__item {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}">
            <i class="bx bx-support side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Tickets') }}<span class="badge bg-warning-transparent ms-2">{{ auth()->user()->applyHistoryLimit(\App\Models\Ticket::where('status', 'open'))->count() }}</span></span>
          </a>
        </li>
        @endif
        <!-- End::slide -->
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_notifications_view'))
        <li class="slide">
          <a href="{{ route('admin.notifications.index') }}" class="side-menu__item {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}">
            <i class="bx bx-bell side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Thông Báo') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">Staff Manager</span></li>
        <!-- End::slide__category -->
        <!-- Start::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_affiliates_view'))
        <li class="slide">
          <a href="{{ route('admin.affiliates') }}" class="side-menu__item {{ request()->routeIs('admin.affiliates') ? 'active' : '' }}">
            <i class="bx bx-share side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Tiếp Thị Liên Kết') }} <span class="badge bg-danger-gradient">{{ \App\Models\User::whereNotNull('referral_by')->count() }}</span></span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_staff_withdraws_view'))
        <li class="slide">
          <a href="{{ route('admin.staff.withdraws') }}" class="side-menu__item {{ request()->routeIs('admin.staff.withdraws') ? 'active' : '' }}">
            <i class="bx bx-transfer side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Yêu Cầu Rút Tiền') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">Data Manager</span></li>
        <!-- End::slide__category -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_posts_view'))
        <li class="slide">
          <a href="{{ route('admin.posts') }}" class="side-menu__item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
            <i class="bx bx-comment side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Bài Viết') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_withdraws_view'))
        <li class="slide">
          <a href="{{ route('admin.withdraws') }}" class="side-menu__item {{ request()->routeIs('admin.withdraws') ? 'active' : '' }}">
            <i class="bx bx-money side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Yêu Cầu Rút thưởng') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_inventories_vars_view') || auth()->user()->hasPermission('admin_inventories_view'))
        <li class="slide">
          <a href="{{ route('admin.inventories.vars') }}" class="side-menu__item {{ request()->routeIs('admin.inventories.vars') ? 'active' : '' }}">
            <i class="bx bx-gift side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản lý kho hàng') }}</span>
          </a>
        </li>
        <li class="slide">
          <a href="{{ route('admin.inventories') }}" class="side-menu__item {{ request()->routeIs('admin.inventories') ? 'active' : '' }}">
            <i class="bx bx-server side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản lý phần thưởng') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->





        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_coupons_view'))
        <li class="slide">
          <a href="{{ route('admin.coupons') }}" class="side-menu__item {{ request()->routeIs('admin.coupons') ? 'active' : '' }}">
            <i class="bx bx-purchase-tag-alt side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Mã Giảm Giá') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->
        
        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_promotions_view'))
        <li class="slide">
            <a href="{{ route('admin.promotions') }}" class="side-menu__item {{ request()->routeIs('admin.promotions') ? 'active' : '' }}">
              <i class="bx bx-gift side-menu__icon"></i>
              <span class="side-menu__label">{{ __t('Khuyến Mãi Nạp Tiền') }}</span>
            </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide__category -->
        <li class="slide__category"><span class="category-name">Product Manager</span></li>
        <!-- End::slide__category -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_categories_view'))
        <li class="slide">
          <a href="{{ route('admin.categories') }}" class="side-menu__item {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
            <i class="bx bx-category side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Chuyên Mục') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_service_view'))
        <li class="slide">
          <a href="{{ route('admin.service.index') }}" class="side-menu__item {{ request()->routeIs('admin.service.*') ? 'active' : '' }}">
            <i class="bx bx-star side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Dịch vụ khác') }}</span>
          </a>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_boosting_groups_view') || auth()->user()->hasPermission('admin_boosting_orders_view'))
        <li class="slide has-sub {{ request()->routeIs('admin.boosting.*') ? 'open' : '' }}">
          <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.boosting.*') ? 'active' : '' }}">
            <i class="bx bx-shower side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Dịch Vụ Cày Thuê') }} 
              <span class="badge bg-primary-gradient">{{ auth()->user()->applyHistoryLimit(\App\Models\GBOrder::where('status', 'Pending'))->count() }}</span>
            </span>
            <i class="fe fe-chevron-right side-menu__angle"></i>
          </a>
          <ul class="slide-menu child1">
            <li class="slide side-menu__label1">
              <a href="javascript:void(0)">{{ __t('Dịch Vụ Cày Thuê') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.boosting.groups') }}" class="side-menu__item {{ request()->routeIs('admin.boosting.groups') ? 'active' : '' }}">{{ __t('Quản Lý Nhóm') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.boosting.orders') }}" class="side-menu__item {{ request()->routeIs('admin.boosting.orders') ? 'active' : '' }}">{{ __t('Quản Lý Đơn Hàng') }}</a>
            </li>
          </ul>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_items_groups_view') || auth()->user()->hasPermission('admin_items_orders_view'))
        <li class="slide has-sub {{ request()->routeIs('admin.items.*') ? 'open' : '' }}">
          <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
            <i class="bx bx-archive side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Dịch Vụ Vật Phẩm') }} <span class="badge bg-danger-gradient">{{ auth()->user()->applyHistoryLimit(\App\Models\ItemOrder::where('status', 'Pending'))->count() }}</span></span>
            <i class="fe fe-chevron-right side-menu__angle"></i>
          </a>
          <ul class="slide-menu child1">
            <li class="slide side-menu__label1">
              <a href="javascript:void(0)">{{ __t('Dịch Vụ Vật Phẩm') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.items.groups') }}" class="side-menu__item {{ request()->routeIs('admin.items.groups') ? 'active' : '' }}">{{ __t('Quản Lý Nhóm') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.items.orders') }}" class="side-menu__item {{ request()->routeIs('admin.items.orders') ? 'active' : '' }}">{{ __t('Quản Lý Đơn Hàng') }}</a>
            </li>
          </ul>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_accounts_groups_view') || auth()->user()->hasPermission('admin_accounts_items_view'))
        <li class="slide has-sub {{ request()->routeIs('admin.accounts.*') ? 'open' : '' }}">
          <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
            <i class="bx bx-lemon side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Shop Nick') }}</span>
            <i class="fe fe-chevron-right side-menu__angle"></i>
          </a>
          <ul class="slide-menu child1">
            <li class="slide side-menu__label1">
              <a href="javascript:void(0)">{{ __t('Quản Lý Shop Nick') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.accounts.groups') }}" class="side-menu__item {{ request()->routeIs('admin.accounts.groups') ? 'active' : '' }}">{{ __t('Quản Lý Nhóm') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.accounts.items', ['sold' => 0]) }}"
                class="side-menu__item {{ request()->routeIs('admin.accounts.items') && request()->input('sold') == 0 ? 'active' : '' }}">{{ __t('Quản Lý Tài Khoản') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.accounts.items', ['sold' => 1]) }}"
                class="side-menu__item {{ request()->routeIs('admin.accounts.items') && request()->input('sold') == 1 ? 'active' : '' }}">{{ __t('Quản Lý Đơn Hàng') }}</a>
            </li>
          </ul>
        </li>
        @endif
        <!-- End::slide -->

        <!-- Start::slide -->
        @if (auth()->user()->hasPermission('admin_accountsv2_groups_view') || auth()->user()->hasPermission('admin_accountsv2_items_view'))
        <li class="slide has-sub {{ request()->routeIs('admin.accountsv2.*') ? 'open' : '' }}">
          <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.accountsv2.*') ? 'active' : '' }}">
            <i class="bx bx-sushi side-menu__icon"></i>
            <span class="side-menu__label">{{ __t('Quản Lý Shop Nick v2') }}</span>
            <i class="fe fe-chevron-right side-menu__angle"></i>
          </a>
          <ul class="slide-menu child1">
            <li class="slide side-menu__label1">
              <a href="javascript:void(0)">{{ __t('Quản Lý Shop Nick v2') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.accountsv2.groups') }}" class="side-menu__item {{ request()->routeIs('admin.accountsv2.groups') ? 'active' : '' }}">{{ __t('Quản Lý Nhóm') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.accountsv2.items') }}" class="side-menu__item {{ request()->routeIs('admin.accountsv2.items') ? 'active' : '' }}">{{ __t('Kho Hàng') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.accountsv2.orders') }}" class="side-menu__item {{ request()->routeIs('admin.accountsv2.orders') ? 'active' : '' }}">{{ __t('Quản Lý Đơn Hàng') }}</a>
            </li>
            <li class="slide">
              <a href="{{ route('admin.accountsv2.api') }}" class="side-menu__item {{ request()->routeIs('admin.accountsv2.api') ? 'active' : '' }}">{{ __t('Cấu hình API') }}</a>
            </li>
          </ul>
        </li>
        @endif
        <!-- End::slide -->

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
