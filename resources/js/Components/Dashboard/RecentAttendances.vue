<script setup>
const props = defineProps({
    attendances: Array
});

const getStatusBadge = (status) => {
    const badges = {
        'hadir': 'badge-success',
        'izin': 'badge-warning',
        'sakit': 'badge-info',
        'alpha': 'badge-error'
    };
    return badges[status] || 'badge-ghost';
};

const getStatusText = (status) => {
    const texts = {
        'hadir': 'Hadir',
        'izin': 'Izin',
        'sakit': 'Sakit',
        'alpha': 'Alpha'
    };
    return texts[status] || status;
};

const formatTime = (time) => {
    if (!time) return '-';
    return time.substring(0, 5); // HH:MM
};
</script>

<template>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">Absensi Terbaru</h3>
            
            <div v-if="attendances && attendances.length > 0" class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attendance in attendances" :key="attendance.id">
                            <td>
                                <div class="font-medium">{{ attendance.member?.nama_lengkap }}</div>
                                <div class="text-xs opacity-50">{{ attendance.member?.office?.name }}</div>
                            </td>
                            <td><span class="font-mono text-sm">{{ formatTime(attendance.check_in_time) }}</span></td>
                            <td><span class="font-mono text-sm">{{ formatTime(attendance.check_out_time) }}</span></td>
                            <td>
                                <span class="badge badge-sm" :class="getStatusBadge(attendance.status)">
                                    {{ getStatusText(attendance.status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p>Belum ada absensi hari ini</p>
            </div>
        </div>
    </div>
</template>
