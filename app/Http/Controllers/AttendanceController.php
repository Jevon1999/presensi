<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'http://localhost:1337/api');
    }

    public function index(Request $request)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->get("{$this->apiUrl}/attendances", $request->all());
        
        return $response->json();
    }

    public function show(Request $request, $id)
    {
        $token = $request->bearerToken();
    
        $response = Http::withToken($token)
            ->get("{$this->apiUrl}/attendances/{$id}");
        
        return $response->json();
    }

    public function checkIn(Request $request)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/attendances/check-in", $request->all());
        
        return $response->json();
    }

    public function checkOut(Request $request)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/attendances/check-out", $request->all());
        
        return $response->json();
    }

    public function reset(Request $request, $id)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/attendances/{$id}/reset");
        
        return $response->json();
    }
}
