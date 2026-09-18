<x-app-layout>
  @push('css')
    <link rel="stylesheet" type="text/css"
          href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

    <style>
      /* Ép 2 tab luôn cao bằng nhau và full khung */
      .tab-content, .tab-pane {
        height: 100%;
      }

      /* Hide pin groups but maintain natural height until slick is initialized */
      .slick-responsive:not(.slick-initialized) {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 0.5rem;
      }

      @media (min-width: 768px) {
        .slick-responsive:not(.slick-initialized) {
          grid-template-columns: repeat(6, 1fr) !important;
        }
      }

      /* Hide extra slides to match first row height precisely */
      .slick-responsive:not(.slick-initialized)>div:nth-child(n+4) {
        display: none !important;
      }

      @media (min-width: 768px) {
        .slick-responsive:not(.slick-initialized)>div:nth-child(n+7) {
          display: none !important;
        }
      }

      /* Wrapper for smooth transition */
      .pin-wrapper {
        opacity: 0;
        transition: opacity 0.5s ease;
      }
      .pin-wrapper.show {
        opacity: 1;
      }

      .tab-pane {
        display: none;
      }

      .tab-pane.active,
      .tab-pane.show.active {
        display: block;
      }

      /* Cuộn mượt trong tab */
      .tab-pane .overflow-y-auto {
        scrollbar-width: thin;
        scrollbar-color: #999 transparent;
      }

      /* Custom Responsive Banner Logic */
      .responsive-banner {
          height: auto; /* Mobile default: natural height */
      }
      .responsive-banner-img {
          height: auto;
          object-fit: contain; /* Mobile: Show full image */
      }
      @media (min-width: 1024px) {
          .responsive-banner {
              height: 400px !important; /* Desktop: Fixed height */
          }
          .responsive-banner-img {
              height: 100% !important;
              object-fit: cover !important; /* Desktop: Crop to fill */
          }
      }
      /* Tabs styling */
      #tabs-tab .nav-link {
        color: rgba(255, 255, 255, 0.7) !important;
        border: none !important;
        background: transparent !important;
      }
      #tabs-tab .nav-link.active {
        color: #fff !important;
        border: none !important;
      }
      #tabs-tab .nav-link i {
        color: inherit !important;
      }
      .swal2-container,
      .swal2-top-overlay {
        z-index: 3000 !important;
      }

      /* Modern Seamless Marquee Animation */
      .marquee-wrapper {
        position: relative;
        width: 100%;
        overflow: hidden;
      }
      .marquee-content {
        display: inline-flex;
        white-space: nowrap;
        animation: marquee-slide 25s linear infinite; 
        font-weight: 500;
        color: #374151;
        padding-left: 100%; /* Đảm bảo chữ bắt đầu từ bên phải */
      }
      .marquee-item {
        flex-shrink: 0;
        padding-right: 5px; /* Almost no gap, about 1 space */
      }
      /* Ensure no zoom effect on marquee bars while allowing text to slide */
      .no-hover-effect {
        transition: transform 0s !important;
        transform: none !important;
      }
      .no-hover-effect:active, 
      .no-hover-effect:focus, 
      .no-hover-effect:hover {
        transform: none !important;
        background-color: white !important;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1) !important;
      }
      /* .marquee-wrapper:hover .marquee-content {
        animation-play-state: paused;
      } */
      @keyframes marquee-slide {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
      }
    </style>
  @endpush

  {{-- ẢNH BÌA & TOP NẠP THÁNG + THÔNG BÁO --}}
  <section style="margin-bottom: 20px">

    @php
      $bconfig = Helper::getConfig('theme_custom');
      $boxHeight = $bconfig['box_height'] ?? 800;
      
      // Try to get domain-level youtube first (no global fallback)
      $youtube = Helper::branding('youtube_id', null, false);
      $banner = Helper::branding('banner');
      
      // If domain has no youtube ID, we show the banner (which can be domain-specific or global)
      // This prevents a global youtube ID from overriding a domain-specific banner.
      if (empty($youtube)) {
          $youtube = null;
      }
    @endphp

    @if (theme_config('show_banner', true))
      <div class="grid gap-3 sm:grid-cols-1 lg:grid-cols-3 items-stretch">

        {{-- Banner / Video bên trái --}}
        <div class="lg:col-span-2">
          {{-- Wrapper for banner --}}
          <div class="w-full overflow-hidden rounded-lg bg-black responsive-banner" style="max-width: 1800px;">
            @if(!empty($youtube))
              <iframe class="rounded-lg w-full h-full object-cover aspect-video lg:aspect-auto"
                src="https://www.youtube.com/embed/{{ $youtube }}?autoplay=1&mute=1&loop=1&playlist={{ $youtube }}"
                title="Video Intro" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
            @else
                <img src="{{ Helper::getValidImage($banner) }}" 
                     class="w-full rounded-lg responsive-banner-img" alt="">
            @endif
          </div>
        </div>

        {{-- Tabs bên phải --}}
        <div class="flex">
          <div class="card shadow-lg w-full h-full flex flex-col rounded-lg overflow-hidden shrink-0">

            {{-- Nút TAB --}}
            <ul class="nav nav-tabs flex justify-center border-b-0 rounded-t-lg pt-2"
                style="background-color: var(--primary-color)" id="tabs-tab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active text-white font-semibold px-4 py-2"
                        id="tab-thongbao" data-bs-toggle="pill" data-bs-target="#pane-thongbao"
                        type="button" role="tab" aria-controls="pane-thongbao" aria-selected="true">
                  Thông báo
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link text-white font-semibold px-4 py-2"
                        id="tab-topnap" data-bs-toggle="pill" data-bs-target="#pane-topnap"
                        type="button" role="tab" aria-controls="pane-topnap" aria-selected="false">
                  <i class="fa-solid fa-ranking-star mr-1"></i> Top nạp tháng {{ date('m') }}
                </button>
              </li>
            </ul>

            {{-- NỘI DUNG TAB --}}
            <div class="card-body p-3 flex-1 overflow-hidden bg-white">
              <div class="tab-content h-full" id="tabs-tabContent">

                {{-- TAB 1: THÔNG BÁO --}}
                <div class="tab-pane fade show active" id="pane-thongbao" role="tabpanel" aria-labelledby="tab-topnap" style="height: 300px; overflow-y: auto;">
                  <div class="h-full flex flex-col">
                    @if ($homeNotice = Helper::branding('notice_homepage'))
                      <div class="border-primary rounded-lg text-center flex-1 flex flex-col justify-start p-0">
                        <div class="ws-text-center break-words flex-1 overflow-y-auto">
                          {!! $homeNotice !!}
                        </div>
                      </div>
                    @else
                      <p class="text-center text-gray-500 my-auto">{{ __('Không có thông báo nào') }}</p>
                    @endif
                  </div>
                </div>

                {{-- TAB 2: TOP NẠP --}}
                <div class="tab-pane fade" id="pane-topnap" role="tabpanel" aria-labelledby="tab-topnap" style="height: 300px; overflow-y: auto;">
                  <div class="h-full flex flex-col">
                    @if (!count($top10UserDeposit))
                      <div class="flex-1 flex justify-center items-center text-gray-500">
                        <h6>{{ __t('Chưa có dữ liệu') }}</h6>
                      </div>
                    @else
                      <ul class="space-y-2 overflow-y-auto flex-1">
                        @foreach ($top10UserDeposit as $deposit)
                          <li class="">
                            <button class="btn btn-outline-primary btn-sm w-full">
                              <div class="flex justify-between font-bold">
                                <span>{{ $loop->iteration }}. {{ !empty($deposit->user_fullname) ? $deposit->user_fullname : Helper::hideUsername($deposit->username) }}</span>
                                <span class="text-danger-600">
                                  {{ $deposit->prefix }}{{ Helper::formatCurrency($deposit->total) }}
                                </span>
                              </div>
                            </button>
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif
  </section>

  <section style="margin-bottom: 30px">
    @if (theme_config('show_thongbao', true))
      <div class="mt-4 flex items-center rounded-lg bg-white px-3 py-3 shadow-lg no-hover-effect">
        <div class="marquee-wrapper">
          <div class="marquee-content">
            @php
              $thongbao = '<span class="text-danger-600 font-bold">[' . strtoupper(Helper::getDomain()) . ']</span> ' . (Helper::branding('dashboard_text_1') ?? '-');
            @endphp
            <div class="marquee-item font-bold">{!! $thongbao !!}</div>
          </div>
        </div>
      </div>
    @endif
    @if (theme_config('show_lsmua', true))
      <div class="mt-4 flex items-center rounded-lg bg-white px-3 py-3 shadow-lg overflow-hidden no-hover-effect">
        <i class="fas fa-shopping-cart text-green-600 me-2"></i>
        <div class="marquee-wrapper">
          <div class="marquee-content">
            <div class="marquee-item">{!! $listAccountBuy !!}</div>
          </div>
        </div>
      </div>
    @endif
  </section>

  @if ($pin_groups->count() > 0)
    @if (theme_config('pin_type', 'slide') === 'slide')
      <div id="section-pin-groups" class="pin-wrapper">
        <div class="slick-responsive">
        @foreach ($pin_groups as $pin)
          <div class="py-2 hover:bg-white hover:text-red-500 transition duration-200 rounded-lg">
            <a href="{{ $pin->link }}" target="{{ $pin->open_type }}" class="flex items-center flex-col justify-center text-center">
              <img style="border-radius:15px;" 
                   class="inline-block h-16 lg:h-18" 
                   alt="" 
                   src="{{ Helper::getValidImage($pin->image) }}">
              <span class="mt-2 font-semibold text-sm lg:text-base whitespace-normal text-center">{{ $pin->name }}</span>
            </a>
          </div>
        @endforeach
        </div>
      </div>
    @elseif(theme_config('pin_type') === 'grid')
      <section class="section-product">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          @foreach ($pin_groups as $pin)
            <div>
              <div class="py-2 col-span-4 md:col-span-3 lg:col-span-2 hover:bg-white hover:text-red-500 transition duration-200 rounded-lg">
                <a href="{{ $pin->link }}" target="{{ $pin->open_type }}" class="flex items-center flex-col justify-center text-center">
                  <img style="border-radius:15px;" 
                       class="inline-block h-16 lg:h-18" 
                       alt="" 
                       src="{{ Helper::getValidImage($pin->image) }}">
                  <span class="mt-2 font-semibold text-sm lg:text-base whitespace-normal">{{ $pin->name }}</span>
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @endif
  @endif





  {{-- CHUYÊN MỤC DỊCH VỤ (Service Categories - Always at Top) --}}
  @if (isset($serviceCategories) && $serviceCategories->count() > 0)
    <section class="section-product">
      <x-category-list :categories="$serviceCategories" :bconfig="$bconfig" type="all" />
    </section>
  @endif

  {{-- DỊCH VỤ ROBUX --}}
  @if (isset($robuxServices) && $robuxServices->count() > 0)
    <section class="section-product mb-8">
        <div class="container mx-auto">
            {{-- Header Style Matching Screenshot --}}
            <div class="flex items-center mb-3">
                <div class="flex items-center w-full">
                    <span class="text-sm font-bold text-gray-400 uppercase tracking-wider mr-4 whitespace-nowrap">
                        {{ $robuxTitle }}
                    </span>
                    <div class="flex-grow border-t border-gray-800 dark:border-gray-700"></div>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($robuxServices as $item)
                    <div class="group-item relative overflow-hidden rounded bg-[#111827] border border-gray-800 hover:border-blue-600 transition-colors duration-200">
                        <div class="flex items-center p-3">
                            {{-- Image/Icon --}}
                            <div class="flex-shrink-0 mr-4">
                                <a href="{{ route('store.item', ['slug' => $item->slug]) }}">
                                    <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center p-2">
                                         <img src="{{ asset('/images/svg/spinner.svg') }}"
                                             data-src="{{ Helper::getValidImage($item->image) }}"
                                             class="lazyload w-full h-full object-contain"
                                             alt="{{ $item->name }}">
                                    </div>
                                </a>
                            </div>

                            {{-- Content --}}
                            <div class="flex-grow min-w-0 flex flex-col justify-center">
                                <h2 class="text-base md:text-lg font-bold text-gray-100 mb-0 leading-tight uppercase font-heading">
                                    <a href="{{ route('store.item', ['slug' => $item->slug]) }}" class="hover:text-blue-500 transition-colors">
                                        {{ $item->name }}
                                    </a>
                                </h2>
                            </div>

                            {{-- Button --}}
                            <div class="flex-shrink-0 ml-4">
                                <a href="{{ route('store.item', ['slug' => $item->slug]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded transition-colors duration-200" style="min-width: 100px;">
                                    {{ __t('Mua ngay') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
  @endif

  {{-- DANH MỤC SẢN PHẨM --}}
  @if ($categories->count() > 0)
    <section class="section-product">
      <x-category-list :categories="$categories" :bconfig="$bconfig" type="all" />
    </section>
  @endif

@if (Helper::branding('notice_featured_homepage'))
  @push('scripts')
    <script type="module">
      $(document).ready(() => {
        // Kiểm tra trong localStorage có lưu thời gian tắt chưa
        let dismissUntil = localStorage.getItem("dismiss_modal_dashboard");
        let now = new Date().getTime();
 
        if (!dismissUntil || now > parseInt(dismissUntil)) {
          Swal.fire({
            position: "top",
            title: '<div style="font-size: 20px">{{ __t('Thông Báo Mới') }}</div>',
            html: {!! json_encode(Helper::branding('notice_featured_homepage')) !!},
            showDenyButton: true,
            confirmButtonText: "Đóng",
            denyButtonText: "Tắt trong 2 giờ",
            customClass: {
              container: 'swal2-top-overlay'
            },
            didOpen: () => {
              // Force z-index via inline style as fallback
              const container = document.querySelector('.swal2-container');
              if (container) {
                container.style.setProperty('z-index', '3000', 'important');
              }
            }
          }).then((result) => {
            if (result.isDenied) {
              // Lưu thời gian hết hạn sau 2 giờ
              let expireTime = new Date().getTime() + (2 * 60 * 60 * 1000);
              localStorage.setItem("dismiss_modal_dashboard", expireTime);
            }
          });
        }
      });
    </script>
  @endpush
@endif

  @section('scripts')
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>

      let $slickEl = $('.slick-responsive');
      
      $slickEl.on('init', function(event, slick){
          $('#section-pin-groups').addClass('show');
      });

      $slickEl.slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        prevArrow: '',
        nextArrow: '',
        responsive: [{
          breakpoint: 768, // kích thước màn hình nhỏ hơn hoặc bằng 768px
          settings: {
            slidesToShow: 3
          }
        }]
      });
    </script>
  @endsection
</x-app-layout>
