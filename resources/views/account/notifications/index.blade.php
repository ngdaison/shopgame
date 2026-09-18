<x-app-layout page-title="Thông báo">
    <style>
        .notify-item {
            transition: background-color 0.2s;
        }
        .notify-unread {
            background-color: #fff3cd !important; /* Warning yellow tint */
        }
        .notify-item:hover {
            background-color: color-mix(in srgb, var(--primary-color), white 90%) !important; /* Dynamic hover tint */
        }
        .btn-detail {
            border: 1px solid #e5e7eb; 
            transition: all 0.2s;
        }
        .btn-detail:hover {
            background-color: #f3f4f6;
            transform: translateY(-1px);
        }
    </style>

    <div class="max-w-4xl mx-auto py-0 px-4 -mt-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        Thông báo
                    </h1>

                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">{{ $notifications->total() }} thông báo</span>
                    @if(isset($hasUnread) && $hasUnread)
                        <button type="button" id="btn-read-all" class="text-xs font-medium hover:underline flex items-center gap-1" style="color: var(--primary-color);">
                            {{ __t('Đánh dấu đã đọc tất cả') }}
                        </button>
                    @endif
                </div>
            </div>

            <div id="page-notification-list" class="notification-list">
                @forelse($notifications as $item)
                <div class="px-4 py-4 border-b border-gray-100 last:border-0 cursor-pointer notify-item {{ !$item->is_read ? 'notify-unread' : 'bg-white' }}" 
                     data-id="{{ $item->id }}"
                     onclick="window.location.href='{{ route('account.notifications.show', $item->code) }}'">
                    <div class="flex items-start">
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start gap-4">
                                <h6 class="{{ !$item->is_read ? 'font-black text-slate-900' : 'font-bold text-slate-700' }} text-base truncate leading-tight">
                                    {{ $item->title }}
                                </h6>
                                <span class="text-[12px] text-gray-400 font-medium whitespace-nowrap mt-0.5">
                                    {{ $item->created_at->format('H:i d/m/Y') }}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ strip_tags($item->content) }}
                            </div>

                            <div class="mt-3 flex justify-end">
                                <span class="px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm border border-gray-100 text-gray-600 bg-gray-50">
                                    Chi Tiết
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center p-10 bg-white">
                    <img src="/assets/images/empty.svg" alt="Empty" class="h-32 mx-auto mb-3">
                    <p class="text-gray-500">Bạn chưa có thông báo nào.</p>
                </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
            <div class="p-3 bg-white border-t border-gray-100">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>

    <script>
        function toggleDesc(el) {
            el.classList.toggle('rotate-180');
        }

        document.addEventListener("DOMContentLoaded", function() {
            const btnReadAll = document.getElementById('btn-read-all');
            if (btnReadAll) {
                btnReadAll.addEventListener('click', function() {
                    // Disable button
                    btnReadAll.disabled = true;
                    btnReadAll.style.opacity = '0.5';
                    btnReadAll.innerText = 'Đang xử lý...';

                    axios.post('{{ route('account.notifications.read_all') }}')
                        .then(response => {
                            if (response.data.success) {
                                // Show Success Toast
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
                                    text: response.data.message
                                });

                                // Remove unread styling from all items
                                const unreadItems = document.querySelectorAll('.notify-unread');
                                unreadItems.forEach(item => {
                                    item.classList.remove('notify-unread');
                                    item.classList.add('bg-white');
                                    
                                    // Update title font weight
                                    const title = item.querySelector('h6');
                                    if (title) {
                                        title.classList.remove('font-black', 'text-slate-900');
                                        title.classList.add('font-bold', 'text-slate-700');
                                    }
                                });

                                // Update Header Badge if clear
                                const badge = document.getElementById('nav-unread-badge');
                                if (badge) {
                                    badge.classList.add('hidden');
                                    badge.innerText = '0';
                                }
                                
                                // Hide Button
                                btnReadAll.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error(error);
                             const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: 'Không thể cập nhật trạng thái đã đọc.'
                            });
                            // Reset button
                            btnReadAll.disabled = false;
                            btnReadAll.style.opacity = '1';
                            btnReadAll.innerText = '{{ __t('Đánh dấu đã đọc tất cả') }}';
                        });
                });
            }
        });
    </script>
</x-app-layout>
