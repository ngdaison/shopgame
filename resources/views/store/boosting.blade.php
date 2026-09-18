@section('title', $pageTitle)
<x-app-layout>
  <section>
<div class="mb-5">
    <h1 class="mb-3 text-primary underline " style="font-size: 25px;">{{ __t('Cày Thuê') }}</h1>
    <h2 class="text-danger-black" style="font-size: 34px;">{{ $group->name }}</h2>
</div>

    <div id="app">
      <boosting-index group-id="{{ $group->id }}" :group='@json($group)' />
    </div>
  </section>

  @push('scripts')
    @vite('resources/js/modules/store/boosting/index.js')
  @endpush
</x-app-layout>
