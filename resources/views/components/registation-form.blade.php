<form method="POST" action="{{ route('register') }}" class="space-y-4">
  @csrf

  {{-- Username --}}
  <div class="fromGroup">
    <label for="username" class="form-label block capitalize">
      {{ __t('Tên tài khoản') }}
    </label>
    <input type="text" name="username" id="username" class="form-control border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none shadow-none @error('username') !border !border-red-500 @enderror py-2" placeholder="{{ __t('Nhập tên tài khoản của bạn') }}" required autofocus
      value="{{ old('username') }}">
    <x-input-error :messages="$errors->get('username')" class="mt-2" />
  </div>

  {{-- Email --}}
  <div class="fromGroup">
    <label for="email" class="form-label block capitalize">
      {{ __t('Địa chỉ e-mail') }}
    </label>
    <input type="text" name="email" id="email" class="form-control border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none shadow-none @error('email') !border !border-red-500 @enderror py-2" placeholder="{{ __t('Không bắt buộc, dùng để cấp lại mật khẩu') }}"
      value="{{ old('email') }}">
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
  </div>

  {{-- Password --}}
  <div class="fromGroup">
    <label for="password" class="form-label block capitalize">
      {{ __t('Mật khẩu') }}
    </label>
    <input type="password" name="password" id="password" class="form-control border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none shadow-none @error('password') !border !border-red-500 @enderror py-2" placeholder="{{ __t('Nhập mật khẩu  của bạn') }}" required
      autocomplete="new-password">
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
  </div>

  {{-- Terms & Condition Checkbox --}}
  {{-- <div class="flex justify-between">
    <div class="checkbox-area">
      <label class="inline-flex cursor-pointer items-center" for="checkbox">
        <input type="checkbox" class="hidden" name="terms" id="checkbox" required>
        <span class="relative inline-flex h-4 w-4 flex-none rounded border border-slate-100 bg-slate-100 transition-all duration-150 ltr:mr-3 rtl:ml-3 dark:border-slate-800 dark:bg-slate-900">
          <img src="/images/icon/ck-white.svg" alt="" class="m-auto block h-[10px] w-[10px] opacity-0"></span>
        <span class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __t('You accept our Terms and Conditions and Privacy Policy') }}</span>
      </label>
    </div>
    <x-input-error :messages="$errors->get('terms')" class="mt-2" />
  </div> --}}

  <button type="submit" class="btn btn-dark block w-full text-center">
    {{ __t('Đăng Ký Ngay') }}
  </button>
</form>
