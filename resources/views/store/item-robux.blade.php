@section('title', $pageTitle)
<x-app-layout>
    <div id="app" class="container mx-auto px-4 py-4 bg-slate-100">
        <item-robux :group='@json($group)' 
                    :group-image="'{{ \Helper::getValidImage($group->image) }}'"
                    :robux-rate='@json($robux_rate)'
                    :robux-tax="{{ $robux_tax }}"
                    :robux-type="'{{ $robux_type }}'"
                    :spinner="'{{ asset('/images/svg/spinner.svg') }}'" />
    </div>

    @push('scripts')
        @vite('resources/js/modules/store/robux/index.js')
    @endpush
</x-app-layout>
