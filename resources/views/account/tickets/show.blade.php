@section('title', $pageTitle)
<x-app-layout>

  <div id="app" data-ticket-code="{{ $ticket->code }}">
  </div>

  @push('scripts')
    @vite('resources/js/modules/account/ticket/show.js')
  @endpush
</x-app-layout>
