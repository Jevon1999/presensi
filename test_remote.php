<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = App\Models\User::first()->createToken('test')->plainTextToken;
$res = Illuminate\Support\Facades\Http::withToken($token)
    ->withHeaders(['Accept' => 'application/json'])
    ->get('https://api.globalintermedia.online/api/statistics');

$body = $res->body();
$json = json_decode($body, true);

if ($json && isset($json['message'])) {
    echo "API EXCEPTION: " . $json['message'] . "\n";
    if (isset($json['file'])) echo "FILE: " . $json['file'] . ":" . $json['line'] . "\n";
} else {
    // try to parse html if not json
    echo "RAW BODY:\n";
    echo strip_tags($body);
}
