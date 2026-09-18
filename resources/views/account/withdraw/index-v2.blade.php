@section('title', __t($pageTitle))
<x-app-layout>
  <section class="space-y-6">
    <div class="flex flex-col justify-between md:flex-row md:gap-4">
      <div class="basis-1/2">
        <div class="card mb-2 py-3 md:py-4 px-4 rounded-none md:rounded">
          <label class="font-semibold block text-black dark:text-white text-2xl">Chọn trò chơi</label>
          <span class="text-xs block text-zinc-700 dark:text-white mb-3">Chọn game muốn rút vật phẩm dưới đây.</span>
          <div class="grid grid-cols-12 gap-2 w-full text-sm select_game">
            @foreach ($inventories as $inventory)
              @php
                if (!$inventory->inventory_var || $inventory->inventory_var->is_active !== true) {
                    continue;
                }
              @endphp

              <button class="dark:text-white w-full flex flex-col justify-center items-center border py-4 col-span-6 sm:col-span-3 md:col-span-2 border-gray-300 border-dashed px-3 rounded font-bold whitespace-nowrap"
                type="button" data-id="{{ $inventory->id }}" id="_click_{{ $inventory->id }}" onclick="onSelectGame(this)">
                <img src="{{ $inventory->inventory_var->image }}" style="width: 5rem; border-radius: 1.5rem;">
                <label class="block mt-2 font-medium">{{ $inventory->inventory_var->name }}</label>
              </button>
            @endforeach
          </div>
          <div></div>
        </div>
        <div class="load_form"></div>
      </div>
      <div class="basis-1/2">
        <div class="card mb-2 py-3 md:py-4 px-4 rounded-none md:rounded">
          <div class="font-semibold block text-black dark:text-white text-2xl mb-3">{{ __t('Hướng Dẫn Rút Thưởng') }}</div>

          {!! Helper::getNotice('withdraw_v2_tut') !!}
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-body flex flex-col p-6">
        <div class="font-semibold block text-black dark:text-white text-2xl mb-3">{{ __t('Lịch Sử Rút Thưởng') }}</div>
        <div class="card-body px-6 pb-6" id="app">
          <withdraw-index />
        </div>
      </div>
    </div>
  </section>

  @push('scripts')
    <script>
      function onSelectGame(e) {
        const id = e.getAttribute('data-id')
        const url = '/account/withdraws-v2/forms?id=' + id

        const btnSubmit = $("#frm_submit")
        if (btnSubmit) {
          $setLoading(btnSubmit)
        }

        document.querySelectorAll('.select_game button').forEach((el) => {
          $(el).removeClass('border-red-600 text-red-600 process')
        })
        $(e).addClass('border-red-600 text-red-600 process')

        $('.load_form').load(url)
      }


      @if ($inventories->count() > 0)
        $(document).ready(function() {
          document.getElementById('_click_{{ $inventories[0]->id }}').click()
        })
      @endif
    </script>
    @vite('resources/js/modules/account/withdraw-v2/index.js')
  @endpush
</x-app-layout>
