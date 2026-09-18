@section('title', __t($pageTitle))
<x-app-layout>
  <div class="order-details-container space-y-4 text-gray-800 dark:text-gray-200">
    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Left Column: Transaction Info -->
        <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-5 shadow-sm h-full">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-200 dark:border-[#2d2d3a]">
                <div class="text-lg font-bold text-gray-900 dark:text-white uppercase">
                    {{ __t('Thông Tin Giao Dịch') }} - <span class="text-danger-500">{{ $account->buyer_code ?? $account->code ?? '-' }}</span>
                </div>
            </div>

            @php
                $isV1 = ($account instanceof \App\Models\ListItem);
                $isV2 = ($account instanceof \App\Models\ResourceV2) || ($account instanceof \App\Models\ResourceV2O);
                $isBulk = ($account instanceof \App\Models\BulkOrder) || ($isV2 && $account->is_bulk);
                
                $typeLabel = 'Tài Khoản';
                if ($isBulk) $typeLabel = 'Acc v2 Bulk Orders';
                elseif ($isV2) $typeLabel = 'Acc v2';
                elseif ($isV1) $typeLabel = 'Acc v1';
                
                $warrantyHours = $account->warranty_hours ?? ($account->group?->warranty_hours ?? ($account->parent?->group?->warranty_hours ?? null));
                $shouldShowWarranty = (($warrantyHours ?? 0) > 0 || !empty($account->warranty_expire_at));
            @endphp
            
            <div class="space-y-3">
                <!-- Row 1: Product & Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Sản phẩm:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white uppercase">{{ ($isBulk && $account instanceof \App\Models\BulkOrder && $account->orders->first()?->parent?->name) ? $account->orders->first()->parent->name : ($account->name ?? ($account->parent?->name ?? ($account->group?->name ?? ($account->parent?->group?->name ?? ($account->product_name ?? '-'))))) }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Loại:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">{{ $typeLabel }}</div>
                    </div>
                </div>

                <!-- Row 2: Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Ngày mua:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">
                            @if ($account->buyer_date)
                                {{ \Carbon\Carbon::parse($account->buyer_date)->format('H:i:s - d/m/Y') }}
                            @elseif ($account->created_at)
                                {{ $account->created_at->format('H:i:s - d/m/Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Cập nhật:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">{{ $account->updated_at?->format('H:i:s - d/m/Y') ?? '-' }}</div>
                    </div>
                </div>
                
                <!-- Row 3: Warranty -->
                @if($shouldShowWarranty)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Bảo hành:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white flex items-center gap-2">
                             {{ $warrantyHours ? $warrantyHours . 'h' : '0' }}
                        </div>
                    </div>
                     <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Hết hạn:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white flex items-center gap-2">
                            @if(($account->order_status ?? 'Completed') === 'Completed')
                                @if (!empty($account->warranty_expire_at))
                                    {{ \Carbon\Carbon::parse($account->warranty_expire_at)->format('H:i:s - d/m/Y') }}
                                @else
                                    -
                                @endif
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Row 4: Status & Payment -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Trạng thái:') }}</div>
                        <div>
                             @php
                                // Check both order_status and status fields for compatibility with Bulk Orders
                                $rawStatus = $account->order_status ?? $account->status ?? 'Completed';
                                $status = $rawStatus;
                                // Map Pending to Processing for display (no "Chờ xử lý" status)
                                if (in_array(strtolower($status), ['pending', '0', 'assigned'])) $status = 'Processing';
                                // Normalize status values
                                if (in_array(strtolower($status), ['completed', 'success', '1'])) $status = 'Completed';
                                if (in_array(strtolower($status), ['processing'])) $status = 'Processing';
                                if (in_array(strtolower($status), ['cancelled', 'canceled', 'error', '2'])) $status = 'Cancelled';
                                $statusMap = [
                                    'Completed' => ['text' => 'Hoàn thành', 'color' => 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20'],
                                    'Processing' => ['text' => 'Đang xử lý', 'color' => 'text-blue-500 bg-blue-500/10 border-blue-500/20'],
                                    'Cancelled' => ['text' => 'Đã hoàn tiền', 'color' => 'text-amber-500 bg-amber-500/10 border-amber-500/20'],
                                ];
                                $st = $statusMap[$status] ?? $statusMap['Completed'];
                            @endphp
                            <span class="px-2 py-1 rounded text-sm font-bold border {{ $st['color'] }}">
                                {{ $st['text'] }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Thanh toán:') }}</div>
                        <div class="text-base font-bold text-emerald-500">{{ Helper::formatCurrency($account->payment ?? ($account->buyer_paym ?? 0)) }}</div>
                    </div>
                </div>
            </div>

            @php
                $isWarrantyActive = false;
                if (($account->order_status ?? 'Completed') === 'Completed') {
                    if (!empty($account->warranty_expire_at)) {
                        $isWarrantyActive = \Carbon\Carbon::parse($account->warranty_expire_at)->gt(now());
                    } elseif (($warrantyHours ?? 0) == 0) {
                        $baseTime = $account->buyer_date ?? $account->created_at;
                        $isWarrantyActive = \Carbon\Carbon::parse($baseTime)->addDay()->gt(now());
                    }
                }

                $hasWarrantyTicket = false;
                if ($isWarrantyActive) {
                    $hasWarrantyTicket = \App\Models\Ticket::where('user_id', auth()->id())
                        ->where('title', 'like', "%{$account->buyer_code}%")
                        ->exists();
                }
            @endphp

            @if($isWarrantyActive && !$hasWarrantyTicket)
                <div class="mt-3" id="warranty_section">
                    <div id="app-warranty-request" 
                        data-code="{{ $account->buyer_code }}" 
                        data-category="{{ $isV1 ? 'Bảo hành tài khoản' : 'Bảo hành tài khoản v2' }}" 
                        data-submit-url="{{ route('account.tickets.store') }}" 
                        data-redirect-url="{{ route('account.tickets.show', ['code' => '__code__']) }}">
                    </div>
                </div>
            @elseif($hasWarrantyTicket)
                <div class="mt-3">
                    <div class="w-full py-2 px-4 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold rounded-lg flex items-center justify-center gap-2 border border-emerald-500/20">
                        <iconify-icon icon="heroicons-outline:check-circle" class="text-xl"></iconify-icon>
                        {{ __t('Đã gửi yêu cầu bảo hành') }}
                    </div>
                </div>
            @endif
        </div>

        @if($isBulk)
            <!-- Right Column: Order Status Stepper (for Bulk) -->
            <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-6 shadow-sm h-full">
                <div class="text-sm font-bold pb-2 mb-2 border-b border-gray-100 dark:border-[#2d2f36] text-gray-900 dark:text-white uppercase">{{ __t('Trạng thái đơn hàng') }}</div>
                
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between relative">
                    @php
                        $rawOrderStatus = $account->order_status ?? $account->status ?? 'Completed';
                        $orderStatus = $rawOrderStatus;
                        // Normalize status values
                        if (in_array(strtolower($orderStatus), ['pending', '0', 'assigned'])) $orderStatus = 'Processing';
                        if (in_array(strtolower($orderStatus), ['completed', 'success', '1'])) $orderStatus = 'Completed';
                        if (in_array(strtolower($orderStatus), ['processing'])) $orderStatus = 'Processing';
                        if (in_array(strtolower($orderStatus), ['cancelled', 'canceled', 'error', '2'])) $orderStatus = 'Cancelled';
                        
                        $steps = [
                            ['key' => 'Payment', 'title' => 'Thanh toán', 'desc' => 'Đơn hàng đã được thanh toán', 'icon' => 'heroicons-outline:document-text'],
                            ['key' => 'Processing', 'title' => 'Đang xử lý', 'desc' => 'Hệ thống đang xử lý đơn hàng của bạn', 'icon' => 'heroicons-outline:shield-check'],
                            ['key' => 'Completed', 'title' => 'Hoàn thành', 'desc' => 'Đơn hàng đã hoàn tất', 'icon' => 'heroicons-outline:check-circle'],
                        ];
                        
                        $currentIdx = 0;
                        if ($orderStatus == 'Processing') $currentIdx = 1;
                        if ($orderStatus == 'Completed') $currentIdx = 2;
                        if ($orderStatus == 'Cancelled') $currentIdx = -1;
                    @endphp

                    @foreach($steps as $index => $step)
                        <div class="flex items-start lg:items-center group relative {{ !$loop->last ? 'lg:flex-1' : '' }}">
                            @if(!$loop->last)
                            <div class="absolute left-6 top-6 -bottom-1 w-0.5 bg-gray-200 dark:bg-[#2d2f36] lg:hidden"></div>
                            @endif

                            <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center border-2 transition-colors duration-200 relative z-10 
                                {{ $index <= $currentIdx ? 'bg-blue-600/10 border-blue-600 text-blue-600' : 'bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-[#2d2f36] text-gray-400 dark:text-gray-600' }}">
                                <iconify-icon icon="{{ $step['icon'] }}" class="text-2xl"></iconify-icon>
                            </div>

                            <div class="ml-4 lg:ml-3 pb-8 lg:pb-0">
                                <div class="text-base font-bold {{ $index <= $currentIdx ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ __t($step['title']) }}
                                </div>
                                <div class="text-sm mt-1 {{ $index <= $currentIdx ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ __t($step['desc']) }}
                                </div>
                            </div>

                            @if(!$loop->last)
                                <div class="hidden lg:block flex-grow h-px bg-gray-200 dark:bg-[#2d2f36] mx-4"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Right Column: Order Info (for Single Account) -->
            <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-5 shadow-sm h-full">
                <div class="mb-5 pb-3 border-b border-gray-200 dark:border-[#2d2d3a]">
                    <div class="text-lg font-bold text-gray-900 dark:text-white uppercase">
                        {{ __t('Thông Tin Đơn Hàng') }}
                    </div>
                </div>

                @php
                     $displayUser = $account->username;
                     $displayPass = '';
                     $displayExtra = $account->extra_data;
                     
                     if (empty($account->password) && strpos($account->username, ':') !== false) {
                         $parts = explode(':', $account->username, 3);
                         $displayUser = $parts[0] ?? '';
                         $displayPass = $parts[1] ?? '';
                         $rest = isset($parts[2]) ? $parts[2] : '';
                         if ($rest) {
                             $displayExtra = $displayExtra ? $rest . '|' . $displayExtra : $rest;
                         }
                     } else {
                         if (!empty($account->password)) {
                             $displayPass = $account->password;
                         }
                     }
                @endphp
                
                <div class="space-y-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Tài khoản') }}</span>
                            <div class="flex items-center h-[32px]">
                                <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden h-full">
                                    <input type="text" class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px]" style="border: none !important; outline: none !important; box-shadow: none !important;" value="{{ $displayUser }}" readonly>
                                    <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full border-l border-gray-200 dark:border-[#3f3f50]" onclick="copyToClipboard('{{ $displayUser }}', 'Tài khoản')">
                                        <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-lg"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Mật khẩu') }}</span>
                            <div class="flex items-center h-[32px]">
                                <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden h-full">
                                    <input type="password" class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px]" style="border: none !important; outline: none !important; box-shadow: none !important;" id="password_field" value="{{ $displayPass }}" readonly>
                                    <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full" onclick="togglePasswordVisibility()">
                                        <iconify-icon icon="heroicons-outline:eye-off" id="toggle_eye" class="text-lg"></iconify-icon>
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full border-l border-gray-200 dark:border-[#3f3f50]" onclick="copyToClipboard('{{ $displayPass }}', 'Mật khẩu')">
                                        <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-lg"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Cookie') }}</span>
                        <div class="flex items-center h-[32px]">
                            <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden h-full">
                                <input type="text" class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px]" style="border: none !important; outline: none !important; box-shadow: none !important;" value="{{ $displayExtra }}" readonly>
                                <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full border-l border-gray-200 dark:border-[#3f3f50]" onclick="copyToClipboard('{{ $displayExtra }}', 'Cookie')">
                                    <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-lg"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                         <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">{{ __t('Ghi chú của bạn') }}</span>
                         <div class="flex items-center">
                             <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden">
                                 <textarea id="order_note" class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-2 px-2 text-[12px] resize-none focus:bg-white dark:focus:bg-[#1e1e2d] transition-colors" rows="2" style="border: none !important; outline: none !important; box-shadow: none !important;" placeholder="{{ __t('Nhập ghi chú của bạn...') }}">{{ $account->order_note }}</textarea>
                             </div>
                         </div>
                    </div>

                    @if(!empty($account->admin_note))
                    <div class="mt-2">
                         <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">{{ __t('Admin phản hồi') }}</span>
                         <div class="flex items-center">
                             <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden"> 
                                 <textarea readonly class="bg-transparent text-gray-500 dark:text-gray-400 font-bold w-full py-2 px-2 text-[12px] resize-none" rows="2" style="border: none !important; outline: none !important; box-shadow: none !important;">{{ $account->admin_note }}</textarea>
                             </div>
                         </div>
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    @if($isBulk)
        <style>
            .bulk-table-container {
                border: 1px solid #e0e0e0 !important;
                border-radius: 0 !important;
            }
            #bulk-accounts-table {
                border-collapse: collapse !important;
                min-width: 900px !important;
            }
            #bulk-accounts-table th, 
            #bulk-accounts-table td {
                border: 1px solid #e0e0e0 !important;
                border-radius: 0 !important;
                padding: 8px !important;
            }
            #bulk-accounts-table thead th {
                background: var(--theme-bg, #f5f5f5) !important;
                color: var(--theme-text, #000) !important;
                text-align: center;
                font-weight: bold;
                text-transform: uppercase;
                border-bottom: 2px solid #e0e0e0 !important;
            }
            #bulk-accounts-table input[type="checkbox"] {
                accent-color: #000;
            }
            .account-cell {
                padding: 2px 4px;
                vertical-align: middle;
            }
            .account-scroll {
                width: 100%;
                min-height: 25px;
                height: 25px; /* Default 1 row height */
                overflow-y: auto;
                overflow-x: hidden;
                white-space: pre-wrap;
                word-break: break-all;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                line-height: 1.1;
                padding: 4px;
                background: #f9f9f9;
                border: 1px solid #e5e7eb !important; /* Lighter gray border */
                display: block;
                resize: vertical; /* Enable vertical resize */
                outline: none !important;
                box-shadow: none !important;
            }
            .account-scroll:focus {
                border-color: #e5e7eb !important;
                outline: none !important;
                box-shadow: none !important;
            }
            .dark .account-scroll {
                background: #14141f;
                color: #e5e7eb;
                border-color: #2d2d3a !important;
            }
            /* Custom Scrollbar for 1-row cell */
            .account-scroll::-webkit-scrollbar {
                width: 3px;
            }
            .account-scroll::-webkit-scrollbar-thumb {
                background: #dcdcdc;
            }
        </style>
        <!-- Bottom: Advanced Account Table (for Bulk) -->
        <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg shadow-sm mt-4 p-5 overflow-hidden">
            <!-- Search Bar (Row 1) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <input type="text" id="search-keyword" placeholder="{{ __t('Tài khoản') }}" 
                       class="bg-gray-100 dark:bg-[#14141f] border-none rounded-md px-4 py-2.5 text-sm focus:ring-1 focus:ring-blue-500 transition-all dark:text-white">
                <button onclick="applyFilters()" class="bg-gray-200 dark:bg-[#2d2d3a] text-gray-700 dark:text-gray-300 font-bold rounded-md py-2.5 px-4 flex items-center justify-center gap-2 hover:bg-gray-300 dark:hover:bg-[#3f3f50] transition-colors">
                    <iconify-icon icon="heroicons-outline:search" class="text-xl"></iconify-icon>
                    {{ __t('Tìm kiếm') }}
                </button>
                <button onclick="resetFilters()" class="bg-gray-200 dark:bg-[#2d2d3a] text-gray-700 dark:text-gray-300 font-bold rounded-md py-2.5 px-4 flex items-center justify-center gap-2 hover:bg-gray-300 dark:hover:bg-[#3f3f50] transition-colors">
                    <iconify-icon icon="heroicons-outline:trash" class="text-xl"></iconify-icon>
                    {{ __t('Bỏ lọc') }}
                </button>
            </div>

            <!-- Limit Selector (Row 2) -->
            <div class="flex items-center gap-2 mb-6">
                <span class="text-gray-500 font-bold text-sm tracking-wide uppercase">SHOW :</span>
                <div class="relative min-w-[120px]">
                    <select id="show-limit" onchange="applyFilters()" class="appearance-none w-full bg-white dark:bg-[#14141f] border border-gray-300 dark:border-[#2d2f36] rounded px-3 py-1.5 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 dark:text-white">
                        <option value="1000">1.000</option>
                        <option value="500">500</option>
                        <option value="100">100</option>
                        <option value="50">50</option>
                        <option value="10" selected>10</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-400">
                        <iconify-icon icon="heroicons-outline:chevron-down"></iconify-icon>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bulk-table-container rounded">
                <table id="bulk-accounts-table" class="w-full text-sm border-collapse">
                    <thead>
                        <tr>
                            <th class="p-3 w-12 text-center">
                                <input type="checkbox" id="select-all-accounts" class="rounded border-none accent-blue-500">
                            </th>
                            <th class="p-3 text-center font-bold uppercase w-1/4">{{ __t('UID') }}</th>
                            <th class="p-3 text-center font-bold uppercase w-1/2">{{ __t('Tài khoản') }}</th>
                            <th class="p-3 w-32 text-center font-bold uppercase">{{ __t('Thao tác') }}</th>
                        </tr>
                    </thead>
                    <tbody id="bulk-table-body">
                        @foreach($account->orders as $index => $subOrder)
                            @php
                                $fullInfo = $subOrder->username . ($subOrder->password ? ':' . $subOrder->password : '') . ($subOrder->extra_data ? ':' . $subOrder->extra_data : '');
                                $parts = explode(':', $fullInfo);
                                $uid = count($parts) >= 2 ? $parts[0] . ':' . $parts[1] : $parts[0];
                            @endphp
                            <tr class="account-row hover:bg-gray-50 dark:hover:bg-white/5 transition-colors" data-uid="{{ $uid }}" data-info="{{ $fullInfo }}">
                                <td class="p-3 text-center align-middle">
                                    <input type="checkbox" class="account-checkbox rounded border-gray-300 dark:border-gray-600 dark:bg-[#1e1e2d]" 
                                           data-info="{{ $fullInfo }}" data-uid="{{ $uid }}">
                                </td>
                                <td class="p-3 text-center font-bold text-gray-800 dark:text-gray-200 align-middle">
                                    {{ $uid }}
                                </td>
                                <td class="account-cell align-middle">
                                    <textarea class="account-scroll" readonly>{{ $fullInfo }}</textarea>
                                </td>
                                <td class="p-3 text-center align-middle whitespace-nowrap">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <button class="bg-cyan-500 hover:bg-cyan-600 text-white rounded px-3 py-2 text-xs font-bold shadow-md transition-all flex items-center justify-center gap-1 w-full max-w-[100px] shrink-0 active:scale-95"
                                                onclick="copyToClipboard('{{ $fullInfo }}', 'Thông tin')">
                                            <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-sm"></iconify-icon>
                                            {{ __t('Copy') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Bulk Action Menu -->
            <div class="mt-4 flex justify-between items-center">
                 <div class="relative inline-block text-left">
                    <button id="wrench-menu-btn" class="bg-[#1e1e2d] border border-gray-700 text-white p-2 px-3 rounded shadow-md hover:bg-black transition-all flex items-center gap-2 group">
                        <iconify-icon icon="heroicons-solid:wrench" class="text-lg group-hover:rotate-45 transition-transform"></iconify-icon>
                        <iconify-icon icon="heroicons-outline:chevron-down" class="text-xs"></iconify-icon>
                    </button>
                    <!-- Dropdown -->
                    <div id="wrench-dropdown" class="hidden absolute left-0 bottom-full mb-2 w-72 bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg shadow-xl z-50 overflow-hidden">
                        <div class="py-1">
                            <button onclick="bulkAction('export')" class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-white/10 flex items-center gap-3 transition-colors">
                                <iconify-icon icon="heroicons-solid:document-download" class="text-xl text-gray-600 dark:text-gray-400"></iconify-icon>
                                {{ __t('Lưu các tài khoản đã chọn vào tệp .txt') }}
                            </button>
                            <button onclick="bulkAction('copy-all')" class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-white/10 flex items-center gap-3 transition-colors">
                                <iconify-icon icon="heroicons-solid:clipboard-document-list" class="text-xl text-gray-600 dark:text-gray-400"></iconify-icon>
                                {{ __t('Sao chép các tài khoản đã chọn') }}
                            </button>
                            <button onclick="bulkAction('copy-uid')" class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-white/10 flex items-center gap-3 transition-colors border-t border-gray-100 dark:border-[#2d2f36]">
                                <iconify-icon icon="heroicons-solid:identification" class="text-xl text-gray-600 dark:text-gray-400"></iconify-icon>
                                {{ __t('Chỉ sao chép UID các tài khoản đã chọn') }}
                            </button>
                        </div>
                    </div>
                 </div>
                 
                  <div id="pagination-container" class="flex items-center gap-2">
                      <!-- Pagination will be rendered here -->
                  </div>
                  
                  <div id="results-count" class="text-sm text-gray-500 font-medium">
                      {{ __t('Showing :count Results', ['count' => count($account->orders)]) }}
                  </div>
            </div>
            
            <div class="mt-6">
                 <span class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-2 block uppercase text-[10px]">{{ __t('Ghi chú của bạn') }}</span>
                 <textarea id="order_note_bulk" class="w-full bg-gray-50 dark:bg-[#14141f] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-3 text-sm dark:text-gray-300 h-24 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="{{ __t('Nhập ghi chú của bạn...') }}">{{ $account->order_note }}</textarea>
            </div>
        </div>
    @else
        <!-- Bottom: Order Status Stepper (for Single Account) -->
        <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-6 shadow-sm mt-4">
            <div class="text-sm font-bold pb-2 mb-2 border-b border-gray-100 dark:border-[#2d2f36] text-gray-900 dark:text-white uppercase">{{ __t('Trạng thái đơn hàng') }}</div>
            
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between relative">
                @php
                    $rawOrderStatus = $account->order_status ?? $account->status ?? 'Completed';
                    $orderStatus = $rawOrderStatus;
                    // Normalize status values
                    if (in_array(strtolower($orderStatus), ['pending', '0', 'assigned'])) $orderStatus = 'Processing';
                    if (in_array(strtolower($orderStatus), ['completed', 'success', '1'])) $orderStatus = 'Completed';
                    if (in_array(strtolower($orderStatus), ['processing'])) $orderStatus = 'Processing';
                    if (in_array(strtolower($orderStatus), ['cancelled', 'canceled', 'error', '2'])) $orderStatus = 'Cancelled';
                    
                    $steps = [
                        ['key' => 'Payment', 'title' => 'Thanh toán', 'desc' => 'Đơn hàng đã được thanh toán', 'icon' => 'heroicons-outline:document-text'],
                        ['key' => 'Processing', 'title' => 'Đang xử lý', 'desc' => 'Hệ thống đang xử lý đơn hàng của bạn', 'icon' => 'heroicons-outline:shield-check'],
                        ['key' => 'Completed', 'title' => 'Hoàn thành', 'desc' => 'Đơn hàng đã hoàn tất', 'icon' => 'heroicons-outline:check-circle'],
                    ];
                    
                    $currentIdx = 0;
                    if ($orderStatus == 'Processing') $currentIdx = 1;
                    if ($orderStatus == 'Completed') $currentIdx = 2;
                    if ($orderStatus == 'Cancelled') $currentIdx = -1;
                @endphp

                @foreach($steps as $index => $step)
                    <div class="flex items-start lg:items-center group relative {{ !$loop->last ? 'lg:flex-1' : '' }}">
                        @if(!$loop->last)
                        <div class="absolute left-6 top-6 -bottom-1 w-0.5 bg-gray-200 dark:bg-[#2d2f36] lg:hidden"></div>
                        @endif

                        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center border-2 transition-colors duration-200 relative z-10 
                            {{ $index <= $currentIdx ? 'bg-blue-600/10 border-blue-600 text-blue-600' : 'bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-[#2d2f36] text-gray-400 dark:text-gray-600' }}">
                            <iconify-icon icon="{{ $step['icon'] }}" class="text-2xl"></iconify-icon>
                        </div>

                        <div class="ml-4 lg:ml-3 pb-8 lg:pb-0">
                            <div class="text-base font-bold {{ $index <= $currentIdx ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ __t($step['title']) }}
                            </div>
                            <div class="text-sm mt-1 {{ $index <= $currentIdx ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-600' }}">
                                {{ __t($step['desc']) }}
                            </div>
                        </div>

                        @if(!$loop->last)
                            <div class="hidden lg:block flex-grow h-px bg-gray-200 dark:bg-[#2d2f36] mx-4"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

  </div>

  @push('scripts')
    @vite('resources/js/modules/account/order/index.js')
    <script>
      function togglePasswordVisibility() {
          const passwordInput = document.getElementById('password_field');
          const eyeIcon = document.getElementById('toggle_eye');
          
          if (passwordInput.type === 'password') {
              passwordInput.type = 'text';
              eyeIcon.setAttribute('icon', 'heroicons-outline:eye');
          } else {
              passwordInput.type = 'password';
              eyeIcon.setAttribute('icon', 'heroicons-outline:eye-off');
          }
      }

      function copyToClipboard(text, label) {
          if (!text) return;
          navigator.clipboard.writeText(text).then(() => {
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
              Toast.fire({
                  icon: 'success',
                  title: 'Thành công',
                  text: `Copy ${label} thành công!`
              });
          }).catch(err => {
              console.error('Failed to copy: ', err);
          });
      }

      // Selection & Bulk Actions
      function toggleSelectionMenu() {
          const dropdown = document.getElementById('wrench-dropdown');
          if (dropdown) dropdown.classList.toggle('hidden');
      }

      const wrenchMenuBtn = document.getElementById('wrench-menu-btn');
      if (wrenchMenuBtn) {
          wrenchMenuBtn.addEventListener('click', (e) => {
              e.stopPropagation();
              toggleSelectionMenu();
          });
      }

      document.addEventListener('click', () => {
          const dropdown = document.getElementById('wrench-dropdown');
          if (dropdown && !dropdown.classList.contains('hidden')) {
              dropdown.classList.add('hidden');
          }
      });

      const selectAll = document.getElementById('select-all-accounts');
      if (selectAll) {
          selectAll.addEventListener('change', () => {
              const checkboxes = document.querySelectorAll('.account-checkbox');
              checkboxes.forEach(cb => cb.checked = selectAll.checked);
          });
      }

      function bulkAction(type) {
          const selected = Array.from(document.querySelectorAll('.account-checkbox:checked'));
          if (selected.length === 0) {
              Swal.fire('Chú ý', 'Vui lòng chọn ít nhất một tài khoản', 'warning');
              return;
          }

          let result = '';
          if (type === 'export' || type === 'copy-all') {
              result = selected.map(cb => cb.dataset.info).join('\n');
              if (type === 'export') {
                  const blob = new Blob([result], { type: 'text/plain' });
                  const url = window.URL.createObjectURL(blob);
                  const a = document.createElement('a');
                  a.href = url;
                  a.download = `orders_{{ $account->buyer_code }}.txt`;
                  document.body.appendChild(a);
                  a.click();
                  window.URL.revokeObjectURL(url);
                  document.body.removeChild(a);
                  return;
              }
          } else if (type === 'copy-uid') {
              result = selected.map(cb => cb.dataset.uid).join('\n');
          }

          if (result) {
              copyToClipboard(result, 'Danh sách đã chọn');
          }
      }

      let currentPage = 1;

      function applyFilters() {
          const keyword = document.getElementById('search-keyword').value.toLowerCase().trim();
          const limit = parseInt(document.getElementById('show-limit').value);
          const allRows = Array.from(document.querySelectorAll('.account-row'));
          
          // Filter rows first
          const filteredRows = allRows.filter(row => {
              const uid = row.dataset.uid.toLowerCase();
              const info = row.dataset.info.toLowerCase();
              return uid.includes(keyword) || info.includes(keyword);
          });

          const totalResults = filteredRows.length;
          const totalPages = Math.ceil(totalResults / limit) || 1;
          
          if (currentPage > totalPages) currentPage = totalPages;
          if (currentPage < 1) currentPage = 1;

          const startIndex = (currentPage - 1) * limit;
          const endIndex = startIndex + limit;

          // Hide all rows
          allRows.forEach(row => row.style.display = 'none');

          // Show rows for current page
          filteredRows.slice(startIndex, endIndex).forEach(row => {
              row.style.display = '';
          });

          const countEl = document.getElementById('results-count');
          if (countEl) {
              const shown = Math.min(endIndex, totalResults);
              countEl.innerText = `Showing ${startIndex + 1} to ${shown} of ${totalResults} Results`;
          }

          renderPagination(totalPages);
      }

      function renderPagination(totalPages) {
          const container = document.getElementById('pagination-container');
          if (!container) return;

          if (totalPages <= 1) {
              container.innerHTML = '';
              return;
          }

          let html = '';
          
          // Previous button
          html += `<button onclick="changePage(${currentPage - 1})" class="p-2 rounded bg-gray-100 dark:bg-[#2d2d3a] hover:bg-gray-200 dark:hover:bg-[#3f3f50] disabled:opacity-50 transition-colors" ${currentPage === 1 ? 'disabled' : ''}>
                    <iconify-icon icon="heroicons-outline:chevron-left"></iconify-icon>
                  </button>`;

          // Page numbers
          const maxVisible = 5;
          let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
          let endPage = Math.min(totalPages, startPage + maxVisible - 1);
          
          if (endPage - startPage + 1 < maxVisible) {
              startPage = Math.max(1, endPage - maxVisible + 1);
          }

          for (let i = startPage; i <= endPage; i++) {
              html += `<button onclick="changePage(${i})" class="px-3 py-1 rounded font-bold text-sm transition-colors ${i === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-[#2d2d3a] text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#3f3f50]'}">
                        ${i}
                      </button>`;
          }

          // Next button
          html += `<button onclick="changePage(${currentPage + 1})" class="p-2 rounded bg-gray-100 dark:bg-[#2d2d3a] hover:bg-gray-200 dark:hover:bg-[#3f3f50] disabled:opacity-50 transition-colors" ${currentPage === totalPages ? 'disabled' : ''}>
                    <iconify-icon icon="heroicons-outline:chevron-right"></iconify-icon>
                  </button>`;

          container.innerHTML = html;
      }

      function changePage(page) {
          currentPage = page;
          applyFilters();
          // Scroll to top of table
          document.querySelector('.bulk-table-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      function resetFilters() {
          document.getElementById('search-keyword').value = '';
          document.getElementById('show-limit').value = '10';
          currentPage = 1;
          applyFilters();
      }

      // Initialize
      document.addEventListener('DOMContentLoaded', () => {
          applyFilters();
      });

      // Auto save note
      const noteInput = document.getElementById('order_note');
      const noteInputBulk = document.getElementById('order_note_bulk');
      let isSaving = false;
      let lastSavedNote = '';

      const activeInput = noteInput || noteInputBulk;
      if (activeInput) {
          lastSavedNote = activeInput.value;

          activeInput.addEventListener('blur', () => {
             saveNote(activeInput);
          });

          window.addEventListener('beforeunload', () => {
             saveNote(activeInput);
          });
      }

      async function saveNote(inputEl) {
          if (isSaving || !inputEl) return;
          const note = inputEl.value;
          if (note === lastSavedNote) return;

          isSaving = true;
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          
          try {
              const response = await fetch('{{ route('account.orders.accounts.update-note') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrfToken
                  },
                  body: JSON.stringify({
                      id: {{ $account->id }},
                      type: '{{ ($isBulk ?? false) ? "bulk" : (($isV2 ?? false) ? "v2" : "v1") }}',
                      note: note
                  }),
                  keepalive: true
              });

              if (!response.ok) throw new Error('Network error');
              const data = await response.json();
              
              if (data.status) {
                  lastSavedNote = note;
                  const Toast = Swal.mixin({
                      toast: true,
                      position: 'top-end',
                      showConfirmButton: false,
                      timer: 3000,
                      timerProgressBar: true,
                  });
                  Toast.fire({
                      icon: 'success',
                      title: '{{ __t("Thành công") }}',
                      text: data.message
                  });
              }
          } catch (e) {
              console.error(e);
          } finally {
              isSaving = false;
          }
      }
    </script>
  @endpush
</x-app-layout>
