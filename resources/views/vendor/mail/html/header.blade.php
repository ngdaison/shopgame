@props(['url'])
<tr>
<td class="header">
  <a href="{{ $url }}" style="display: inline-block;">
    @php
      try {
        $siteTitle = class_exists('Helper') ? \Helper::branding('title', null, true) : null;
      } catch (\Exception $e) {
        $siteTitle = null;
      }

      $siteTitle = $siteTitle ?: config('app.name', 'KiyoVN');
    @endphp

    @if (trim($slot) === 'KiyoVN' || trim($slot) === '')
      {{ $siteTitle }}
    @else
      {{ $slot }}
    @endif
  </a>
</td>
</tr>
