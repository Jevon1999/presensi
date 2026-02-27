<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatsCard from '@/Components/Dashboard/StatsCard.vue';
import AttendanceChart from '@/Components/Dashboard/AttendanceChart.vue';
import RecentAttendances from '@/Components/Dashboard/RecentAttendances.vue';
import MembersWithoutAttendance from '@/Components/Dashboard/MembersWithoutAttendance.vue';

const props = defineProps({
    stats: Object,
    recent_attendances: Array,
    members_without_attendance: Array,
    weekly_stats: Object,
    weekly_attendances: Array,
    auth_token: String,
});

const currentTime = ref(new Date());

// Update time every second
onMounted(() => {
    setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>System Overview</template>

        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight">System Overview</h2>
                    <p class="text-gray-500 mt-1 text-sm">Welcome back! Here's what's happening in your organization today.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500">{{ formattedDate }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ formattedTime }}</p>
                </div>
            </div>

            <!-- Stats Cards Grid - Compact -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatsCard
                    label="Attendance Today"
                    :value="stats.today_attendance"
                    :trend="`${stats.attendance_rate}%`"
                    :trend-up="stats.attendance_rate >= 80"
                    icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                    color-class="text-blue-600"
                    bg-class="bg-blue-50"
                />
                
                <StatsCard
                    label="Active Members"
                    :value="stats.total_members"
                    trend="+12%"
                    :trend-up="true"
                    icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                    color-class="text-emerald-600"
                    bg-class="bg-emerald-50"
                />
                
                <StatsCard
                    label="Attendance Rate"
                    :value="`${stats.attendance_rate}%`"
                    trend="+2.4%"
                    :trend-up="true"
                    icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    :color-class="stats.attendance_rate >= 80 ? 'text-purple-600' : 'text-orange-600'"
                    :bg-class="stats.attendance_rate >= 80 ? 'bg-purple-50' : 'bg-orange-50'"
                />
                
                <StatsCard
                    label="Not Checked In"
                    :value="stats.absent"
                    :trend="stats.absent > 10 ? '+5%' : '-5%'"
                    :trend-up="false"
                    icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    color-class="text-orange-600"
                    bg-class="bg-orange-50"
                />
            </div>

            <!-- Charts Row - Compact -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Attendance Chart -->
                <div class="lg:col-span-2">
                    <AttendanceChart 
                        :weekly-stats="weekly_stats"
                        :weekly-attendances="weekly_attendances"
                    />
                </div>

                <!-- Members Without Attendance -->
                <div>
                    <MembersWithoutAttendance 
                        :members="members_without_attendance"
                    />
                </div>
            </div>

            <!-- Recent Attendances Table -->
            <div>
                <RecentAttendances 
                    :attendances="recent_attendances"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
