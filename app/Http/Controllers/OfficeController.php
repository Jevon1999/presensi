<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OfficeController extends Controller
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

    public function index(Request $request)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/offices");

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            return Inertia::render('Offices/Index', [
                'offices' => $response->json()['data'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Offices index error: ' . $e->getMessage());
            return Inertia::render('Offices/Index', [
                'offices' => [],
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $response = $this->api()->post("{$this->apiUrl}/offices", $request->all());

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }
            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal menambahkan kantor. (' . $response->status() . ')');
            }

            return back()->with('success', 'Kantor berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Office store error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/offices/{$id}");
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal memuat data.'], $response->status());
            }
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat data.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = $this->api()->put("{$this->apiUrl}/offices/{$id}", $request->all());

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }
            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengupdate kantor. (' . $response->status() . ')');
            }

            return back()->with('success', 'Kantor berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Office update error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $response = $this->api()->delete("{$this->apiUrl}/offices/{$id}");

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }
            if (!$response->successful()) {
                $msg = $response->json('message') ?? 'Gagal menghapus kantor.';
                return back()->with('error', $msg);
            }

            return back()->with('success', 'Kantor berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Office destroy error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }
}
