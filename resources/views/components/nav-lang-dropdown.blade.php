@php
  $currentLang = app()->getLocale();
  
  // Safe flag mapping function
  $getFlag = function($locale) {
      $mapping = [
          'en' => 'united-states',
          'vn' => 'vietnam',
          'vi' => 'vietnam',
          'ja' => 'japan',
          'th' => 'thailand',
          'cn' => 'china',
          'kr' => 'south-korea'
      ];
      return $mapping[$locale] ?? $locale;
  };

  $currentLangFlag = $getFlag($currentLang);
  $host = request()->getHost();
  $availableLocales = \App\Models\Language::where('status', true)
    ->where(function($query) use ($host) {
        $query->where('domain', $host)
              ->orWhereNull('domain')
              ->orWhere('domain', '');
    })
    ->get();
@endphp

<div class="leading-0 relative h-full flex items-center" x-data="{ listView: false }">
  <button @click="listView = !listView" class="inline-flex items-center rounded-lg text-center text-sm font-medium text-slate-800 focus:outline-none focus:ring-0 dark:text-white" type="button" aria-expanded="false">
    <iconify-icon class="mr-2 text-lg" icon="emojione:flag-for-{{ $currentLangFlag }}"></iconify-icon>
    <span class="dropdown-option hidden">{{ strtoupper($currentLang) }}</span>
  </button>
  <div x-show="listView" @click.outside="listView = false"
       x-transition:enter="transition ease-out duration-100"
       x-transition:enter-start="transform opacity-0 scale-95"
       x-transition:enter-end="transform opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-75"
       x-transition:leave-start="transform opacity-100 scale-100"
       x-transition:leave-end="transform opacity-0 scale-95"
       x-cloak
       class="dropdown-menu absolute top-full right-0 z-[1100] w-32 divide-y divide-slate-100 overflow-hidden rounded-md border bg-white shadow-lg dark:border-slate-900 dark:bg-slate-800"
       style="margin-top: 1px;">
    <ul class="py-1 text-sm text-slate-800 dark:text-slate-200">
      @foreach ($availableLocales as $item)
        <li>
          <a href="{{ route('set-locale', ['locale' => $item->iso_code]) }}"
            class="{{ $currentLang == $item->iso_code ? 'bg-slate-100 dark:bg-slate-800' : '' }} flex items-center px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600 dark:hover:text-white">
            <iconify-icon class="mt-1 text-lg" icon="emojione:flag-for-{{ $getFlag($item->iso_code) }}">
            </iconify-icon>
            <span class="dropdown-option ml-2">{{ strtoupper($item->iso_code) }}</span>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</div>
