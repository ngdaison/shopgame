@section('title', __t($pageTitle))
<x-app-layout>
  <section id="vue-app" class="space-y-6">
    <div class="mx-auto max-w-4xl">
      @php
        $noticeContent = Helper::getNotice('page_deposit_card');
        $cleanNotice = trim(strip_tags(html_entity_decode($noticeContent ?? '')));
      @endphp
      @if (!empty($cleanNotice))
      <div class="mt-6">
        <div class="card">
          <div class="card-body flex flex-col p-6">
            <div class="card-text h-full space-y-4">
              {!! $noticeContent !!}
            </div>
          </div>
        </div>
      </div>
      @endif
      
      {{-- Tabs menu --}}
      <div class="mt-6">
        <ul class="flex cursor-pointer flex-wrap justify-center text-center font-medium text-white">
          @if ($cardOn)
          @endif
        </ul>

        {{-- Logo các nhà mạng --}}
        <div class="flex justify-center items-center space-x-8 mb-4">
           @php
              $cardList = !empty($fees) ? array_keys($fees) : [];
           @endphp
           @foreach($cardList as $type)
              <img src="{{ Helper::getValidImage('/images/logo/' . ucfirst(strtolower($type)) . '.png') }}" alt="{{ $type }}" style="height: 18px;" onerror="this.style.display='none'">
           @endforeach
        </div>

        {{-- Nội dung tab --}}
        <div class="tab-content mt-4">
          @if ($cardOn)
            <div id="tab-card" class="tab-pane active">
              <div class="rounded-lg bg-[#025464] p-4">
                <form class="space-y-3" id="form-sendcard">
                  <div class="input-area">
                    <label class="font-medium text-white">{{ __t('Loại thẻ') }}</label>
                    <select class="form-control" name="telco" required>
                      <option value="">{{ __t('Chọn loại thẻ') }}</option>
                      @php
                          // cardList is already defined above
                      @endphp
                      @foreach ($cardList as $type)
                        @php $fee = $fees[$type] ?? 20; @endphp
                        <option value="{{ $type }}">
                          {{ $type }}@if ($fee != 0) - {{ __t('Phí') }} {{ $fee }}% @endif
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="input-area">
                    <label class="font-medium text-white">{{ __t('Mệnh giá') }}</label>
                    <select class="form-control" name="amount" required>
                      <option value="">{{ __t('Chọn mệnh giá') }}</option>
                    </select>
                  </div>

                  <div class="input-area">
                    <label class="font-medium text-white">{{ __t('Số serial') }}</label>
                    <input type="text" class="form-control" name="serial" placeholder="{{ __t('Nhập số serial') }}" required>
                  </div>

                  <div class="input-area">
                    <label class="font-medium text-white">{{ __t('Mã thẻ') }}</label>
                    <input type="text" class="form-control" name="code" placeholder="{{ __t('Nhập mã thẻ') }}" required>
                  </div>

                  <div class="font-medium text-lime-400 text-white italic">
                    {{ __t('Nếu Chọn Sai Mệnh Giá Sẽ Bị Mất Thẻ!') }}
                  </div>

                  <div class="input-area">
                    <button class="btn btn-success w-full" type="submit">
                      {{ __t('Gửi để nhận') }} <span class="real_amount">0đ</span>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>


    <div class="card">
        <header class="card-header noborder">
            <h4 class="card-title">{{ __t('Lịch sử nạp thẻ') }}</h4>
        </header>
        <div class="card-body px-6 pb-6" id="vue-transaction-history">
            <account-card-list />
        </div>
    </div>
  </section>

  @push('scripts')
    @vite(['resources/js/modules/account/profile/index.js'])
    <script type="module">
      const CARD_FEES = @json($fees);
      const SPECIFIC_FEES = @json($specific_fees);
      const ALLOWED_DENOMINATIONS = @json($allowed_denominations);

      const sumAmount = () => {
        const telco = $('[name="telco"]').val();
        const amount = parseInt($('[name="amount"]').val());
        let fee = CARD_FEES[telco] ?? 0;
        
        // Check specific fee
        if (SPECIFIC_FEES?.[telco]?.[amount] !== undefined) {
             fee = SPECIFIC_FEES[telco][amount];
        }

        if (amount && telco) {
          const real = fee === 0 ? amount : amount - (amount * fee / 100);
          $(".real_amount").text($formatCurrency(real));
        } else {
          $(".real_amount").text("0đ");
        }
      }

      $('[name="amount"]').on('change', sumAmount);
      
      $('[name="telco"]').on('change', function() {
          const telco = $(this).val();
          const $amountSelect = $('[name="amount"]');
          const currentAmount = parseInt($amountSelect.val());
          
          let allowed = [];
          
          if (ALLOWED_DENOMINATIONS?.[telco]) {
              allowed = ALLOWED_DENOMINATIONS[telco];
          }
          
          // Rebuild options
          $amountSelect.empty();
          $amountSelect.append('<option value="">{{ __t('Chọn mệnh giá') }}</option>');
          
          allowed.forEach(val => {
              let text = $formatCurrency(val);
              if (SPECIFIC_FEES?.[telco]?.[val] !== undefined) {
                  text += ` - {{ __t('Phí riêng') }} ${SPECIFIC_FEES[telco][val]}%`;
              }
              $amountSelect.append(`<option value="${val}">${text}</option>`);
          });
          
          if (allowed.includes(currentAmount)) {
              $amountSelect.val(currentAmount);
          }
          
          sumAmount();
      });

      $('#form-sendcard').on('submit', async function (e) {
        e.preventDefault();
        $showLoading();

        try {
          const { data: result } = await axios.post('/api/accounts/send-card', $formDataToPayload(new FormData(this)));
          Swal.fire('Thành công', result.message, 'success').then(() => this.reset());
        } catch (err) {
          Swal.fire('Thất bại', err.response?.data?.message || 'Lỗi không xác định', 'error');
        }
      });

      $('.tab-button').click(function () {
        $('.tab-button').removeClass('active-tab bg-[#025464]').addClass('bg-gray-600');
        $(this).addClass('active-tab bg-[#025464]');
        $('.tab-pane').removeClass('active').hide();
        $('#' + $(this).data('tab')).addClass('active').show();
      });
    </script>
  @endpush
</x-app-layout>
