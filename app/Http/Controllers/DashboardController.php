<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'https://api.globalintermedia.online/api');
    }

    public function index(Request $request)
    {
        $token = session('auth_token');

        if (!$token) {
            return redirect()->route('login');
        }

        try {
            $today = now()->format('Y-m-d');

            $responses = Http::pool(fn ($pool) => [
                $pool->as('today_attendances')
                    ->withToken($token)
                    ->timeout(15)
                    ->get("{$this->apiUrl}/attendances", ['date' => $today]),
                $pool->as('members')
                    ->withToken($token)
                    ->timeout(15)
                    ->get("{$this->apiUrl}/members"),
                $pool->as('offices')
                    ->withToken($token)
                    ->timeout(15)
                    ->get("{$this->apiUrl}/offices"),
            ]);

            // Check for 401 - token expired
            foreach ($responses as $key => $response) {
                if ($response instanceof \Exception) {
                    throw new \Exception('Tidak dapat terhubung ke server API.');
                }
                if ($response->status() === 401) {
                    session()->forget(['auth_token', 'user']);
                    return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                }
            }

            $todayAttendances = $responses['today_attendances']->json()['data'] ?? [];
            $members = $responses['members']->json()['data'] ?? [];
            $offices = $responses['offices']->json()['data'] ?? [];

            $activeMembers = array_filter($members, fn($m) => ($m['status_aktif'] ?? true));
            $totalMembers = count($activeMembers);
            $todayCount = count($todayAttendances);
            $attendanceRate = $totalMembers > 0 ? round(($todayCount / $totalMembers) * 100, 1) : 0;

            $attendedIds = array_column($todayAttendances, 'member_id');
            $absentMembers = array_values(array_filter(
                $activeMembers,
                fn($m) => !in_array($m['id'], $attendedIds)
            ));

            return Inertia::render('Dashboard/Index', [
                'stats' => [
                    'today_attendance' => $todayCount,
                    'total_members' => $totalMembers,
                    'total_offices' => count($offices),
                    'attendance_rate' => $attendanceRate,
                    'absent' => count($absentMembers),
                ],
                'recent_attendances' => array_slice($todayAttendances, 0, 10),
                'absent_members' => array_slice($absentMembers, 0, 10),
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Dashboard connection error: ' . $e->getMessage());

            return Inertia::render('Dashboard/Index', [
                'stats' => [
                    'today_attendance' => 0,
                    'total_members' => 0,
                    'total_offices' => 0,
                    'attendance_rate' => 0,
                    'absent' => 0,
                ],
                'recent_attendances' => [],
                'absent_members' => [],
                'error' => 'Tidak dapat terhubung ke server. Periksa koneksi internet.',
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());

            return Inertia::render('Dashboard/Index', [
                'stats' => [
                    'today_attendance' => 0,
                    'total_members' => 0,
                    'total_offices' => 0,
                    'attendance_rate' => 0,
                    'absent' => 0,
                ],
                'recent_attendances' => [],
                'absent_members' => [],
                'error' => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }
}
