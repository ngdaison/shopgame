<div class="flex relative h-full items-center" x-data="{ open: false }">
  <button @click="open = !open" class="inline-flex items-center rounded-lg text-center text-sm font-medium text-slate-800 focus:outline-none focus:ring-0 dark:text-white" type="button">
    <div class="flex items-center gap-2">
        <div class="h-9 w-9 flex-shrink-0 rounded-full" style="border-radius: 50% !important;">
          <img class="h-full w-full rounded-full object-cover"
               style="border-radius: 50% !important; aspect-ratio: 1/1 !important;"
               src="{{ Helper::getValidImage(Auth::user()?->avatar, '/images/avatar/av-1.svg') }}" 
               alt="" />
        </div>
        <div class="ltr:text-left rtl:text-right block">
          <span class="flex-none items-center overflow-hidden text-ellipsis whitespace-nowrap text-[15px] font-bold text-slate-600 dark:text-white flex">
            {{ Str::limit(Auth::user()?->full_name ?: (Auth::user()?->fullname ?: Auth::user()?->username), 20) ?? __t('Khách') }}
          </span>
          <small class="text-slate-800 dark:text-white block text-[13px] font-bold mt-0.5">{{ Helper::formatCurrency(auth()->user()?->balance ?? 0) }}</small>
        </div>
    </div>
    <svg class="hidden h-[16px] w-[16px] text-base dark:text-white lg:inline-block" style="margin-left: 6px;" aria-hidden="true" fill="none" stroke="currentColor" viewbox="0 0 24 24"
      xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
    </svg>
  </button>
  <!-- Dropdown menu -->
  <div x-show="open" @click.outside="open = false" x-transition x-cloak
       class="dropdown-menu absolute right-0 top-full mt-1 z-[1100] w-44 divide-y divide-slate-100 overflow-hidden rounded-md border bg-white shadow dark:border-slate-700 dark:bg-slate-800"
       style="margin-top: 1px;">
    <ul class="py-1 text-sm text-slate-800 dark:text-slate-200">
      @if (Auth::check())
        <li>
          <a href="{{ route('account.deposits.index') }}" class="font-inter flex items-center px-4 py-2 text-sm font-normal text-slate-600 hover:bg-slate-100 dark:text-white dark:hover:bg-slate-600 dark:hover:text-white"
            @class([
                'active' => request()->routeIs('account.deposits.index'),
            ])>
            <iconify-icon class="text-textColor mr-2 text-lg dark:text-white" icon="carbon:money"></iconify-icon>
            <span class="dropdown-option">
              {{ __t('Nạp Tiền') }}
            </span>
          </a>
        </li>
        <li>
          <a href="{{ route('account.profile.index') }}" class="font-inter flex items-center px-4 py-2 text-sm font-normal text-slate-600 hover:bg-slate-100 dark:text-white dark:hover:bg-slate-600 dark:hover:text-white"
            @class([
                'country-list',
                'active' => request()->routeIs('profiles.index'),
            ])>
            <iconify-icon class="text-textColor mr-2 text-lg dark:text-white" icon="carbon:user-avatar">
            </iconify-icon>
            <span class="dropdown-option">
              {{ __t('Thông Tin') }}
            </span>
          </a>
        </li>
        @foreach (Helper::getUserRoles(Auth::user()) as $appRole)
          <li>
            <a href="{{ $appRole['route'] }}" class="font-inter flex items-center px-4 py-2 text-sm font-normal text-slate-600 hover:bg-slate-100 dark:text-white dark:hover:bg-slate-600 dark:hover:text-white">
              <iconify-icon class="text-textColor mr-2 text-lg dark:text-white" icon="{{ $appRole['icon'] }}">
              </iconify-icon>
              <span class="dropdown-option">
                {{ $appRole['label'] }}
              </span>
            </a>
          </li>
        @endforeach

        {{-- Logout --}}
        <li>
          <form method="POST" action="{{ route('logout') }}"
            class="font-inter flex items-center px-4 py-2 text-sm font-normal text-slate-600 hover:bg-slate-100 dark:text-white dark:hover:bg-slate-600 dark:hover:text-white">
            @csrf
            <button type="submit" class="country-list flex items-start">
              <iconify-icon class="text-textColor mr-2 text-lg dark:text-white" icon="carbon:logout">
              </iconify-icon>
              <span class="dropdown-option">
                {{ __t('Đăng Xuất') }}
              </span>
            </button>
          </form>
        </li>
      @else
        <li>
          <a href="{{ route('login') }}" class="font-inter flex items-center px-4 py-2 text-sm font-normal text-slate-600 hover:bg-slate-100 dark:text-white dark:hover:bg-slate-600 dark:hover:text-white"
            @class([
                'active' => request()->routeIs('login'),
            ])>
            <iconify-icon class="text-textColor mr-2 text-lg dark:text-white" icon="material-symbols:login"></iconify-icon>
            <span class="dropdown-option">
              {{ __t('Đăng Nhập') }}
            </span>
          </a>
        </li>
        <li>
          <a href="{{ route('register') }}" class="font-inter flex items-center px-4 py-2 text-sm font-normal text-slate-600 hover:bg-slate-100 dark:text-white dark:hover:bg-slate-600 dark:hover:text-white"
            @class([
                'active' => request()->routeIs('register'),
            ])>
            <iconify-icon class="text-textColor mr-2 text-lg dark:text-white" icon="solar:user-linear"></iconify-icon>
            <span class="dropdown-option">
              {{ __t('Tạo Tài Khoản') }}
            </span>
          </a>
        </li>
        <li>
          <a href="{{ route('password.request') }}" class="font-inter flex items-center px-4 py-2 text-sm font-normal text-slate-600 hover:bg-slate-100 dark:text-white dark:hover:bg-slate-600 dark:hover:text-white"
            @class([
                'active' => request()->routeIs('password.request'),
            ])>
            <iconify-icon class="text-textColor mr-2 text-lg dark:text-white" icon="solar:password-linear"></iconify-icon>
            <span class="dropdown-option">
              {{ __t('Quên Mật Khẩu?') }}
            </span>
          </a>
        </li>
      @endif
    </ul>
  </div>
</div>
