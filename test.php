<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// simulate frontend call to local api
$token = App\Models\User::first()->createToken('test')->plainTextToken;
$res = Illuminate\Support\Facades\Http::withToken($token)
        ->get('http://localhost:1337/api/statistics');

echo json_encode(['status' => $res->status(), 'data' => $res->json()]);
