<?php
// Test Laravel bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "Laravel loaded successfully!\n";

// Test route
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content length: " . strlen($response->getContent()) . " bytes\n";

if ($response->getStatusCode() === 404) {
    echo "\n404 Error - Response preview:\n";
    echo substr($response->getContent(), 0, 1000);
}

$kernel->terminate($request, $response);
