@props(['categories', 'serviceCategories' => null, 'type' => 'account', 'bconfig' => null, 'exclude' => []])

<style>
    /* Định nghĩa bố cục lưới 5 cột mặc định */
    .group-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
    }

    /* Điều chỉnh bố cục lưới 2 cột cho thiết bị di động */
    @media (max-width: 767px) {
        .group-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    /* Hiệu ứng hover cho tất cả các item */
    .group-item, .spin-quest-item, .rounded-lg.bg-white {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .group-item:hover, .spin-quest-item:hover, .rounded-lg.bg-white:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    }
</style>

@foreach ($categories as $index => $category)
    <!-- Debug: Category {{ $index }} - Class: {{ get_class($category) }} - Name: {{ $category->name }} - Groups: {{ $category instanceof \App\Models\ServiceCategory ? $category->resolved_groups->count() : 'N/A' }} -->
    <div class="space-y-4 mb-4">
        {{-- Header của danh mục --}}
        <div class="flex items-center space-x-3 mb-4">
            @php
                $slug = $category->slug ?? str()->slug($category->name); // Use slug if available, else generate from name
                
                // Determine the correct URL
                $categoryUrl = route('categories.show', ['slug' => $slug]);
                $customRoutes = [
                    'tai-khoan' => route('store.account.list'),
                    'tai-khoan-v2' => route('store.accountv2.list'),
                    'vat-pham' => route('store.item.list'),
                    'cay-thue' => route('store.boosting.list'),
                ];
                if (array_key_exists($slug, $customRoutes)) {
                    $categoryUrl = $customRoutes[$slug];
                }
                
                $isSpecial = in_array($slug, ['tai-khoan', 'tai-khoan-v2', 'vat-pham', 'cay-thue']);
            @endphp
            @if(!empty(trim($category->image)))
            <a @if(!$isSpecial) href="{{ $categoryUrl }}" @endif class="shrink-0 {{ $isSpecial ? 'cursor-default' : '' }}">
                <img src="{{ Helper::getValidImage($category->image) }}" 
                     alt="" 
                     class="w-10 h-10 md:w-12 md:h-12 object-contain">
            </a>
            @endif
            <div class="flex flex-col justify-center">
                <h1 class="text-xl md:text-3xl font-extrabold text-[#334155] dark:text-white tracking-tight">
                    <a @if(!$isSpecial) href="{{ $categoryUrl }}" @endif class="hover:text-primary transition-colors">
                        {{ $category->name }}
                    </a>
                </h1>
                @if (!empty($category->sub_name))
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 font-medium">
                        {{ $category->sub_name }}
                    </p>
                @endif
            </div>
        </div>

        @php
            // 🛒 UNIFIED GROUPS Section (SpinQuests + Product Groups)
            $unifiedItems = collect();
            $isServiceCategory = is_a($category, 'App\Models\ServiceCategory');
            $isRegularCategory = is_a($category, 'App\Models\Category');

            // 1. Add SpinQuests if applicable (Only for Regular Category model)
            if (($type === 'account' || $type === 'account_v2' || $type === 'all' || $type === 'item') && $isRegularCategory) {
                $spinQuests = $category->spinServices()->where('status', true)->get();
                foreach($spinQuests as $sq) {
                    $unifiedItems->push([
                        'type'        => 'spin',
                        'id'          => $sq->id,
                        'name'        => $sq->name,
                        'image'       => $sq->cover,
                        'price'       => $sq->price,
                        'play_times'  => $sq->play_times,
                        'redirectUrl' => route('games.spin-quest', ['id' => $sq->id]),
                        'badgeText'   => __t('Sẵn Sàng'),
                        'badgeClass'  => 'bg-green-500',
                        'in_stock'    => 1, // To avoid "Hết hàng" logic if any
                        'priority'    => $sq->priority ?? 0
                    ]);
                }

                // Robux
                if ($category->relationLoaded('robuxServices')) {
                    $robuxServices = $category->robuxServices;
                } else {
                    $robuxServices = $category->robuxServices()->where('status', true)->get();
                }
                foreach($robuxServices as $rb) {
                     $unifiedItems->push([
                        'type'        => 'robux',
                        'id'          => $rb->id,
                        'name'        => $rb->name,
                        'sub_name'    => $rb->sub_name, 
                        'image'       => $rb->image,
                        'display_mode'=> $rb->display_mode ?? 'grid',
                        'redirectUrl' => route('store.item', ['slug' => $rb->slug ?? str()->slug($rb->name)]),
                        'badgeText'   => __t('Sẵn Sàng'),
                        'badgeClass'  => 'bg-green-500',
                        'in_stock'    => 1,
                        'priority'    => $rb->priority ?? 0
                    ]);
                }
            }

            // 2. Add Product Groups or self (if spin)
            if ($isServiceCategory) {
                if ($category->product_type === 'spin') {
                    $groups = collect([$category]);
                } else {
                    $groups = $category->resolved_groups->where('status', true);
                }
            } else {
                if ($type === 'all' || !in_array($type, ['item', 'boosting', 'account_v2', 'account'])) {
                    // Merge all group types for "all" or undefined types
                    $groups = collect();
                    
                    if (!in_array('account', $exclude)) {
                        $groups = $groups->merge($category->accountGroups()->where('status', true)->get());
                    }
                    if (!in_array('account_v2', $exclude)) {
                        $groups = $groups->merge($category->accountV2Groups()->where('status', true)->get());
                    }
                    if (!in_array('item', $exclude)) {
                        $groups = $groups->merge($category->itemGroups()->where('status', true)->get());
                    }
                    if (!in_array('boosting', $exclude)) {
                        $groups = $groups->merge($category->gbGroups()->where('status', true)->get());
                    }
                } else {
                    // Filter by specific type
                    $relation = match($type) {
                        'item' => 'itemGroups',
                        'boosting' => 'gbGroups',
                        'account_v2' => 'accountV2Groups',
                        default => 'accountGroups',
                    };
                    $groups = $category->$relation;
                }
            }

            foreach($groups as $group) {
                $redirectUrl = '#';
                $gType = 'account';
                if ($group instanceof \App\Models\GroupV2) {
                    $redirectUrl = route('store.accountv2', ['slug' => $group->slug]);
                    $gType = 'account_v2';
                } elseif ($group instanceof \App\Models\ItemGroup) {
                    $redirectUrl = route('store.item', ['slug' => $group->slug]);
                    $gType = 'item';
                } elseif ($group instanceof \App\Models\GBGroup) {
                    $redirectUrl = route('store.boosting', ['slug' => $group->slug]);
                    $gType = 'boosting';
                } elseif ($group instanceof \App\Models\ServiceCategory) {
                    if (($group->product_type ?? '') === 'robux') {
                         $redirectUrl = route('store.item', ['slug' => $group->slug ?? str()->slug($group->name)]);
                         $gType = 'robux';
                    } else {
                         $redirectUrl = route('games.spin-quest', ['id' => $group->id]);
                         $gType = 'spin';
                    }
                } else {
                    // Account (Group)
                    $redirectUrl = route('store.account', ['slug' => $group->slug]);
                    $gType = 'account';
                }

                $badgeText = '';
                $badgeClass = '';
                if ($gType === 'account' || $gType === 'account_v2') {
                    if ($group->in_stock > 0) {
                        $badgeText = __t('Còn') . ': ' . number_format($group->in_stock);
                        $badgeClass = 'bg-green-500';
                    } else {
                        $badgeText = __t('Hết hàng');
                        $badgeClass = 'bg-red-500';
                    }
                } elseif ($gType === 'spin') {
                    $badgeText = __t('Sẵn Sàng');
                    $badgeClass = 'bg-green-500';
                } else {
                    $badgeText = __t('Sẵn Sàng');
                    $badgeClass = 'bg-green-500';
                }

                $unifiedItems->push([
                    'type'        => $gType,
                    'id'          => $group->id, // Ensured ID is present
                    'name'        => $group->name,
                    'sub_name'    => $group->sub_name,
                    'image'       => ($gType === 'spin' ? $group->cover : $group->image),
                    'in_stock'    => 1, // Default for non-account types
                    'sold'        => ($gType === 'spin' ? $group->play_times : ($group->sold_count ?? $group->sold ?? 0)),
                    'play_times'  => ($gType === 'spin' ? $group->play_times : 0),
                    'price'       => ($gType === 'spin' ? $group->price : null),
                    'redirectUrl' => $redirectUrl,
                    'badgeText'   => $badgeText,
                    'badgeClass'  => $badgeClass,
                    'priority'    => $group->priority ?? 0
                ]);
            }

            // Sort by priority
            $sortedItems = $unifiedItems->sortByDesc('priority');
        @endphp

        <div class="group-grid">
            @foreach ($sortedItems as $item)
                <div class="group-item relative overflow-hidden rounded-lg shadow">
                    
                    {{-- Image và tồn kho Badge (User Style: rounded-lg, lazyload) --}}
                    <div class="group-image relative rounded-lg overflow-hidden" style="aspect-ratio: 16/9;">
                        <a href="{{ $item['redirectUrl'] }}">
                            <img src="{{ asset('/images/svg/spinner.svg') }}"
                                data-src="{{ Helper::getValidImage($item['image']) }}"
                                class="lazyload w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                                alt="{{ $item['name'] }}">
                        </a>
                        
                        {{-- BADGE TỒN KHO/TRẠNG THÁI TRÊN ẢNH --}}
                        <div class="absolute bottom-0 right-0 text-white text-xs font-semibold p-1">
                            <span class="inline-flex items-center px-2 py-1 {{ $item['badgeClass'] }} rounded-lg">
                                {{ $item['badgeText'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Thông tin và Buttons (User Style: p-1) --}}
                    <div class="p-1"> 
                        <h2 class="text-sm md:text-lg font-bold text-truncate hover:whitespace-normal mb-0 leading-tight">
                            {{ $item['name'] }}
                        </h2>
                        
                        {{-- Sub Name (Red & Prominent) --}}
                        @if (isset($item['sub_name']) && $item['sub_name'])
                            <h4 class="text-xs md:text-sm font-bold text-red-600 text-truncate hover:whitespace-normal mb-0 leading-tight">
                                {{ $item['sub_name'] }}
                            </h4>
                        @endif

                        {{-- Số lượng đã bán --}}
                        @if ($item['type'] !== 'account' && $item['type'] !== 'account_v2' && ($item['sold'] ?? 0) > 0)
                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-1 mb-2">
                                Đã bán: {{ number_format($item['sold']) }}
                            </div>
                        @elseif(($item['type'] === 'spin' || $item['type'] === 'robux') && ($item['play_times'] ?? 0) > 0)
                             <div class="text-xs text-gray-600 dark:text-gray-300 mt-1 mb-2">
                                Đã chơi: {{ number_format($item['play_times']) }}
                            </div>
                        @endif
                        
                        {{-- Khu vực Nút (Buttons) --}}
                        <div class="flex justify-center mt-0 space-x-1">
                            @php
                                $btnImg = $bconfig['buy_button_img'] ?? asset('_assets/images/stores/view-all.gif');
                            @endphp
                            @if ($btnImg != '0')
                                <a href="{{ $item['redirectUrl'] }}" class="flex-shrink-0">
                                    <img src="{{ $btnImg }}" class="w-full max-h-12" alt="{{ __t('Xem Tất Cả') }}">
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach