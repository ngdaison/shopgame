@section('title', __t($pageTitle ?? 'Xác thực hành động'))
<x-app-layout>
  <div class="card auth-box flex h-full flex-col justify-center">
    <div class="mb-4 text-center 2xl:mb-10">
      <h4 class="font-medium"> {{ __t($pageTitle ?? 'Xác Thực') }}</h4>
      <div class="text-base text-slate-500">
        {{ __t($description ?? 'Vui lòng nhập mã xác thực để tiếp tục.') }}
      </div>
    </div>

    <form id="verify-form" action="{{ route('account.verify.post') }}" method="POST" class="space-y-4">
        @csrf
        
        @if(isset($payload))
             <input type="hidden" name="payload" value="{{ $payload }}">
        @endif
        
        @if(isset($verifyMethod) && $verifyMethod === 'email')
            <div class="input-area">
                <label class="form-label">{{ __t('Mã OTP từ Email') }}</label>
                <div class="relative">
                    <input type="text" name="otp" class="form-control" placeholder="Nhập mã 6 số từ Email" required>
                </div>
                <p id="otp-message" class="text-xs {{ $errors->has('otp') ? 'text-red-500' : 'text-slate-500' }} mt-1">
                    @if($errors->has('otp'))
                        {{ $errors->first('otp') }}
                    @else
                        {{ __t('Mã đã được gửi đến email của bạn.') }}
                    @endif
                </p>
                <div id="resend-container" class="mt-2 text-xs">
                    <button type="button" id="resend-btn" onclick="resendOtp()" class="text-primary-500 font-medium hover:underline disabled:text-slate-400 disabled:no-underline" disabled>
                        @if(($remaining ?? 0) > 0)
                            {{ __t('Gửi lại mã') }} (<span id="countdown">{{ $remaining }}</span>s)
                        @else
                            {{ __t('Đang gửi mã...') }}
                        @endif
                    </button>
                </div>
                @if(Cache::has('verify_pending_email_' . Auth::id()))
                     <p class="text-[10px] text-primary italic">(*) Xác minh gửi đến: {{ Cache::get('verify_pending_email_' . Auth::id()) }}</p>
                @endif
            </div>
        @elseif(isset($verifyMethod) && $verifyMethod === 'google2fa')
            <div class="input-area">
                <label class="form-label">{{ __t('Mã Google Authenticator') }}</label>
                <div class="relative">
                    <input type="text" name="otp" class="form-control" placeholder="Nhập mã 6 số từ ứng dụng 2FA" required>
                </div>
                <p id="otp-message" class="text-xs text-red-500 mt-1"></p>
            </div>
        @elseif(isset($verifyMethod) && $verifyMethod === 'google2fa_setup')
            <div class="text-center mb-6">
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">{{ __t('Dùng ứng dụng Google Authenticator để quét mã QR bên dưới.') }}</p>
                @if(isset($qrCodeImage))
                    <div class="inline-block p-4 bg-white rounded-lg mb-4 border border-slate-200">
                        <img src="{{ $qrCodeImage }}" alt="QR Setup" class="mx-auto w-[180px] h-[180px] select-none" draggable="false" oncontextmenu="return false;">
                    </div>
                @endif
                @if(isset($secret))
                    <div class="mb-4">
                        <p class="text-xs text-slate-500 mb-1">{{ __t('Hoặc nhập thủ công mã bí mật:') }}</p>
                        <code class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-primary-500 font-bold select-all">{{ $secret }}</code>
                    </div>
                @endif
            </div>
            <div class="input-area">
                <label class="form-label">{{ __t('Nhập Mã Xác Minh (6 số)') }}</label>
                <div class="relative">
                    <input type="text" name="otp" class="form-control" placeholder="000000" maxlength="6" required>
                </div>
                <p id="otp-message" class="text-xs text-red-500 mt-1"></p>
            </div>
        @endif


        <button type="submit" id="submit-btn" class="btn btn-dark block w-full text-center">
            {{ __t('Xác Nhận') }}
        </button>
    </form>

    <div class="mx-auto mt-12 text-sm font-normal uppercase text-slate-500 dark:text-slate-400 md:max-w-[345px]">
      <a href="{{ route('account.verify.cancel') }}" class="font-medium text-slate-900 hover:underline dark:text-white">
        {{ __t('Hủy bỏ & Quay lại') }}
      </a>
    </div>
  </div>
