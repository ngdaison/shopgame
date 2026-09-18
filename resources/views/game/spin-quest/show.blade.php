@section('title', $pageTitle)
@section('css')
  <style>
    @media (max-width: 768px) {
      .v-luckywheel .wheel-wrapper {
        width: 300px;
        height: 300px;
        position: relative;
      }

      .v-luckywheel .wheel-pointer {
        width: 90px;
        height: 90px;
        background: url(/images/spin-quest/pointer1.png);
        background-size: 75%;
        background-repeat: no-repeat;
        background-position: 50%;
        position: absolute;
        left: 50%;
        top: 49.7%;
        transform: translate(-50%, -50%);
        text-align: center;
        line-height: 60px;
        z-index: 10;
      }

      .v-luckywheel .wheel-bg {
        width: 100%;
        height: 100%;
        border-radius: unset;
        overflow: hidden;
        transition: transform 4s ease-in-out;
        background-size: 100% 100% !important;
      }

      .v-luckywheel .wheel-bg.freeze {
        transition: none;
        background: red;
      }

      .v-luckywheel .prize-list {
        width: 100%;
        height: 100%;
        position: relative;
        text-align: center;
      }

      .v-luckywheel .prize-item-wrapper {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 150px;
        height: 150px;
      }

      .v-luckywheel .prize-item {
        width: 100%;
        height: 100%;
        transform-origin: bottom;
      }

      .v-luckywheel .prize-item .prize-name {
        padding: 14px 0;
        font-weight: 700;
      }
    }

    @media (min-width: 769px) {
      .v-luckywheel .wheel-wrapper {
        width: 750px;
        height: 750px;
        position: relative;
      }

      .v-luckywheel .wheel-pointer {
        width: 122px;
        height: 122px;
        background: url(/images/spin-quest/pointer1.png);
        background-size: 71%;
        background-repeat: no-repeat;
        background-position: 50%;
        position: absolute;
        left: 50%;
        top: 48.7%;
        transform: translate(-50%, -50%);
        z-index: 10;
      }

      .v-luckywheel .wheel-bg {
        width: 100%;
        height: 100%;
        border-radius: unset;
        overflow: hidden;
        transition: transform 4s ease-in-out;
        background-size: 100% 100% !important;
      }

      .v-luckywheel .wheel-bg.freeze {
        transition: none;
        background: red;
      }

      .v-luckywheel .prize-list {
        width: 100%;
        height: 100%;
        position: relative;
        text-align: center;
      }

      .v-luckywheel .prize-item-wrapper {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 250px;
        height: 250px;
      }

      .v-luckywheel .prize-item {
        width: 100%;
        height: 100%;
        transform-origin: bottom;
      }

      .v-luckywheel .prize-item .prize-name {
        padding: 14px 0;
        font-weight: 700;
      }
    }

    .v-luckywheel img {
      border-radius: unset;
      overflow: hidden;
    }
  </style>
@endsection
<x-app-layout meta-seo="ocean">
  <section>
    <div class="mb-5 text-center">
      <h1 class="mb-1 text-[20px] md:text-[30px] category-name text-primary"> <i class="fa-solid fa-dharmachakra text-primary"></i> {{ $pageTitle }} <i class="fa-solid fa-dharmachakra text-primary text-primary"></i></h1>
      <div class="bg-primary mx-auto h-[3px] w-[170px]"></div>
    </div>
    <div class="mt-4 flex items-center rounded-lg bg-white dark:bg-[#201E43] dark: text-white px-3 py-3 shadow-lg mb-3">
      {!! $spinQuest->descr !!}
    </div>
    <div>
      <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
        <div class="border-primary shadow-primary col-span-1 gap-4 rounded-xl border border-b p-4 text-center bg-white dark:bg-[#201E43] shadow-xl sm:col-span-2">
          <div class="v-luckywheel relative flex justify-center">
            <div class="wheel-wrapper">
              <a class="wheel-pointer cursor-pointer opacity-75 hover:opacity-100 start-spin" id="start"></a>
              <img alt="" src="{{ Helper::getValidImage($spinQuest->image) }}" id="spin">
            </div>

          </div>
        </div>
        <div>
          <div class="shadow-primary border-primary col-span-1 gap-4 rounded-xl border border-b bg-white dark:bg-[#201E43] p-4 text-center shadow-xl sm:col-span-2 max-h-[790px]">
            <div class="mb-2">
              <button class="btn btn-sm btn-primary w-full mb-2 start-spin"><i class="fas fa-credit-card me-1"></i> Giá {{ Helper::formatCurrency($spinQuest->price) }} / Lượt</button>
              <div class="flex gap-3">
                <button class="btn btn-sm btn-danger w-full" id="play_test"><i class="fas fa-play me-1"></i> Chơi Thử</button>

              </div>
            </div>
            <div class="border border-b-black-200"></div>
            <marquee direction="down" class="p-3" height="700" onmouseover="this.stop();" onmouseout="this.start();" scrolldelay="1" behavior="scroll">
              <p class="text-center text-gray-500 my-auto">{{ __('Chưa có dữ liệu lượt chơi') }}</p>
            </marquee>
          </div>
        </div>
      </div>
    </div>
  </section>
  @push('scripts')
    <script src="/plugins/rotate/rotate.js"></script>
    <script>
      $(document).ready(function() {

        var bRotate = false;

        function rotateFn(angles, txt, type) {
          bRotate = !bRotate;
          $('#spin').stopRotate();
          $('#spin').rotate({
            angle: 0,
            animateTo: angles + 1800,
            duration: 4000, // tốc độ quay tay
            callback: function() {
              var awar = txt;

              Swal.fire('Thành công !', awar, 'success').then(() => {
                location.reload()
              })

              bRotate = !bRotate;
            }
          })
        }

        var isProcessing = false;

        $('.start-spin').click(function() {
          if (bRotate || isProcessing) {
            return;
          };

          isProcessing = true;
          // Add loading state or disable button if needed visually

          axios.post('/api/games/spin-quest/turn', {
            id: {{ $spinQuest->id }}
          }).then(({
            data: r
          }) => {
            rotateFn(r.location, r.message, "success")
          }).catch(e => {
            Swal.fire('Oops ...', $catchMessage(e), 'error')
          }).finally(() => {
            isProcessing = false;
          })
        });

        $('#play_test').click(function(e) {
          if (bRotate) {
            return;
          };

          $setLoading(e.target)

          axios.post('/api/games/spin-quest/turn-test', {
            id: {{ $spinQuest->id }},
            test: true
          }).then(({
            data: r
          }) => {
            rotateFn(r.location, r.message, "success")
          }).catch(e => {
            Swal.fire('Oops ...', $catchMessage(e), 'error')
          }).finally(() => {
            $removeLoading(e.target)
          })
        });

      });
    </script>
  @endpush
</x-app-layout>
