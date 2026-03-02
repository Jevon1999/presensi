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

    public function dashboard()
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/member/dashboard");

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user', 'member']);
                return redirect()->route('login')->with('error', 'Sesi telah berakhir.');
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

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user', 'member']);
                return redirect()->route('login')->with('error', 'Sesi telah berakhir.');
            }

            $data = $response->json();

            return Inertia::render('Member/Progress', [
                'progresses' => $response->successful() ? $data : ['data' => []],
            ]);
        } catch (\Exception $e) {
            Log::error('Member progress error: ' . $e->getMessage());
            return Inertia::render('Member/Progress', [
                'progresses' => ['data' => []],
                'error' => 'Gagal memuat data.',
            ]);
        }
    }

    public function report(Request $request)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/member/report", [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user', 'member']);
                return redirect()->route('login')->with('error', 'Sesi telah berakhir.');
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
