<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProgressController extends Controller
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
            $responses = Http::pool(fn ($pool) => [
                $pool->as('progresses')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/progresses", $request->only(['member_id', 'start_date', 'end_date', 'page'])),
                $pool->as('members')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/members", ['per_page' => 200]),
            ]);

            if ($responses['progresses'] instanceof \Exception) {
                throw $responses['progresses'];
            }

            if ($responses['progresses']->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            return Inertia::render('Progresses/Index', [
                'progresses' => $responses['progresses']->json(),
                'members' => $responses['members']->json('data', []),
                'filters' => $request->only(['member_id', 'start_date', 'end_date']),
            ]);
        } catch (\Exception $e) {
            Log::error('Progresses index error: ' . $e->getMessage());
            return Inertia::render('Progresses/Index', [
                'progresses' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0, 'from' => 0, 'to' => 0, 'links' => []],
                'members' => [],
                'filters' => $request->only(['member_id', 'start_date', 'end_date']),
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $response = $this->api()->post("{$this->apiUrl}/progresses", $request->all());

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }
            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal menambahkan progress. (' . $response->status() . ')');
            }

            return back()->with('success', 'Progress berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Progress store error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/progresses/{$id}");
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
            $response = $this->api()->put("{$this->apiUrl}/progresses/{$id}", $request->all());

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }
            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengupdate progress. (' . $response->status() . ')');
            }

            return back()->with('success', 'Progress berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Progress update error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $response = $this->api()->delete("{$this->apiUrl}/progresses/{$id}");

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal menghapus progress. (' . $response->status() . ')');
            }

            return back()->with('success', 'Progress berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Progress destroy error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }
}
