<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StatisticsController extends Controller
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

    public function index(Request $request)
    {
        try {
            $params = $request->only(['start_date', 'end_date', 'office_id']);

            // Default: bulan berjalan
            if (empty($params['start_date'])) {
                $params['start_date'] = now()->startOfMonth()->format('Y-m-d');
            }
            if (empty($params['end_date'])) {
                $params['end_date'] = now()->format('Y-m-d');
            }

            $responses = Http::pool(fn ($pool) => [
                $pool->as('stats')->withToken($this->token())->timeout(20)
                    ->get("{$this->apiUrl}/statistics", $params),
                $pool->as('offices')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/offices"),
            ]);

            if ($responses['stats']->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            if (!$responses['stats']->successful()) {
                throw new \Exception('API Error ' . $responses['stats']->status() . ': ' . substr($responses['stats']->body(), 0, 100));
            }

            $statsData = $responses['stats']->json();
            $offices   = $responses['offices']->json()['data'] ?? [];

            return Inertia::render('Statistics/Index', [
                'stats'   => $statsData,
                'offices' => $offices,
                'filters' => $params,
            ]);
        } catch (\Exception $e) {
            Log::error('Statistics index error: ' . $e->getMessage());

            return Inertia::render('Statistics/Index', [
                'stats'   => null,
                'offices' => [],
                'filters' => $request->only(['start_date', 'end_date', 'office_id']),
                'error'   => 'Gagal memuat statistik: ' . $e->getMessage(),
            ]);
        }
    }
}
