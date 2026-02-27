<script setup>
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    attendance: { type: Object, default: null },
    loading: Boolean,
})

const formatDate = (d) => {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

const formatTime = (t) => t || '-'
</script>

<template>
    <div v-if="loading" class="flex items-center justify-center py-12">
        <span class="loading loading-spinner loading-md text-blue-500"></span>
    </div>
    <div v-else-if="attendance" class="space-y-5">
        <!-- Member Info -->
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold">
                {{ (attendance.member?.nama_lengkap || '?')[0].toUpperCase() }}
            </div>
            <div>
                <p class="font-semibold text-slate-800">{{ attendance.member?.nama_lengkap || '-' }}</p>
                <p class="text-xs text-slate-400">{{ attendance.member?.office?.name || '-' }} &middot; {{ attendance.member?.no_hp || '' }}</p>
            </div>
        </div>

        <!-- Date & Status -->
        <div class="bg-slate-50 rounded-xl p-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500">Tanggal</span>
                <span class="text-sm font-medium text-slate-700">{{ formatDate(attendance.tanggal) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500">Status</span>
                <Badge :status="attendance.status" type="attendance" />
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500">Check In</span>
                <span class="text-sm font-medium text-slate-700">{{ formatTime(attendance.check_in_time) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500">Check Out</span>
                <span class="text-sm font-medium text-slate-700">{{ formatTime(attendance.check_out_time) }}</span>
            </div>
        </div>

        <!-- Permissions -->
        <div v-if="attendance.permissions && attendance.permissions.length">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Izin / Sakit</h4>
            <div v-for="p in attendance.permissions" :key="p.id" class="bg-amber-50 rounded-xl p-3 mb-2">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold text-amber-700">{{ p.jenis || p.type || '-' }}</span>
                    <span class="text-[10px] text-amber-500">{{ p.tanggal || '' }}</span>
                </div>
                <p class="text-xs text-amber-800">{{ p.keterangan || p.reason || '-' }}</p>
            </div>
        </div>

        <!-- Reset Logs -->
        <div v-if="attendance.reset_logs && attendance.reset_logs.length">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Riwayat Reset</h4>
            <div v-for="r in attendance.reset_logs" :key="r.id" class="bg-slate-50 rounded-xl p-3 mb-2">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold text-slate-600">{{ r.admin?.name || 'Admin' }}</span>
                    <span class="text-[10px] text-slate-400">{{ r.created_at ? new Date(r.created_at).toLocaleString('id-ID') : '' }}</span>
                </div>
                <p class="text-xs text-slate-500">{{ r.reason || r.alasan || '-' }}</p>
            </div>
        </div>
    </div>
    <div v-else class="text-center py-12 text-sm text-slate-400">Data tidak tersedia.</div>
</template>
