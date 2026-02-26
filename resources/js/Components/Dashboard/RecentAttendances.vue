<script setup>
const props = defineProps({
    attendances: Array
});

const getStatusBadge = (status) => {
    const badges = {
        'hadir': 'bg-success/10 text-success border-success/30',
        'izin': 'bg-warning/10 text-warning border-warning/30',
        'sakit': 'bg-info/10 text-info border-info/30',
        'alpha': 'bg-error/10 text-error border-error/30'
    };
    return badges[status] || 'bg-base-300 text-base-content/50 border-neutral/30';
};

const getStatusText = (status) => {
    const texts = {
        'hadir': 'PRESENT',
        'izin': 'LEAVE',
        'sakit': 'SICK',
        'alpha': 'ABSENT'
    };
    return texts[status] || status.toUpperCase();
};

const formatTime = (time) => {
    if (!time) return '-';
    return time.substring(0, 5); // HH:MM
};
</script>

<template>
    <div class="bg-base-200 border border-neutral/30 rounded-md">
        <div class="p-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 bg-primary rounded"></div>
                <h3 class="text-xs font-mono font-semibold tracking-wider uppercase">RECENT ACTIVITY LOG</h3>
            </div>
            
            <div v-if="attendances && attendances.length > 0" class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <thead>
                        <tr class="border-b border-neutral/30 text-base-content/50">
                            <th class="text-left py-2 px-2 font-semibold tracking-wider">UNIT</th>
                            <th class="text-left py-2 px-2 font-semibold tracking-wider">IN</th>
                            <th class="text-left py-2 px-2 font-semibold tracking-wider">OUT</th>
                            <th class="text-left py-2 px-2 font-semibold tracking-wider">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attendance in attendances" :key="attendance.id" class="border-b border-neutral/20 hover:bg-base-300/30">
                            <td class="py-2.5 px-2">
                                <div class="font-semibold">{{ attendance.member?.nama_lengkap }}</div>
                                <div class="text-[10px] text-base-content/40 uppercase">{{ attendance.member?.office?.name }}</div>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-success">{{ formatTime(attendance.check_in_time) }}</span></td>
                            <td class="py-2.5 px-2"><span class="text-warning">{{ formatTime(attendance.check_out_time) }}</span></td>
                            <td class="py-2.5 px-2">
                                <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider border rounded" :class="getStatusBadge(attendance.status)">
                                    {{ getStatusText(attendance.status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-else class="text-center py-8">
                <svg class="w-10 h-10 mx-auto mb-2 text-base-content/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-xs font-mono text-base-content/40 uppercase tracking-wider">NO ACTIVITY RECORDED</p>
            </div>
        </div>
    </div>
</template>
