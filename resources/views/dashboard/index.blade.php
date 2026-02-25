@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div x-data="dashboardData()" x-init="loadData()">
    
    <!-- Welcome Message -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Welcome back, <span x-text="userName">Admin</span>!</h2>
        <p class="text-gray-600">Here's what's happening with your attendance today.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Attendances Today -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-200 text-sm font-medium">Today's Attendance</p>
                    <h3 class="text-3xl font-bold mt-2" x-text="stats.todayAttendance">-</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-purple-200 text-sm">
                <span x-text="stats.todayPercentage">0</span>% of total members
            </p>
        </div>

        <!-- Total Members -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-200 text-sm font-medium">Total Members</p>
                    <h3 class="text-3xl font-bold mt-2" x-text="stats.totalMembers">-</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-blue-200 text-sm">
                Active members in system
            </p>
        </div>

        <!-- Total Offices -->
        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-200 text-sm font-medium">Total Offices</p>
                    <h3 class="text-3xl font-bold mt-2" x-text="stats.totalOffices">-</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-green-200 text-sm">
                Registered office locations
            </p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Attendances (Left - 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Attendances</h3>
                <a href="/absensi" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
            </div>
            
            <!-- Loading State -->
            <div x-show="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                <p class="text-gray-600 mt-2">Loading...</p>
            </div>

            <!-- Attendance List -->
            <div x-show="!loading && attendances.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Office</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="attendance in attendances.slice(0, 5)" :key="attendance.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" 
                                                 :src="`https://ui-avatars.com/api/?name=${attendance.member?.name || 'User'}&background=random`" 
                                                 alt="">
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900" x-text="attendance.member?.name || 'N/A'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600" x-text="attendance.office?.name || 'N/A'"></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600" x-text="formatTime(attendance.check_in_time)"></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Present
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && attendances.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-gray-600">No attendance records yet</p>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Stats</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">On Time</span>
                        <span class="font-semibold text-green-600" x-text="stats.onTime">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Late</span>
                        <span class="font-semibold text-yellow-600" x-text="stats.late">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Absent</span>
                        <span class="font-semibold text-red-600" x-text="stats.absent">0</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Activity Feed</h3>
                <div class="space-y-3">
                    <template x-for="(activity, index) in recentActivity" :key="index">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 mt-2 rounded-full bg-blue-500"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-800" x-text="activity.text"></p>
                                <p class="text-xs text-gray-500" x-text="activity.time"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function dashboardData() {
    return {
        loading: true,
        userName: 'Admin',
        attendances: [],
        stats: {
            todayAttendance: 0,
            totalMembers: 0,
            totalOffices: 0,
            todayPercentage: 0,
            onTime: 0,
            late: 0,
            absent: 0
        },
        recentActivity: [
            { text: 'New attendance recorded', time: '5 min ago' },
            { text: 'Member added to system', time: '1 hour ago' },
            { text: 'Office location updated', time: '2 hours ago' }
        ],

        async loadData() {
            try {
                // Validate token first
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    console.error('No auth token found');
                    window.location.href = '/login';
                    return;
                }
                
                // Ensure token is set in axios headers
                window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                console.log('Token set, fetching data...');

                // Fetch user info
                const userRes = await window.api.getUser();
                console.log('User response:', userRes.data);
                
                // Handle different response structures
                const userData = userRes.data.user || userRes.data;
                this.userName = userData.name || 'Admin';
                window.userName = this.userName;

                // Fetch all data in parallel
                const [attendancesRes, membersRes, officesRes] = await Promise.all([
                    window.api.getAttendances(),
                    window.api.getMembers(),
                    window.api.getOffices()
                ]);

                this.attendances = attendancesRes.data.data || attendancesRes.data || [];
                
                // Calculate stats
                const today = new Date().toISOString().split('T')[0];
                const todayAttendances = this.attendances.filter(a => 
                    a.check_in_time && a.check_in_time.startsWith(today)
                );

                this.stats.todayAttendance = todayAttendances.length;
                this.stats.totalMembers = membersRes.data.data?.length || membersRes.data?.length || 0;
                this.stats.totalOffices = officesRes.data.data?.length || officesRes.data?.length || 0;
                this.stats.todayPercentage = this.stats.totalMembers > 0 
                    ? Math.round((this.stats.todayAttendance / this.stats.totalMembers) * 100)
                    : 0;
                
                // Mock stats for now
                this.stats.onTime = Math.floor(this.stats.todayAttendance * 0.8);
                this.stats.late = Math.floor(this.stats.todayAttendance * 0.15);
                this.stats.absent = this.stats.totalMembers - this.stats.todayAttendance;

            } catch (err) {
                console.error('Failed to load dashboard data:', err);
                if (err.response?.status === 401) {
                    window.location.href = '/login';
                }
            } finally {
                this.loading = false;
            }
        },

        formatTime(datetime) {
            if (!datetime) return 'N/A';
            return new Date(datetime).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endsection