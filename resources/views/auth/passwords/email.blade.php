<x-app-layout>
  <div class="card auth-box flex h-full flex-col justify-center">
    <div class="mb-4 text-center 2xl:mb-10">
      <h4 class="font-medium"> {{ __t('Đặt Lại Mật Khẩu') }}</h4>
      <div class="text-base text-slate-500">
        {{ __t('Nhập Email Của Bạn Để Chúng Tôi Gửi Lại Mật Khẩu Mới') }}
      </div>
    </div>

    @if (session('status'))
      <div class="alert alert-success" role="alert">
        {{ session('status') }}
      </div>
    @endif

    <!-- START::RESET FORM -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
      @csrf
      {{-- Email --}}
      <div class="fromGroup">
        <label for="email" class="form-label block capitalize">{{ __t('Địa Chỉ Email') }}</label>
        <div class="relative">
          <input type="text" name="email" id="email" class="form-control @error('email') !border !border-red-500 @enderror py-2" placeholder="{{ __t('Nhập Địa Chỉ Email') }}" autofocus
            value="{{ old('email') }}" required>
          <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
      </div>

      <button type="submit" class="btn btn-dark block w-full text-center">
        {{ __t('Đặt Lại Mật Khẩu') }}
      </button>
    </form>

    <!-- END::RESET FORM -->
  </div>
</x-app-layout>
