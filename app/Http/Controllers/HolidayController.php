<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HolidayController extends Controller
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = rtrim(env('API_URL', 'https://api.globalintermedia.online/api'), '/');
    }

    private function token(): ?string
    {
        return session('auth_token');
    }

    /**
     * Tampilkan halaman daftar hari libur.
     */
    public function index(Request $request)
    {
        $year = (int) ($request->query('year', now()->year));

        try {
            $response = Http::withToken($this->token())
                ->acceptJson()
                ->timeout(15)
                ->get("{$this->apiUrl}/holidays", ['year' => $year]);

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            $data = $response->json();
        } catch (\Exception $e) {
            Log::error('HolidayController::index error: ' . $e->getMessage());
            $data = ['data' => [], 'total' => 0, 'year' => $year];
        }

        return Inertia::render('Holidays/Index', [
            'holidays'     => $data['data']   ?? [],
            'total'        => $data['total']  ?? 0,
            'selectedYear' => $year,
        ]);
    }

    /**
     * Trigger sync dari libur.deno.dev (AJAX-friendly, return JSON).
     */
    public function sync(Request $request)
    {
        $year = (int) ($request->input('year', now()->year));

        try {
            $response = Http::withToken($this->token())
                ->acceptJson()
                ->timeout(30)
                ->post("{$this->apiUrl}/holidays/sync", ['year' => $year]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'message' => 'Gagal sync: ' . ($response->json('message') ?? 'Error'),
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('HolidayController::sync error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal terhubung ke server.'], 500);
        }
    }

    /**
     * Tambah hari libur manual.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama'    => 'required|string|max:255',
        ]);

        try {
            $response = Http::withToken($this->token())
                ->acceptJson()
                ->timeout(15)
                ->post("{$this->apiUrl}/holidays", $validated);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan.');
            }

            $errMsg = $response->json('message') ?? 'Gagal menyimpan hari libur.';
            return redirect()->back()->with('error', $errMsg)->withInput();

        } catch (\Exception $e) {
            Log::error('HolidayController::store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal terhubung ke server.')->withInput();
        }
    }

    /**
     * Hapus hari libur.
     */
    public function destroy(int $id)
    {
        try {
            $response = Http::withToken($this->token())
                ->acceptJson()
                ->timeout(15)
                ->delete("{$this->apiUrl}/holidays/{$id}");

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Hari libur berhasil dihapus.');
            }

            return redirect()->back()->with('error', $response->json('message') ?? 'Gagal menghapus.');

        } catch (\Exception $e) {
            Log::error('HolidayController::destroy error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal terhubung ke server.');
        }
    }
}
