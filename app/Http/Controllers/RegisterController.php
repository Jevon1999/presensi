<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisterController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'https://api.globalintermedia.online/api');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $response = Http::timeout(15)->post($this->apiUrl . '/register', [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
            ]);
        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }

        if ($response->status() === 422) {
            $errors = $response->json('errors', []);
            return back()->withErrors($errors);
        }

        if (!$response->successful()) {
            $msg = $response->json('message') ?: 'Registrasi gagal.';
            return back()->with('error', $msg);
        }

        $data = $response->json();
        $token = $data['access_token'] ?? $data['token'] ?? null;
        $user = $data['user'] ?? null;
        $member = $data['member'] ?? null;

        if (!$token) {
            return back()->with('error', 'Registrasi gagal: tidak menerima token.');
        }

        session(['auth_token' => $token]);
        session(['user' => $user]);
        session(['member' => $member]);

        // New user → redirect to member application page
        return redirect('/member/apply')->with('success', 'Registrasi berhasil! Silakan lengkapi data pendaftaran magang.');
    }
}
