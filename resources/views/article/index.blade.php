@section('title', __t($pageTitle))
<x-app-layout>
  <section class="space-y-6">
    <div class="text-center">
      <h1 class="text-xl md:text-4xl mb-1 text-primary"> <span class="category-name">{{ __t('Tin Tức Mới') }}</span> </h1>
      <div class="h-[3px] bg-primary w-[170px] mx-auto"></div>
    </div>

    <div class="grid xl:grid-cols-3 grid-cols-1 gap-5">
      @foreach ($articles as $post)
        <div class="card">
          <div class=" h-[210px] w-full mb-2 ">
            <a href="{{ route('articles.show', ['slug' => $post->slug]) }}">
              <img src="{{ Helper::getValidImage($post->thumbnail) }}" 
                   alt="" 
                   class="w-full h-full object-cover rounded-t-lg">
            </a>
          </div>
          <div class="px-2 pb-2 mb-3 text-start font-bold text-truncate  hover:whitespace-normal">
            <a href="{{ route('articles.show', ['slug' => $post->slug]) }}" class="text-lg">{{ $post->title }}</a>
          </div>
          <div class="px-5 flex pb-3 justify-between">
            <a href="#!" class="btn btn-sm btn-outline-danger">{{ __t('Ngày:') }} {{ $post->created_at }}</a>
            <a href="{{ route('articles.show', ['slug' => $post->slug]) }}" class="btn btn-sm btn-primary">{{ __t('Xem Thêm') }} <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-3">
      {{ $articles->links() }}
    </div>
  </section>
</x-app-layout>
