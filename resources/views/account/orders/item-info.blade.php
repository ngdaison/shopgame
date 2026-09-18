@section('title', __t($pageTitle))
<x-app-layout>
  <div class="order-details-container space-y-4 text-gray-900 dark:text-gray-200">
    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Left Column: Transaction Info -->
        <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-5 h-full shadow-sm">
            <div class="mb-5 pb-3 border-b border-gray-200 dark:border-[#2d2d3a]">
                <div class="text-lg font-bold text-gray-900 dark:text-white uppercase">
                    {{ __t('Thông Tin Giao Dịch') }} - <span class="text-danger-500">{{ $item->code }}</span>
                </div>
            </div>
            
            @php
                $isRobux = ($item->robux > 0);
                $isAccountWithPass = (!empty($item->input_pass));
                
                if (($item->type ?? '') === 'gamepass') {
                    $typeLabel = 'Link GamePass';
                } elseif (($item->type ?? '') === 'robux') {
                    $typeLabel = 'Roblox';
                } elseif (!empty($item->input_pass)) {
                     $typeLabel = 'Tài khoản + Mật Khẩu';
                } else {
                     $typeLabel = 'Tài Khoản';
                }
                
                $warrantyExpireAt = $item->warranty_expire_at;
                $warrantyHours = 0;
                $baseTime = $item->assigned_completed ?? $item->created_at;
                
                if ($warrantyExpireAt) {
                    $warrantyHours = \Carbon\Carbon::parse($warrantyExpireAt)->diffInHours($baseTime);
                } else {
                    $warrantyHours = $item->group->warranty_hours ?? 0;
                }

                $isWarrantyActive = false;
                if ($item->status === 'Completed') {
                    if (!empty($warrantyExpireAt)) {
                        $isWarrantyActive = \Carbon\Carbon::parse($warrantyExpireAt)->gt(now());
                    } elseif ($warrantyHours == 0) {
                        $isWarrantyActive = \Carbon\Carbon::parse($baseTime)->addDay()->gt(now());
                    }
                }

                $hasWarrantyTicket = false;
                if ($isWarrantyActive) {
                    $hasWarrantyTicket = \App\Models\Ticket::where('user_id', auth()->id())
                        ->where('title', 'like', "%{$item->code}%")
                        ->exists();
                }

                $shouldShowWarranty = ($warrantyHours > 0 || !empty($warrantyExpireAt));
            @endphp
            <div class="space-y-3">
                <!-- Row 1: Product & Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Sản phẩm:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white uppercase">{{ preg_replace('/^Gói | \(ROBUX 120H\)/u', '', $item->name ?? '-') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Loại:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">{{ $typeLabel }}</div>
                    </div>
                </div>

                <!-- Row 2: Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Ngày mua:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">{{ $item->created_at->format('H:i:s - d/m/Y') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Cập nhật:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">{{ $item->updated_at->format('H:i:s - d/m/Y') }}</div>
                    </div>
                </div>

                <!-- Row 3: Warranty -->
                @if($shouldShowWarranty)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Bảo hành:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">
                             {{ $warrantyHours == 0 ? '0' : $warrantyHours . 'h' }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Hết hạn:') }}</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white flex items-center gap-2">
                            @if($item->status === 'Completed')
                                @if($warrantyExpireAt)
                                     {{ \Carbon\Carbon::parse($warrantyExpireAt)->format('H:i:s - d/m/Y') }}
                                @else
                                     -
                                @endif
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Row 4: Status & Payment -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Trạng thái:') }}</div>
                        <div>
                             @php
                                $status = $item->status ?? 'Pending';
                                // Map Pending to Processing for display (no "Chờ xử lý" status)
                                // if ($status === 'Pending') $status = 'Processing'; <-- Reverted
                                $statusMap = [
                                    'Completed' => ['text' => 'Hoàn thành', 'color' => 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20'],
                                    'Pending' => ['text' => 'Chờ xử lý', 'color' => 'text-amber-500 bg-amber-500/10 border-amber-500/20'],
                                    'Processing' => ['text' => 'Đang xử lý', 'color' => 'text-blue-500 bg-blue-500/10 border-blue-500/20'],
                                    'Cancelled' => ['text' => 'Đã hủy', 'color' => 'text-red-500 bg-red-500/10 border-red-500/20'],
                                ];
                                $st = $statusMap[$status] ?? $statusMap['Pending'];
                            @endphp
                            <span class="px-2 py-1 rounded text-sm font-bold border {{ $st['color'] }}">
                                {{ $st['text'] }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-base font-bold text-gray-500 dark:text-gray-300 min-w-[80px]">{{ __t('Thanh toán:') }}</div>
                        <div class="text-base font-bold text-emerald-500">{{ Helper::formatCurrency($item->payment) }}</div>
                    </div>
                </div>
            </div>

            @if($isWarrantyActive && !$hasWarrantyTicket)
                <div class="mt-3" id="warranty_section">
                    <div id="app-warranty-request" 
                        data-code="{{ $item->code }}" 
                        data-category="Bảo hành vật phẩm" 
                        data-submit-url="{{ route('account.tickets.store') }}" 
                        data-redirect-url="{{ route('account.tickets.show', ['code' => '__code__']) }}">
                    </div>
                </div>
            @elseif($hasWarrantyTicket)
                <div class="mt-3">
                    <div class="w-full py-2 px-4 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold rounded-lg flex items-center justify-center gap-2 border border-emerald-500/20">
                        <iconify-icon icon="heroicons-outline:check-circle" class="text-xl"></iconify-icon>
                        {{ __t('Đã gửi yêu cầu bảo hành') }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Order Info -->
        <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-5 h-full shadow-sm">
            <div class="mb-5 pb-3 border-b border-gray-200 dark:border-[#2d2d3a]">
                <div class="text-lg font-bold text-gray-900 dark:text-white uppercase">
                    {{ __t('Thông Tin Đơn Hàng') }}
                </div>
            </div>

            <div class="space-y-1">
                @if($isRobux)
                    <!-- Robux Type: Link GamePass -->

                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Link GamePass') }}</span>
                        <div class="flex items-center h-[32px]">
                            <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden h-full">
                                <input type="text" value="{{ $item->input_user }}" readonly class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px]" style="border: none !important; outline: none !important; box-shadow: none !important;" />
                                <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full border-l border-gray-200 dark:border-[#3f3f50]" onclick="copyToClipboard('{{ $item->input_user }}', 'Link GamePass')">
                                    <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-lg"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                @elseif($isAccountWithPass)
                    <!-- Account + Pass Type -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Tài khoản') }}</span>
                            <div class="flex items-center h-[32px]">
                                <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden h-full">
                                    <input type="text" value="{{ $item->input_user }}" readonly class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px]" style="border: none !important; outline: none !important; box-shadow: none !important;" />
                                    <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full border-l border-gray-200 dark:border-[#3f3f50]" onclick="copyToClipboard('{{ $item->input_user }}', 'Tài khoản')">
                                        <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-lg"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Mật khẩu') }}</span>
                            <div class="flex items-center h-[32px]">
                                <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden h-full">
                                    <input type="password" id="password_field" value="{{ $item->input_pass }}" readonly class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px]" style="border: none !important; outline: none !important; box-shadow: none !important;" />
                                    <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full" onclick="togglePasswordVisibility()">
                                        <iconify-icon icon="heroicons-outline:eye-off" id="toggle_eye" class="text-lg"></iconify-icon>
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full border-l border-gray-200 dark:border-[#3f3f50]" onclick="copyToClipboard('{{ $item->input_pass }}', 'Mật khẩu')">
                                        <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-lg"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Simple Account Type -->
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Tài khoản') }}</span>
                        <div class="flex items-center h-[32px]">
                            <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden h-full">
                                <input type="text" value="{{ $item->input_user }}" readonly class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px]" style="border: none !important; outline: none !important; box-shadow: none !important;" />
                                <button class="text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 transition-colors focus:outline-none flex items-center justify-center h-full border-l border-gray-200 dark:border-[#3f3f50]" onclick="copyToClipboard('{{ $item->input_user }}', 'Tài khoản')">
                                    <iconify-icon icon="heroicons-outline:clipboard-copy" class="text-lg"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($item->input_contact) && $item->input_contact !== '-')
                 <div>
                      <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Liên Hệ') }}</span>
                      <div class="flex items-center">
                          <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden"> 
                              <textarea readonly class="bg-transparent text-gray-500 dark:text-gray-400 font-bold w-full py-1.5 px-2 text-[12px] resize-none" rows="2" style="border: none !important; outline: none !important; box-shadow: none !important;">{{ $item->input_contact }}</textarea>
                          </div>
                      </div>
                 </div>
                 @endif

                <div>
                     <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Ghi chú') }}</span>
                     <div class="flex items-center">
                         <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden">
                             <textarea id="order_note" class="bg-transparent text-gray-900 dark:text-white font-bold w-full py-1.5 px-2 text-[12px] resize-none focus:bg-white dark:focus:bg-[#1e1e2d] transition-colors" rows="2" style="border: none !important; outline: none !important; box-shadow: none !important;" placeholder="{{ __t('Nhập ghi chú của bạn...') }}">{{ $item->order_note }}</textarea>
                         </div>
                     </div>
                </div>

                @if(!empty($item->admin_note))
                <div>
                     <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1.5 block">{{ __t('Admin phản hồi') }}</span>
                     <div class="flex items-center">
                         <div class="relative bg-gray-50 dark:bg-[#2b2b40] border border-gray-200 dark:border-[#3f3f50] rounded-md flex-1 flex items-center overflow-hidden"> 
                             <textarea readonly class="bg-transparent text-gray-500 dark:text-gray-400 font-bold w-full py-1.5 px-2 text-[12px] resize-none" rows="2" style="border: none !important; outline: none !important; box-shadow: none !important;">{{ $item->admin_note }}</textarea>
                         </div>
                     </div>
                </div>
                @endif
            </div>
        </div>
        </div>
    </div>
    
    <!-- Order Status Stepper -->
    <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-6 shadow-sm mt-4">
        <div class="text-sm font-bold pb-2 mb-2 border-b border-gray-100 dark:border-[#2d2f36] text-gray-900 dark:text-white uppercase">{{ __t('Trạng thái đơn hàng') }}</div>
        
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between relative">
            @php
                $status = $item->status ?? 'Pending';
                $steps = [
                    ['key' => 'Payment', 'title' => 'Thanh toán', 'desc' => 'Đơn hàng đã được thanh toán', 'icon' => 'heroicons-outline:document-text'],
                    ['key' => 'Processing', 'title' => 'Đang xử lý', 'desc' => 'Hệ thống đang xử lý đơn hàng của bạn', 'icon' => 'heroicons-outline:shield-check'],
                ];
                
                if ($status === 'Cancelled') {
                    $steps[] = ['key' => 'Cancelled', 'title' => 'Đã hủy', 'desc' => 'Đơn hàng đã bị hủy', 'icon' => 'heroicons-outline:x-circle'];
                } else {
                    $steps[] = ['key' => 'Completed', 'title' => 'Hoàn thành', 'desc' => 'Đơn hàng đã hoàn tất', 'icon' => 'heroicons-outline:check-circle'];
                }
                
                $currentIdx = 0; // Payment is always done if we see this
                if ($status == 'Processing') $currentIdx = 1;
                if ($status == 'Completed' || $status == 'Cancelled') $currentIdx = 2;
            @endphp

            @foreach($steps as $index => $step)
                <div class="flex items-start lg:items-center group relative {{ !$loop->last ? 'lg:flex-1' : '' }}">
                    <!-- Line (Vertical for Mobile) -->
                    @if(!$loop->last)
                    <div class="absolute left-6 top-6 -bottom-1 w-0.5 bg-gray-200 dark:bg-[#2d2f36] lg:hidden"></div>
                    @endif

                    <!-- Icon -->
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center border-2 transition-colors duration-200 relative z-10 
                        {{ $index <= $currentIdx ? ($step['key'] === 'Cancelled' ? 'bg-red-600/10 border-red-600 text-red-600' : 'bg-blue-600/10 border-blue-600 text-blue-600') : 'bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-[#2d2f36] text-gray-400 dark:text-gray-600' }}">
                        <iconify-icon icon="{{ $step['icon'] }}" class="text-2xl"></iconify-icon>
                    </div>

                    <!-- Content -->
                    <div class="ml-4 lg:ml-3 pb-8 lg:pb-0">
                        <div class="text-base font-bold {{ $index <= $currentIdx ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $step['title'] }}
                        </div>
                        <div class="text-sm mt-1 {{ $index <= $currentIdx ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-600' }}">
                            {{ $step['desc'] }}
                        </div>
                    </div>

                    <!-- Line (Horizontal for PC) -->
                    @if(!$loop->last)
                        <div class="hidden lg:block flex-grow h-px bg-gray-200 dark:bg-[#2d2f36] mx-4"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

  </div>

  @push('scripts')
    @vite('resources/js/modules/account/order/index.js')
    <script>
      function togglePasswordVisibility() {
          const passwordInput = document.getElementById('password_field');
          const eyeIcon = document.getElementById('toggle_eye');
          
          if (passwordInput.type === 'password') {
              passwordInput.type = 'text';
              eyeIcon.setAttribute('icon', 'heroicons-outline:eye');
          } else {
              passwordInput.type = 'password';
              eyeIcon.setAttribute('icon', 'heroicons-outline:eye-off');
          }
      }

      function copyToClipboard(text, label) {
          if (!text) return;
          navigator.clipboard.writeText(text).then(() => {
              const Toast = Swal.mixin({
                  toast: true,
                  position: 'top-end',
                  showConfirmButton: false,
                  timer: 3000,
                  timerProgressBar: true,
                  didOpen: (toast) => {
                      toast.addEventListener('mouseenter', Swal.stopTimer)
                      toast.addEventListener('mouseleave', Swal.resumeTimer)
                  }
              });
              Toast.fire({
                  icon: 'success',
                  title: 'Thành công',
                  text: `Copy ${label} thành công!`
              });
          }).catch(err => {
              console.error('Failed to copy: ', err);
          });
      }

      // Auto save note
      const noteInput = document.getElementById('order_note');
      let isSaving = false;
      let lastSavedNote = ''; // Store the last saved content

      if (noteInput) {
          lastSavedNote = noteInput.value; // Initialize

          noteInput.addEventListener('blur', () => {
             saveNote();
          });
          
          window.addEventListener('beforeunload', () => {
             saveNote();
          });
      }

      async function saveNote() {
          if (isSaving) return;
          const note = noteInput.value;
          
          // Dirty check: only save if content changed
          if (note === lastSavedNote) {
              return;
          }

          isSaving = true;
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          try {
              const response = await fetch('{{ route('account.orders.items.update-note') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrfToken
                  },
                  body: JSON.stringify({
                      id: {{ $item->id }},
                      note: note
                  }),
                  keepalive: true
              });

              if (!response.ok) throw new Error('Network error');
              const data = await response.json();
              
              const Toast = Swal.mixin({
                  toast: true,
                  position: 'top-end',
                  showConfirmButton: false,
                  timer: 3000,
                  timerProgressBar: true,
                  didOpen: (toast) => {
                      toast.addEventListener('mouseenter', Swal.stopTimer)
                      toast.addEventListener('mouseleave', Swal.resumeTimer)
                  }
              });

              if (data.status) {
                  lastSavedNote = note; // Update last saved state only on success
                  Toast.fire({
                      icon: 'success',
                      title: '{{ __t("Thành công") }}',
                      text: data.message
                  });
              } else {
                  Toast.fire({
                      icon: 'error',
                      title: '{{ __t("Lỗi") }}',
                      text: '{{ __t("Có lỗi xảy ra") }}'
                  });
              }
          } catch (e) {
              console.error(e);
              // Only show error toast if page is likely still visible (not unloading)
              if (!e.message.includes('Network error')) {
                  const Toast = Swal.mixin({
                      toast: true,
                      position: 'top-end',
                      showConfirmButton: false,
                      timer: 3000,
                      timerProgressBar: true,
                  });
                  Toast.fire({
                      icon: 'error',
                      title: 'Lỗi',
                      text: 'Lỗi kết nối'
                  });
              }
          } finally {
              isSaving = false;
          }
      }
      function openWarrantyTicket() {
          const type = '{{ $item->group->name ?? "Sản phẩm" }}';
          const code = '{{ $item->buyer_code }}';
          const title = `Bảo hành ${type} - ${code}`;
          const category = 'Bảo hành';
          
          const url = new URL('{{ route('account.tickets.index') }}', window.location.origin);
          url.searchParams.append('title', title);
          url.searchParams.append('category', category);
          
          window.location.href = url.toString();
      }
    </script>
  @endpush
</x-app-layout>
