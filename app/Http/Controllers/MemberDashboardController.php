<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberDashboardController extends Controller
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

    /**
     * Hapus session dan redirect ke login.
     * Dipanggil jika API mengembalikan 401/403/404 (akun dihapus / token kadaluarsa).
     */
    private function invalidSession(string $msg = 'Sesi telah berakhir, silakan login kembali.')
    {
        session()->forget(['auth_token', 'user', 'member']);
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login')->with('error', $msg);
    }

    public function dashboard()
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/member/dashboard");

            if ($response->status() === 401 || $response->status() === 403) {
                return $this->invalidSession();
            }

            // 404 = akun/member sudah dihapus oleh admin
            if ($response->status() === 404) {
                return $this->invalidSession('Akun Anda tidak ditemukan. Mungkin telah dihapus oleh admin.');
            }

            if (!$response->successful()) {
                return Inertia::render('Member/Dashboard', [
                    'error' => $response->json('message') ?: 'Gagal memuat dashboard.',
                ]);
            }

            $data = $response->json();

            return Inertia::render('Member/Dashboard', [
                'member' => $data['member'] ?? null,
                'today' => $data['today'] ?? null,
                'stats' => $data['stats'] ?? null,
                'recent_attendances' => $data['recent_attendances'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Member dashboard error: ' . $e->getMessage());
            return Inertia::render('Member/Dashboard', [
                'error' => 'Gagal memuat data.',
            ]);
        }
    }

    public function progress()
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/member/progress");

            if ($response->status() === 401 || $response->status() === 403) {
                return $this->invalidSession();
            }

            if ($response->status() === 404) {
                return $this->invalidSession('Akun Anda tidak ditemukan. Mungkin telah dihapus oleh admin.');
            }

            $data = $response->json();

            return Inertia::render('Member/Progress', [
                'progresses'       => $response->successful() ? $data : ['data' => []],
                'today_attendance' => $data['today_attendance'] ?? null,
                'member'           => $data['member'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Member progress error: ' . $e->getMessage());
            return Inertia::render('Member/Progress', [
                'progresses'       => ['data' => []],
                'today_attendance' => null,
                'member'           => null,
                'error'            => 'Gagal memuat data.',
            ]);
        }
    }

    public function storeProgress(Request $request)
    {
        try {
            $response = $this->api()->post("{$this->apiUrl}/member/progress", $request->all());

            if ($response->status() === 401 || $response->status() === 403) {
                $msg = $response->json('message') ?? 'Akses ditolak.';
                if ($response->status() === 401) return $this->invalidSession();
                return back()->with('error', $msg);
            }
            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal menyimpan progress. (' . $response->status() . ')');
            }

            return back()->with('success', 'Progress berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('Member store progress error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function updateProgress(Request $request, $id)
    {
        try {
            $response = $this->api()->put("{$this->apiUrl}/member/progress/{$id}", $request->all());

            if ($response->status() === 401 || $response->status() === 403) {
                $msg = $response->json('message') ?? 'Akses ditolak.';
                if ($response->status() === 401) return $this->invalidSession();
                return back()->with('error', $msg);
            }
            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengupdate progress. (' . $response->status() . ')');
            }

            return back()->with('success', 'Progress berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Member update progress error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function destroyProgress(Request $request, $id)
    {
        try {
            $response = $this->api()->delete("{$this->apiUrl}/member/progress/{$id}");

            if ($response->status() === 401 || $response->status() === 403) {
                $msg = $response->json('message') ?? 'Akses ditolak.';
                if ($response->status() === 401) return $this->invalidSession();
                return back()->with('error', $msg);
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal menghapus progress. (' . $response->status() . ')');
            }

            return back()->with('success', 'Progress berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Member destroy progress error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function report(Request $request)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/member/report", [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            if ($response->status() === 401 || $response->status() === 403) {
                return $this->invalidSession();
            }

            if ($response->status() === 404) {
                return $this->invalidSession('Akun Anda tidak ditemukan. Mungkin telah dihapus oleh admin.');
            }

            $data = $response->successful() ? $response->json() : [];

            return Inertia::render('Member/Report', [
                'member' => $data['member'] ?? null,
                'period' => $data['period'] ?? null,
                'stats' => $data['stats'] ?? null,
                'attendances' => $data['attendances'] ?? [],
                'filters' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Member report error: ' . $e->getMessage());
            return Inertia::render('Member/Report', [
                'error' => 'Gagal memuat laporan.',
            ]);
        }
    }
}
