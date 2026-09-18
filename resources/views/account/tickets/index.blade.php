@section('title', $pageTitle)
<x-app-layout>
  <div class="card mb-3">
    <div class="card-body flex flex-col p-6">
      <div class="card-text h-full space-y-4">
        <div class="alert alert-outline-primary dark:alert-outline-primary">
            <div class="flex items-center space-x-3 rtl:space-x-reverse">
                <iconify-icon class="text-2xl" icon="fluent:info-24-regular"></iconify-icon>
                <div class="flex-1 font-Inter">
                    <h5 class="font-medium">Hỗ trợ khách hàng</h5>
                    <div class="text-sm">Vui lòng mô tả đúng vấn đề, đội ngũ hỗ trợ sẽ phản hồi sớm nhất.</div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <div id="app" data-categories="{{ json_encode(array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', Helper::getConfig('ticket_config')['ticket_categories'] ?? '', -1, PREG_SPLIT_NO_EMPTY))))) }}">
    <ticket-index />
  </div>

  @push('scripts')
    @vite('resources/js/modules/account/ticket/index.js')
  @endpush
</x-app-layout>
