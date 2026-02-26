<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

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
            // Fetch dashboard data from API
            $today = now()->format('Y-m-d');
            $startOfWeek = now()->startOfWeek()->format('Y-m-d');
            $endOfWeek = now()->endOfWeek()->format('Y-m-d');
            
            // Parallel API calls
            $responses = Http::pool(fn ($pool) => [
                $pool->as('today_attendances')
                    ->withToken($token)
                    ->get("{$this->apiUrl}/attendances", ['date' => $today]),
                $pool->as('members')
                    ->withToken($token)
                    ->get("{$this->apiUrl}/members"),
                $pool->as('offices')
                    ->withToken($token)
                    ->get("{$this->apiUrl}/offices"),
                $pool->as('weekly_report')
                    ->withToken($token)
                    ->get("{$this->apiUrl}/attendances/report", [
                        'start_date' => $startOfWeek,
                        'end_date' => $endOfWeek,
                    ]),
            ]);

            // Process responses
            $todayAttendances = $responses['today_attendances']->json()['data'] ?? [];
            $members = $responses['members']->json()['data'] ?? [];
            $offices = $responses['offices']->json()['data'] ?? [];
            $weeklyReport = $responses['weekly_report']->json();

            // Calculate stats
            $totalMembers = count(array_filter($members, fn($m) => $m['status_aktif']));
            $todayAttendanceCount = count($todayAttendances);
            $attendanceRate = $totalMembers > 0 
                ? round(($todayAttendanceCount / $totalMembers) * 100, 1)
                : 0;

            // Members without attendance today
            $attendedMemberIds = array_column($todayAttendances, 'member_id');
            $membersWithoutAttendance = array_filter(
                $members,
                fn($m) => $m['status_aktif'] && !in_array($m['id'], $attendedMemberIds)
            );

            return Inertia::render('Dashboard/Index', [
                'stats' => [
                    'today_attendance' => $todayAttendanceCount,
                    'total_members' => $totalMembers,
                    'total_offices' => count($offices),
                    'attendance_rate' => $attendanceRate,
                    'on_time' => count(array_filter($todayAttendances, fn($a) => ($a['status'] ?? '') === 'hadir')),
                    'late' => 0, // Calculate based on check-in time if needed
                    'absent' => $totalMembers - $todayAttendanceCount,
                ],
                'recent_attendances' => array_slice($todayAttendances, 0, 10),
                'members_without_attendance' => array_values($membersWithoutAttendance),
                'weekly_stats' => $weeklyReport['statistics'] ?? [],
                'weekly_attendances' => $weeklyReport['attendances'] ?? [],
                'auth_token' => $token, // Pass token for client-side API calls
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Failed to load dashboard data');
        }
    }
}
