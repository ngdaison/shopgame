<div class="sidebar-wrapper">
  <div>
    <div class="logo-wrapper"><a href="{{ route('staff.dashboard') }}">
        <img class="img-fluid for-light" style="width: 76%; height: 60px;" src="/_admin/images/cmsnt_dark.png" alt=""></a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"></i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="{{ route('staff.dashboard') }}">
        <div class="icon-box-sidebar"><i data-feather="grid"></i></div>
      </a></div>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn">
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
          <li class="pin-title sidebar-list">
            <h6>Pinned</h6>
          </li>
          <hr>
          <li class="sidebar-list">
            <i class="fa fa-thumb-tack"></i>
            <a class="sidebar-link sidebar-title link-nav" href="{{ route('staff.dashboard') }}">
              <i data-feather="home"> </i><span>Bảng điều khiển</span>
            </a>
          </li>

          @if (auth()->user()->colla_type === 'items')
            <li class="sidebar-list">
              <i class="fa fa-thumb-tack"></i>
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('staff.orders.items.index') }}">
                <i data-feather="list"> </i><span>Đơn hàng vật phẩm</span>
              </a>
            </li>
          @endif
          @if (auth()->user()->colla_type === 'boosting')
            <li class="sidebar-list">
              <i class="fa fa-thumb-tack"></i>
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('staff.orders.boostings.index') }}">
                <i data-feather="list"> </i><span>Đơn hàng cày thuê</span>
              </a>
            </li>
          @endif
          @if (auth()->user()->colla_type === 'account')
            <li class="sidebar-list">
              <i class="fa fa-thumb-tack"></i>
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('staff.products.accounts.groups') }}">
                <i data-feather="server"> </i><span>Quản lý tài khoản</span>
              </a>
            </li>
            <li class="sidebar-list">
              <i class="fa fa-thumb-tack"></i>
              <a class="sidebar-link sidebar-title link-nav" href="{{ route('staff.orders.accounts.index') }}">
                <i data-feather="list"> </i><span>Đơn hàng tài khoản</span>
              </a>
            </li>
          @endif
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>
