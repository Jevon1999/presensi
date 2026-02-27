<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'https://api.globalintermedia.online/api');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $response = Http::post($this->apiUrl . '/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!$response->successful()) {
            return back()->with('error', 'Invalid credentials');
        }

        $data = $response->json();
        
        // Store token and user in session
        session(['auth_token' => $data['token']]);
        session(['user' => $data['user']]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $token = session('auth_token');

        if ($token) {
            Http::withToken($token)->post("{$this->apiUrl}/logout");
        }

        session()->forget(['auth_token', 'user']);

        return redirect()->route('login');
    }

    public function me(Request $request)
    {
        $token = session('auth_token');

        if (!$token) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $response = Http::withToken($token)
            ->get("{$this->apiUrl}/me");

        return $response->json();
    }
}
