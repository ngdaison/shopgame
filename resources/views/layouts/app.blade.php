@props(['pageTitle' => 'Default Title', 'postTitle' => null, 'meta_seo' => null, 'contentClass' => 'flex-1', 'hasStickyFooter' => true])



<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" class="light layout-full nav-sticky horizontalMenu">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta http-equiv="content-language" content="{{ currentLang() === 'vn' ? 'vi' : 'en' }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @hasSection('description')
    <meta name="description" content="@yield('description')">
  @else
    <meta name="description" content="{{ Helper::branding('description') }}">
  @endif
  @hasSection('keywords')
    <meta name="keywords" content="@yield('keywords')">
  @else
    <meta name="keywords" content="{{ Helper::branding('keywords') }}">
  @endif
  <meta name="author" content="{{ Helper::branding('author') }}">
  <meta name="robots" content="index, follow">
  <meta name="googlebot" content="index, follow">
  <meta name="google" content="notranslate">
  <meta name="generator" content="{{ strtoupper($_SERVER['HTTP_HOST']) }}">

  <meta name="application-name" content="{{ Helper::branding('title') }}">
  <meta property="og:image" content="{{ Helper::getValidImage(Helper::branding('logo_share')) }}">
  <meta property="og:image:secure_url" content="{{ Helper::getValidImage(Helper::branding('logo_share')) }}">
  {{-- <meta property="og:image:width" content="128">
  <meta property="og:image:height" content="128"> --}}
  {{-- <meta property="og:image:type" content="image/png"> --}}
  <meta property="og:image:alt" content="{{ Helper::branding('title') }}">
  <meta property="og:title" content="{{ Helper::branding('title') }}">
  <meta property="og:site_name" content="{{ Helper::branding('title') }}">
  <meta property="og:description" content="{{ Helper::branding('description') }}">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:type" content="website">

  <link rel="shortcut icon" href="{{ Helper::getValidImage(Helper::branding('favicon')) }}" type="image/x-icon">

  @hasSection('postTitle')
    <title>@yield('postTitle')</title>
  @endif
  @hasSection('title')
    <title>@yield('title') - {{ Helper::branding('title') }}</title>
  @else
    @hasSection('pageTitle')
      <title>@yield('pageTitle')</title>
    @else
      <title>{{ Helper::branding('title') }}</title>
    @endif
  @endif

  <script>
    (function() {
        if (window.location.search.indexOf('utm_source') > -1) {
            var url = new URL(window.location.href);
            url.searchParams.delete('utm_source');
            window.history.replaceState({}, document.title, url.toString());
        }
    })();

    // Global image error handler
    (function() {
        window.fallbackImgSrc = "{{ asset('/images/svg/spinner.svg') }}";
        window.addEventListener('error', function (e) {
            if (e.target && e.target.tagName === 'IMG') {
                const img = e.target;
                if (img.src.indexOf(window.fallbackImgSrc) === -1) {
                    img.src = window.fallbackImgSrc;
                } else {
                    img.style.display = 'none';
                }
            }
        }, true);
    })();
  </script>
  <script>
    window.paceOptions = {
      ajax: {
        trackMethods: ['GET', 'POST'],
        ignoreURLs: [/api\/users\/tickets\/[^\/]+$/] // Ignore background polling for tickets
      },
      restartOnRequestAfter: false,
      restartOnPushState: false
    };
  </script>
  <script src="https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pace-js@latest/pace-theme-default.min.css">
  <style>
    .pace .pace-progress {
        background: #ff4d4f; /* Matches the user's red theme if desired, or keep default */
    }
    
    /* Alpine.js x-cloak: hide elements before Alpine initializes */
    [x-cloak] {
        display: none !important;
    }
  </style>

  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Signika:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Play:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  {{-- Scripts --}}

  <script>
    window.webData = @json([
        'csrfToken' => csrf_token(),
    ]);
    window.userData = @json(auth()->user());
  </script>

  @vite(['resources/css/app.scss', 'resources/js/custom/store.js'])

  @include('layouts.partials.custom-head')

  @stack('css')
  @yield('css')

  {!! Helper::getNotice('header_script') !!}

  <!-- v-translate -->
  <script>
    window.LANG = @json(getLangJson() ?? [])


    window.$__t = function(key) {
      if (window.LANG[key] === undefined) {
        // console.log(key);
      }
      return window.LANG[key] || key;
    }

    window.__defaultLang = '{{ currentLang() }}';
    window.__usdRate = '{{ usdRate() }}';
    
    @php
       $currencyCode = Helper::currentCurrency();
       $currencyRate = 1;
       $currencyModel = \App\Models\Currency::where('code', $currencyCode)->first();
       if ($currencyModel) {
            $currencyRate = $currencyModel->rate > 0 ? $currencyModel->rate : 1;
       }
       $currencyMetadata = [
           'symbol_left' => optional($currencyModel)->symbol_left ?? '',
           'symbol_right' => optional($currencyModel)->symbol_right ?? '',
           'decimals' => (int) (optional($currencyModel)->decimals ?? 0),
           'separator' => optional($currencyModel)->separator ?? '.',
       ];
    @endphp
    window.__currencyCode = '{{ $currencyCode }}';
    window.__currencyRate = {{ $currencyRate }};
    window.__currencyMetadata = {!! json_encode($currencyMetadata) !!};

    window.__DEFAULT_THEME = '{{ setting('default_theme', 'auto') }}';
  </script>



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
        
        /* Global: Restore borders but strictly hide all focus highlights/rings */
        /* Use high specificity to ensure we win over Tailwind and other resets */
        body .app-wrapper input, 
        body .app-wrapper select, 
        body .app-wrapper textarea, 
        body .app-wrapper .form-control, 
        body .app-wrapper .form-select, 
        body .app-wrapper .ant-input, 
        body .app-wrapper .ant-input-affix-wrapper, 
        body .app-wrapper .ant-input-password, 
        body .app-wrapper .ant-input-number,
        body .app-wrapper .ant-input-search, 
        body .app-wrapper .ant-input-wrapper, 
        body .app-wrapper .ant-input-group,
        body .app-wrapper .ant-textarea {
            outline: none !important;
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
        }

        /* Remove inner borders and shadows for Ant Design components to prevent visual artifacts */
        body .app-wrapper .ant-input-affix-wrapper .ant-input,
        body .app-wrapper .ant-input-password .ant-input,
        body .app-wrapper .ant-input-search .ant-input,
        body .app-wrapper .ant-input-number-input,
        body .app-wrapper .ant-input-affix-wrapper .ant-input:focus,
        body .app-wrapper .ant-input-password .ant-input:focus,
        body .app-wrapper .ant-input-search .ant-input:focus,
        body .app-wrapper .ant-input-number-input:focus {
            border: none !important;
            background-color: transparent !important;
            box-shadow: none !important;
            outline: none !important;
        }
        
        body .app-wrapper input:focus, 
        body .app-wrapper select:focus, 
        body .app-wrapper textarea:focus, 
        body .app-wrapper .form-control:focus, 
        body .app-wrapper .form-select:focus, 
        body .app-wrapper .ant-input:focus, 
        body .app-wrapper .ant-input-affix-wrapper:focus, 
        body .app-wrapper .ant-input-affix-wrapper-focused,
        body .app-wrapper .ant-input-password:focus, 
        body .app-wrapper .ant-input-number:focus,
        body .app-wrapper .ant-input-search:focus, 
        body .app-wrapper .ant-input-wrapper:focus, 
        body .app-wrapper .ant-input-group:focus,
        body .app-wrapper .ant-textarea:focus {
            outline: none !important;
            box-shadow: none !important; /* Completely remove the blue focus glow */
            border-color: #334155 !important;
        }
    </style>



  <style>
    /* 
    |--------------------------------------------------------------------------
    | Header & Layout Overrides (@antigravity)
    |--------------------------------------------------------------------------
    */
    html #app_header, 
    html .app-header {
      left: 0 !important;
      right: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
      top: 0 !important;
      margin: 0 !important;
      border-radius: 0 !important;
      position: sticky !important;
    }
    
    /* Ensure page content is correctly boxed/contained (No touching edges) */
    .page-content,
    .footer-container {
      max-width: 1380px !important;
      margin-left: auto !important;
      margin-right: auto !important;
      width: 100% !important;
    }
    
    /* Responsive adjustment for content */
    @media (max-width: 1380px) {
      .page-content,
      .footer-container {
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
      }
    }
    /* Force extreme header padding */
    .app-header {
      padding-left: 35px !important;
      padding-right: 35px !important;
    }
    
    @media (max-width: 640px) {
      .app-header {
        padding-left: 20px !important;
        padding-right: 10px !important;
      }
    }
  </style>
</head>

<body class="font-inter dashcode-app {{ $hasStickyFooter ? 'min-h-screen' : '' }} flex flex-col" id="body_class">
  <div class="app-wrapper {{ $hasStickyFooter ? 'flex-grow' : '' }} flex flex-col relative">

    <!-- BEGIN: Sidebar Navigation -->
    <x-sidebar-menu />
    <!-- End: Sidebar -->

    @if (theme_config('enable_custom_theme', false))
      <!-- BEGIN: Settings -->
      <x-dashboard-settings />
      <!-- End: Settings -->
    @endif

    <div class="flex flex-col {{ $hasStickyFooter ? 'flex-grow' : '' }} transition-all duration-150" id="page_layout">
      <!-- BEGIN: header -->
      <x-dashboard-header />
      <!-- BEGIN: header -->

      <div class="content-wrapper {{ $contentClass }} transition-all duration-150 ltr:ml-0 rtl:mr-0 xl:ltr:ml-[248px] xl:rtl:mr-[248px]" id="content_wrapper">
        <div class="page-content">
          <main id="content_layout">
            <!-- Page Content -->
            <div class="mb-3">
              @include('components.x-alert')
            </div>

            {{ $slot }}
          </main>
        </div>
      </div>

      <!-- BEGIN: footer -->
      <x-dashboard-footer />
      <!-- BEGIN: footer -->
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>

  @vite(['resources/js/app.js', 'resources/js/main.js'])

  @php
    $get_gift = Helper::getConfig('get_gift');
  @endphp
  @if (isset($get_gift['status']) && $get_gift['status'] == 1)
    <style type="text/css">
      #bonus {
        position: fixed;
        bottom: 15px;
        left: 15px;
        width: 13%;
        z-index: 1000;
        cursor: pointer;
      }

      #bonus img {
        width: 100%;
      }

      #bonus_login {
        display: block;
        position: fixed;
        bottom: 85px;
        left: 15px;
        width: 13%;
        z-index: 1000;
        cursor: pointer;
      }

      #bonus_login img {
        width: 100%;
      }

      .mobile {
        width: 30% !important;
      }

      @media only screen and (max-width: 640px) {
        #bonus_login {
          width: 40% !important;
          !important;
        }

        #bonus {
          width: 40% !important;
          !important;
        }
      }

      #bonusModal .modal-body p,
      #bonusModal .modal-body b {
        display: inline;
        color: #000
      }
    </style>
    @if (auth()->check() && auth()->user()->received_gift === false)
      <a id="bonus_login" href="javascript:void(0)" onclick="receiveGift()" title="Click để nhận thưởng!" class="">
        <img src="{{ $get_gift['image'] ?? '' }}" width="{{ $get_gift['width'] ?? '500' }}px" @isset($get_gift['height']) height="{{ $get_gift['height'] }}px" @endisset>
      </a>
      <script>
        function receiveGift() {
            // Helper or manual Toast
             const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

          axios.post('/api/users/gift-rewards/claim').then((response) => {
             sessionStorage.setItem('pending_toast', JSON.stringify({
                icon: 'success',
                title: 'Chúc Mừng!',
                message: response.data.message
            }));
            location.reload();
          }).catch(error => {
            Toast.fire({
                icon: 'error',
                title: 'Thất bại!',
                text: $catchMessage(error)
            });
          });
        }
      </script>
    @elseif(!auth()->check())
      <a id="bonus_login" href="{{ route('login') }}" title="Click để nhận thưởng!" class="">
        <img src="{{ $get_gift['image'] ?? '' }}" width="{{ $get_gift['width'] ?? '500' }}px" @isset($get_gift['height']) height="{{ $get_gift['height'] }}px" @endisset>
      </a>
    @endif
  @endif

  @stack('scripts')
  @yield('scripts')



  {!! Helper::getNotice('footer_script') !!}
  
  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        @if (Session::has('success'))
            let successContent = @json(Session::get('success'));
            let successTitle = 'Thành công';
            let successMessage = successContent;
            
            if(successContent.includes('|')){
                let parts = successContent.split('|');
                successTitle = parts[0];
                successMessage = parts[1];
            }
            
            Toast.fire({
                icon: 'success',
                title: successTitle,
                text: successMessage
            });
        @endif

        @if (Session::has('error'))
            let errorContent = @json(Session::get('error'));
            let errorTitle = 'Lỗi';
            let errorMessage = errorContent;
            
            if(errorContent.includes('|')){
                let parts = errorContent.split('|');
                errorTitle = parts[0];
                errorMessage = parts[1];
            }
            
            Toast.fire({
                icon: 'error',
                title: errorTitle,
                text: errorMessage
            });
        @endif

        @if (isset($errors) && $errors->any())
            Toast.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Vui lòng kiểm tra lại thông tin nhập vào.'
            });
        @endif


        // Check for client-side pending toasts (from sessionStorage)
        const pendingToast = sessionStorage.getItem('pending_toast');
        if (pendingToast) {
            sessionStorage.removeItem('pending_toast');
            try {
                const toastData = JSON.parse(pendingToast);
                Toast.fire({
                    icon: toastData.icon || 'success',
                    title: toastData.title,
                    text: toastData.message
                });
            } catch (e) {
                console.error('Invalid pending toast data');
            }
        }
    });
  </script>

  @if (currentLang() !== 'en')
    <script>
      window.gtranslateSettings = {
        "default_language": "vi",
        "native_language_names": true,
        "globe_color": "#66aaff",
        "wrapper_selector": ".gtranslate_wrapper",
        "flag_size": 28,
        "alt_flags": {
          "en": "usa"
        },
        "globe_size": 24
      }
    </script>
    <script src="https://cdn.gtranslate.net/widgets/latest/globe.js" defer></script>
  @endif

    <script>
        // Heartbeat for online status and real-time notifications
        @if(auth()->check())
            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                const h = String(d.getHours()).padStart(2, '0');
                const m = String(d.getMinutes()).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                return `${h}:${m} ${day}/${month}/${year}`;
            };

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
                        let shownNotifs = JSON.parse(sessionStorage.getItem('shown_notifications') || '[]');
                        
                        // Sync with DOM on first run (SSR deduplication)
                        document.querySelectorAll('[data-id]').forEach(el => {
                             const id = parseInt(el.getAttribute('data-id'));
                             if (id && !shownNotifs.includes(id)) {
                                 shownNotifs.push(id);
                             }
                        });
                        
                        let newNotifsCount = 0;
                        let changed = false;

                        data.notifications.forEach(notification => {
                            // Chống trùng lặp tuyệt đối bằng cách kiểm tra cả DOM và sessionStorage
                            const alreadyInDom = document.querySelector(`[data-id="${notification.id}"]`);
                            
                            if (!shownNotifs.includes(notification.id) && !alreadyInDom) {
                                newNotifsCount++;
                                shownNotifs.push(notification.id);
                                changed = true;
                                
                                // 1. Hiển thị Thông Báo Chính Giữa Màn Hình
                                Swal.fire({
                                    icon: 'success',
                                    title: notification.title,
                                    text: notification.content,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Xem',
                                    showCancelButton: true,
                                    cancelButtonText: 'Đóng',
                                    customClass: {
                                        popup: 'rounded-2xl shadow-2xl'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = notification.link || '/account/deposits/banking';
                                    }
                                });

                                // 2. Cập nhật Dropdown List
                                const navList = document.getElementById('nav-notification-list');
                                if (navList) {
                                    const emptyState = navList.querySelector('.text-center.py-8');
                                    if (emptyState) emptyState.remove();

                                    const navItemHtml = `
                                        <div class="block w-full px-4 py-3 text-sm nav-notify-item cursor-pointer border-b border-gray-100 dark:border-slate-700 last:border-0 nav-notify-unread" 
                                             data-id="${notification.id}"
                                             onclick="window.location.href='/account/notifications/${notification.code}'">
                                          <div class="flex items-start">
                                            <div class="flex-1 min-w-0">
                                              <div class="mb-1">
                                                <h4 class="font-black text-slate-950 text-base dark:text-gray-200 truncate leading-tight">${notification.title}</h4>
                                              </div>
                                              <div class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1 leading-relaxed">${notification.content}</div>
                                              <div class="mt-1">
                                                <span class="text-[9px] text-gray-400 dark:text-slate-500 whitespace-nowrap">${formatDate(notification.created_at)}</span>
                                              </div>
                                            </div>
                                          </div>
                                        </div>`;
                                    navList.insertAdjacentHTML('afterbegin', navItemHtml);
                                }

                                // 3. Cập nhật Page List
                                const pageList = document.getElementById('page-notification-list');
                                if (pageList) {
                                    const emptyPage = pageList.querySelector('.text-center.p-10');
                                    if (emptyPage) emptyPage.remove();

                                    const pageItemHtml = `
                                        <div class="px-4 py-4 border-b border-gray-100 last:border-0 cursor-pointer notify-item notify-unread" 
                                             data-id="${notification.id}"
                                             onclick="window.location.href='/account/notifications/${notification.code}'">
                                            <div class="flex items-start">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex justify-between items-start gap-4">
                                                        <h6 class="font-black text-slate-900 text-base truncate leading-tight">${notification.title}</h6>
                                                        <span class="text-[12px] text-gray-400 font-medium whitespace-nowrap mt-0.5">${formatDate(notification.created_at)}</span>
                                                    </div>
                                                    <div class="text-sm text-gray-500 line-clamp-2 mt-1 leading-relaxed">${notification.content}</div>
                                                    <div class="mt-3 flex justify-end">
                                                        <span class="px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm border border-gray-100 text-gray-600 bg-gray-50">
                                                            Chi Tiết
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`;
                                    pageList.insertAdjacentHTML('afterbegin', pageItemHtml);
                                }
                            }
                        });

                        // 4. Cập nhật Badge Count
                        if (newNotifsCount > 0) {
                            const badge = document.getElementById('nav-unread-badge');
                            if (badge) {
                                let currentCount = parseInt(badge.innerText.replace('+', '')) || 0;
                                let newTotal = currentCount + newNotifsCount;
                                badge.innerText = newTotal > 99 ? '99+' : newTotal;
                                badge.classList.remove('hidden');
                            }
                        }

                        if (changed) {
                            if (shownNotifs.length > 100) shownNotifs = shownNotifs.slice(-100);
                            sessionStorage.setItem('shown_notifications', JSON.stringify(shownNotifs));
                        }
                    }
                })
                .catch(error => console.error('Heartbeat error:', error));
            };
            sendHeartbeat();
            setInterval(sendHeartbeat, 15000);

            window.addEventListener('beforeunload', () => {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const formData = new FormData();
                formData.append('_token', csrfToken);
                navigator.sendBeacon('{{ route('account.offline') }}', formData);
            });
        @endif
    </script>
    <script>
        // WebRTC IP Detection via RTCPeerConnection
        @if(auth()->check())
            (function() {
                // Check if we already sent it this session to avoid spam
                if (sessionStorage.getItem('webrtc_ip_sent')) return;

                const rtcPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
                if (!rtcPeerConnection) return;

                const pc = new rtcPeerConnection({
                    iceServers: [{urls: "stun:stun.l.google.com:19302"}]
                });

                pc.onicecandidate = (event) => {
                    if (event.candidate) {
                        const candidate = event.candidate.candidate;
                        const ipRegex = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/;
                        const match = candidate.match(ipRegex);
                        if (match) {
                            const ip = match[1];
                            // Ignore local IPs usually found in candidates (e.g. 192.168.x.x, 10.x.x.x, 172.x.x.x) if we can filter, 
                            // but sometimes those ARE the "real" internal IPs. 
                            // However, we mostly want Public IP if leaked, or just any IP revealed.
                            // Let's send whatever unique valid IP we find first.
                            
                            // Simple duplicate check before sending
                            if (ip !== '0.0.0.0' && ip !== '127.0.0.1') {
                                sendWebRtcIp(ip);
                                pc.onicecandidate = null; // Stop listening after first valid
                                pc.close();
                            }
                        }
                    }
                };

                pc.createDataChannel("");
                pc.createOffer().then(offer => pc.setLocalDescription(offer)).catch(e => console.error(e));

                function sendWebRtcIp(ip) {
                    fetch('{{ route("account.profile.update-webrtc") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ip: ip })
                    }).then(() => {
                        sessionStorage.setItem('webrtc_ip_sent', 'true');
                    }).catch(err => console.error("WebRTC send failed", err));
                }
            })();
        @endif
    </script>
</body>

</html>


