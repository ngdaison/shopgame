@section('title', $meta_seo['title'] ?? $pageTitle)
@section('keywords', $meta_seo['keywords'] ?? '')
@section('description', $group->descr ?? '')

<x-app-layout meta-seo="ocean">
    <section>
        {{-- Tiêu đề nhóm --}}
        <div class="mb-5">
            <h1 class="mb-3 text-primary underline" style="font-size: 25px;">{{ __t('Tài Khoản') }}</h1>
            <h2 class="text-danger-black" style="font-size: 34px;">{{ $group->name }}</h2>
        </div>

        {{-- Lưu ý --}}
        {{-- Vue component --}}
        <div id="app">
            <account-index group-id="{{ $group->id }}" :group='@json($group)' />
        </div>

        {{-- Nội dung SEO nếu có --}}
        @if ($group->descr_seo)
            <div class="border border-primary p-3 bg-white rounded-lg mb-3 mt-5">
                {!! $group->descr_seo !!}
            </div>
        @endif
    </section>

    @push('scripts')
        @vite('resources/js/modules/store/account/index.js')
    @endpush
</x-app-layout>
