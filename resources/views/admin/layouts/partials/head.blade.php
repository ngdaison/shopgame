<head>

  <!-- Meta Data -->
  <meta charset="UTF-8">
  <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title') - Admin Control Panel v3</title>
  <meta name="Description" content="Admin Control Panel">
  <meta name="Author" content="quocbaodev">
  <meta name="keywords" content="Admin Control Panel">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Favicon -->
  <link rel="icon" href="/_admin/images/brand-logos/favicon.ico" type="image/x-icon">

  <!-- Choices JS -->
  <script src="/_admin/libs/choices.js/public/assets/scripts/choices.min.js"></script>

  <!-- Main Theme Js -->
  <script src="/_admin/js/main.js"></script>

  <!-- Bootstrap Css -->
  <link id="style" href="/_admin/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Style Css -->
  <link href="/_admin/css/styles.min.css" rel="stylesheet">

  <!-- Icons Css -->
  <link href="/_admin/css/icons.css" rel="stylesheet">

  <!-- Node Waves Css -->
  <link href="/_admin/libs/node-waves/waves.min.css" rel="stylesheet">

  <!-- Simplebar Css -->
  <link href="/_admin/libs/simplebar/simplebar.min.css" rel="stylesheet">

  <!-- Color Picker Css -->
  <link rel="stylesheet" href="/_admin/libs/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="/_admin/libs/@simonwep/pickr/themes/nano.min.css">

  <!-- Choices Css -->
  <link rel="stylesheet" href="/_admin/libs/choices.js/public/assets/styles/choices.min.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Signika:wght@600;700;800&display=swap" rel="stylesheet">

  <!-- Extra Plugin -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.27/sweetalert2.min.css">

  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

  <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    toastr.options = {
      "closeButton": true,
      "debug": false,
      "newestOnTop": true,
      "progressBar": true,
      "positionClass": "toast-top-right",
      "preventDuplicates": false,
      "onclick": null,
      "showDuration": "0",
      "hideDuration": "0",
      "timeOut": "5000",
      "extendedTimeOut": "1000",
      "showMethod": "show",
      "hideMethod": "hide"
    };
  </script>
  <script>
    window.webData = @json([
        'csrfToken' => csrf_token(),
    ]);
    window.userData = @json(auth()->user());
  </script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <script>
    window.LANG = @json(getLangJson() ?? [])

    window.__t = function(key) {
      if (window.LANG[key] === undefined) {
        // console.log(key);
      }
      return window.LANG[key] || key;
    }

    window.__defaultLang = '{{ currentLang() }}';
  </script>
  <!-- extra style -->
  <style>
    * {
      font-family: 'Signika', sans-serif;
    }
    .image-preview-wrapper {
        position: relative;
        display: inline-block;
        max-width: 100%;
    }
    .delete-image-btn {
        position: absolute;
        top: 2px;
        right: 2px;
        background: rgba(255, 91, 91, 0.9);
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        border: 1px solid white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        z-index: 10;
        transition: all 0.2s;
    }
    .delete-image-btn:hover {
        background: #ff0000;
        transform: scale(1.1);
        color: #fff;
    }
    /* Select2 z-index fix */
    .select2-container {
        z-index: 9999 !important;
    }
  </style>
  @yield('css')
  @yield('styles')




</head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.27/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
