<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    stats: Object,
    recent_attendances: Array,
    absent_members: Array,
    error: String,
})

// Live clock
const now = ref(new Date())
let clockInterval = null
onMounted(() => { clockInterval = setInterval(() => { now.value = new Date() }, 1000) })
onUnmounted(() => { clearInterval(clockInterval) })

// Refresh
const isRefreshing = ref(false)
const refreshDashboard = () => {
    isRefreshing.value = true
    router.reload({
        onFinish: () => { isRefreshing.value = false },
    })
}

const formattedDate = computed(() => {
    return now.value.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})
const formattedTime = computed(() => {
    return now.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
})

// Stats cards config
const statCards = computed(() => [
    {
        label: 'Hadir Hari Ini',
        value: props.stats?.today_attendance ?? 0,
        icon: 'check_circle',
        color: 'emerald',
        bgClass: 'bg-emerald-50',
        iconClass: 'text-emerald-600 bg-emerald-100',
        subtitle: `${props.stats?.attendance_rate ?? 0}% kehadiran`,
    },
    {
        label: 'Total Anggota',
        value: props.stats?.total_members ?? 0,
        icon: 'group',
        color: 'blue',
        bgClass: 'bg-blue-50',
        iconClass: 'text-blue-600 bg-blue-100',
        subtitle: 'Anggota aktif',
    },
    {
        label: 'Belum Hadir',
        value: props.stats?.absent ?? 0,
        icon: 'person_off',
        color: 'amber',
        bgClass: 'bg-amber-50',
        iconClass: 'text-amber-600 bg-amber-100',
        subtitle: 'Belum absen hari ini',
    },
    {
        label: 'Total Kantor',
        value: props.stats?.total_offices ?? 0,
        icon: 'apartment',
        color: 'purple',
        bgClass: 'bg-purple-50',
        iconClass: 'text-purple-600 bg-purple-100',
        subtitle: 'Lokasi terdaftar',
    },
])

// Attendance rate for ring chart
const rate = computed(() => props.stats?.attendance_rate ?? 0)
const circumference = 2 * Math.PI * 54
const strokeDashoffset = computed(() => circumference - (rate.value / 100) * circumference)

