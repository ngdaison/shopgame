<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "Testing default locale...\n";
try {
    $locale = Helper::getDefaultLocale();
    echo "Default locale: $locale\n";
} catch (\Exception $e) {
    echo "Error getting default locale: " . $e->getMessage() . "\n";
}

echo "\nTesting route matching...\n";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET');

echo "Request URI: " . $request->getRequestUri() . "\n";
echo "Request path: " . $request->path() . "\n";

try {
    $response = $kernel->handle($request);
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 404) {
        echo "\nChecking logs...\n";
        $logFile = __DIR__.'/../storage/logs/laravel.log';
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            if (!empty($logs)) {
                echo "Last 500 chars of log:\n";
                echo substr($logs, -500);
            } else {
                echo "Log file is empty\n";
            }
        }
    }
    
    $kernel->terminate($request, $response);
} catch (\Exception $e) {
    echo "Error handling request: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
