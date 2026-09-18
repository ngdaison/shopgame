@section('css')
<style>
    .counter-value { display: inline-block; }
    
    /* Clean tab content display */
    .tab-content.hidden {
        display: none !important;
    }
    .tab-content.block {
        display: block !important;
    }
    
    .withdraw-method-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    .withdraw-method-card.active {
        border-color: #3b82f6;
        background-color: rgba(59, 130, 246, 0.05);
    }
    
    /* Ensure tabs are clickable without aggressive z-index unless necessary */
    .tab-btn {
        cursor: pointer !important;
    }
</style>
@endsection

<x-app-layout>
  <script>
      /**
       * Affiliate Configuration (Global)
       */
      window.AffiliateConfig = {
          update_code_url: "{{ route('api.users.affiliates.update-code') }}",
          history_url: "{{ route('api.users.affiliates.history') }}",
          min_withdraw: {{ (int)($config['min_withdraw'] ?? 100000) }},
          max_withdraw: {{ (int)($config['max_withdraw'] ?? 10000000) }},
          balance: {{ (int)(Auth::user()->balance_1 ?? 0) }},
          withdraw_url: "{{ route('api.users.affiliates.withdraw') }}",
          withdraw_history_url: "{{ route('api.users.affiliates.withdraw-history') }}"
      };

      /**
       * Tab Switching Logic (Isolated for stability)
       */
      function profileTabSwitch(target) {
          console.log('[DEBUG] Tab Switch Triggered:', target);
          if (!target) return;

          try {
              // 1. Hide all contents
              const contents = document.querySelectorAll('.tab-content');
              contents.forEach(el => {
                  el.classList.add('hidden');
                  el.classList.remove('block');
              });

              // 2. Show active content
              const activeContent = document.getElementById('tab-' + target);
              if (activeContent) {
                  activeContent.classList.remove('hidden');
                  activeContent.classList.add('block');
                  console.log('[DEBUG] Activated:', 'tab-' + target);
              } else {
                  console.error('[DEBUG] Tab content not found:', 'tab-' + target);
              }

              // 3. Update buttons
              const buttons = document.querySelectorAll('.tab-btn');
              buttons.forEach(btn => {
                  btn.classList.remove('border-primary-500', 'text-primary-500', 'font-bold', 'active');
                  btn.classList.add('border-transparent', 'text-slate-600');
              });

              const activeBtn = document.getElementById('tab-btn-' + target);
              if (activeBtn) {
                  activeBtn.classList.add('border-primary-500', 'text-primary-500', 'font-bold', 'active');
                  activeBtn.classList.remove('border-transparent', 'text-slate-600');
                  console.log('[DEBUG] Button Updated:', 'tab-btn-' + target);
              }

              // 4. Update URL (without reload)
              const url = new URL(window.location.href);
              const currentTab = url.searchParams.get('tab') || 'info';
              
              if (target === 'info') {
                  url.searchParams.delete('tab');
              } else {
                  url.searchParams.set('tab', target);
              }
              
              // Only push state if it changed to avoid redundant history entries
              if (currentTab !== target) {
                  window.history.pushState({}, '', url);
              }
          } catch (e) {
              console.error('[DEBUG] Switch Error:', e);
          }
      }
      window.affiliateTabSwitch = profileTabSwitch;

      /**
       * Handle Update Code Form
       */
      window.handleUpdateCode = function(e) {
          e.preventDefault();
          const form = e.target;
          const config = window.AffiliateConfig || {};
          const formData = new FormData(form);
          
          if (!window.axios) {
              console.error('[DEBUG] Axios not found!');
              return false;
          }

          window.axios.post(config.update_code_url, formData)
              .then(response => {
                  if (response.data.status === 200) {
                      // Use layout's built-in pending_toast system
                      sessionStorage.setItem('pending_toast', JSON.stringify({
                          icon: 'success',
                          title: 'Thành công!',
                          message: response.data.message
                      }));
                      window.location.reload();
                  } else {
                      window.Swal.fire({
                          icon: 'error',
                          title: 'Lỗi!',
                          text: response.data.message || 'Có lỗi xảy ra',
                      });
                  }
              })
              .catch(error => {
                  console.error('[DEBUG] Update Code Error:', error);
                  let msg = 'Có lỗi xảy ra khi xử lý yêu cầu.';
                  
                  if (error.response && error.response.data) {
                      if (error.response.data.message) {
                          msg = error.response.data.message;
                      } else if (error.response.data.errors) {
                          msg = Object.values(error.response.data.errors).flat().join('\n');
                      }
                  } else if (error.message) {
                      msg = error.message;
                  }

                  window.Swal.fire({
                      icon: 'error',
                      title: 'Lỗi!',
                      text: msg,
                      confirmButtonText: 'Đóng'
                  });
              });
          return false;
      };

      /**
       * Handle Withdraw Form
       */
      window.handleWithdraw = function(e) {
          e.preventDefault();
          const form = e.target;
          const config = window.AffiliateConfig || {};
          const submitBtn = document.getElementById('btn-withdraw-submit');
          
          if (!window.axios || !window.Swal) {
              console.error('[DEBUG] Global dependencies missing (Axios/Swal)');
              return false;
          }

          const originalBtnText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

          const formData = new FormData(form);

          window.axios.post(config.withdraw_url, formData)
              .then(response => {
                  if (response.data.status === 200) {
                      // Use layout's built-in pending_toast system
                      sessionStorage.setItem('pending_toast', JSON.stringify({
                          icon: 'success',
                          title: 'Thành công!',
                          message: response.data.message
                      }));
                      window.location.reload();
                  } else {
                      window.Swal.fire({
                          icon: 'error',
                          title: 'Lỗi!',
                          text: response.data.message || 'Có lỗi xảy ra',
                      });
                  }
              })
              .catch(error => {
                  console.error('Withdraw Error:', error);
                  let msg = 'Có lỗi xảy ra khi xử lý yêu cầu.';
                  if (error.response && error.response.data && error.response.data.message) {
                      msg = error.response.data.message;
                  } else if (error.response && error.response.data && error.response.data.errors) {
                       msg = Object.values(error.response.data.errors).flat().join('\n');
                  }
                  
                  window.Swal.fire({
                      icon: 'error',
                      title: 'Lỗi!',
                      text: msg,
                  });
              })
              .finally(() => {
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = originalBtnText;
              });
          return false;
      };

      /**
       * Handle Bank Selection
       */
      window.handleBankSelect = function(el) {
          const selectedOption = el.options[el.selectedIndex];
          if (selectedOption && selectedOption.value) {
              const name = selectedOption.getAttribute('data-name');
              const number = selectedOption.getAttribute('data-number');
              const owner = selectedOption.getAttribute('data-owner');

              const bankNameInput = document.getElementById('bank_name');
              const accNumInput = document.getElementById('account_number');
              const accNameInput = document.getElementById('account_name');

              if(bankNameInput) bankNameInput.value = name || '';
              if(accNumInput) accNumInput.value = number || '';
              if(accNameInput) accNameInput.value = owner || '';
          }
      };

      // Handle initial tab from URL - Immediate check + DOMContentLoaded check
      (function() {
          function initTab() {
              const urlParams = new URLSearchParams(window.location.search);
              const activeTab = urlParams.get('tab') || 'info';
              console.log('[DEBUG] Initializing Tab:', activeTab);
              profileTabSwitch(activeTab);
          }

          if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', function() {
                  // Slight delay to outrun other scripts that might reset UI
                  setTimeout(initTab, 50);
              });
          } else {
              setTimeout(initTab, 50);
          }
      })();
  </script>

  <div class="tf-container pb-20">
    <div class="tf-spacing-16"></div>
    
    <!-- Header Notice -->
    <div class="card bg-white dark:bg-slate-900 shadow-sm rounded-xl mb-6 border border-slate-100 dark:border-slate-800">
      <div class="card-body p-4 text-sm">
        {!! $config['notice'] ?? 'Chào mừng bạn đến với chương trình Tiếp thị liên kết!' !!}
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
      <div class="card border-0 bg-blue-50 dark:bg-blue-900/20 p-5 rounded-2xl">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-600">
            <i class="fas fa-wallet text-xl"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __t('Số Dư Hoa Hồng') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none counter-value" data-value="{{ Auth::user()->balance_1 ?? 0 }}" data-type="currency">0</h3>
          </div>
        </div>
      </div>
      <div class="card border-0 bg-emerald-50 dark:bg-emerald-900/20 p-5 rounded-2xl">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-600">
            <i class="fas fa-hand-holding-usd text-xl"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __t('Hoa Hồng Đã Rút') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none counter-value" data-value="{{ Auth::user()->total_withdraw ?? 0 }}" data-type="currency">0</h3>
          </div>
        </div>
      </div>
      <div class="card border-0 bg-rose-50 dark:bg-rose-900/20 p-5 rounded-2xl">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-600">
            <i class="fas fa-chart-line text-xl"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __t('Tổng Thu Nhập') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none counter-value" data-value="{{ $totalCommission ?? 0 }}" data-type="currency">0</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="card border-0 bg-amber-50 dark:bg-amber-900/20 p-5 rounded-2xl">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-600">
            <i class="fas fa-mouse-pointer text-xl"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __t('Lượt Clicks') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none counter-value" data-value="{{ $affiliate->clicks ?? 0 }}" data-type="number">0</h3>
          </div>
        </div>
      </div>
      <div class="card border-0 bg-indigo-50 dark:bg-indigo-900/20 p-5 rounded-2xl">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-600">
            <i class="fas fa-user-plus text-xl"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __t('Thành Viên Mới') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none counter-value" data-value="{{ $affiliate->signups ?? 0 }}" data-type="number">0</h3>
          </div>
        </div>
      </div>
      <div class="card border-0 bg-violet-50 dark:bg-violet-900/20 p-5 rounded-2xl">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-violet-500/10 flex items-center justify-center text-violet-600">
            <i class="fas fa-shopping-cart text-xl"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __t('Tổng Đơn Hàng') }}</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none counter-value" data-value="{{ $totalOrders ?? 0 }}" data-type="number">0</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation -->
    <div class="w-full relative z-[50]">
        <div class="mb-6">
            <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700">
                <button type="button" onclick="profileTabSwitch('info')" id="tab-btn-info" class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-primary-500 text-primary-500 font-bold transition-all active cursor-pointer">
                    {{ __t('Thông Tin') }}
                </button>
                <button type="button" onclick="profileTabSwitch('history')" id="tab-btn-history" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all cursor-pointer">
                    {{ __t('Lịch Sử') }}
                </button>
                <button type="button" onclick="profileTabSwitch('withdraw')" id="tab-btn-withdraw" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all cursor-pointer">
                    {{ __t('Rút Tiền') }}
                </button>
            </div>
        </div>
    </div>

    <div class="tab-content-wrapper">
        <!-- Tab Content: Info -->
        <div id="tab-info" class="tab-content block">
            <div class="grid grid-cols-1 gap-8">
                <div class="card bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-6">
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                      <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-4 uppercase flex items-center gap-2">
                        <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                        {{ __t('Liên Kết Giới Thiệu') }}
                      </h4>
                      <div class="p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/50 rounded-xl mb-6">
                        <p class="text-sm text-blue-700 dark:text-blue-300 leading-relaxed">
                          <i class="fas fa-info-circle mr-1"></i>
                          {{ __t('Bạn sẽ nhận được') }} <b>{{ $config['comm_percent'] ?? 10 }}%</b> 
                          {{ ($config['commission_type'] ?? 'deposit') == 'deposit' ? __t('hoa hồng khi cấp dưới nạp tiền') : __t('hoa hồng khi cấp dưới mua sản phẩm') }}.
                        </p>
                      </div>
                      <div class="space-y-4">
                        <div class="input-area">
                          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">{{ __t('Link của bạn') }}</label>
                          <div class="flex">
                            <input type="text" id="referral-link" class="form-control flex-1 !rounded-r-none bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700" value="{{ route('ref', ['ref' => $affiliate->code]) }}" readonly>
                            <button class="btn btn-primary px-4 rounded-l-none" onclick="copyToClipboard('referral-link')">
                              <i class="fas fa-copy"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div>
                      <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-4 uppercase flex items-center gap-2">
                        <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                        {{ __t('Tùy Chỉnh Mã Giới Thiệu') }}
                      </h4>
                      <form id="update-code-form" method="POST" action="{{ route('api.users.affiliates.update-code') }}" onsubmit="return handleUpdateCode(event)">
                        @csrf
                        <div class="input-area mb-4">
                          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">{{ __t('Mã giới thiệu mới') }}</label>
                          <input type="text" name="code" class="form-control w-full bg-transparent border-slate-300 dark:border-slate-700 rounded-xl" value="{{ $affiliate->code }}" placeholder="Nhập mã bạn muốn...">
                          <p class="text-[10px] text-slate-500 mt-2">{{ __t('Mã này sẽ thay thế mã mặc định trong link của bạn.') }}</p>
                        </div>
                        <button type="submit" class="btn btn-dark w-full py-3 rounded-xl font-bold uppercase tracking-wider">
                          {{ __t('Cập Nhật Ngay') }}
                        </button>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Added History Part into Info Tab -->
                <div class="card bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden p-6">
                  <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 uppercase flex items-center gap-2">
                    <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                    {{ __t('Lịch Sử Nhận Hoa Hồng (Gần Đây)') }}
                  </h4>
                  <div id="vue-wallet-history-info"></div>
                </div>
            </div>
        </div>

        <!-- Tab Content: History -->
        <div id="tab-history" class="tab-content hidden">
            <div class="card bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden p-6">
              <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 uppercase flex items-center gap-2">
                <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                {{ __t('Lịch Sử Nhận Hoa Hồng') }}
              </h4>
              <div id="vue-wallet-history"></div>
            </div>
        </div>

        <!-- Tab Content: Withdraw -->
        <div id="tab-withdraw" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Form Area -->
              <div class="lg:col-span-1">
                <div class="card bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-6">
                  <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 uppercase flex items-center gap-2">
                    <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                    {{ __t('Yêu Cầu Rút Tiền') }}
                  </h4>
                  
                  <div class="p-4 bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-800/30 rounded-xl mb-6">
                    <div class="flex gap-3">
                      <iconify-icon icon="heroicons:information-circle" class="text-xl text-orange-500"></iconify-icon>
                      <div class="text-xs text-orange-700 dark:text-orange-300">
                        <p class="font-bold mb-1">{{ __t('Quy định rút tiền') }}:</p>
                        <ul class="list-disc pl-4 space-y-1">
                          <li>{{ __t('Số tiền rút tối thiểu') }}: <b>{{ number_format($config['min_withdraw'] ?? 0) }} VNĐ</b></li>
                          <li>{{ __t('Số dư hiện khả dụng') }}: <b>{{ number_format(Auth::user()->balance_1 ?? 0) }} VNĐ</b></li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <form id="withdraw-form" method="POST" action="{{ route('api.users.affiliates.withdraw') }}" onsubmit="return handleWithdraw(event)">
                    @csrf
                    <input type="hidden" name="withdraw_to" value="bank">

                    <div class="input-area mb-4">
                      <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">{{ __t('Số tiền cần rút') }}</label>
                      <div class="relative">
                        <input type="number" name="amount" class="form-control w-full bg-transparent border-slate-300 dark:border-slate-700 rounded-xl font-bold text-primary-500" value="{{ $config['min_withdraw'] ?? 0 }}" required>
                      </div>
                    </div>

                    <div id="bank-selector-area" class="space-y-4 mb-4">
                      <div class="input-area">
                        <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">{{ __t('Chọn Ngân Hàng') }}</label>
                        @if($banks->isEmpty())
                          <a href="{{ route('account.profile.index') }}" class="flex items-center justify-center py-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-slate-500 hover:text-primary-500 hover:border-primary-500 transition-all text-xs cursor-pointer font-medium italic">
                            <i class="fas fa-plus-circle mr-2"></i> {{ __t('Vui lòng liên kết ngân hàng trong cài đặt tài khoản.') }}
                          </a>
                        @else
                          <select id="saved-bank-select" class="form-control w-full bg-transparent border-slate-300 dark:border-slate-700 rounded-xl mb-2" onchange="handleBankSelect(this)" required>
                            <option value="">-- {{ __t('Chọn ngân hàng') }} --</option>
                            @foreach($banks as $bank)
                              <option value="{{ $bank->id }}" data-name="{{ $bank->bank_name }}" data-number="{{ $bank->account_number }}" data-owner="{{ $bank->account_name }}">
                                {{ $bank->bank_name }} - {{ $bank->account_number }} - {{ $bank->account_name }}
                              </option>
                            @endforeach
                          </select>
                          {{-- Link removed by user request --}}
                        @endif
                      </div>
                      
                      <div class="hidden">
                        <input type="hidden" name="bank_name" id="bank_name" required>
                        <input type="hidden" name="account_number" id="account_number" required>
                        <input type="hidden" name="account_name" id="account_name" required>
                      </div>
                    </div>

                    <div class="input-area mb-6">
                      <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">{{ __t('Ghi chú (Nếu có)') }}</label>
                      <textarea name="user_note" class="form-control w-full bg-transparent border-slate-300 dark:border-slate-700 rounded-xl" rows="2" placeholder="{{ __t('Nhập ghi chú...') }}"></textarea>
                    </div>

                    <button type="submit" id="btn-withdraw-submit" class="btn btn-dark w-full py-4 rounded-xl font-black uppercase tracking-widest shadow-xl shadow-gray-900/20">
                      {{ __t('Xác Nhận Rút Tiền') }}
                    </button>
                  </form>
                </div>
              </div>

              <!-- History Area -->
              <div class="lg:col-span-2">
                <div class="card bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-6 h-full">
                  <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 uppercase flex items-center gap-2">
                    <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                    {{ __t('Lịch Sử Rút Tiền') }}
                  </h4>
                  <div id="vue-withdraw-history"></div>
                </div>
              </div>
            </div>
        </div>
    </div>
  </div>

  @push('scripts')
      <script>
        // Tab switching handles URL sync manually
  
        document.addEventListener('DOMContentLoaded', function() {
            // Additional initialization if needed
        });
  
        // Global helper for Copy
        window.copyToClipboard = function(id) {
            const copyText = document.getElementById(id);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value).then(() => {
                 Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Đã sao chép!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
      </script>
      @vite('resources/js/modules/affiliate/index.js')
  @endpush
</x-app-layout>