// Format time helper
const formatTime = (datetime) => {
    if (!datetime) return '-'
    const d = new Date(datetime)
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

// Status color
const statusColor = (status) => {
    const map = {
        'hadir': 'bg-emerald-100 text-emerald-700',
        'terlambat': 'bg-amber-100 text-amber-700',
        'izin': 'bg-blue-100 text-blue-700',
        'sakit': 'bg-orange-100 text-orange-700',
        'alpha': 'bg-red-100 text-red-700',
    }
    return map[status] || 'bg-slate-100 text-slate-700'
}
</script>

<template>
    <Head title="Dashboard" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold">Dashboard</h2>
            <p class="text-sm text-slate-500 mt-1">{{ formattedDate }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                @click="refreshDashboard"
                :disabled="isRefreshing"
                class="bg-white border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-1.5 shadow-sm hover:bg-slate-50 transition-colors disabled:opacity-50"
                title="Refresh data"
            >
                <span class="material-symbols-rounded text-slate-500 text-[18px]" :class="{ 'animate-spin': isRefreshing }">refresh</span>
                <span class="text-sm text-slate-600 hidden sm:inline">Refresh</span>
            </button>
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 flex items-center gap-2 shadow-sm">
                <span class="material-symbols-rounded text-blue-500 text-[18px]">schedule</span>
                <span class="text-sm font-mono font-semibold tabular-nums">{{ formattedTime }}</span>
            </div>
        </div>
    </div>

    <!-- Error Banner -->
    <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-rounded text-[18px]">warning</span>
            {{ error }}
        </div>
        <button @click="refreshDashboard" class="text-xs font-semibold bg-red-100 hover:bg-red-200 px-3 py-1 rounded-lg transition-colors">
            Coba lagi
        </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div
            v-for="(card, i) in statCards"
            :key="i"
            class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow duration-300"
        >
            <div class="flex items-center justify-between mb-3">
                <div :class="card.iconClass" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center">
                    <span class="material-symbols-rounded text-[20px]">{{ card.icon }}</span>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-bold tracking-tight">{{ card.value }}</div>
            <p class="text-xs text-slate-500 mt-1 font-medium">{{ card.label }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ card.subtitle }}</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Attendance Ring Chart + Absent Members -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Attendance Rate Ring -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm">
                <h3 class="font-semibold text-slate-600 mb-4 text-sm">Tingkat Kehadiran</h3>
                <div class="flex justify-center py-2">
                    <div class="relative">
                        <svg class="w-32 h-32 -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="54" fill="none" stroke-width="10" class="stroke-slate-100" />
                            <circle
                                cx="60" cy="60" r="54" fill="none"
                                stroke-width="10"
                                stroke-linecap="round"
                                class="stroke-blue-500 transition-all duration-1000"
                                :stroke-dasharray="circumference"
                                :stroke-dashoffset="strokeDashoffset"
                            />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-2xl font-bold leading-none">{{ rate }}%</p>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">Hadir</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-slate-500">Hadir</span>
                        <span class="font-bold ml-auto">{{ stats?.today_attendance ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-2 h-2 rounded-full bg-slate-200"></span>
                        <span class="text-slate-500">Absen</span>
                        <span class="font-bold ml-auto">{{ stats?.absent ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Absent Members -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-600 text-sm">Belum Hadir</h3>
                    <span class="text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">{{ absent_members?.length ?? 0 }}</span>
                </div>
                <div v-if="absent_members?.length" class="space-y-2 max-h-60 overflow-y-auto">
                    <div
                        v-for="member in absent_members"
                        :key="member.id"
                        class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-[10px] font-bold shrink-0">
                            {{ (member.nama || member.name || '?')[0].toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate">{{ member.nama || member.name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ member.jabatan || member.position || '-' }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-6">
                    <span class="material-symbols-rounded text-emerald-400 text-4xl">celebration</span>
                    <p class="text-sm text-slate-500 mt-2">Semua hadir!</p>
                </div>
            </div>
        </div>

        <!-- Recent Attendances Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-5 flex items-center justify-between border-b border-slate-100">
                <h3 class="font-semibold text-slate-600 text-sm">Absensi Terbaru Hari Ini</h3>
                <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                    {{ recent_attendances?.length ?? 0 }} data
                </span>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden divide-y divide-slate-50">
                <div
                    v-for="att in recent_attendances"
                    :key="att.id"
                    class="p-4 flex items-center gap-3"
                >
                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold shrink-0">
                        {{ ((att.member?.nama || att.member?.name || '?')[0] || '?').toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ att.member?.nama || att.member?.name || '-' }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[11px] text-slate-400">
                                <span class="material-symbols-rounded text-[12px] align-middle">login</span>
                                {{ formatTime(att.check_in) }}
                            </span>
                            <span v-if="att.check_out" class="text-[11px] text-slate-400">
                                <span class="material-symbols-rounded text-[12px] align-middle">logout</span>
                                {{ formatTime(att.check_out) }}
                            </span>
                        </div>
                    </div>
                    <span
                        :class="statusColor(att.status)"
                        class="text-[10px] font-bold px-2 py-1 rounded-full uppercase shrink-0"
                    >
                        {{ att.status || 'hadir' }}
                    </span>
                </div>
                <div v-if="!recent_attendances?.length" class="p-8 text-center">
                    <span class="material-symbols-rounded text-slate-300 text-4xl">inbox</span>
                    <p class="text-sm text-slate-400 mt-2">Belum ada data absensi</p>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50">
                            <th class="px-5 py-3">Anggota</th>
                            <th class="px-5 py-3">Kantor</th>
                            <th class="px-5 py-3">Check In</th>
                            <th class="px-5 py-3">Check Out</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr
                            v-for="att in recent_attendances"
                            :key="att.id"
                            class="group hover:bg-slate-50/50 transition-colors"
                        >
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-[10px] font-bold">
                                        {{ ((att.member?.nama || att.member?.name || '?')[0] || '?').toUpperCase() }}
                                    </div>
                                    <span class="text-sm font-medium">{{ att.member?.nama || att.member?.name || '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-500">{{ att.office?.nama || att.office?.name || '-' }}</td>
                            <td class="px-5 py-3 text-sm text-slate-500 font-mono">{{ formatTime(att.check_in) }}</td>
                            <td class="px-5 py-3 text-sm text-slate-500 font-mono">{{ att.check_out ? formatTime(att.check_out) : '-' }}</td>
                            <td class="px-5 py-3">
                                <span
                                    :class="statusColor(att.status)"
                                    class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase"
                                >
                                    {{ att.status || 'hadir' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!recent_attendances?.length">
                            <td colspan="5" class="px-5 py-12 text-center">
                                <span class="material-symbols-rounded text-slate-300 text-4xl">inbox</span>
                                <p class="text-sm text-slate-400 mt-2">Belum ada data absensi hari ini</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
