@php
  $defaultTheme = setting('default_theme', 'light');
  $primaryColor = setting('primary_color');
  $fontFamilySetting = setting('font_family', 'Signika');
  
  // If set to '0', disable custom font.
  $fontFamily = ($fontFamilySetting === '0') ? null : $fontFamilySetting;

  $ringOffsetShadow = 'var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color)';
  $ringShadow = 'var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color)';
  $defaultShadow = '0 0 #0000';
  $ringOpacity = 0.8;
  $ringOffsetWidth = '1px';

  if (!function_exists('hexToRgb')) {
    function hexToRgb($hex) {
      if (!$hex) return '0, 0, 0';
      $hex = str_replace("#", "", $hex);
      if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
      } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
      }
      return "$r, $g, $b";
    }
  }
  
  $rgbPrimaryColor = $primaryColor ? hexToRgb($primaryColor) : '0, 0, 0';
@endphp

@if($fontFamily && $fontFamily !== 'Signika')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontFamily) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
@endif

@if($fontFamily)
<style>
  body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea, div {
    font-family: '{{ $fontFamily }}', sans-serif !important;
    letter-spacing: 0.5px;
  }
  /* Ensure icons are not overridden */
  i, [class*="fa-"], [class*="fas"], [class*="far"], [class*="fab"] {
    font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome", sans-serif !important;
  }
</style>
@endif

@if($primaryColor)

<style>
  /* * {
    font-family: 'Signika', sans-serif;
    letter-spacing: 0.5px;
  } */

  :root {
    --primary-color: {{ $primaryColor }};
    --primary-color-rgb: {{ $rgbPrimaryColor }};
  }

  .text-primary {
    color: var(--primary-color) !important;
  }

  .shadow-primary {
    --tw-ring-offset-shadow: {{ $ringOffsetShadow }};
    --tw-ring-shadow: {{ $ringShadow }};
    box-shadow: {{ $defaultShadow }} !important;
    --tw-ring-opacity: {{ $ringOpacity }};
    --tw-ring-offset-width: {{ $ringOffsetWidth }};
  }
  
  .bg-primary {
    background-color: var(--primary-color) !important;
  }

  .btn-primary,
  .ant-btn-primary {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
  }

  .btn-primary:hover {
    background-color: color-mix(in srgb, var(--primary-color), black 10%) !important;
    border-color: color-mix(in srgb, var(--primary-color), black 10%) !important;
    box-shadow: var(--primary-color) !important;
    
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--primary-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000) !important;
    --tw-ring-opacity: 0.8;
    --tw-ring-offset-width: 1px;
  }

  .btn-outline-primary {
    color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
  }

  .btn-outline-primary:hover {
    background-color: var(--primary-color) !important;
    color: #fff !important;
  }

  a {
    color: var(--primary-color);
    text-decoration: none;
  }

  a:hover {
    color: color-mix(in srgb, var(--primary-color), black 10%);
  }
  
  .border-primary {
    border-color: var(--primary-color) !important;
  }

  .dropdown-item:active {
    background-color: var(--primary-color);
  }

  .form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem color-mix(in srgb, var(--primary-color), transparent 75%);
  }

  .form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
  }

  .pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
  }

  .pagination .page-link {
    color: var(--primary-color);
  }

  .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
    background-color: var(--primary-color);
  }
  
  .main-menu>ul>li.menu-item-has-children>ul.sub-menu li a:hover {
    color: var(--primary-color) !important;
  }

  .main-menu>ul>li>a:hover {
    color: var(--primary-color) !important;
  }

  .main-menu>ul>li:hover>a .icon-box {
    color: var(--primary-color) !important;
  }

  .main-menu>ul>li:hover>a .text-box {
    color: var(--primary-color) !important;
  }
  
  .category-name {
    text-shadow: 0 0 3px #fff, 0 0 3px var(--primary-color);
  }

  /* Custom Scrollbar */
  @if($defaultTheme !== 'auto' && $defaultTheme !== 'default')
  ::-webkit-scrollbar {
    width: 5px;
  }

  ::-webkit-scrollbar-track {
    background: #282a38;
  }

  ::-webkit-scrollbar-thumb {
    background: var(--primary-color);
  }

  ::-webkit-scrollbar-thumb:hover {
    background: #e23388;
  }
  @endif

  /* Selection Style */
  @if($defaultTheme !== 'auto' && $defaultTheme !== 'default')
  ::selection {
    background: var(--primary-color);
    color: #fff;
  }
  @endif
</style>
@endif

@if (theme_config('background_color'))
  <style>
    .app-wrapper {
      background-color: {{ theme_config('background_color') }};
    }
  </style>
@elseif(theme_config('background_image'))
  <style>
    body {
      background-image: url('{{ theme_config('background_image') }}');
      background-size: cover;
      background-attachment: fixed;
      background-position: center;
      background-repeat: no-repeat;
    }
  </style>
@endif
<script>
  window.colorPrimary = "{{ $primaryColor }}";
</script>
