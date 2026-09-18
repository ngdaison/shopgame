@section('title', $pageTitle)
@section('keywords', $article->meta_data['keywords'] ?? null)
@section('description', $article->description)
<x-app-layout>
  <section class="space-y-6">
    <div class="text-center">
      <h1 class="text-lg md:text-[30px] mb-3 category-name text-primary">{{ $article->title }}</h1>
      <hr />
    </div>
    <div class="card">
      <div class="card-body">
        <div class="card-text p-6">
          {!! $article->content !!}
        </div>
      </div>
    </div>
  </section>
</x-app-layout>
