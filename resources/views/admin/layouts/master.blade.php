
<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
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
  <style>
    /* SweetAlert2 Toast Overrides */
    .swal2-toast .swal2-title {
        margin-bottom: 0px !important;
    }
    .swal2-toast .swal2-html-container {
        margin-top: 2px !important;
    }
    .swal2-timer-progress-bar {
        background: #d1d5db !important; /* Light grey progress bar like in the screenshot */
        height: 3px !important;
    }
    .swal2-popup.swal2-toast {
        padding: 0.75rem 0.75rem 1rem !important; /* Specific padding to show progress bar clearly at bottom */
        overflow: hidden !important;
    }
    /*page-overlay*/
    #page-overlay {
      opacity: 0;
      top: 0px;
      left: 0px;
      position: fixed;
      background-color: rgba(249, 249, 249, 0.8);
      height: 100%;
      width: 100%;
      /* Keep below SweetAlert2 / Bootstrap modals to avoid overlaying dialogs */
      z-index: 1049;
      -webkit-transition: opacity 0.2s linear;
      -moz-transition: opacity 0.2s linear;
      transition: opacity 0.2s linear;
    }

    #page-overlay.visible {
      opacity: 1;
    }

    #page-overlay.visible.active,
    #page-overlay.visible.active img {
      display: block;
    }

    #page-overlay.hidden {
      opacity: 0;
      height: 0px;
      width: 0px;
      z-index: -10000;
    }

    #page-overlay .loader-wrapper-outer {
      background-color: transparent;
      z-index: 9999;
      margin: auto;
      width: 100%;
      height: 100%;
      overflow: hidden;
      display: table;
      text-align: center;
      vertical-align: middle;
    }

    #page-overlay .loader-wrapper-inner {
      display: table-cell;
      vertical-align: middle;
    }

    #page-overlay .loader {
      margin: auto;
    }

    @keyframes lds-double-ring {
      0% {
        -webkit-transform: rotate(0);
        transform: rotate(0);
      }

      100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @-webkit-keyframes lds-double-ring {
      0% {
        -webkit-transform: rotate(0);
        transform: rotate(0);
      }

      100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @keyframes lds-double-ring_reverse {
      0% {
        -webkit-transform: rotate(0);
        transform: rotate(0);
      }

      100% {
        -webkit-transform: rotate(-360deg);
        transform: rotate(-360deg);
      }
    }

    @-webkit-keyframes lds-double-ring_reverse {
      0% {
        -webkit-transform: rotate(0);
        transform: rotate(0);
      }

      100% {
        -webkit-transform: rotate(-360deg);
        transform: rotate(-360deg);
      }
    }

    #page-overlay .lds-double-ring {
      position: relative;
    }

    #page-overlay .lds-double-ring div {
      box-sizing: border-box;
    }

    #page-overlay .lds-double-ring>div {
      position: absolute;
      width: 44px;
      height: 44px;
      top: 78px;
      left: 78px;
      border-radius: 50%;
      border: 4px solid #000;
      border-color: #2196f3 transparent #2196f3 transparent;
      -webkit-animation: lds-double-ring 1s linear infinite;
      animation: lds-double-ring 1s linear infinite;
    }

    #page-overlay .lds-double-ring>div:nth-child(2),
    #page-overlay .lds-double-ring>div:nth-child(4) {
      width: 32px;
      height: 32px;
      top: 84px;
      left: 84px;
      -webkit-animation: lds-double-ring_reverse 1s linear infinite;
      animation: lds-double-ring_reverse 1s linear infinite;
    }

    #page-overlay .lds-double-ring>div:nth-child(2) {
      border-color: transparent #2196f3 transparent #2196f3;
    }

    #page-overlay .lds-double-ring>div:nth-child(3) {
      border-color: transparent;
    }

    #page-overlay .lds-double-ring>div:nth-child(3) div {
      position: absolute;
      width: 100%;
      height: 100%;
      -webkit-transform: rotate(45deg);
      transform: rotate(45deg);
    }

    #page-overlay .lds-double-ring>div:nth-child(3) div:before,
    #page-overlay .lds-double-ring>div:nth-child(3) div:after {
      content: "";
      display: block;
      position: absolute;
      width: 4px;
      height: 4px;
      top: -4px;
      left: 16px;
      background: #2196f3;
      border-radius: 50%;
      box-shadow: 0 40px 0 0 #2196f3;
    }

    #page-overlay .lds-double-ring>div:nth-child(3) div:after {
      left: -4px;
      top: 16px;
      box-shadow: 40px 0 0 0 #2196f3;
    }

    #page-overlay .lds-double-ring>div:nth-child(4) {
      border-color: transparent;
    }

    #page-overlay .lds-double-ring>div:nth-child(4) div {
      position: absolute;
      width: 100%;
      height: 100%;
      -webkit-transform: rotate(45deg);
      transform: rotate(45deg);
    }

    #page-overlay .lds-double-ring>div:nth-child(4) div:before,
    #page-overlay .lds-double-ring>div:nth-child(4) div:after {
      content: "";
      display: block;
      position: absolute;
      width: 4px;
      height: 4px;
      top: -4px;
      left: 10px;
      background: #2196f3;
      border-radius: 50%;
      box-shadow: 0 28px 0 0 #2196f3;
    }

    #page-overlay .lds-double-ring>div:nth-child(4) div:after {
      left: -4px;
      top: 10px;
      box-shadow: 28px 0 0 0 #2196f3;
    }

    #page-overlay .lds-double-ring {
      width: 200px !important;
      height: 200px !important;
      display: inline-block;
      -webkit-transform: translate(-100px, -100px) scale(1) translate(100px, 100px);
      transform: translate(-100px, -100px) scale(1) translate(100px, 100px);
    }
    /* Hide DataTables sorting icons for non-orderable columns */
    table.dataTable thead th[data-orderable="false"]::before,
    table.dataTable thead th[data-orderable="false"]::after,
    table.dataTable thead td[data-orderable="false"]::before,
    table.dataTable thead td[data-orderable="false"]::after {
        display: none !important;
    }
    table.dataTable thead th[data-orderable="false"],
    table.dataTable thead td[data-orderable="false"] {
        background-image: none !important;
        cursor: default !important;
    }
    
    /* Select2 Fix for long text and overflow */
    .select2-container {
        max-width: 100% !important;
    }
    .select2-results__option {
        word-break: break-all !important;
        white-space: normal !important;
    }
    .select2-selection__rendered {
        white-space: normal !important;
        word-break: break-all !important;
    }

    /* Choices.js Custom Styling (from currency management) */
    .choices__inner {
        min-height: 44px;
        border-radius: 0.375rem !important;
        background-color: #fff !important;
    }
    .choices__list--dropdown {
        z-index: 10000 !important;
        background-color: white !important;
        background: white !important;
        color: #333 !important;
        border: 1px solid #ddd !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        position: absolute !important;
        width: 100% !important;
    }
    .choices__list--dropdown .choices__list,
    .choices__list--dropdown div[role="listbox"] {
        max-height: 250px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        background-color: white !important;
        scrollbar-gutter: stable;
    }
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #7367f0 !important;
        color: #fff !important;
    }
    /* Custom Scrollbar for Choices.js */
    .choices__list--dropdown .choices__list::-webkit-scrollbar {
        width: 10px !important;
        display: block !important;
    }
    .choices__list--dropdown .choices__list::-webkit-scrollbar-track {
        background: #f1f1f1 !important;
    }
    .choices__list--dropdown .choices__list::-webkit-scrollbar-thumb {
        background: #7367f0 !important;
        border-radius: 4px;
        border: 2px solid #f1f1f1;
    }
    .choices__list--dropdown .choices__list::-webkit-scrollbar-thumb:hover {
        background: #5a4cf5 !important;
    }
  </style>
  <div id="page-overlay" style="display: none;">
    <div class="loader-wrapper-outer">
      <div class="loader-wrapper-inner">
        <div class="lds-double-ring">
          <div></div>
          <div></div>
          <div>
            <div></div>
          </div>
          <div>
            <div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Loader -->

  <div class="page" style="min-height: 100vh; display: flex; flex-direction: column;">
    <!-- app-header -->
    @include('admin.layouts.partials.header')
    <!-- /app-header -->

    <!-- Start::app-sidebar -->
    @include('admin.layouts.partials.sidebar')
    <!-- End::app-sidebar -->

    <!-- Start::app-content -->
    <div class="main-content app-content flex-grow-1" style="flex: 1;">
      <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
          <h1 class="page-title fw-semibold fs-18 mb-0">@yield('title')</h1>
          <div class="ms-md-1 ms-0">
            @yield('header_actions')
          </div>
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
  <!-- Scroll To Top -->

  <script>
      window.userData = {
          access_token: '{{ auth()->check() ? auth()->user()->access_token : "" }}'
      };
  </script>

  @include('admin.layouts.partials.vendor')

  <!-- Heartbeat for online status (Admin) -->
  <script>
      @if(auth()->check())
          const sendHeartbeat = () => {
              fetch('{{ route('account.heartbeat') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                  }
              })
              .then(response => response.json())
              .then(data => {
                  if (data.status && data.notifications && data.notifications.length > 0) {
                      let shownNotifs = JSON.parse(sessionStorage.getItem('shown_notifications_admin') || '[]');
                      let changed = false;

                      data.notifications.forEach(notification => {
                          if (!shownNotifs.includes(notification.id)) {
                              shownNotifs.push(notification.id);
                              changed = true;
                              
                              // Hiển thị Toast cho Admin
                              if (typeof Swal !== 'undefined') {
                                  Swal.fire({
                                      title: `<div class="text-left font-bold text-base leading-none mb-0">${notification.title}</div>`,
                                      html: `<div class="text-left text-sm text-gray-500 mt-0">${notification.content}</div>`,
                                      toast: true, 
                                      position: 'top-end', 
                                      showConfirmButton: true, 
                                      confirmButtonText: 'Xem', 
                                      showCancelButton: true, 
                                      cancelButtonText: 'Đóng', 
                                      timer: 10000, 
                                      timerProgressBar: true,
                                  }).then((result) => { 
                                      if (result.isConfirmed && notification.link) window.location.href = notification.link; 
                                  });
                              }
                          }
                      });

                      if (changed) {
                          if (shownNotifs.length > 100) shownNotifs = shownNotifs.slice(-100);
                          sessionStorage.setItem('shown_notifications_admin', JSON.stringify(shownNotifs));
                      }
                  }
              })
              .catch(error => console.error('Admin Heartbeat error:', error));
          };
          sendHeartbeat(); // Call immediately
          setInterval(sendHeartbeat, 15000); // Check every 15 seconds
          
          window.addEventListener('beforeunload', () => {
              const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
              const formData = new FormData();
              formData.append('_token', csrfToken);
              navigator.sendBeacon('{{ route('account.offline') }}', formData);
          });
      @endif
  </script>
</body>

</html>
