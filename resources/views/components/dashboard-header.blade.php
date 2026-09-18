<div class="sticky top-0 z-[1000] overflow-visible" id="app_header">
  <div class="app-header z-[1000] px-9 sm:px-[173px] bg-white shadow-sm dark:bg-slate-800 dark:shadow-slate-700 overflow-visible">
    <div class="flex h-full items-stretch justify-between overflow-visible">
      <div class="vertical-box flex items-center space-x-12 rtl:space-x-reverse md:space-x-12">
        <div class="inline-block xl:hidden">
          <x-application-logo class="mobile-logo" />
        </div>
        <button class="smallDeviceMenuController open-sdiebar-controller hidden md:inline-block xl:hidden">
          <iconify-icon class="relative top-[2px] bg-transparent text-xl leading-none text-slate-900 dark:text-white" icon="heroicons-outline:menu-alt-3"></iconify-icon>
        </button>
        <button class="sidebarOpenButton !ml-0 text-xl text-slate-900 dark:text-white hidden xl:inline-block">
          <iconify-icon icon="ph:arrow-right-bold"></iconify-icon>
        </button>
        {{-- <x-header-search /> --}}
      </div>
      <!-- end vertcial -->

      <div class="horizental-box flex items-center space-x-12 rtl:space-x-reverse">
        <x-application-logo />
        <button class="smallDeviceMenuController open-sdiebar-controller hidden md:inline-block xl:hidden">
          <iconify-icon class="relative top-[2px] bg-transparent text-xl leading-none text-slate-900 dark:text-white" icon="heroicons-outline:menu-alt-3"></iconify-icon>
        </button>
        {{-- <x-header-search /> --}}

      </div>
      <!-- end horizontal -->

      <!-- start horizontal nav -->
      <x-topbar-menu />
      <!-- end horizontal nav -->

      <div class="nav-tools leading-0 flex items-stretch" style="gap: 10px;">
        <x-nav-lang-dropdown />
        @if (theme_config('enable_custom_theme', false))
          <x-dark-light />
          <x-gray-scale />
        @endif
        <x-nav-notification-dropdown />
        <x-nav-user-dropdown />
        <div class="h-full flex items-center">
          <button class="smallDeviceMenuController leading-0 block md:hidden">
            <iconify-icon class="cursor-pointer text-2xl text-slate-900 dark:text-white" icon="heroicons-outline:menu-alt-3"></iconify-icon>
          </button>
        </div>
        <!-- end mobile menu -->
      </div>
      <!-- end nav tools -->
    </div>
  </div>
</div>

<!-- BEGIN: Search Modal -->
<div class="modal fade backdrop-brightness-10 fixed inset-0 left-0 top-0 hidden h-full w-full overflow-y-auto overflow-x-hidden bg-slate-900/40 outline-none backdrop-blur-sm backdrop-filter" id="searchModal"
  tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
  <div class="modal-dialog pointer-events-none relative top-1/4 w-auto">
    <div class="modal-content pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-lg outline-none dark:bg-slate-900">
      <form>
        <div class="relative">
          <button class="absolute left-0 top-1/2 flex h-full w-9 -translate-y-1/2 items-center justify-center text-xl dark:text-slate-300">
            <iconify-icon icon="heroicons-solid:search"></iconify-icon>
          </button>
          <input type="text" class="form-control !py-[14px] !pl-10" placeholder="Search" autofocus>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END: Search Modal -->
