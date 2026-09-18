{!! config('app.url') !!}

{{ getAppTitleWithFallback() }}

{!! $slot !!}

© {{ date('Y') }} {{ getAppTitleWithFallback() }}. @lang('All rights reserved.')
