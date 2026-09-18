<x-app-layout>
  <div class="card auth-box flex h-full flex-col justify-center">
    <div class="mb-4 text-center 2xl:mb-10">
      <h4 class="font-medium"> {{ __t('Đặt Lại Mật Khẩu') }}</h4>
    </div>

    @if (session('status'))
      <div class="alert alert-success" role="alert">
        {{ session('status') }}
      </div>
    @endif

    <!-- START::RESET FORM -->
    <form method="POST" action="{{ route('password.update') }}">
      @csrf

      <input type="hidden" name="token" value="{{ $token }}">

      <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

      <div class="row mb-3">
        <label for="password" class="col-md-4 form-label text-md-end">{{ __t('Mật khẩu mới') }}</label>

        <div class="col-md-6">
          <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

          @error('password')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
          @enderror
        </div>
      </div>

      <div class="row mb-3">
        <label for="password-confirm" class="col-md-4 form-label text-md-end">{{ __t('Nhập lại mật khẩu') }}</label>

        <div class="col-md-6">
          <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
        </div>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-primary">
          {{ __t('Đặt Lại Ngay') }}
        </button>
      </div>
    </form>

    <!-- END::RESET FORM -->
  </div>
</x-app-layout>
