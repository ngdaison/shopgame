<!DOCTYPE html>
<html lang="vi" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<script>
    if (localStorage.getItem('admin_sidebar_state')) {
        document.documentElement.setAttribute('data-toggled', localStorage.getItem('admin_sidebar_state'));
    }
</script>

@include('admin.layouts.partials.head')

<body>

  <!-- Start Switcher -->
  @include('admin.layouts.partials.switcher')
  <!-- End Switcher -->

  <!-- Loader -->
  <div id="loader">
    <img src="/_admin/images/media/loader.svg" alt="">
  </div>
  <!-- Loader -->

  <div class="page">

    <!-- app-header -->
    @include('partner.layouts.partials.header')
    <!-- /app-header -->

    <!-- Start::app-sidebar -->
    @include('partner.layouts.partials.sidebar')
    <!-- End::app-sidebar -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
      <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
          <h1 class="page-title fw-semibold fs-18 mb-0">@yield('title')</h1>
        </div>
        <!-- Page Header Close -->

        <!-- Alert Component -->
        @include('admin.layouts.includes.alert')
        <!-- Alert Component Close -->

        <!-- Start:: Content -->
        @yield('content')
        <!-- End:: Content -->

      </div>
    </div>
    <!-- End::app-content -->

    <!-- Footer Start -->
    @include('admin.layouts.partials.footer')
    <!-- Footer End -->

  </div>

  <!-- Scroll To Top -->
  <div class="scrollToTop">
    <span class="arrow"><i class="ri-arrow-up-s-fill fs-20"></i></span>
  </div>
  <div id="responsive-overlay"></div>
  
  <script>
      // Sidebar Persistence Logic
      document.addEventListener('DOMContentLoaded', () => {
          const toggleBtn = document.querySelector('.sidemenu-toggle');
          if (toggleBtn) {
              toggleBtn.addEventListener('click', () => {
                  setTimeout(() => {
                      const state = document.documentElement.getAttribute('data-toggled');
                      localStorage.setItem('admin_sidebar_state', state || 'close');
                  }, 100);
              });
          }
      });
  </script>

  @include('partner.layouts.partials.vendor')

</body>

</html>