@push('scripts')
  <script>
    $(document).ready(function() {
      let Toast;
      function getToast() {
        if (!Toast && typeof Swal !== 'undefined') {
          Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        }
        return Toast;
      }

      function showToast(icon, title) {
        const toast = getToast();
        if (toast) {
          toast.fire({ icon: icon, title: title });
        } else if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: icon, title: title, timer: 2000, showConfirmButton: false });
        }
      }

      @if (isset($verifyMethod) && $verifyMethod === 'email')
        let timer = null;

        function startCountdown(seconds) {
          if (timer) clearInterval(timer);
          
          let count = Math.floor(seconds); // Use floor for better sync
          if (count <= 0) {
             $('#resend-btn').prop('disabled', false).html('Gửi lại mã ngay').addClass('text-primary-500').removeClass('text-slate-400');
             return;
          }

          $('#resend-btn').prop('disabled', true).removeClass('text-primary-500').addClass('text-slate-400');
          $('#resend-btn').html(`Gửi lại mã (<span id="countdown">${count}</span>s)`);

          timer = setInterval(function() {
            count--;
            if (count <= 0) {
              clearInterval(timer);
              timer = null;
              $('#resend-btn').prop('disabled', false).html('Gửi lại mã ngay').addClass('text-primary-500').removeClass('text-slate-400');
            } else {
              let countdownEl = $('#countdown');
              if (countdownEl.length) {
                countdownEl.text(count);
              } else {
                $('#resend-btn').html(`Gửi lại mã (<span id="countdown">${count}</span>s)`);
              }
            }
          }, 1000);
        }

        // Initial setup from server
        @php $initialRemaining = $remaining ?? 0; @endphp
        let initialRemaining = {{ $initialRemaining }};

        if (initialRemaining > 0) {
          startCountdown(initialRemaining);
        } else {
          // Auto-trigger the first OTP send after entering the page
          $('#resend-btn').prop('disabled', true).html('{{ __t("Đang gửi mã...") }}').removeClass('text-primary-500').addClass('text-slate-400');
          setTimeout(function() {
             sendRequest();
          }, 800); // 800ms delay to ensure page is visible first
        }

        function sendRequest() {
          showToast('info', 'Đang gửi mã OTP...');
          $.post('{{ route('account.verify.resend') }}', {
            _token: '{{ csrf_token() }}'
          }).done(function(res) {
            showToast('success', 'Đã gửi mã OTP thành công!');
            startCountdown(60);
          }).fail(function(xhr) {
            let msg = 'Gửi OTP thất bại. Vui lòng thử lại.';
            if (xhr.status === 429) {
              msg = xhr.responseJSON.message;
              let remaining = xhr.responseJSON.remaining || 60;
              startCountdown(remaining);
            } else {
              $('#resend-btn').prop('disabled', false).html('Gửi lại mã ngay').addClass('text-primary-500').removeClass('text-slate-400');
            }

            showToast('error', msg);
          });
        }

        window.resendOtp = function() {
          if ($('#resend-btn').is(':disabled')) return;
          $('#resend-btn').prop('disabled', true);
          sendRequest();
        };
      @endif

      // Form Submission (Always active)
      $('#verify-form').on('submit', function(e) {
          e.preventDefault();
          const $btn = $('#submit-btn');
          const originalHtml = $btn.html();
          
          $btn.prop('disabled', true).text('Đang xác thực...');

          $.ajax({
              url: $(this).attr('action'),
              method: 'POST',
              data: $(this).serialize(),
              success: function(res) {
                  if (res.success) {
                      showToast('success', res.message || 'Xác thực thành công!');
                      setTimeout(() => {
                          window.location.href = res.redirect_url || '/';
                      }, 300);
                  } else {
                      $btn.prop('disabled', false).html(originalHtml);
                      let msg = res.message || 'Xác thực thất bại. Vui lòng thử lại.';
                      $('#otp-message').removeClass('text-slate-500').addClass('text-red-500').text(msg);
                  }
              },
              error: function(xhr) {
                  $btn.prop('disabled', false).html(originalHtml);
                  let msg = 'Xác thực thất bại. Vui lòng thử lại.';
                  if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                      msg = xhr.responseJSON.message;
                  } else if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.otp) {
                      msg = xhr.responseJSON.errors.otp[0];
                  }
                  
                  // Show error in the text area instead of toast for better UX
                  $('#otp-message').removeClass('text-slate-500').addClass('text-red-500').text(msg);
              }
          });
      });
    });
  </script>
@endpush
</x-app-layout>
