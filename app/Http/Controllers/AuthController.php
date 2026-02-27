<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'https://api.globalintermedia.online/api');
    }

    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = Http::timeout(15)->post($this->apiUrl . '/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Login connection error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
        } catch (\Exception $e) {
            Log::error('Login unexpected error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }

        if ($response->status() === 401 || $response->status() === 403) {
            $msg = $response->json('message') ?: 'Email atau password salah.';
            return back()->withErrors(['email' => $msg])->with('error', $msg);
        }

        if ($response->status() === 422) {
            $errors = $response->json('errors', []);
            $message = $response->json('message', 'Data tidak valid.');
            // For wrong credentials, show a clear Indonesian message
            $friendlyMsg = 'Email atau password salah.';
            if (!empty($errors['email'])) {
                $friendlyMsg = is_array($errors['email']) ? $errors['email'][0] : $errors['email'];
            }
            return back()->withErrors(['email' => $friendlyMsg])->with('error', $friendlyMsg);
        }

        if (!$response->successful()) {
            Log::error('Login API error', ['status' => $response->status(), 'body' => $response->body()]);
            $msg = 'Gagal login. Server mengembalikan error (' . $response->status() . ').';
            return back()->with('error', $msg);
        }

        $data = $response->json();

        // Handle different token key names from API
        $token = $data['token'] ?? $data['access_token'] ?? $data['data']['token'] ?? $data['data']['access_token'] ?? null;
        $user = $data['user'] ?? $data['data']['user'] ?? $data['data'] ?? null;

        if (!$token) {
            Log::error('Login API response - no token found', $data ?? []);
            return back()->with('error', 'Login gagal: tidak menerima token dari server.');
        }

        // Store token and user in session
        session(['auth_token' => $token]);
        session(['user' => $user]);

        return redirect()->route('dashboard')->with('success', 'Selamat datang!');
    }

    public function logout(Request $request)
    {
        $token = session('auth_token');

        if ($token) {
            try {
                Http::timeout(10)->withToken($token)->post("{$this->apiUrl}/logout");
            } catch (\Exception $e) {
                Log::warning('Logout API call failed: ' . $e->getMessage());
            }
        }

        session()->forget(['auth_token', 'user']);
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil logout.');
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
