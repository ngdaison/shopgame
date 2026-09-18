@props(['categories', 'type' => 'account', 'bconfig' => null])
@if ($bconfig && isset($bconfig['product_cover']))
  <style>
    .border-image-card {
      border-width: 5px;
      /* Adjust the border width as needed */
      border-style: solid;
      /* Ensure the border style is set */
      border-image-source: url("{{ $bconfig['product_cover'] }}");
      /* URL of the image used for the border */
      border-image-slice: 30;
      /* Adjust this value based on how the image should be sliced */
      border-image-repeat: stretch;
      /* Options: stretch, repeat, round */
      border-image-outset: 0;
      /* Optional: Adjusts how far the border extends beyond the element */
    }
  </style>
@endif
@foreach ($categories as $category)
  <div class="space-y-6 mb-10">
    <div class="text-center">
      <h1 class="text-xl md:text-4xl mb-1 text-primary"> <span class="category-name">{{ $category->name }}</span> </h1>
      <div class="h-1 bg-primary w-40 mx-auto"></div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
      @if ($type === 'account')
        @foreach (\App\Models\SpinQuest::whereNotNull('category_id')->where('category_id', $category->id)->where('status', true)->get() as $spinQuest)
          <div class="rounded-lg bg-white dark:bg-black-500 border border-primary hover:shadow-sm hover:shadow-primary-400 border-image-card">
            <div class="card-body">
              <a href="{{ route('games.spin-quest', ['id' => $spinQuest->id]) }}" class="block">
                <img src="{{ asset('/images/svg/spinner.svg') }}" data-src="{{ $spinQuest->cover }}" class="lazyload w-full h-28 md:h-44 lg:h-48 rounded-t-lg object-fit" alt="{{ $spinQuest->name }}" />
              </a>
              <div class="pt-3 pb-3 cursor-pointer">
                <div class="text-center">
                  <h2 class="text-sm md:text-lg font-bold text-truncate hover:whitespace-normal mb-2">
                    {{ $spinQuest->name }}
                  </h2>
                </div>
                <h4 class="text-xs md:text-sm lg:text-base font-bold text-center lg:flex lg:justify-around mb-2">
                  <div>
                    <i class="fas fa-credit-card me-1"></i>{{ __t('Giá') }} : <span class="text-green-500">{{ Helper::formatCurrency($spinQuest->price) }}</span>
                  </div>
                  <div>
                    <i class="fas fa-play me-1"></i> {{ __t('Đã chơi') }} : <span class="text-red-500">{{ number_format($spinQuest->play_times) }}</span> lần
                  </div>
                </h4>
                <div class="flex justify-center">
                  <a href="{{ route('games.spin-quest', ['id' => $spinQuest->id]) }}" class="btn btn-sm btn-primary"> <i class="fas fa-gift"></i> Chơi Ngay <i class="fas fa-gift"></i></a>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      @endif
      @foreach ($category->groups()->where('status', true)->orderBy('priority', 'desc')->get() as $group)
        @php
          $redirectUrl = '/tai-khoan/' . $group->slug;

          if ($type === 'item') {
              $redirectUrl = '/vat-pham/' . $group->slug;
          } elseif ($type === 'boosting') {
              $redirectUrl = '/cay-thue/' . $group->slug;
          } elseif ($type === 'account_v2') {
              $redirectUrl = '/tai-khoan-v2/' . $group->slug;
          }
        @endphp
        <div class="rounded-lg bg-white dark:bg-black-500 border border-primary hover:shadow-sm hover:shadow-primary-400 border-image-card">
          <div class="card-body">
            <a href="{{ $redirectUrl }}" class="block">
              <img src="{{ asset('/images/svg/spinner.svg') }}" data-src="{{ $group->image }}" class="lazyload w-full h-28 md:h-44 lg:h-48 rounded-t-lg object-fit border border-gray-300" alt="{{ $group->name }}" />
            </a>
            <div class="pt-3 pb-3 cursor-pointer">
              <div class="flex flex-col items-center">
                <div class="text-center w-full">
                  <h2 class="text-sm md:text-lg font-bold text-truncate hover:whitespace-normal mb-2">{{ $group->name }}</h2>
                  @if ($type === 'account')
                    @if ($group->in_stock > 0)
                      <h4 class="text-xs md:text-sm lg:text-base font-bold">
                        @if ($bconfig['product_info_type'] ?? false)
                          {{ __t('Đã Bán') }} <span class="text-primary-500">{{ $group->sold_count }}</span> <span class="hidden md:inline-block">{{ __t('Nick') }}</span>
                          <span>|</span>
                        @endif
                        {{ __t('Còn') }} <span class="text-red-500">{{ $group->in_stock }}</span> <span class="hidden md:inline-block">{{ __t('Nick') }}</span>
                      </h4>
                    @elseif($group->total_item !== 0)
                      <h4 class="text-xs md:text-sm lg:text-base font-bold">{{ __t('Bán Hết') }} <span class="text-red-500">{{ $group->sold_count }}</span> Nick</h4>
                    @else
                      <h4 class="text-xs md:text-sm lg:text-base font-bold">{{ __t('Trạng Thái') }}: <span class="text-danger-500">{{ __t('Hết Hàng') }}</span></h4>
                    @endif
                  @elseif($type === 'account_v2')
                    <h4 class="text-xs md:text-sm lg:text-base font-bold">
                      @if ($bconfig['product_info_type'] ?? false)
                        {{ __t('Đã Bán') }} <span class="text-primary-500">{{ $group->sold }}</span> <span class="hidden md:inline-block">{{ __t('Nick') }}</span>
                        <span>|</span>
                      @endif
                      {{ __t('Còn') }} <span class="text-red-500">{{ $group->in_stock }}</span> <span class="hidden md:inline-block">{{ __t('Nick') }}</span>
                    </h4>
                    {{-- <h4 class="text-xs md:text-sm lg:text-base font-bold">
                      {{ __t('Đã Bán') }} <span class="text-green-500">{{ $group->sold }}</span> | {{ __t('Còn lại') }} <span class="text-red-500">{{ $group->in_stock }}</span>
                    </h4> --}}
                  @else
                    <h4 class="text-xs md:text-sm lg:text-base font-bold">{{ __t('Trạng Thái') }}: <span class="text-green-500">{{ __t('Sẵn Sàng') }}</span></h4>
                  @endif
                </div>
                <div class="flex justify-center mt-2">
                  <a href="{{ $redirectUrl }}">
                    <img src="{{ $bconfig['buy_button_img'] ?? asset('_assets/images/stores/view-all.gif') }}" class="w-full max-h-12" alt="{{ __t('Xem Tất Cả') }}">
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endforeach
