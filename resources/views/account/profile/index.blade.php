@section('title', __t($pageTitle))
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        .cropper-container-wrapper {
            max-height: 400px;
            overflow: hidden;
        }
        #cropper-image {
            max-width: 100%;
            display: block;
        }
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
        
        /* Custom Toggle Switch Styles */
        .card-body .switch-custom {
            position: relative !important;
            display: inline-block !important;
            width: 60px !important;
            height: 30px !important;
        }
        .card-body .switch-custom input[type="checkbox"] {
            position: absolute !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            pointer-events: none !important;
            visibility: hidden !important;
        }
        .card-body .switch-custom .slider-custom {
            position: absolute !important;
            cursor: pointer !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background-color: #ccc !important;
            transition: .4s !important;
            border-radius: 30px !important;
            border: 1px solid #bbb !important;
        }
        .card-body .switch-custom .slider-custom:before {
            position: absolute !important;
            content: "" !important;
            height: 24px !important;
            width: 24px !important;
            left: 2px !important;
            bottom: 2px !important;
            background-color: white !important;
            transition: .4s !important;
            border-radius: 50% !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        }
        .card-body .switch-custom input[type="checkbox"]:checked + .slider-custom {
            background-color: #22c55e !important;
            border-color: #22c55e !important;
        }
        .card-body .switch-custom input[type="checkbox"]:checked + .slider-custom:before {
            transform: translateX(30px) !important;
        }
        /* Ensure proper spacing from footer */
        .profile-page {
            padding-bottom: 10px !important;
        }
        
        /* Force remove focus ring for all profile and modal inputs */
        .profile-page .form-control:focus,
        .modal .form-control:focus,
        .modal .form-select:focus {
            box-shadow: none !important;
            outline: none !important;
        }

        .modal-content {
            border-radius: 0.5rem !important;
        }
        
        .modal .form-control, .modal select {
            height: 40px !important;
            border-radius: 6px !important;
            border: 1px solid #e2e8f0 !important;
            padding-left: 0.75rem !important;
            font-size: 14px !important;
        }
        
        .dark .modal .form-control, .dark .modal select {
            border-color: #334155 !important;
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        .modal-header .btn-close {
            background-size: 12px !important;
            border-radius: 50% !important;
            padding: 10px !important;
            background-color: #f1f5f9 !important;
            opacity: 1 !important;
            transition: all 0.2s !important;
        }

        .dark .modal-header .btn-close {
            background-color: #1e293b !important;
            filter: invert(1) grayscale(100%) brightness(200%) !important;
        }

        .modal-header .btn-close:hover {
            transform: rotate(90deg) !important;
            background-color: #e2e8f0 !important;
        }
        
        .dark .modal-header .btn-close:hover {
            background-color: #334155 !important;
        }
    </style>
@endsection

<x-app-layout :contentClass="''" :hasStickyFooter="false">
    <div class="space-y-6 profile-page pb-12 md:pb-20">
        
        {{-- Section 1: Thông tin cá nhân --}}
        <div class="card">
            <div class="card-body p-6">
                <h4 class="card-title mb-6">{{ __t('Thông Tin Cá Nhân') }}</h4>
            
                <form action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8 profile-axios-form" data-reload="true">
                    @csrf
                    <input type="hidden" name="update_type" value="personal_info">
                    <input type="hidden" name="avatar_code" id="avatar_code">
                    
                    {{-- Column 1: Avatar --}}
                    <div class="col-span-1 flex flex-col items-center">
                        <div class="relative inline-block mb-4">
                            <img src="{{ Helper::getValidImage($user->avatar, '/images/avatar/av-1.svg') }}" 
                                 alt="Avatar" 
                                 class="w-[150px] h-[150px] rounded-full object-cover"
                                 id="avatar-preview">
                        </div>
                        <div>
                            <label for="avatar-upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-md font-semibold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-widest focus:outline-none transition ease-in-out duration-150">
                                {{ __t('Chọn Ảnh') }}
                            </label>
                            <input type="file" id="avatar-upload" name="avatar" class="hidden" accept="image/*" onchange="previewImage(event)">
                        </div>
                    </div>

                    {{-- Column 2 & 3: Editable Info --}}
                    <div class="col-span-1 lg:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Username --}}
                            <div class="input-area">
                                <label class="form-label mb-2 block">{{ __t('Tên Đăng Nhập') }}</label>
                                @php
                                    $canChangeUsername = true;
                                    $usernameDisableReason = '';
                                    if(empty($user->email)) {
                                        $canChangeUsername = false;
                                        $usernameDisableReason = 'Cần có Email để đổi Tên Đăng Nhập';
                                    } elseif($user->email && !$user->email_verified_at) {
                                        $canChangeUsername = false;
                                        $usernameDisableReason = 'Cần xác thực Email trước khi đổi';
                                    } elseif($user->username_changed_at && \Carbon\Carbon::parse($user->username_changed_at)->addMonth()->isFuture()) {
                                        $canChangeUsername = false;
                                        $usernameDisableReason = 'Mở lại vào ' . \Carbon\Carbon::parse($user->username_changed_at)->addMonth()->format('d/m/Y');
                                    }
                                @endphp
                                <input type="text" name="username" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md" value="{{ $user->username }}" @if(!$canChangeUsername) disabled @endif>
                                @if(!$canChangeUsername)
                                    <div class="text-[10px] text-red-500 mt-1">{{ __t($usernameDisableReason) }}</div>
                                @else
                                    <p class="text-[10px] text-orange-500 mt-1">{{ __t('Đổi 1 lần/tháng') }}</p>
                                @endif
                            </div>

                            {{-- Email --}}
                            <div class="input-area">
                                <label class="form-label mb-2 block flex items-center gap-2">
                                    <span>{{ __t('Email') }}</span>
                                    @if($user->email && !$user->email_verified_at)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Chưa Xác Thực
                                        </span>
                                    @elseif($user->email_verified_at)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Đã Xác Thực
                                        </span>
                                    @endif
                                </label>
                                @php
                                    $canChangeEmail = true;
                                    $emailDisableReason = '';
                                    if (in_array($user->register_by, ['GOOGLE', 'FACEBOOK', 'DISCORD'])) {
                                        $canChangeEmail = false;
                                        $emailDisableReason = 'Tài khoản MXH không thể đổi Email';
                                    } elseif($user->email_verified_at && $user->email_changed_at && \Carbon\Carbon::parse($user->email_changed_at)->addMonths(6)->isFuture()) {
                                        $canChangeEmail = false;
                                        $emailDisableReason = 'Mở lại vào ' . \Carbon\Carbon::parse($user->email_changed_at)->addMonths(6)->format('d/m/Y');
                                    }
                                @endphp
                                <input type="email" name="email" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md" value="{{ $user->email }}" @if(!$canChangeEmail) disabled @endif>
                                @if($user->email && !$user->email_verified_at)
                                    <button type="button" id="btn-send-verification" onclick="sendEmailVerification()" class="mt-2 btn btn-sm btn-primary py-1">
                                        {{ __t('Gửi Link Xác Thực') }}
                                    </button>
                                @endif
                                @if(!$canChangeEmail)
                                    <div class="text-[10px] text-red-500 mt-1">{{ __t($emailDisableReason) }}</div>
                                @elseif($user->email_verified_at)
                                    <p class="text-[10px] text-orange-500 mt-1">{{ __t('Đổi 1 lần/6 tháng') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="input-area">
                                <label class="form-label mb-2 block">{{ __t('Họ và Tên') }}</label>
                                <input type="text" name="full_name" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md" value="{{ $user->full_name }}" placeholder="Nhập họ tên của bạn">
                            </div>
                            <div class="input-area">
                                <label class="form-label mb-2 block">{{ __t('Số Điện Thoại') }}</label>
                                @php
                                    $canChangePhone = true;
                                    $phoneDisableReason = '';
                                    if(empty($user->email)) {
                                        $canChangePhone = false;
                                        $phoneDisableReason = 'Cần có Email để đổi Số Điện Thoại';
                                    } elseif($user->email && !$user->email_verified_at) {
                                        $canChangePhone = false;
                                        $phoneDisableReason = 'Cần xác thực Email trước khi đổi';
                                    } elseif($user->phone_changed_at && \Carbon\Carbon::parse($user->phone_changed_at)->addMonths(6)->isFuture()) {
                                        $canChangePhone = false;
                                        $phoneDisableReason = 'Mở lại vào ' . \Carbon\Carbon::parse($user->phone_changed_at)->addMonths(6)->format('d/m/Y');
                                    }
                                @endphp
                                <input type="text" name="phone" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md" value="{{ $user->phone }}" placeholder="Nhập số điện thoại" @if(!$canChangePhone) disabled @endif>
                                @if(!$canChangePhone)
                                    <div class="text-[10px] text-red-500 mt-1">{{ __t($phoneDisableReason) }}</div>
                                @else
                                    <p class="text-[10px] text-orange-500 mt-1">{{ __t('Đổi 1 lần/6 tháng') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="input-area">
                            <label class="form-label mb-2 block">{{ __t('Giới Tính') }}</label>
                            <select name="gender" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md">
                                <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="lesbian" {{ $user->gender == 'lesbian' ? 'selected' : '' }}>Lesbian (Đồng tính nữ)</option>
                                <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Nam 🏳️‍🌈</option>
                                <option value="bisexual" {{ $user->gender == 'bisexual' ? 'selected' : '' }}>Bisexual (Song tính)</option>
                                <option value="gay" {{ ($user->gender == 'gay' || empty($user->gender)) ? 'selected' : '' }}>Gay (Đồng tính nam)</option>
                                <option value="transgender" {{ $user->gender == 'transgender' ? 'selected' : '' }}>Transgender (Chuyển giới) 🏳️‍⚧️</option>
                            </select>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="btn btn-dark px-6">{{ __t('Lưu Thông Tin') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Section 2: Tabs - Cố định lỗi thẻ row và cột --}}
        <div class="w-full">
            <div class="mb-6">
                <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700">
                    <button onclick="profileTabSwitch('basic')" id="tab-btn-basic" class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-primary-500 text-primary-500 font-bold transition-all active">
                        {{ __t('Thông Tin Hệ Thống') }}
                    </button>
                    <button onclick="profileTabSwitch('security')" id="tab-btn-security" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all">
                        {{ __t('Xác Thực và Bảo Mật') }}
                    </button>
                    <button onclick="profileTabSwitch('banks')" id="tab-btn-banks" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all">
                        {{ __t('Ngân Hàng') }}
                    </button>
                    <button onclick="profileTabSwitch('passkey')" id="tab-btn-passkey" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all">
                        {{ __t('Passkey (Thiết bị)') }}
                    </button>
                    <button onclick="profileTabSwitch('change-password')" id="tab-btn-change-password" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all">
                        {{ __t('Đổi Mật Khẩu') }}
                    </button>
                    <button onclick="profileTabSwitch('linked-account')" id="tab-btn-linked-account" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all">
                        {{ __t('Tài Khoản Liên Kết') }}
                    </button>
                    <button onclick="profileTabSwitch('diary')" id="tab-btn-diary" class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border-b-2 border-transparent hover:text-primary-500 hover:border-primary-500 transition-all">
                        {{ __t('Nhật Ký Hoạt Động') }}
                    </button>
                </div>
            </div>

            <div class="tab-content-wrapper">
                {{-- Tab Content 1 --}}
                <div id="tab-basic" class="tab-content block">
                    <div class="card mb-0">
                        <header class="card-header border-b border-slate-100 dark:border-slate-700 p-4">
                            <h4 class="card-title">{{ __t('Thông Tin Hệ Thống') }}</h4>
                        </header>
                        <div class="card-body p-6 space-y-4">
                            <div class="flex justify-between items-center border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                                <span class="text-slate-500">{{ __t('Email') }}</span>
                                <span class="font-medium">{{ $user->email }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                                <span class="text-slate-500">{{ __t('Ngày đăng ký') }}</span>
                                <span class="font-medium">{{ $user->created_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                                <span class="text-slate-500">{{ __t('Đăng nhập lần cuối') }}</span>
                                <span class="font-medium text-blue-500">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : __t('Chưa đăng nhập') }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                                <span class="text-slate-500">{{ __t('IP Đăng Nhập') }}</span>
                                <span class="font-mono bg-slate-100 dark:bg-slate-900 px-2 py-1 rounded text-xs">{{ $user->ip_address }}</span>
                            </div>
                            <div class="flex flex-col gap-1 border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                                <span class="text-slate-500">{{ __t('Thiết bị đăng nhập') }}</span>
                                <span class="text-xs text-slate-600 dark:text-slate-400 break-all">{{ $user->user_agent ?? request()->userAgent() }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-slate-500 font-bold">{{ __t('Số dư hiện tại') }}</span>
                                <span class="font-bold text-red-500 text-xl">{{ Helper::formatCurrency($user->balance) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab Content 2 --}}
                <div id="tab-security" class="tab-content hidden">
                    <div class="card mb-0">
                        <header class="card-header border-b border-slate-100 dark:border-slate-700 p-4">
                            <h4 class="card-title">{{ __t('Xác Thực và Bảo Mật') }}</h4>
                        </header>
                        <div class="card-body p-6">
                            <form action="{{ route('account.profile.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 profile-axios-form" data-reload="true">
                                @csrf
                                <input type="hidden" name="update_type" value="security_settings">
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700 col-span-1 md:col-span-2">
                                    <div>
                                        <div class="text-sm font-bold">{{ __t('Bảo mật Trình Duyệt & IP') }}</div>
                                        <div class="text-xs text-slate-500">{{ __t('Chỉ xem được đơn hàng từ thiết bị đã mua') }}</div>
                                    </div>
                                    <label class="switch-custom">
                                        <input type="checkbox" name="secure_order_view" value="1" {{ $user->secure_order_view ? 'checked' : '' }}>
                                        <span class="slider-custom"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <div class="text-sm font-bold">{{ __t('Xác minh bằng OTP Mail') }}</div>
                                        <div class="text-xs text-slate-500">{{ __t('Yêu cầu mã từ Email khi đăng nhập') }}</div>
                                    </div>
                                    <label class="switch-custom">
                                        <input type="checkbox" name="login_verify_email" value="1" {{ $user->login_verify_email ? 'checked' : '' }}>
                                        <span class="slider-custom"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <div class="text-sm font-bold">{{ __t('Google Authenticator') }}</div>
                                        <div class="text-xs text-slate-500">{{ __t('Yêu cầu mã 2FA khi đăng nhập') }}</div>
                                    </div>
                                    <label class="switch-custom">
                                        <input type="checkbox" id="google2fa_toggle" name="login_verify_google2fa" {{ $user->login_verify_google2fa ? 'checked' : '' }} value="1">
                                        <span class="slider-custom"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <div class="text-sm font-bold">{{ __t('Thông báo qua Mail') }}</div>
                                        <div class="text-xs text-slate-500">{{ __t('Gửi mail khi đăng nhập thành công') }}</div>
                                    </div>
                                    <label class="switch-custom">
                                        <input type="checkbox" name="notify_login_success" value="1" {{ $user->notify_login_success ? 'checked' : '' }}>
                                        <span class="slider-custom"></span>
                                    </label>
                                </div>
                                <div class="md:col-span-2 text-right pt-4">
                                    <button type="submit" class="btn btn-dark px-8">{{ __t('Lưu Cấu Hình Bảo Mật') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tab Content 3: Saved Banks --}}
                <div id="tab-banks" class="tab-content hidden">
                    <div class="card mb-0">
                        <header class="card-header border-b border-slate-100 dark:border-slate-700 p-4 flex justify-between items-center">
                            <h4 class="card-title">{{ __t('Thẻ Ngân Hàng Đã Lưu') }}</h4>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
                                {{ __t('Thêm Ngân Hàng') }}
                            </button>
                        </header>
                        <div class="card-body p-6">
                            @if($user->banks->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($user->banks as $bank)
                                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700 flex justify-between items-center group">
                                            <div>
                                                <div class="font-bold text-primary-500 mb-1">{{ $bank->bank_name }} ({{ $bank->bank_code }})</div>
                                                <div class="text-sm font-mono tracking-wider">{{ $bank->account_number }}</div>
                                                <div class="text-xs text-slate-500 uppercase mt-1">{{ $bank->account_name }}</div>
                                            </div>
                                            <label class="switch-custom scale-75 origin-right">
                                                <input type="checkbox" name="status" onchange="toggleBankStatusAjax({{ $bank->id }}, this)" {{ $bank->status ? 'checked' : '' }}>
                                                <span class="slider-custom"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-slate-500">
                                    <iconify-icon icon="heroicons:credit-card" class="text-4xl mb-2"></iconify-icon>
                                    <p>{{ __t('Bạn chưa lưu ngân hàng nào.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tab Content 4: Change Password --}}
                <div id="tab-change-password" class="tab-content hidden">
                    <div class="card mb-0">
                        <header class="card-header border-b border-slate-100 dark:border-slate-700 p-4">
                            <h4 class="card-title">{{ __t('Đổi Mật Khẩu') }}</h4>
                        </header>
                        <div class="card-body p-6">
                            @php
                                $hasPassword = $user->has_password ?? true;
                                $needsVerify = $user->login_verify_google2fa || ($user->login_verify_email && $user->email_verified_at);
                            @endphp

                            @if($needsVerify)
                                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 flex items-center gap-3">
                                    <iconify-icon icon="heroicons:shield-check" class="text-2xl text-blue-500 flex-shrink-0"></iconify-icon>
                                    <div class="text-sm text-blue-800 dark:text-blue-200">
                                        @if($user->login_verify_google2fa)
                                            {{ __t('Cập nhật mật khẩu sẽ yêu cầu xác thực qua') }} <strong>Google Authenticator</strong>.
                                        @else
                                            {{ __t('Cập nhật mật khẩu sẽ gửi mã OTP đến email') }} <strong>{{ $user->email }}</strong>.
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('accounts.profile.update-password') }}" method="POST" class="max-w-xl mx-auto space-y-4 profile-axios-form" data-reload="false">
                                @csrf
                                @if($hasPassword)
                                <div class="input-area">
                                    <label class="form-label mb-2 block">{{ __t('Mật Khẩu Hiện Tại') }}</label>
                                    <input type="password" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md" name="old_password" required placeholder="******">
                                    <x-input-error :messages="$errors->get('old_password')" class="mt-1" />
                                </div>
                                @else
                                <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                    <p class="text-sm text-amber-800 dark:text-amber-200">
                                        <iconify-icon icon="heroicons:information-circle" class="inline text-lg mr-1"></iconify-icon>
                                        {{ __t('Bạn đăng ký qua mạng xã hội và chưa có mật khẩu. Hãy đặt mật khẩu mới.') }}
                                    </p>
                                </div>
                                @endif
                                <div class="input-area">
                                    <label class="form-label mb-2 block">{{ __t('Mật Khẩu Mới') }}</label>
                                    <input type="password" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md" name="new_password" required placeholder="******">
                                    <x-input-error :messages="$errors->get('new_password')" class="mt-1" />
                                </div>
                                <div class="input-area">
                                    <label class="form-label mb-2 block">{{ __t('Xác Nhận Mật Khẩu Mới') }}</label>
                                    <input type="password" class="form-control bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md" name="confirm_password" required placeholder="******">
                                </div>
                                <button type="submit" class="btn btn-primary w-full py-3">{{ __t('Cập Nhật Mật Khẩu Mới') }}</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tab Content: Linked Accounts --}}
                <div id="tab-linked-account" class="tab-content hidden">
                    <div class="card mb-0">
                        <header class="card-header border-b border-slate-100 dark:border-slate-700 p-4 flex items-center gap-2">
                            <iconify-icon icon="heroicons:link" class="text-xl text-primary-500"></iconify-icon>
                            <h4 class="card-title">{{ __t('Tài Khoản Liên Kết') }}</h4>
                        </header>
                        <div class="card-body p-6">
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-5 leading-relaxed">
                                {{ __t('Liên kết tài khoản mạng xã hội để đăng nhập nhanh hơn. Bạn có thể liên kết cùng lúc nhiều tài khoản. Nếu email trùng khớp, email sẽ được xác thực tự động.') }}
                            </p>

                            @php
                                $socialProviders = [
                                    'google' => [
                                        'name'      => 'Google',
                                        'img'       => asset('images/icon/google_v2.png'),
                                        'logoBg'    => 'bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/30 dark:to-orange-900/30',
                                        'logoBorder'=> 'border-2 border-red-300 dark:border-red-700',
                                        'cardLinked'=> 'border-l-4 border-l-red-400',
                                        'badge'     => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800',
                                        'btnLink'   => 'bg-red-500 text-white',
                                    ],
                                    'facebook' => [
                                        'name'      => 'Facebook',
                                        'img'       => asset('images/icon/facebook_v2.png'),
                                        'logoBg'    => 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-900/30',
                                        'logoBorder'=> 'border-2 border-blue-400 dark:border-blue-600',
                                        'cardLinked'=> 'border-l-4 border-l-blue-500',
                                        'badge'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800',
                                        'btnLink'   => 'bg-blue-600 text-white',
                                    ],
                                    'discord' => [
                                        'name'      => 'Discord',
                                        'img'       => asset('images/icon/discord.svg'),
                                        'logoBg'    => 'bg-gradient-to-br from-indigo-50 to-violet-100 dark:from-indigo-900/30 dark:to-violet-900/30',
                                        'logoBorder'=> 'border-2 border-indigo-400 dark:border-indigo-600',
                                        'cardLinked'=> 'border-l-4 border-l-indigo-500',
                                        'badge'     => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 ring-1 ring-indigo-200 dark:ring-indigo-800',
                                        'btnLink'   => 'bg-indigo-500 text-white',
                                    ],
                                ];
                                $userSocialLinks = $user->social_links ?? [];
                                $linkedCount = count(array_filter($userSocialLinks, fn($v) => !empty($v)));
                            @endphp

                            <div class="space-y-3">
                            @foreach($socialProviders as $key => $provider)
                                @php
                                    $isLinked       = $user->hasSocialLinked($key);
                                    $config         = getSocialConfig($key);
                                    $isConfigured   = $config
                                        && !empty($config['client_key'] ?? $config['client_id'] ?? null)
                                        && !empty($config['client_secret'] ?? null);
                                    $isRegisteredBy = strtoupper($key) === $user->register_by;
                                    $canUnlink      = ($user->has_password ?? true) || $linkedCount > 1;
                                @endphp

                                <div id="linked-card-{{ $key }}"
                                     class="relative flex items-center justify-between p-4 pr-5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 cursor-default">

                                    {{-- Left: Logo + Info --}}
                                    <div class="flex items-center gap-4">
                                        {{-- Logo with colored background --}}
                                        <div class="relative w-14 h-14 flex-shrink-0">
                                            <div class="w-14 h-14 rounded-2xl {{ $provider['logoBg'] }} {{ $provider['logoBorder'] }} flex items-center justify-center p-2.5">
                                                <img src="{{ $provider['img'] }}"
                                                     alt="{{ $provider['name'] }}"
                                                     class="w-full h-full object-contain">
                                            </div>
                                            @if($isLinked)
                                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center ring-2 ring-white dark:ring-slate-800">
                                                    <iconify-icon icon="heroicons:check" class="text-white text-[10px]"></iconify-icon>
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Name + Status --}}
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-slate-100 text-[15px] leading-tight">{{ $provider['name'] }}</div>
                                            @if($isLinked)
                                                <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $provider['badge'] }}">
                                                        <iconify-icon icon="heroicons:check-circle-solid" class="text-sm"></iconify-icon>
                                                        {{ __t('Đã liên kết') }}
                                                    </span>
                                                    @if($isRegisteredBy)
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800">
                                                            <iconify-icon icon="heroicons:star-solid" class="text-[10px]"></iconify-icon>
                                                            {{ __t('Tài khoản gốc') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ __t('Chưa liên kết') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Right: Action buttons --}}
                                    <div class="flex-shrink-0 flex items-center gap-2">
                                        @if($isLinked)
                                            {{-- Unlink button (always shown if can unlink) --}}
                                            @if(!$isRegisteredBy && $canUnlink)
                                                <button type="button"
                                                        onclick="unlinkSocial('{{ $key }}', '{{ $provider['name'] }}')"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20">
                                                    <iconify-icon icon="heroicons:link-slash" class="text-sm"></iconify-icon>
                                                    {{ __t('Hủy Liên Kết') }}
                                                </button>
                                            @elseif($isRegisteredBy)
                                                <span class="text-[11px] text-slate-400 italic">{{ __t('Tài khoản gốc') }}</span>
                                            @else
                                                <span class="text-[11px] text-amber-500 italic">{{ __t('Cần mật khẩu để hủy') }}</span>
                                            @endif

                                             {{-- Re-link (refresh OAuth token) via popup --}}
                                            <button type="button"
                                               onclick="linkSocial('{{ route('account.linked-accounts.link', $key) }}', '{{ $provider['name'] }}')"
                                               title="{{ __t('Cập nhật liên kết') }}"
                                               class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-slate-400 bg-slate-100 dark:bg-slate-800">
                                                <iconify-icon icon="heroicons:arrow-path" class="text-base"></iconify-icon>
                                            </button>
                                        @else
                                            {{-- Link button --}}
                                                <button type="button"
                                                   onclick="linkSocial('{{ route('account.linked-accounts.link', $key) }}', '{{ $provider['name'] }}')"
                                                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold rounded-xl {{ $provider['btnLink'] }}">
                                                    <iconify-icon icon="heroicons:link" class="text-base"></iconify-icon>
                                                    {{ __t('Liên Kết') }}
                                                </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            </div>

                            {{-- Hidden unlink forms (AJAX submit via JS) --}}
                            @foreach($socialProviders as $key => $p)
                                <form id="unlink-form-{{ $key }}" action="{{ route('account.linked-accounts.unlink', $key) }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Tab Content: Passkey --}}
                <div id="tab-passkey" class="tab-content hidden">
                    <div class="card mb-0">
                        <header class="card-header border-b border-slate-100 dark:border-slate-700 p-4 flex justify-between items-center">
                            <h4 class="card-title">{{ __t('Quản Lý Thiết Bị (Passkey)') }}</h4>
                            <button type="button" onclick="registerPasskey()" class="btn btn-sm btn-primary flex items-center gap-1">
                                <iconify-icon icon="heroicons:plus" class="text-lg"></iconify-icon>
                                {{ __t('Thêm Thiết Bị') }}
                            </button>
                        </header>
                        <div class="card-body p-6">
                             <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                <div class="flex items-start gap-3">
                                    <iconify-icon icon="heroicons:finger-print" class="text-2xl text-blue-500"></iconify-icon>
                                    <div>
                                        <h5 class="font-bold text-blue-900 dark:text-blue-100 mb-1">{{ __t('Passkey là gì?') }}</h5>
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            {{ __t('Sử dụng TouchID, FaceID hoặc Windows Hello để xác thực nhanh chóng và an toàn hơn.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div id="passkey-list" class="space-y-4">
                                @forelse($user->webauthnKeys->sortByDesc('created_at') as $key)
                                    <div class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-full">
                                                <iconify-icon icon="heroicons:device-phone-mobile" class="text-xl text-slate-500 dark:text-slate-400"></iconify-icon>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-slate-900 dark:text-slate-100">{{ $key->name }}</h5>
                                                <p class="text-xs text-slate-500">
                                                    {{ __t('Thêm vào:') }} {{ $key->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="deletePasskey({{ $key->id }})" class="btn btn-sm btn-danger-light p-2 rounded-full transition-colors" title="{{ __t('Xóa thiết bị') }}">
                                            <iconify-icon icon="heroicons:trash" class="text-lg text-red-500"></iconify-icon>
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-slate-500">
                                        <iconify-icon icon="heroicons:shield-check" class="text-4xl mb-2 opacity-50"></iconify-icon>
                                        <p>{{ __t('Chưa có thiết bị nào được thêm.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab Content 4 --}}
                <div id="tab-diary" class="tab-content hidden">
                    <div class="card">
                        <header class="card-header border-b border-slate-100 dark:border-slate-700 p-4">
                            <h4 class="card-title">{{ __t('Nhật Ký Hoạt Động') }}</h4>
                        </header>
                        <div class="card-body p-6">
                            <div id="vue-history-app">
                                <account-history />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals remain the same logic --}}
    {{-- 2FA Modal Removed (Moved to Redirection Flow) --}}

    {{-- Cropper Modal --}}
    <div class="modal fade fixed inset-0 z-[999] hidden h-full w-full bg-slate-900/60 outline-none backdrop-blur-sm transition-all duration-300" id="cropperModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog pointer-events-none relative h-full w-full flex items-center justify-center p-4">
            <div class="modal-content pointer-events-auto relative w-full max-w-4xl flex flex-col rounded-2xl border-none bg-white shadow-2xl outline-none dark:bg-slate-900 overflow-hidden">
                <div class="modal-header bg-slate-900 text-white border-none p-5 flex justify-between items-center">
                    <h5 class="modal-title font-bold text-lg leading-none">{{ __t('Cắt Ảnh Đại Diện') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 bg-slate-200">
                    <div class="cropper-container-wrapper">
                        <img id="cropper-image" src="">
                    </div>
                </div>
                <div class="modal-footer justify-between bg-white dark:bg-slate-800 p-5 flex items-center gap-4">
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex items-center justify-center w-10 h-10 p-0" onclick="cropper.rotate(-90)"><iconify-icon icon="heroicons:arrow-uturn-left" class="text-xl"></iconify-icon></button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex items-center justify-center w-10 h-10 p-0" onclick="cropper.rotate(90)"><iconify-icon icon="heroicons:arrow-uturn-right" class="text-xl"></iconify-icon></button>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="btn btn-light px-6 py-2.5 rounded-lg text-sm font-bold" data-bs-dismiss="modal">{{ __t('Hủy bỏ') }}</button>
                        <button type="button" class="btn btn-primary px-8 py-2.5 rounded-lg text-sm font-bold shadow-lg shadow-primary-500/30" onclick="cropAndSave()">{{ __t('Lưu thay đổi') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

      {{-- Add Bank Modal --}}
      <div class="modal fade fixed inset-0 z-[999] hidden h-full w-full bg-slate-900/60 outline-none backdrop-blur-sm transition-all duration-300" id="addBankModal" tabindex="-1" role="dialog" aria-labelledby="addBankModalLabel" aria-hidden="true">
          <div class="modal-dialog pointer-events-none relative h-full w-full flex items-center justify-center p-4">
              <div class="modal-content pointer-events-auto relative w-full !max-w-lg flex flex-col rounded-md border-none bg-white shadow-2xl outline-none dark:bg-slate-900 overflow-hidden">
                  <div class="modal-header border-b border-slate-100 dark:border-slate-700 p-5 flex justify-between items-center bg-white dark:bg-slate-900">
                      <h5 class="font-bold text-lg text-slate-800 dark:text-slate-100 flex items-center gap-2" id="addBankModalLabel">
                          <span class="inline-block w-1 h-5 bg-primary-500 rounded-sm"></span>
                          {{ __t('Thêm Thẻ Ngân Hàng') }}
                      </h5>
                      <button type="button" class="btn-close opacity-50 hover:opacity-100 transition-opacity p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center" data-bs-dismiss="modal" aria-label="Close">
                          <iconify-icon icon="heroicons:x-mark" class="text-xl"></iconify-icon>
                      </button>
                  </div>
                  <div class="modal-body p-6">
                      <form action="{{ route('account.profile.banks.add') }}" method="POST" class="affiliate-bank-form" data-reload="true">
                          @csrf
                          <div class="space-y-4">
                              <div class="input-area">
                                  <label class="form-label mb-2 block font-bold text-slate-700 dark:text-slate-300">{{ __t('Ngân Hàng') }}</label>
                                  <select name="bank_code" class="form-control appearance-none w-full bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md shadow-none" required>
                                      <option value="">{{ __t('Chọn Ngân Hàng') }}</option>
                                      @foreach (Helper::getListBank() as $bank)
                                          <option value="{{ $bank['code'] }}">{{ $bank['shortName'] }} - {{ $bank['name'] }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                  <div class="input-area">
                                      <label class="form-label mb-2 block font-bold text-slate-700 dark:text-slate-300">{{ __t('Số Tài Khoản') }}</label>
                                      <input type="text" name="account_number" class="form-control w-full bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md shadow-none" required placeholder="{{ __t('Nhập số tài khoản') }}">
                                  </div>
                                  <div class="input-area">
                                      <label class="form-label mb-2 block font-bold text-slate-700 dark:text-slate-300">{{ __t('Chủ Tài Khoản') }}</label>
                                      <input type="text" name="account_name" class="form-control w-full uppercase bg-transparent border border-slate-300 dark:border-slate-700 focus:border-primary-500 !focus:ring-0 !focus:outline-none rounded-md shadow-none" required placeholder="{{ __t('NHAP TEN CHU TAI KHOAN') }}">
                                  </div>
                              </div>
                              
                              <div class="pt-2">
                                  <button type="submit" class="btn btn-primary w-full py-3 rounded-md font-bold shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40 transition-all transform active:scale-[0.98] text-base">
                                      {{ __t('Thêm Ngân Hàng Mới') }}
                                  </button>
                              </div>
                          </div>
                      </form>
                  </div>
              </div>
          </div>
      </div>
    </div>
  </div>
    @push('scripts')
        @vite(['resources/js/modules/account/profile/index.js'])
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
        <script>
            // Global functions for inline usage

            let Toast;

            function profileTabSwitch(target) {
                // Handle legacy or default
                if (target === 'system-info') {
                    target = 'basic';
                }

                // 1. Tìm tất cả các nội dung tab và ẩn đi
                const contents = document.querySelectorAll('.tab-content');
                contents.forEach(el => {
                    el.classList.add('hidden');
                    el.classList.remove('block');
                });

                // 2. Hiện tab được chọn
                const activeContent = document.getElementById('tab-' + target);
                if (activeContent) {
                    activeContent.classList.remove('hidden');
                    activeContent.classList.add('block');
                }

                // 3. Cập nhật trạng thái Active cho Button
                const buttons = document.querySelectorAll('.tab-btn');
                buttons.forEach(btn => {
                    btn.classList.remove('border-primary-500', 'text-primary-500', 'font-bold');
                    btn.classList.add('border-transparent', 'text-slate-600');
                });
                
                const activeBtn = document.getElementById('tab-btn-' + target);
                if (activeBtn) {
                    activeBtn.classList.add('border-primary-500', 'text-primary-500', 'font-bold');
                    activeBtn.classList.remove('border-transparent', 'text-slate-600');
                }
                // 4. Update URL (Preserved)
                try {
                    const url = new URL(window.location.href);
                    if (target === 'basic') {
                        url.searchParams.delete('tab');
                    } else {
                        url.searchParams.set('tab', target);
                    }
                    window.history.pushState({}, '', url);
                } catch (e) {
                    console.error('URL update failed', e);
                }
            }

            function getToast() {
                if (!Toast && typeof Swal !== 'undefined') {
                    Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                }
                return Toast;
            }

            function toggleBankStatusAjax(id, checkbox) {
                const url = `{{ url('/account/profile/banks/toggle') }}/${id}`;
                checkbox.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        getToast()?.fire({
                            icon: 'success',
                            title: '{{ __t('Thành công') }}',
                            text: data.message
                        });
                    } else {
                        getToast()?.fire({
                            icon: 'error',
                            title: '{{ __t('Lỗi') }}',
                            text: data.message
                        });
                        checkbox.checked = !checkbox.checked;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    checkbox.checked = !checkbox.checked;
                })
                .finally(() => {
                    checkbox.disabled = false;
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                // 1. Handle Deep Linking
                const urlParams = new URLSearchParams(window.location.search);
                if (tab) {
                    profileTabSwitch(tab);
                } else {
                    profileTabSwitch('basic');
                }

                // Check for pending toast from sessionStorage
                const pendingToast = sessionStorage.getItem('pending_toast');
                if (pendingToast) {
                    const toastData = JSON.parse(pendingToast);
                    getToast()?.fire({
                        icon: toastData.icon,
                        title: toastData.title,
                        text: toastData.text || toastData.message
                    });
                    sessionStorage.removeItem('pending_toast');
                }

                // Check for Laravel Session Flash Messages (from Redirects)
                @if(session('success'))
                    getToast()?.fire({
                        icon: 'success',
                        title: '{{ __t('Thành công') }}',
                        text: "{{ session('success') }}"
                    });
                @endif

                @if(session('error'))
                    getToast()?.fire({
                        icon: 'error',
                        title: '{{ __t('Lỗi') }}',
                        text: "{{ session('error') }}"
                    });
                @endif

                // 2. Handle Axios Forms
                const axiosForms = document.querySelectorAll('.profile-axios-form');
                axiosForms.forEach(form => {
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const btn = form.querySelector('button[type="submit"]');
                        const action = form.getAttribute('action');
                        const method = form.getAttribute('method').toUpperCase();
                        const reload = form.dataset.reload === 'true';

                        // Loading State (Disabled only, no visual change)
                        btn.disabled = true;

                        const formData = new FormData(form);

                        try {
                            const response = await axios({
                                method: method,
                                url: action,
                                data: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                }
                            });

                            const data = response.data;

                            if (data.status || data.success) {
                                // Handle server-requested redirect (e.g., for verification)
                                if (data.redirect_url) {
                                    window.location.href = data.redirect_url;
                                    return;
                                }

                                // Success - Reload immediately after saving message
                                if (reload) {
                                    sessionStorage.setItem('pending_toast', JSON.stringify({
                                        icon: 'success',
                                        title: 'Thành công!',
                                        message: data.message || 'Thao tác thành công'
                                    }));
                                    window.location.reload();
                                } else {
                                    getToast()?.fire({
                                        icon: 'success',
                                        title: '{{ __t('Thành công!') }}',
                                        text: data.message || '{{ __t('Thao tác thành công') }}'
                                    });
                                }
                            } else {
                                // Failed (Functional)
                                getToast()?.fire({
                                    icon: 'error',
                                    title: '{{ __t('Thất bại!') }}',
                                    text: data.message || '{{ __t('Có lỗi xảy ra') }}'
                                });
                            }
                        } catch (error) {
                            console.error(error);
                            let errorMsg = '{{ __t('Có lỗi xảy ra, vui lòng thử lại.') }}';
                            if (error.response && error.response.data) {
                                errorMsg = error.response.data.message || JSON.stringify(error.response.data.errors);
                            }
                            
                            getToast()?.fire({
                                icon: 'error',
                                title: '{{ __t('Lỗi!') }}',
                                text: errorMsg
                            });
                        } finally {
                            btn.disabled = false;
                        }
                    });
                });

                // 3. Handle Bank Forms (affiliate-bank-form)
                const bankForms = document.querySelectorAll('.affiliate-bank-form');
                bankForms.forEach(form => {
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const btn = form.querySelector('button[type="submit"]');
                        const action = form.getAttribute('action');
                        const method = form.getAttribute('method').toUpperCase();
                        const reload = form.dataset.reload === 'true';

                        // Loading State (Disabled only, no visual change)
                        btn.disabled = true;

                        const formData = new FormData(form);

                        try {
                            const response = await axios({
                                method: method,
                                url: action,
                                data: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                }
                            });

                            const data = response.data;

                            if (data.status || data.success) {
                                // Success - Reload immediately after saving message
                                if (reload) {
                                    sessionStorage.setItem('pending_toast', JSON.stringify({
                                        icon: 'success',
                                        title: 'Thành công!',
                                        message: data.message || 'Thao tác thành công'
                                    }));
                                    window.location.reload();
                                } else {
                                    getToast()?.fire({
                                        icon: 'success',
                                        title: '{{ __t('Thành công!') }}',
                                        text: data.message || '{{ __t('Thao tác thành công') }}'
                                    });
                                }
                            } else {
                                // Failed (Functional)
                                getToast()?.fire({
                                    icon: 'error',
                                    title: '{{ __t('Thất bại!') }}',
                                    text: data.message || '{{ __t('Có lỗi xảy ra') }}'
                                });
                            }
                        } catch (error) {
                            console.error(error);
                            let errorMsg = '{{ __t('Có lỗi xảy ra, vui lòng thử lại.') }}';
                            if (error.response && error.response.data) {
                                errorMsg = error.response.data.message || JSON.stringify(error.response.data.errors);
                            }
                            
                            getToast()?.fire({
                                icon: 'error',
                                title: '{{ __t('Lỗi!') }}',
                                text: errorMsg
                            });
                        } finally {
                            btn.disabled = false;
                        }
                    });
                });
            });

            // ─── Link Social Account ────────────────────────────────────
            function linkSocial(url, providerName) {
                // Navigate directly instead of opening a popup
                window.location.href = url;
            }

            // ─── Unlink Social Account (AJAX) ────────────────────────────────────
            function unlinkSocial(provider, providerName) {
                Swal.fire({
                    title: '{{ __t('Hủy liên kết?') }}',
                    html: `{{ __t('Bạn có chắc muốn hủy liên kết tài khoản') }} <strong>${providerName}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '{{ __t('Hủy Liên Kết') }}',
                    cancelButtonText: '{{ __t('Không') }}',
                }).then(async (result) => {
                    if (!result.isConfirmed) return;

                    const form = document.getElementById('unlink-form-' + provider);
                    if (!form) return;

                    try {
                        const formData = new FormData(form);
                        const response = await axios.post(form.action, formData);
                        const data = response.data;

                        if (data.success) {
                            getToast()?.fire({ icon: 'success', title: '{{ __t('Thành công!') }}', text: data.message });

                            // Reload page to reflect changes
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Toast.fire({ icon: 'error', title: '{{ __t('Thất bại!') }}', text: data.message || '{{ __t('Có lỗi xảy ra') }}' });
                        }
                    } catch (err) {
                        const msg = err.response?.data?.message || '{{ __t('Có lỗi xảy ra, vui lòng thử lại.') }}';
                        Toast.fire({ icon: 'error', title: '{{ __t('Lỗi!') }}', text: msg });
                    }
                });
            }

            // Email Verification with Cooldown
            function sendEmailVerification() {
                const btn = document.getElementById('btn-send-verification');
                if (!btn || btn.disabled) return;

                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '{{ __t('Đang gửi...') }}';

                console.log('Sending verification request...');
                axios.get('{{ route('account.email.send-verification', [], false) }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 60000 // 60s timeout
                })
                .then(res => {
                    const data = res.data;
                    console.log('Got Response:', data);
                    if (data.success) {
                        getToast()?.fire({
                            icon: 'success',
                            title: '{{ __t('Thành Công') }}',
                            text: data.message || '{{ __t('Đã gửi email link xác thực thành công') }}'
                        });
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    } else {
                        getToast()?.fire({
                            icon: 'error',
                            title: '{{ __t('Lỗi!') }}',
                            text: data.message || '{{ __t('Có lỗi xảy ra, vui lòng thử lại.') }}'
                        });
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error('AJAX Error:', err);
                    let msg = 'Có lỗi xảy ra, vui lòng thử lại.';
                    if (err.code === 'ECONNABORTED') {
                         msg = 'Yêu cầu quá hạn (Timeout). Vui lòng kiểm tra lại kết nối mạng hoặc thử lại.';
                    } else if (err.response && err.response.status === 429) {
                        msg = 'Vui lòng chờ 60 giây trước khi yêu cầu lại.';
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    } else if (err.response && err.response.data && err.response.data.message) {
                        msg = err.response.data.message;
                    }
                    
                    getToast()?.fire({
                        icon: 'error',
                        title: '{{ __t('Lỗi!') }}',
                        text: msg
                    });
                    
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                })
                .finally(() => {
                    // Check button text - if it's counting down, don't reset
                    if (btn.innerHTML === '{{ __t('Đang gửi...') }}') { 
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                });
            }

        </script>

        <script src="{{ asset('vendor/webauthn/webauthn.js') }}"></script>
        <script>
            function registerPasskey() {
                Swal.fire({
                    title: '{{ __t('Đặt tên cho thiết bị') }}',
                    input: 'text',
                    inputLabel: '{{ __t('Ví dụ: iPhone của tôi, MacBook Pro...') }}',
                    showCancelButton: true,
                    confirmButtonText: '{{ __t('Tiếp tục') }}',
                    cancelButtonText: '{{ __t('Hủy') }}',
                    showLoaderOnConfirm: true,
                    preConfirm: (name) => {
                        if (!name) {
                            Swal.showValidationMessage('{{ __t('Vui lòng nhập tên thiết bị') }}');
                            return false;
                        }
                        
                        return axios.post('{{ route('account.passkey.options') }}')
                            .then(response => {
                                return {
                                    name: name,
                                    publicKey: response.data
                                };
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `{{ __t('Lỗi:') }} ${error.response?.data?.message || error.message}`
                                );
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        const name = result.value.name;
                        const publicKey = result.value.publicKey;

                        const webAuthn = new WebAuthn();

                        webAuthn.register(publicKey, function(data) {
                            axios.post('{{ route('account.passkey.register') }}', {
                                ...data,
                                name: name
                            })
                            .then(response => {
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{ __t('Thành công!') }}',
                                    text: response.data.message
                                }).then(() => {
                                    window.location.reload();
                                });
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __t('Lỗi đăng ký!') }}',
                                    text: error.response?.data?.error || error.message
                                });
                            });
                        });
                    }
                });
            }

            function deletePasskey(id) {
                Swal.fire({
                    title: '{{ __t('Xác nhận xóa?') }}',
                    text: '{{ __t('Bạn có chắc chắn muốn xóa thiết bị này không?') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '{{ __t('Xóa ngay') }}',
                    cancelButtonText: '{{ __t('Hủy') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.post('/account/passkey/delete/' + id)
                            .then(response => {
                                Swal.fire(
                                    '{{ __t('Đã xóa!') }}',
                                    '{{ __t('Thiết bị đã được xóa thành công.') }}',
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            })
                            .catch(error => {
                                Swal.fire(
                                    '{{ __t('Lỗi!') }}',
                                    '{{ __t('Không thể xóa thiết bị. Vui lòng thử lại.') }}',
                                    'error'
                                );
                            });
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>