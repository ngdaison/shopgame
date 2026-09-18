@section('title', $pageTitle)
<x-app-layout>
  <section>
<div class="mb-5">
    <h1 class="mb-3 text-primary underline " style="font-size: 25px;">{{ __t('Vật Phẩm') }}</h1>
    <h2 class="text-danger-black" style="font-size: 34px;">{{ $group->name }}</h2>
</div>
    <div id="app">
      <item-index group-id="{{ $group->id }}" :group='@json($group)' :packages='@json($packages)' />
    </div>
  </section>

  @push('scripts')
    @vite('resources/js/modules/store/item/index.js')
  @endpush
</x-app-layout>
