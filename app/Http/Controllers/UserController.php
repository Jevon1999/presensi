<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'https://api.globalintermedia.online/api');
    }

    private function token()
    {
        return session('auth_token');
    }

    private function api()
    {
        return Http::withToken($this->token())->timeout(15);
    }

    private function handleApiError($response, $default = 'Terjadi kesalahan.')
    {
        if ($response->status() === 401) {
            session()->forget(['auth_token', 'user']);
            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
        }
        if ($response->status() === 422) {
            return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
        }
        if ($response->status() === 404) {
            return back()->with('error', 'Data tidak ditemukan.');
        }
        return back()->with('error', $default . ' (' . $response->status() . ')');
    }

    public function index(Request $request)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/users", $request->only(['search', 'role', 'is_active', 'page']));

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            if (!$response->successful()) {
                Log::warning('Users API error: status=' . $response->status());
                return Inertia::render('Users/Index', [
                    'users' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0],
                    'filters' => $request->only(['search', 'role', 'is_active']),
                    'error' => 'Gagal memuat data. (' . $response->status() . ')',
                ]);
            }

            return Inertia::render('Users/Index', [
                'users' => $response->json(),
                'filters' => $request->only(['search', 'role', 'is_active']),
            ]);
        } catch (\Exception $e) {
            Log::error('Users index error: ' . $e->getMessage());
            return Inertia::render('Users/Index', [
                'users' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0],
                'filters' => $request->only(['search', 'role', 'is_active']),
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $response = $this->api()->post("{$this->apiUrl}/users", $request->all());

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal menambahkan user.');
            }

            return back()->with('success', 'User berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('Users store error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/users/{$id}");

            if (!$response->successful()) {
                return back()->with('error', 'Gagal memuat detail user.');
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat data.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = $this->api()->put("{$this->apiUrl}/users/{$id}", $request->all());

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal mengupdate user.');
            }

            return back()->with('success', 'User berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Users update error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $response = $this->api()->delete("{$this->apiUrl}/users/{$id}");

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal menghapus user.');
            }

            return back()->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Users destroy error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }
}
