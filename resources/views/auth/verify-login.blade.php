@section('title', __t('Xác thực đăng nhập'))
<x-app-layout>
  <div class="card auth-box flex h-full flex-col justify-center">
    <div class="mb-4 text-center 2xl:mb-10">
      <h4 class="font-medium"> {{ __t('Xác Thực Đăng Nhập') }}</h4>
      <div class="text-base text-slate-500">
        {{ __t('Vui lòng nhập mã xác thực để tiếp tục') }}
      </div>
    </div>

    <form action="{{ route('account.login_verify.post') }}" method="POST" class="space-y-4">
        @csrf
        @if(session('verify_email'))
            <div class="input-area">
                <label class="form-label">{{ __t('Mã OTP từ Email') }}</label>
                <div class="relative">
                    <input type="text" name="email_otp" class="form-control" placeholder="Nhập mã 6 số từ Email">
                </div>
                <p class="text-xs text-slate-500 mt-1">Mã đã được gửi đến email của bạn.</p>
            </div>
        @endif

        @if(session('verify_2fa'))
            <div class="input-area">
                <label class="form-label">{{ __t('Mã Google Authenticator') }}</label>
                <div class="relative">
                    <input type="text" name="google_2fa" class="form-control" placeholder="Nhập mã 6 số từ ứng dụng 2FA">
                </div>
            </div>
        @endif

        <button type="submit" class="btn btn-dark block w-full text-center">
            {{ __t('Xác Nhận') }}
        </button>
    </form>

    <div class="mx-auto mt-12 text-sm font-normal uppercase text-slate-500 dark:text-slate-400 md:max-w-[345px]">
      <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="font-medium text-slate-900 hover:underline dark:text-white bg-transparent border-0 p-0">
            {{ __t('Hủy bỏ và Đăng xuất') }}
          </button>
      </form>
    </div>
  </div>
</x-app-layout>
