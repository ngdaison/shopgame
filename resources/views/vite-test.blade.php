<!DOCTYPE html>
<html>
<head>
    <title>Vite Test</title>
    @vite(['resources/css/app.scss'])
</head>
<body>
    <h1 style="color: red;">If you see styled content below, Vite is working!</h1>
    <div class="p-4 bg-blue-500 text-white">
        This should have blue background if Tailwind CSS is loaded.
    </div>
    
    <hr>
    <h2>Debug Info:</h2>
    <pre>
APP_ENV: {{ config('app.env') }}
APP_DEBUG: {{ config('app.debug') ? 'true' : 'false' }}
ASSET_URL: {{ config('app.asset_url') ?? 'not set' }}
    </pre>
</body>
</html>
