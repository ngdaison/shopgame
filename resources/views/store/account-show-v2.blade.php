@section('title', $pageTitle)
<x-app-layout>
  <script>
    const ITEM_DATA = @json(['code' => $item->code]);
  </script>
  <section class="space-y-6">
    <div>
      <div class="card">
        <div class="card-body grid grid-cols-1 gap-x-6 p-6 md:grid-cols-2">
          <div class="mb-5 md:mb-0">
            <img src="{{ Helper::getValidImage($item->image) }}" 
                 class="h-[200px] w-full cursor-pointer rounded-lg md:h-[300px]" 
                 alt="">
          </div>
          <div class="flex flex-col justify-center space-y-3 text-center">
            <div class="mb-2">
              <h1 class="mb-1 text-[20px] md:text-[30px] ">{{ __t('Chi Tiết Tài khoản') }} #<span class="copy cursor-pointer text-red-600" data-clipboard-text="{{ $item->code }}">{{ $item->code }}</span></h1>
              <div class="mx-auto h-[3px] w-[200px] bg-primary"></div>
            </div>
            @if ($item->group)
              <h1 class="text-[12.4px] md:text-lg "><a href="{{ route('store.account', ['slug' => $item->group->slug]) }}">{{ $item->group->name }}</a></h1>
            @endif
            <div>
              @if ((int) $item->discount !== 0)
                <h2 class="text-[18px] md:text-[24px]">
                  Giá: <span class="text-primary">{{ $item->price_str }}</span> <span class="text-sm text-red-500">giảm {{ $item->discount }}%</span>
                </h2>
              @else
                <h2 class="text-[18px] md:text-[24px] text-primary">{{ __t('Giá') }}: {{ $item->price_str }}</h2>
              @endif
            </div>
            <div class="flex flex-wrap justify-center gap-3 md:flex-nowrap">
              @foreach ($item->highlights as $hl)
                @if (is_string($hl))
                  <div class="w-auto md:w-full">
                    <button class="btn btn-sm btn-primary inline-flex items-center capitalize">
                      <iconify-icon class="ltr:mr-1 rtl:ml-1" icon="heroicons-outline:star"></iconify-icon>
                      {{ $hl }}
                    </button>
                  </div>
                @elseif (isset($hl['name']) && isset($hl['value']))
                  <div class="w-auto md:w-full">
                    <button class="btn btn-sm btn-primary inline-flex items-center capitalize">
                      <iconify-icon class="ltr:mr-1 rtl:ml-1" icon="heroicons-outline:star"></iconify-icon>
                      {{ $hl['name'] ?? '-' }} : {{ $hl['value'] ?? '-' }}
                    </button>
                  </div>
                @elseif(isset($hl[0]))
                  <div class="w-auto md:w-full">
                    <button class="btn btn-sm btn-primary inline-flex items-center capitalize">
                      <iconify-icon class="ltr:mr-1 rtl:ml-1" icon="heroicons-outline:check-circle"></iconify-icon>
                      {{ $hl[0] }}
                    </button>
                  </div>
                @endif
              @endforeach
            </div>
            <div class="text-center  hover:scale-110 transition-all">
              @if ($item->group->game_type === 'thue-dot-kich')
                <div class="mt-5">
                  <a href="{{ Helper::getConfig('contact_info')['facebook'] ?? '#!' }}" target="_blank" class="btn btn-primary relative bg-cover bg-center bg-no-repeat "
                    style="background-image: url(/images/matrix.jpg)">
                    <i class="fas fa-share me-2"></i> {{ __t('Liên Hệ Admin') }}</a>
                </div>
              @else
                @if ($item->is_sold === true)
                  <button class="btn mt-5 btn-success relative bg-cover bg-center bg-no-repeat cursor-wait" style="background-image: url(/images/matrix.jpg)" disabled>
                    <i class="fas fa-shopping-cart me-2"></i> {{ __t('Đã Được Bán') }}
                  </button>
                @else
                  @php
                    // Calculate stock based on product type
                    if ($item->client_type === 'api' && $item->api_domain && $item->api_key && $item->api_id) {
                        // For API products, use the amount attribute which caches API stock
                        $stock = $item->amount;
                    } else {
                        // For normal products, count available resources
                        $stock = $item->resources()->where('buyer_name', null)->where('buyer_code', null)->count();
                    }
                    
                    $maxBuy = $stock;
                    if ($item->is_bulk > 1) {
                        $maxBuy = min($item->is_bulk, $stock);
                    } elseif ($item->is_bulk == 1) {
                        $maxBuy = 1;
                    }
                    
                    // Determine button state
                    $hasStock = $stock > 0;
                    $allowPreorder = $item->allow_preorder == 1;
                  @endphp

                  @if ($maxBuy > 1 && $hasStock)
                  <div class="mb-4 flex items-center justify-center gap-3">
                    <label class="font-bold">{{ __t('Số lượng') }}:</label>
                    <input type="number" id="buy-quantity" 
                           class="w-20 text-center border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none rounded-md" 
                           value="1" min="1" max="{{ $maxBuy }}">
                    <span class="text-xs text-slate-500">
                        (Max: {{ $maxBuy }})
                    </span>
                  </div>
                  @endif

                  @if ($hasStock)
                    {{-- In Stock: Show normal purchase button --}}
                    <button class="btn mt-5 btn-primary relative bg-cover bg-center bg-no-repeat " style="background-image: url(/images/matrix.jpg)" id="btnBuy" onclick="confirmBuy()">
                      <i class="fas fa-credit-card me-2"></i> {{ __t('Thanh Toán') }}</button>

                    <div class="mt-2 font-bold">Chỉ còn lại <span class="text-green-600">{{ $stock }}</span> tài khoản</div>
                  @else
                    {{-- Out of Stock --}}
                    @if ($allowPreorder)
                      {{-- Pre-order Enabled: Show pre-order button --}}
                      <button class="btn mt-5 btn-warning relative bg-cover bg-center bg-no-repeat " style="background-image: url(/images/matrix.jpg)" id="btnBuy" onclick="confirmBuy()">
                        <i class="fas fa-clock me-2"></i> {{ __t('Đặt Trước') }}</button>
                      
                      <div class="mt-2 font-bold text-orange-600">Hết hàng - Có thể đặt trước</div>
                    @else
                      {{-- Pre-order Disabled: Show out of stock button --}}
                      <button class="btn mt-5 btn-danger relative bg-cover bg-center bg-no-repeat cursor-not-allowed" style="background-image: url(/images/matrix.jpg)" disabled>
                        <i class="fas fa-times-circle me-2"></i> {{ __t('Hết Hàng') }}</button>
                      
                      <div class="mt-2 font-bold text-red-600">Tạm thời hết hàng</div>
                    @endif
                  @endif
                @endif
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
    <div>
      <div class="card">
        <div class="card-body flex flex-col p-6">
          <header class="-mx-6 mb-5 flex items-center border-b border-slate-100 px-6 pb-5 dark:border-slate-700">
            <div class="flex-1">
              <div class="card-title text-slate-900 dark:text-white">{{ __t('Chi tiết sản phẩm') }} - {{ $item->name }} : </div>
            </div>
          </header>
          <div class="card-text h-full">
            <div>
              <ul class="nav nav-pills flex items-center flex-wrap list-none pl-0 mb-6 space-x-4 justify-center" id="pills-tabHorizontal" role="tablist">
                <li class="nav-item text-center" role="presentation">
                  <a href="#pills-infomation"
                    class="nav-link block font-medium font-Inter text-sm leading-tight capitalize rounded-md px-6 py-3 focus:outline-none focus:ring-0 active dark:bg-slate-900 dark:text-slate-300"
                    id="pills-home-tabHorizontal" data-bs-toggle="pill" data-bs-target="#pills-infomation" role="tab" aria-controls="pills-infomation" aria-selected="true">Thông Tin</a>
                </li>
                @if ($item->group?->game_type === 'lien-minh')
                  <li class="nav-item text-center" role="presentation">
                    <a href="#pills-champions" class="nav-link block font-medium font-Inter text-sm leading-tight capitalize rounded-md px-6 py-3 focus:outline-none focus:ring-0 dark:bg-slate-900 dark:text-slate-300"
                      id="pills-profile-tabHorizontal" data-bs-toggle="pill" data-bs-target="#pills-champions" role="tab" aria-controls="pills-champions" aria-selected="false">
                      Tướng <span class="badge bg-danger-500 text-white">{{ count($item->list_champ) }}</span>
                    </a>
                  </li>
                  <li class="nav-item text-center" role="presentation">
                    <a href="#pills-skins" class="nav-link block font-medium font-Inter text-sm leading-tight capitalize rounded-md px-6 py-3 focus:outline-none focus:ring-0 dark:bg-slate-900 dark:text-slate-300 "
                      id="pills-contact-tabHorizontal" data-bs-toggle="pill" data-bs-target="#pills-skins" role="tab" aria-controls="pills-skins" aria-selected="false">
                      Trang Phục <span class="badge bg-danger-500 text-white">{{ count($item->list_skin) }}</span>
                    </a>
                  </li>
                @endif
              </ul>
              <div class="tab-content" id="pills-tabContentHorizontal">
                <div class="tab-pane fade show active" id="pills-infomation" role="tabpanel" aria-labelledby="pills-home-tabHorizontal">
                  <div class="space-y-5">
                    {{-- <div class="rounded-md bg-slate-800 px-6 py-[18px] text-sm font-normal text-white dark:bg-slate-900 dark:text-slate-300">
                      <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <iconify-icon class="text-2xl" icon="system-uicons:target"></iconify-icon>
                        <p class="font-Inter">
                          {!! $item->description !!}
                        </p>
                      </div>
                    </div> --}}

                    <div class="card border border-red-400 p-3">
                      <div class="card-body">
                        {!! $item->description !!}
                      </div>
                    </div>

                    @if (theme_config('show_all_account_img'))
                      <div class="grid grid-cols-1 gap-3">
                        @foreach ($item->list_image as $image)
                          <div class="gallery cursor-pointer">
                            <a href="{{ asset($image) }}" data-fancybox="gallery" data-caption="Image #{{ $loop->iteration }}">
                              <img class="w-full h-full rounded-sm lazyload" 
                                   src="{{ asset('/images/svg/spinner.svg') }}" 
                                   data-src="{{ asset($image) }}" 
                                   alt="">
                            </a>
                          </div>
                        @endforeach
                      </div>

                      @push('css')
                        <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
                      @endpush

                      @push('scripts')
                        <script>
                          Fancybox.bind("[data-fancybox]", {
                            // Your custom options
                          });
                        </script>
                      @endpush
                    @else
                      <div class="slider basic-carousel owl-carousel">
                        @foreach ($item->list_image as $image)
                          <img class="lazyload w-full lg:h-[600px]" 
                               src="{{ asset('/images/svg/spinner.svg') }}" 
                               data-src="{{ asset($image) }}" 
                               alt="">
                        @endforeach
                      </div>
                    @endif

                  </div>
                </div>
                @if ($item->group?->game_type === 'lien-minh')
                  <div class="tab-pane fade" id="pills-champions" role="tabpanel" aria-labelledby="pills-profile-tabHorizontal">
                    <div class="grid grid-cols-3 lg:grid-cols-8 gap-3">
                      @foreach ($item->list_champ as $champ)
                        <div class="text-center">
                          <img src="{{ asset('/images/svg/spinner.svg') }}" 
                               data-src="https://img.yourol06.com/img/DataLienMinh/assets/champions/portraits/{{ $champ['id'] }}.png" 
                               class="lazyload" width="100%"
                               alt="">
                          <span class="fw-bold font-bold text-green-700 text-sm md:text-lg">{{ $champ['name'] }}</span>
                        </div>
                      @endforeach
                    </div>
                  </div>
                  <div class="tab-pane fade" id="pills-skins" role="tabpanel" aria-labelledby="pills-contact-tabHorizontal">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                      @foreach ($item->list_skin as $skin)
                        <div class="text-center">
                          <img src="{{ asset('/images/svg/spinner.svg') }}" 
                               data-src="https://static.baocms.net/lien-minh/skins.php?id={{ $skin['id'] }}" 
                               class="lazyload" width="100%"
                               alt="">
                          <span class="fw-bold font-bold text-green-700 text-sm md:text-lg">{{ $skin['name'] }}</span>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="fullpage" onclick="this.style.display='none';"></div>
  </section>
  @push('scripts')
    @vite(['resources/js/plugins/owl.carousel.min.js'])
    <script type="module">
      // Basic Carousel
      $(".basic-carousel").owlCarousel({
        loop: true,
        nav: true,
        items: 1,
        lazyLoad: true,
        navText: [
          '<button class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-left text-white"></i></button>',
          '<button class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right text-white"></i></button>',
        ],
      });
    </script>

    @if ($item->is_sold !== true)
      <script>
        const IS_LOGGED_IN = @json(auth()->check());
        const confirmBuy = async () => {
          if (IS_LOGGED_IN === false) {
             // Helper function from app layout or manual Toast
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
            
            return Toast.fire({
              icon: 'warning',
              title: 'Cảnh báo',
              text: 'Vui lòng đăng nhập để tiếp tục!'
            }).then(() => {
                window.open('/login?path=/tai-khoan-v2/thong-tin/' + ITEM_DATA.code, '_self')
            });
          }

          let quantity = 1;
          const qtyInput = document.getElementById('buy-quantity');
          if (qtyInput) {
            quantity = parseInt(qtyInput.value) || 1;
          }
          
          ITEM_DATA.quantity = quantity;

          $('#btnBuy').html('<i class="fa fa-spinner fa-spin"></i> Đang Xử Lý...').prop('disabled', true);

          const confirm = await Swal.fire({
            title: 'Xác Nhận!',
            text: "Bạn đồng ý thanh toán cho " + quantity + " tài khoản này chứ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Thôi'
          })

          if (confirm.isConfirmed) {
            await buyItem(ITEM_DATA);
          } else {
            $('#btnBuy').removeClass('cursor-wait').html('<i class="fas fa-credit-card me-2"></i> Thanh Toán</button>').prop('disabled', false);
          }
        }

        const buyItem = async (item) => {
             // Check for Toast Mixin availability or define locally
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

          try {
            const {
              data: result
            } = await axios.post('/api/stores/accounts-v2/' + item.code + '/buy', {
                quantity: item.quantity || 1
            })

            // Store pending toast for next page
            sessionStorage.setItem('pending_toast', JSON.stringify({
                icon: 'success',
                title: 'Thành công',
                message: result.message || 'Mua tài khoản thành công!'
            }));

            // Redirect immediately
            window.open('/account/orders/accounts-v2/' + item.code, '_self')
          } catch (error) {
            Toast.fire({
                icon: 'error',
                title: 'Thất bại',
                text: $catchMessage(error)
            });
          } finally {
            $('#btnBuy').removeClass('cursor-wait').html('<i class="fas fa-credit-card me-2"></i> Thanh Toán</button>').prop('disabled', false);
          }
        }
      </script>
    @endif
  @endpush
</x-app-layout>
