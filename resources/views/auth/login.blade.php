@section('title', __t('Đăng nhập tài khoản'))
@section('css')
    <style>
        /* Force remove focus ring/shadow for auth inputs */
        .auth-box .form-control:focus {
            box-shadow: none !important;
            outline: none !important;
            border-color: var(--primary-color) !important;
        }
    </style>
@endsection
<x-app-layout>
  <div class="card auth-box flex h-full flex-col justify-center">
    <div class="mb-4 text-center 2xl:mb-10">
      <h4 class="font-medium"> {{ __t('Đăng Nhập') }}</h4>
      <div class="text-base text-slate-500">
        {{ __t('Đăng Nhập Vào Hệ Thống') }}
      </div>
    </div>

    <!-- START::LOGIN FORM -->
    <x-login-form></x-login-form>
    <!-- END::LOGIN FORM -->

    @php
      $showSocial = false;
      foreach(['google', 'facebook', 'discord'] as $p) {
          $cfg = getSocialConfig($p);
          if ((int)($cfg['client_status'] ?? 0) === 1) {
              $showSocial = true;
              break;
          }
      }
    @endphp

    @if($showSocial)
    <div class="relative border-b border-b-[#9AA2AF] border-opacity-[16%] pt-6">
      <div class="absolute left-1/2 top-1/2 inline-block min-w-max -translate-x-1/2 transform bg-white px-4 text-sm font-normal text-slate-500 dark:bg-slate-800 dark:text-slate-400">
        {{ __t('Hoặc tiếp tục với') }}
      </div>
    </div>
    <div class="mx-auto mt-4 w-full max-w-[242px]">
      <x-social-login></x-social-login>
    </div>
    @endif

    <div class="mx-auto mt-6 text-sm font-normal uppercase text-slate-500 dark:text-slate-400 md:max-w-[345px]">
      {{ __t('Bạn Chưa Có Tài Khoản?') }}
      <a href="{{ route('register') }}" class="font-medium text-slate-900 hover:underline dark:text-white">
        {{ __t('Đăng Ký Ngay') }}
      </a>
    </div>
  </div>
</x-app-layout>
