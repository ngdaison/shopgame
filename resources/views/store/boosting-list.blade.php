<x-app-layout>
    @php
      $bconfig = Helper::getConfig('theme_custom');
    @endphp

    <div class="pt-0">
        {{-- DANH MỤC SẢN PHẨM --}}
        @if ($categories->count() > 0)
            <x-category-list :categories="$categories" :bconfig="$bconfig" type="boosting" />
        @else
             <div class="text-center text-gray-500 py-12">
                <i class="fas fa-gamepad text-4xl mb-3"></i>
                <p>Chưa có chuyên mục cày thuê nào đang hoạt động.</p>
            </div>
        @endif
    </div>
</x-app-layout>
