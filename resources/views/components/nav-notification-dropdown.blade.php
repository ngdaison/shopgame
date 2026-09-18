@php
    if (!auth()->check()) {
        $notifications = collect([]);
        $unreadCount = 0;
    } else {
        $notifications = App\Models\Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $unreadCount = App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
    }
@endphp

<div class="relative h-full flex items-center" x-data="{ open: false }">
  <button @click="open = !open" class="relative lg:h-[32px] lg:w-[32px] lg:bg-slate-50 lg:dark:bg-slate-900 dark:text-white text-slate-900 cursor-pointer
        rounded-full text-[20px] flex items-center justify-center" type="button">
    <iconify-icon icon="heroicons-outline:bell"></iconify-icon>
    @if($unreadCount > 0)
    <span id="nav-unread-badge" class="absolute -right-1 lg:top-0 -top-[6px] h-4 w-4 bg-red-500 text-[8px] font-semibold flex flex-col items-center
          justify-center rounded-full text-white z-[99]">
      {{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
    @else
    <span id="nav-unread-badge" class="hidden absolute -right-1 lg:top-0 -top-[6px] h-4 w-4 bg-red-500 text-[8px] font-semibold flex flex-col items-center
          justify-center rounded-full text-white z-[99]"></span>
    @endif
  </button>
  
  <!-- Notifications Dropdown -->
  <div x-show="open" @click.outside="open = false" x-transition x-cloak
       class="dropdown-menu dropdown-menu-end absolute md:right-0 right-[-80px] sm:right-0 z-[1100] bg-white divide-y divide-slate-100 dark:divide-slate-900 shadow
        dark:bg-slate-800 border dark:border-slate-900 top-full rounded-md overflow-hidden dropdown-mobile-fix" 
        style="width: 400px; max-width: 95vw; margin-top: 1px;">
    
    <!-- Header -->
    <div class="py-3 px-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center" style="background-color: var(--primary-color) !important;">
      <h3 class="text-sm font-bold text-white uppercase" style="color: white !important;">{{ __t('Thông Báo') }}</h3>
      @if($unreadCount > 0)
      <form action="{{ route('account.notifications.read_all') }}" method="POST">
          @csrf
          <button type="submit" class="text-[11px] text-white hover:text-gray-200 underline opacity-90 hover:opacity-100" style="color: white !important;">{{ __t('Đánh dấu đã đọc') }}</button>
      </form>
      @endif
    </div>

    <style>
      .nav-notify-item {
        transition: background-color 0.15s ease-in-out;
      }
      .nav-notify-item:hover {
        background-color: color-mix(in srgb, var(--primary-color), white 90%) !important;
      }
      .nav-notify-unread {
        background-color: #fff3cd !important;
      }
      
      @media (max-width: 640px) {
        .dropdown-mobile-fix {
          position: fixed !important;
          top: 70px !important;
          left: 10px !important;
          right: 10px !important;
          width: calc(100% - 20px) !important;
          max-width: none !important;
          transform: none !important;
          z-index: 2100 !important;
        }
      }
    </style>

    <!-- List -->
    <div id="nav-notification-list" class="max-h-[350px] overflow-y-auto">
      @forelse($notifications as $notification)
      <div class="block w-full px-4 py-3 text-sm nav-notify-item cursor-pointer border-b border-gray-100 dark:border-slate-700 last:border-0 {{ !$notification->is_read ? 'nav-notify-unread' : '' }}" 
           data-id="{{ $notification->id }}"
           onclick="window.location.href='{{ route('account.notifications.show', $notification->code) }}'">
        
        <div class="flex items-start">
          {{-- Info --}}
          <div class="flex-1 min-w-0">
            <div class="mb-1">
              <h4 class="{{ !$notification->is_read ? 'font-black text-slate-950' : 'font-bold text-slate-800' }} text-base dark:text-gray-200 truncate leading-tight">
                {{ $notification->title }}
              </h4>
            </div>

            @if(!empty($notification->content))
            <div class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1 leading-relaxed">
              {{ strip_tags($notification->content) }}
            </div>
            @endif

            @if(!$notification->is_read)
            <div class="mt-1">
              <span class="text-[9px] text-gray-400 dark:text-slate-500 whitespace-nowrap">
                {{ $notification->created_at->format('H:i d/m/Y') }}
              </span>
            </div>
            @endif
          </div>
        </div>
      </div>
      @empty
      <div class="text-center py-8">
        <p class="text-gray-500 text-sm">{{ __t('Không có thông báo mới') }}</p>
      </div>
      @endforelse
    </div>

    <!-- Footer -->
    <div class="py-3 text-center bg-gray-50 dark:bg-slate-800 transition-all hover:bg-gray-100 border-t border-gray-100 dark:border-slate-700">
      <a href="{{ route('account.notifications.index') }}" class="text-sm font-bold uppercase tracking-wider hover:underline" style="color: var(--primary-color) !important;">
        {{ __t('Xem tất cả') }}
      </a>
    </div>
  </div>
</div>
