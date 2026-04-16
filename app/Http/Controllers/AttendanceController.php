<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AttendanceController extends Controller
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
            $params = $request->only(['date', 'member_id', 'status', 'office_id', 'page']);
            if (empty($params['date'])) {
                $params['date'] = now()->format('Y-m-d');
            }

            $responses = Http::pool(fn ($pool) => [
                $pool->as('attendances')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/attendances", $params),
                $pool->as('offices')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/offices"),
                $pool->as('members')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/members", ['per_page' => 200]),
            ]);

            if ($responses['attendances']->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            $attendancesData = $responses['attendances']->json();
            $offices = $responses['offices']->json()['data'] ?? [];
            $members = $responses['members']->json()['data'] ?? [];

            // Calculate summary stats
            $allData = $attendancesData['data'] ?? [];
            $summary = [
                'hadir' => count(array_filter($allData, fn($a) => $a['status'] === 'hadir')),
                'wfo'   => count(array_filter($allData, fn($a) => ($a['work_type'] ?? null) === 'wfo')),
                'wfa'   => count(array_filter($allData, fn($a) => ($a['work_type'] ?? null) === 'wfa')),
                'izin'  => count(array_filter($allData, fn($a) => $a['status'] === 'izin')),
                'sakit' => count(array_filter($allData, fn($a) => $a['status'] === 'sakit')),
                'alpha' => count(array_filter($allData, fn($a) => $a['status'] === 'alpha')),
                'total' => count($allData),
            ];

            return Inertia::render('Attendances/Index', [
                'attendances' => $attendancesData,
                'offices' => $offices,
                'members' => $members,
                'summary' => $summary,
                'filters' => array_merge($request->only(['date', 'member_id', 'status', 'office_id']), ['date' => $params['date']]),
            ]);
        } catch (\Exception $e) {
            Log::error('Attendances index error: ' . $e->getMessage());
            return Inertia::render('Attendances/Index', [
                'attendances' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0, 'from' => 0, 'to' => 0, 'links' => []],
                'offices' => [],
                'members' => [],
                'summary' => ['hadir' => 0, 'wfo' => 0, 'wfa' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'total' => 0],
                'filters' => $request->only(['date', 'member_id', 'status', 'office_id']),
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/attendances/{$id}");

            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal memuat data.'], $response->status());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat data.'], 500);
        }
    }

    public function reset(Request $request, $id)
    {
        try {
            $response = $this->api()->post("{$this->apiUrl}/attendances/{$id}/reset", $request->all());

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }

            if (!$response->successful()) {
                return back()->with('error', 'Gagal mereset absensi. (' . $response->status() . ')');
            }

            return back()->with('success', 'Absensi berhasil direset.');
        } catch (\Exception $e) {
            Log::error('Attendance reset error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    public function report(Request $request)
    {
        try {
            $params = $request->only(['start_date', 'end_date', 'member_id', 'office_id']);
            if (empty($params['start_date'])) {
                $params['start_date'] = now()->startOfMonth()->format('Y-m-d');
            }
            if (empty($params['end_date'])) {
                $params['end_date'] = now()->format('Y-m-d');
            }

            $responses = Http::pool(fn ($pool) => [
                $pool->as('report')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/attendances/report", $params),
                $pool->as('offices')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/offices"),
                $pool->as('members')->withToken($this->token())->timeout(15)
                    ->get("{$this->apiUrl}/members", ['per_page' => 200]),
            ]);

            if ($responses['report']->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            return Inertia::render('Attendances/Report', [
                'report' => $responses['report']->json(),
                'offices' => $responses['offices']->json()['data'] ?? [],
                'members' => $responses['members']->json()['data'] ?? [],
                'filters' => $params,
            ]);
        } catch (\Exception $e) {
            Log::error('Attendance report error: ' . $e->getMessage());
            return Inertia::render('Attendances/Report', [
                'report' => ['period' => [], 'statistics' => [], 'attendances' => []],
                'offices' => [],
                'members' => [],
                'filters' => $request->only(['start_date', 'end_date', 'member_id', 'office_id']),
                'error' => 'Gagal memuat laporan: ' . $e->getMessage(),
            ]);
        }
    }

    public function exportReport(Request $request)
    {
        try {
            $params = $request->only(['start_date', 'end_date', 'member_id', 'office_id']);
            if (empty($params['start_date'])) $params['start_date'] = now()->startOfMonth()->format('Y-m-d');
            if (empty($params['end_date'])) $params['end_date'] = now()->format('Y-m-d');

            $response = $this->api()->get("{$this->apiUrl}/attendances/report", $params);

            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengexport data.');
            }

            $data = $response->json();
            $attendances = $data['attendances'] ?? [];

            $filename = "laporan-absensi-{$params['start_date']}-{$params['end_date']}.csv";

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($attendances, $params) {
                $file = fopen('php://output', 'w');
                // BOM for UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Title
                fputcsv($file, ['Laporan Absensi']);
                fputcsv($file, ["Periode: {$params['start_date']} s/d {$params['end_date']}"]);
                fputcsv($file, []);

                // Header
                fputcsv($file, ['No', 'Tanggal', 'Nama', 'Kantor', 'Check In', 'Check Out', 'Status', 'Kehadiran']);

                $no = 1;
                foreach ($attendances as $att) {
                    fputcsv($file, [
                        $no++,
                        $att['tanggal'] ?? '-',
                        $att['member']['nama_lengkap'] ?? $att['member']['name'] ?? '-',
                        $att['member']['office']['name'] ?? '-',
                        $att['check_in_time'] ?? '-',
                        $att['check_out_time'] ?? '-',
                        $att['status'] ?? '-',
                        isset($att['work_type']) ? strtoupper($att['work_type']) : '-',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Export report error: ' . $e->getMessage());
            return back()->with('error', 'Gagal export: ' . $e->getMessage());
        }
    }
}
