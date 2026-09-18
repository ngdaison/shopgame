@php
  $auth_fb = getSocialConfig('facebook');
  $auth_google = getSocialConfig('google');
  $auth_discord = getSocialConfig('discord');
@endphp
<!-- BEGIN: Social Log in Area -->
<ul class="flex justify-center space-x-4">
  {{-- Google --}}
  @if ((int)($auth_google['client_status'] ?? 0) === 1)
    <li>
      <a href="{{ route('auth.social', ['provider' => 'google']) }}" class="inline-flex h-14 w-14 flex-col items-center justify-center text-2xl hover:opacity-80">
        <img src="{{ Helper::getValidImage('/images/icon/google_v2.png') }}" alt="Google" class="w-10 h-10">
      </a>
    </li>
  @endif
  {{-- Facebook --}}
  @if ((int)($auth_fb['client_status'] ?? 0) === 1)
    <li>
      <a href="{{ route('auth.social', ['provider' => 'facebook']) }}" class="inline-flex h-14 w-14 flex-col items-center justify-center text-2xl hover:opacity-80">
        <img src="{{ Helper::getValidImage('/images/icon/facebook_v2.png') }}" alt="Facebook" class="w-10 h-10">
      </a>
    </li>
  @endif
  {{-- Discord --}}
  @if ((int)($auth_discord['client_status'] ?? 0) === 1)
    <li>
      <a href="{{ route('auth.social', ['provider' => 'discord']) }}" class="inline-flex h-14 w-14 flex-col items-center justify-center text-2xl hover:opacity-80">
        <img src="{{ Helper::getValidImage('/images/icon/discord.svg') }}" alt="Discord" class="w-10 h-10">
      </a>
    </li>
  @endif
</ul>

@push('scripts')
<script>
    if (!window.socialLoginInited) {
        window.socialLoginInited = true;
        
        document.addEventListener('click', function(e) {
            // Find social login links using a robust selector
            const socialLink = e.target.closest('a[href*="/auth/social"]');
            
            if (socialLink) {
                // Ensure it's not a callback URL
                if (socialLink.href.includes('/callback')) return;
                
                e.preventDefault();
                const url = socialLink.href;
                
                // Popup giống login Google thật (500x650, nằm giữa màn hình)
                const width = 500;
                const height = 650;
                
                // Calculate position to center the popup on the screen
                const left = (screen.width / 2) - (width / 2);
                const top = (screen.height / 2) - (height / 2);
                
                const popup = window.open(url, 'socialLoginPopup', `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=no,location=no,menubar=no,resizable=yes`);
                
                if (popup) {
                    popup.focus();
                } else {
                    // Alert the user if the popup was blocked
                    Swal.fire({
                        icon: 'warning',
                        title: 'Popup Blocked',
                        text: 'Vui lòng cho phép hiện cửa sổ bật lên (popup) để đăng nhập bằng mạng xã hội.',
                        confirmButtonText: 'Đã hiểu'
                    });
                }
            }
        });

        // Global message listener for social login success
        window.addEventListener('message', function(event) {
            if (event.data === 'social_login_success') {
                window.location.reload();
            }
        }, false);
    }
</script>
@endpush
<!-- END: Social Log In Area -->
