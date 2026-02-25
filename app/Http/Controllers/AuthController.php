<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'http://localhost:8000/api');
    }

    public function showLogini()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $response = Http::post($this->apiUrl . '/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return $response->json();
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/logout");
        
        return $response->json();
    }

    public function me(Request $request)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->get("{$this->apiUrl}/me");

        return $response->json();
    }

    
}
