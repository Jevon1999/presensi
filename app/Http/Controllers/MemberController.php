<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MemberController extends Controller
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
            $responses = Http::pool(fn ($pool) => [
                $pool->as('members')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/members", $request->only(['search', 'office_id', 'status_aktif', 'status', 'page'])),
                $pool->as('offices')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/offices"),
            ]);

            if ($responses['members']->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            $membersData = $responses['members']->json();
            $offices = $responses['offices']->json()['data'] ?? [];

            return Inertia::render('Members/Index', [
                'members' => $membersData,
                'offices' => $offices,
                'filters' => $request->only(['search', 'office_id', 'status_aktif', 'status']),
            ]);
        } catch (\Exception $e) {
            Log::error('Members index error: ' . $e->getMessage());
            return Inertia::render('Members/Index', [
                'members' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0, 'from' => 0, 'to' => 0, 'links' => []],
                'offices' => [],
                'filters' => $request->only(['search', 'office_id', 'status_aktif', 'status']),
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $response = $this->api()->post("{$this->apiUrl}/members", $request->all());

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal menambahkan anggota.');
            }

            return back()->with('success', 'Anggota berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Members store error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/members/{$id}");

            if (!$response->successful()) {
                return back()->with('error', 'Gagal memuat detail anggota.');
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat data.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = $this->api()->put("{$this->apiUrl}/members/{$id}", $request->all());

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal mengupdate anggota.');
            }

            return back()->with('success', 'Anggota berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Members update error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $response = $this->api()->delete("{$this->apiUrl}/members/{$id}");

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal menghapus anggota.');
            }

            return back()->with('success', 'Anggota berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Members destroy error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $response = $this->api()->put("{$this->apiUrl}/members/{$id}/approve");

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal menyetujui anggota.');
            }

            return redirect()->route('members.index', $request->only(['search', 'office_id', 'status_aktif', 'status']))
                ->with('success', 'Anggota berhasil disetujui.');
        } catch (\Exception $e) {
            Log::error('Members approve error: ' . $e->getMessage());
            return redirect()->route('members.index')->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $response = $this->api()->put("{$this->apiUrl}/members/{$id}/reject", [
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            if (!$response->successful()) {
                return $this->handleApiError($response, 'Gagal menolak anggota.');
            }

            return redirect()->route('members.index', $request->only(['search', 'office_id', 'status_aktif', 'status']))
                ->with('success', 'Anggota berhasil ditolak.');
        } catch (\Exception $e) {
            Log::error('Members reject error: ' . $e->getMessage());
            return redirect()->route('members.index')->with('error', 'Tidak dapat terhubung ke server.');
        }
    }
}
