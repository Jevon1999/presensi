<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'

defineOptions({ layout: MemberLayout })

const props = defineProps({
    member: Object,
    today: Object,
    stats: Object,
    recent_attendances: { type: Array, default: () => [] },
    error: String,
})

// Live clock
const now = ref(new Date())
let clockInterval = null
onMounted(() => { clockInterval = setInterval(() => { now.value = new Date() }, 1000) })
onUnmounted(() => { clearInterval(clockInterval) })

const formattedDate = computed(() => now.value.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))
const formattedTime = computed(() => now.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }))

const isRefreshing = ref(false)
const refreshDashboard = () => {
    isRefreshing.value = true
    router.reload({ onFinish: () => { isRefreshing.value = false } })
}

const todayStatus = computed(() => {
    if (!props.today) return { label: 'Belum Absen', color: 'slate', icon: 'remove_circle_outline' }
    if (props.today.check_out_time) return { label: 'Sudah Pulang', color: 'green', icon: 'check_circle' }
    if (props.today.check_in_time) return { label: 'Sudah Masuk', color: 'blue', icon: 'login' }
    return { label: 'Belum Absen', color: 'slate', icon: 'remove_circle_outline' }
})

const formatTime = (t) => {
    if (!t) return '-'
    const d = new Date(t)
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const formatDate = (d) => {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' })
}
</script>

<template>
    <Head title="Dashboard Member" />

    <div>
        <!-- Error -->
        <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
            <span class="material-symbols-rounded text-red-500 text-[20px]">error</span>
            <div>
                <p class="text-sm font-semibold text-red-800">Gagal Memuat Data</p>
                <p class="text-xs text-red-600 mt-1">{{ error }}</p>
            </div>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Halo, {{ member?.nama_lengkap || 'Member' }} 👋</h1>
                <p class="text-sm text-slate-400 mt-0.5">{{ formattedDate }} — {{ formattedTime }}</p>
            </div>
            <button @click="refreshDashboard" :disabled="isRefreshing"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-sm font-semibold text-slate-600 rounded-xl transition-colors">
                <span class="material-symbols-rounded text-[18px]" :class="{ 'animate-spin': isRefreshing }">refresh</span>
                Refresh
            </button>
        </div>

        <!-- Today's Status Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Hari Ini</h3>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg"
                    :class="{
                        'bg-green-50 text-green-600': todayStatus.color === 'green',
                        'bg-blue-50 text-blue-600': todayStatus.color === 'blue',
                        'bg-slate-100 text-slate-500': todayStatus.color === 'slate',
                    }">
                    <span class="material-symbols-rounded text-[14px]">{{ todayStatus.icon }}</span>
                    {{ todayStatus.label }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Check In</p>
                    <p class="text-lg font-bold text-slate-800">{{ today?.check_in_time ? formatTime(today.check_in_time) : '--:--' }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Check Out</p>
                    <p class="text-lg font-bold text-slate-800">{{ today?.check_out_time ? formatTime(today.check_out_time) : '--:--' }}</p>
                </div>
            </div>
        </div>

        <!-- Monthly Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <span class="material-symbols-rounded text-blue-500 text-[20px]">calendar_today</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Hari Kerja</p>
                        <p class="text-xl font-bold text-slate-800">{{ stats?.working_days || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                        <span class="material-symbols-rounded text-green-500 text-[20px]">check_circle</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Hadir</p>
                        <p class="text-xl font-bold text-slate-800">{{ stats?.total_hadir || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                        <span class="material-symbols-rounded text-red-500 text-[20px]">cancel</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Absen</p>
                        <p class="text-xl font-bold text-slate-800">{{ stats?.total_absen || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                        <span class="material-symbols-rounded text-amber-500 text-[20px]">schedule</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Terlambat</p>
                        <p class="text-xl font-bold text-slate-800">{{ stats?.total_terlambat || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Riwayat Absensi Terbaru</h3>
            </div>
            <div v-if="recent_attendances.length" class="divide-y divide-slate-50">
                <div v-for="att in recent_attendances" :key="att.id" class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">{{ formatDate(att.tanggal) }}</p>
                        <p class="text-xs text-slate-400">{{ att.status || 'hadir' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ formatTime(att.check_in_time) }} — {{ formatTime(att.check_out_time) }}
                        </p>
                        <span v-if="att.is_late" class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">Terlambat</span>
                    </div>
                </div>
            </div>
            <div v-else class="p-8 text-center">
                <span class="material-symbols-rounded text-slate-300 text-[32px] mb-2">event_busy</span>
                <p class="text-sm text-slate-400">Belum ada data absensi</p>
            </div>
        </div>
    </div>
</template>
