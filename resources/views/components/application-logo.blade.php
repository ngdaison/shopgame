<a class="flex items-center" href="{{ url('/') }}">

  <img src="{{ Helper::getValidImage(Helper::branding('logo_light', '/_assets/images/cmsnt_light.png')) }}" 
       class="black_logo w-[130px] md:min-w-[150px] h-[40px] " 
       alt="">
  <img src="{{ Helper::getValidImage(Helper::branding('logo_dark', '/_assets/images/cmsnt_dark.png')) }}" 
       class="white_logo w-[130px] md:min-w-[150px] h-[40px]" 
       alt="">
  {{-- <span class="ltr:ml-3 rtl:mr-3 text-xl font-Inter font-bold text-slate-900 dark:text-white hidden xl:inline-block">SHOPNickV3</span> --}}
</a>
