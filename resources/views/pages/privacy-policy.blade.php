@section('title', __t('Chính sách bảo mật'))
<x-app-layout meta-seo="ocean">
  <section class="space-y-6">
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-3">
      <div class="col-span-1 md:col-span-3 card">
        <div class="card-body flex flex-col p-6">
          <div class="mb-3 text-center">
            <h6 class="card-title mb-0">{{ __t('Chính sách bảo mật') }}</h6>
            <div class="my-3 border-top"></div>
          </div>
          <div class="card-text h-full space-y-4">
            {!! Helper::getNotice('page_privacy_policy') !!}
          </div>
        </div>
      </div>
    </div>
  </section>
</x-app-layout>
