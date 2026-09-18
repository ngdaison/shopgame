@section('title', __t('Đăng ký tài khoản'))
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
      <h4 class="font-medium">{{ __t('Tạo Tài Khoản') }}</h4>
      <div class="text-base text-slate-500">
        {{ __t('Đăng ký tài khoản mới miễn phí') }}
      </div>
    </div>

    <!-- START::LOGIN FORM -->
    <x-registation-form></x-registation-form>
    <!-- END::LOGIN FORM -->

    <div class="relative border-b border-b-[#9AA2AF] border-opacity-[16%] pt-6">
      <div class="absolute left-1/2 top-1/2 inline-block min-w-max -translate-x-1/2 transform bg-white px-4 text-sm font-normal text-slate-500 dark:bg-slate-800 dark:text-slate-400">
        {{ __t('Hoặc tiếp tục với') }}
      </div>
    </div>
    <div class="mx-auto mt-4 w-full max-w-[242px]">
      <x-social-login></x-social-login>
    </div>
    <div class="mx-auto mt-6 text-sm font-normal uppercase text-slate-500 dark:text-slate-400 md:max-w-[345px]">
      <span> {{ __t('BẠN ĐÃ CÓ TÀI KHOẢN?') }}</span>
      <a href="{{ route('login') }}" class="font-medium text-slate-900 hover:underline dark:text-white">
        {{ __t('ĐĂNG NHẬP NGAY') }}
      </a>
    </div>
  </div>
</x-app-layout>
