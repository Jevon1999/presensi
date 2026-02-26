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
        <template #header>Dashboard</template>

        <!-- Welcome Section -->
        <div class="mb-6 bg-gradient-to-r from-primary to-secondary text-white rounded-lg p-6 shadow-lg">
            <h2 class="text-3xl font-bold mb-2">Dashboard Admin</h2>
            <p class="text-lg opacity-90">{{ formattedDate }}</p>
            <p class="text-2xl font-mono mt-2">{{ formattedTime }}</p>
        </div>

        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <StatsCard
                title="Absensi Hari Ini"
                :value="stats.today_attendance"
                :subtitle="`${stats.attendance_rate}% dari total member`"
                icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                color="primary"
            />
            
            <StatsCard
                title="Total Member"
                :value="stats.total_members"
                subtitle="Member aktif"
                icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                color="info"
            />
            
            <StatsCard
                title="Tingkat Kehadiran"
                :value="`${stats.attendance_rate}%`"
                subtitle="Persentase hari ini"
                icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                :color="stats.attendance_rate >= 80 ? 'success' : stats.attendance_rate >= 60 ? 'warning' : 'error'"
            />
            
            <StatsCard
                title="Belum Absen"
                :value="stats.absent"
                subtitle="Member belum check-in"
                icon="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                color="warning"
            />
        </div>

        <!-- Charts and Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
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

        <!-- Recent Attendances -->
        <div>
            <RecentAttendances 
                :attendances="recent_attendances"
            />
        </div>
    </AuthenticatedLayout>
</template>
