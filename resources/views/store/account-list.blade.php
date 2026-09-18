<x-app-layout>
    @php
      $bconfig = Helper::getConfig('theme_custom');
    @endphp

    <div class="pt-0">
        {{-- DANH MỤC SẢN PHẨM --}}
        @if ($categories->count() > 0)
             <x-category-list :categories="$categories" :bconfig="$bconfig" type="account" />
        @else
             <div class="text-center text-gray-500 py-12">
                <i class="fas fa-box-open text-4xl mb-3"></i>
                <p>Chưa có chuyên mục tài khoản nào đang hoạt động.</p>
            </div>
        @endif
    </div>
</x-app-layout>
