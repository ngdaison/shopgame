<x-app-layout page-title="Chi tiết thông báo">
    <div class="max-w-4xl mx-auto py-0 px-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        Chi tiết thông báo
                    </h1>
                </div>
                <a href="{{ route('account.notifications.index') }}" class="text-sm text-gray-500 hover:text-primary-500 transition-colors flex items-center gap-1">
                    <iconify-icon icon="heroicons-outline:arrow-left"></iconify-icon>
                    Quay lại
                </a>
            </div>

            <div class="p-6 md:p-8">
                <div class="flex justify-between items-start gap-4 mb-6">
                    <h2 class="text-2xl font-bold text-slate-900 leading-tight">
                        {{ $notification->title }}
                    </h2>
                    <span class="text-sm text-gray-400 font-medium whitespace-nowrap mt-2">
                        {{ $notification->created_at->format('H:i d/m/Y') }}
                    </span>
                </div>

                <div class="prose prose-slate max-w-none text-gray-600 leading-relaxed">
                    {!! $notification->body ?? $notification->content !!}
                </div>

                @if($notification->link)
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ $notification->link }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-lg font-bold hover:bg-primary-600 transition-all shadow-sm">
                        {{ __t('Xem chi tiết') }}
                        <iconify-icon icon="heroicons-outline:external-link"></iconify-icon>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
