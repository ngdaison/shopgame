@section('title', $pageTitle)
<x-app-layout meta-seo="ocean">
  <section>
    <div class="text-center mb-5">
      <h1 class="text-[20px] md:text-[30px] mb-1 category-name text-primary"><i class="fas fa-shield text-primary"></i> {{ __t('Vòng Quay May Mắn') }} <i class="fas fa-shield text-primary"></i></h1>
      <div class="h-[3px] bg-primary w-[170px] mx-auto"></div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      @foreach ($spinQuests as $spinQuest)
        <div class="rounded-lg bg-white dark:bg-black-500 border border-primary max-h-[270px] lg:max-h-[300px]">
          <div class="card-body">
            <a href="{{ route('games.spin-quest', ['id' => $spinQuest->id]) }}">
              <img alt="" src="{{ Helper::getValidImage($spinQuest->cover) }}" 
                   class="w-full h-[110px] md:h-[140px] lg:h-[180px] rounded-t-lg object-fill" 
                   alt="" />
            </a>
            <div class="p-3 cursor-pointer">
              <div class="flex justify-between mb-4">
                <div class="font-bold text-lg">
                  {{ $spinQuest->name }}
                </div>
                <div class="font-bold text-lg text-primary">
                  {{ Helper::formatCurrency($spinQuest->price) }}
                </div>
              </div>
              <div class="flex flex-col items-center">

                <div class="flex justify-center">
                  <a href="{{ route('games.spin-quest', ['id' => $spinQuest->id]) }}" class="btn btn-sm btn-primary"> <i class="fas fa-share"></i> Chơi Ngay <i class="fas fa-share"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>

</x-app-layout>
