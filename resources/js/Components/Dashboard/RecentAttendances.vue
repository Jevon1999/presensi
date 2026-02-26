<script setup>
const props = defineProps({
    attendances: Array
});

const getStatusBadge = (status) => {
    const badges = {
        'hadir': 'bg-emerald-50 text-emerald-600',
        'izin': 'bg-blue-50 text-blue-600',
        'sakit': 'bg-orange-50 text-orange-600',
        'alpha': 'bg-red-50 text-red-600'
    };
    return badges[status] || 'bg-gray-50 text-gray-600';
};

const getStatusText = (status) => {
    const texts = {
        'hadir': 'On Time',
        'izin': 'Leave',
        'sakit': 'Sick',
        'alpha': 'Absent'
    };
    return texts[status] || status;
};

const getStatusIcon = (status) => {
    if (status === 'hadir') return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    return 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
};

const formatTime = (time) => {
    if (!time) return '-';
    return time.substring(0, 5); // HH:MM
};
</script>

<template>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-gray-50">
            <div>
                <h3 class="text-xl font-bold">Recent Absensi</h3>
                <p class="text-sm text-gray-400">Latest attendance activity across the system</p>
            </div>
            <button class="text-blue-600 font-bold text-sm hover:underline">View All Activity</button>
        </div>
        
        <div v-if="attendances && attendances.length > 0" class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50">
                        <th class="px-6 py-4 text-left">Member</th>
                        <th class="px-6 py-4 text-left">Time</th>
                        <th class="px-6 py-4 text-left">Check Out</th>
                        <th class="px-6 py-4 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="attendance in attendances" :key="attendance.id" class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                    {{ attendance.member?.nama_lengkap?.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-bold text-sm">{{ attendance.member?.nama_lengkap }}</div>
                                    <div class="text-xs text-gray-400">{{ attendance.member?.office?.name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ formatTime(attendance.check_in_time) }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-500">{{ formatTime(attendance.check_out_time) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase', getStatusBadge(attendance.status)]">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon(attendance.status)"/>
                                </svg>
                                {{ getStatusText(attendance.status) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div v-else class="text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-500 font-medium">No attendance recorded today</p>
        </div>
    </div>
</template>
