<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/', 'GET')
);
echo "Status: " . $response->getStatusCode() . "\n";
file_put_contents(__DIR__ . '/home_error.html', $response->getContent());
if (preg_match('/<title>(.*?)<\/title>/s', $response->getContent(), $m)) {
    echo "Title: " . trim($m[1]) . "\n";
}
if (preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $response->getContent(), $m)) {
    echo "H1: " . trim(strip_tags($m[1])) . "\n";
}
if (preg_match('/<p class="text-stone-500[^>]*>(.*?)<\/p>/s', $response->getContent(), $m)) {
    echo "Detail: " . trim(strip_tags($m[1])) . "\n";
}

