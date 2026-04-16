<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import EmptyState from '@/Components/EmptyState.vue'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js'
import { Bar, Line, Doughnut } from 'vue-chartjs'

ChartJS.register(
    CategoryScale, LinearScale, BarElement, LineElement,
    PointElement, ArcElement, Title, Tooltip, Legend, Filler
)

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    stats:   { type: Object, default: null },
    offices: { type: Array,  default: () => [] },
    filters: { type: Object, default: () => ({}) },
    error:   { type: String, default: null },
})

// ============================================================
// Filters
// ============================================================
const formatYMD = (d) => {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

const now      = new Date()
const y        = now.getFullYear()
const m        = now.getMonth()

const startDate   = ref(props.filters.start_date || formatYMD(new Date(y, m, 1)))
const endDate     = ref(props.filters.end_date   || formatYMD(now))
const officeFilter = ref(props.filters.office_id || '')

// Preset periods
const setPreset = (preset) => {
    const d = new Date()
    if (preset === 'week') {
        const day  = d.getDay()
        const diff = d.getDate() - day + (day === 0 ? -6 : 1)
        startDate.value = formatYMD(new Date(d.setDate(diff)))
        endDate.value   = formatYMD(new Date())
    } else if (preset === 'month') {
        startDate.value = formatYMD(new Date(y, m, 1))
        endDate.value   = formatYMD(now)
    } else if (preset === '3month') {
        const s = new Date(); s.setMonth(s.getMonth() - 3)
        startDate.value = formatYMD(s)
        endDate.value   = formatYMD(now)
    } else if (preset === 'year') {
        startDate.value = `${y}-01-01`
        endDate.value   = formatYMD(now)
    }
    applyFilters()
}

const applyFilters = () => {
    router.get('/statistics', {
        start_date: startDate.value || undefined,
        end_date:   endDate.value   || undefined,
        office_id:  officeFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true })
}

// ============================================================
// Data helpers
// ============================================================
const summary   = computed(() => props.stats?.summary        || {})
const trend     = computed(() => props.stats?.trend_daily    || [])
const bySchool  = computed(() => props.stats?.by_school      || [])
const lateRank  = computed(() => props.stats?.late_ranking   || [])
const checkInD  = computed(() => props.stats?.checkin_distribution || {})

// Sort sekolah state
const schoolSort = ref({ key: 'hadir_pct', dir: 'desc' })
const sortedBySchool = computed(() => {
    const arr = [...bySchool.value]
    arr.sort((a, b) => {
        const va = a[schoolSort.value.key] ?? 0
        const vb = b[schoolSort.value.key] ?? 0
        return schoolSort.value.dir === 'asc' ? va - vb : vb - va
    })
    return arr
})
const setSort = (key) => {
    if (schoolSort.value.key === key) {
        schoolSort.value.dir = schoolSort.value.dir === 'asc' ? 'desc' : 'asc'
    } else {
        schoolSort.value = { key, dir: 'desc' }
    }
}

// ============================================================
// Chart Refs (for PNG download)
// ============================================================
const chartDonut   = ref(null)
const chartTrend   = ref(null)
const chartSchool  = ref(null)
const chartWfoWfa  = ref(null)
const chartLate    = ref(null)
const chartCheckIn = ref(null)

const downloadChart = (chartRef, filename) => {
    const chart = chartRef.value?.chart
    if (!chart) return
    const url = chart.toBase64Image('image/png', 1)
    const a   = document.createElement('a')
    a.href     = url
    a.download = filename
    a.click()
}

// ============================================================
// Color Palette (consistent)
// ============================================================
const COLORS = {
    hadir:  '#10b981', // emerald
    alpha:  '#ef4444', // red
    izin:   '#3b82f6', // blue
    sakit:  '#f97316', // orange
    wfo:    '#6366f1', // indigo
    wfa:    '#8b5cf6', // violet
    late:   '#f59e0b', // amber
    trend1: '#3b82f6',
    trend2: '#ef444480',
}

// ============================================================
// Chart Doughnut — Status kehadiran global
// ============================================================
const donutData = computed(() => ({
    labels: ['Hadir', 'Alpha', 'Izin', 'Sakit'],
    datasets: [{
        data: [
            summary.value.hadir  || 0,
            summary.value.alpha  || 0,
            summary.value.izin   || 0,
            summary.value.sakit  || 0,
        ],
        backgroundColor: [COLORS.hadir, COLORS.alpha, COLORS.izin, COLORS.sakit],
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 8,
    }],
}))
const donutOptions = {
    responsive: true,
    cutout: '65%',
    plugins: {
        legend: { position: 'bottom', labels: { padding: 16, font: { size: 11 } } },
        tooltip: {
            callbacks: {
                label: (ctx) => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
                    const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0
                    return ` ${ctx.label}: ${ctx.raw} (${pct}%)`
                }
            }
        }
    },
}

// ============================================================
// Chart Line — Tren harian
// ============================================================
const trendData = computed(() => ({
    labels: trend.value.map(t => {
        const d = new Date(t.date)
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
    }),
    datasets: [
        {
            label: 'Hadir',
            data: trend.value.map(t => t.hadir),
            borderColor: COLORS.hadir,
            backgroundColor: COLORS.hadir + '20',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
        },
        {
            label: 'Alpha',
            data: trend.value.map(t => t.alpha),
            borderColor: COLORS.alpha,
            backgroundColor: COLORS.alpha + '20',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
        },
    ],
}))
const lineOptions = {
    responsive: true,
    interaction: { intersect: false, mode: 'index' },
    plugins: {
        legend: { position: 'top', labels: { font: { size: 11 } } },
    },
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
        x: { ticks: { font: { size: 10 }, maxRotation: 45 } },
    },
}

// ============================================================
// Chart Bar Horizontal — % Hadir per Sekolah
// ============================================================
const schoolBarData = computed(() => {
    const sorted = [...bySchool.value].sort((a, b) => b.hadir_pct - a.hadir_pct)
    return {
        labels: sorted.map(s => s.sekolah),
        datasets: [
            {
                label: 'Hadir %',
                data: sorted.map(s => s.hadir_pct),
                backgroundColor: COLORS.hadir + 'cc',
                borderRadius: 4,
            },
            {
                label: 'Alpha %',
                data: sorted.map(s => s.alpha_pct),
                backgroundColor: COLORS.alpha + 'cc',
                borderRadius: 4,
            },
        ],
    }
})
const schoolBarOptions = {
    indexAxis: 'y',
    responsive: true,
    plugins: {
        legend: { position: 'top', labels: { font: { size: 11 } } },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw}%` } },
    },
    scales: {
        x: { beginAtZero: true, max: 100, ticks: { callback: (v) => v + '%' } },
        y: { ticks: { font: { size: 10 } } },
    },
}

// ============================================================
// Chart Stacked Bar — WFO vs WFA per Sekolah
// ============================================================
const wfoWfaData = computed(() => {
    const sorted = [...bySchool.value].sort((a, b) => (b.wfo + b.wfa) - (a.wfo + a.wfa))
    return {
        labels: sorted.map(s => s.sekolah),
        datasets: [
            {
                label: 'WFO',
                data: sorted.map(s => s.wfo),
                backgroundColor: COLORS.wfo + 'cc',
                borderRadius: 4,
            },
            {
                label: 'WFA',
                data: sorted.map(s => s.wfa),
                backgroundColor: COLORS.wfa + 'cc',
                borderRadius: 4,
            },
        ],
    }
})
const stackedOptions = {
    responsive: true,
    plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
    scales: {
        x: { stacked: true, ticks: { font: { size: 10 }, maxRotation: 45 } },
        y: { stacked: true, beginAtZero: true },
    },
}

// ============================================================
// Chart Bar — Top 10 Terlambat
// ============================================================
const lateData = computed(() => ({
    labels: lateRank.value.map(r => r.nama),
    datasets: [{
        label: 'Jumlah Terlambat',
        data: lateRank.value.map(r => r.count),
        backgroundColor: COLORS.late + 'cc',
        borderRadius: 4,
    }],
}))
const lateOptions = {
    indexAxis: 'y',
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { beginAtZero: true, ticks: { stepSize: 1 } },
        y: { ticks: { font: { size: 10 } } },
    },
}

// ============================================================
// Chart Bar — Distribusi Jam Check-in
// ============================================================
const checkInData = computed(() => ({
    labels: Object.keys(checkInD.value),
    datasets: [{
        label: 'Jumlah',
        data: Object.values(checkInD.value),
        backgroundColor: COLORS.trend1 + 'cc',
        borderRadius: 4,
    }],
}))
const checkInOptions = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
        x: { ticks: { font: { size: 10 } } },
    },
}

// ============================================================
// Summary Cards
// ============================================================
const summaryCards = computed(() => [
    { label: 'Total Absensi', value: summary.value.total     || 0, icon: 'receipt_long',    color: 'bg-slate-100 text-slate-600' },
    { label: 'Hadir',         value: summary.value.hadir     || 0, icon: 'check_circle',    color: 'bg-emerald-50 text-emerald-600' },
    { label: 'Alpha',         value: summary.value.alpha     || 0, icon: 'cancel',          color: 'bg-red-50 text-red-600' },
    { label: 'Izin',          value: summary.value.izin      || 0, icon: 'event_busy',      color: 'bg-blue-50 text-blue-600' },
    { label: 'Sakit',         value: summary.value.sakit     || 0, icon: 'local_hospital',  color: 'bg-orange-50 text-orange-600' },
    { label: 'WFO',           value: summary.value.wfo       || 0, icon: 'business',        color: 'bg-indigo-50 text-indigo-600' },
    { label: 'WFA',           value: summary.value.wfa       || 0, icon: 'home_work',       color: 'bg-violet-50 text-violet-600' },
    { label: 'Terlambat',     value: summary.value.terlambat || 0, icon: 'schedule',        color: 'bg-amber-50 text-amber-600' },
])

// Hadir rate global
const hadirRate = computed(() => {
    const t = summary.value.total || 0
    return t > 0 ? ((summary.value.hadir / t) * 100).toFixed(1) : '0.0'
})
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Statistik Kehadiran</h1>
                <p class="text-sm text-slate-400 mt-0.5">Analisis & visualisasi data absensi</p>
            </div>
            <!-- Hadir Rate Badge -->
            <div v-if="stats" class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-2.5">
                <span class="material-symbols-rounded text-emerald-500 text-[20px]">trending_up</span>
                <div>
                    <p class="text-xs text-emerald-600 font-semibold">Tingkat Kehadiran</p>
                    <p class="text-xl font-bold text-emerald-700 leading-none">{{ hadirRate }}%</p>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-4 flex items-center gap-3">
            <span class="material-symbols-rounded text-red-500">error</span>
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-5">
            <!-- Preset buttons -->
            <div class="flex flex-wrap gap-2 mb-3">
                <span class="text-xs font-semibold text-slate-400 self-center mr-1">Periode:</span>
                <button @click="setPreset('week')"   class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors">Minggu Ini</button>
                <button @click="setPreset('month')"  class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors">Bulan Ini</button>
                <button @click="setPreset('3month')" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors">3 Bulan</button>
                <button @click="setPreset('year')"   class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors">Tahun Ini</button>
            </div>
            <!-- Custom filters -->
            <div class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Dari</label>
                    <flat-pickr
                        v-model="startDate"
                        :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true }"
                        class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none transition-all w-36"
                    />
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Sampai</label>
                    <flat-pickr
                        v-model="endDate"
                        :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true }"
                        class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none transition-all w-36"
                    />
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Kantor</label>
                    <select v-model="officeFilter" @change="applyFilters"
                        class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none transition-all min-w-[140px]">
                        <option value="">Semua Kantor</option>
                        <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>
                </div>
                <button @click="applyFilters"
                    class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Tampilkan
                </button>
            </div>
        </div>

        <!-- No data -->
        <div v-if="!stats">
            <EmptyState icon="bar_chart" title="Belum ada data statistik" description="Pilih periode dan klik Tampilkan." />
        </div>

        <template v-else>
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-5">
                <div v-for="s in summaryCards" :key="s.label" class="bg-white rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center gap-2">
                        <div :class="s.color" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0">
                            <span class="material-symbols-rounded text-[16px]">{{ s.icon }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base font-bold text-slate-800 leading-none">{{ s.value }}</p>
                            <p class="text-[9px] text-slate-400 mt-0.5 font-semibold uppercase truncate">{{ s.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 1: Donut + Hadir per Sekolah -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">
                <!-- Donut -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-slate-700">Distribusi Status</h3>
                        <button @click="downloadChart(chartDonut, 'status-kehadiran.png')"
                            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                            title="Download PNG">
                            <span class="material-symbols-rounded text-[16px]">download</span>
                        </button>
                    </div>
                    <Doughnut ref="chartDonut" :data="donutData" :options="donutOptions" />
                </div>

                <!-- Bar Hadir per Sekolah -->
                <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Kehadiran per Sekolah</h3>
                            <p class="text-[11px] text-slate-400">Persentase hadir vs alpha</p>
                        </div>
                        <button @click="downloadChart(chartSchool, 'kehadiran-sekolah.png')"
                            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                            title="Download PNG">
                            <span class="material-symbols-rounded text-[16px]">download</span>
                        </button>
                    </div>
                    <div v-if="bySchool.length" class="overflow-y-auto" style="max-height:280px">
                        <Bar ref="chartSchool" :data="schoolBarData" :options="schoolBarOptions" />
                    </div>
                    <EmptyState v-else icon="school" title="Belum ada data sekolah" description="" />
                </div>
            </div>

            <!-- Row 2: Tren Harian + WFO vs WFA -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <!-- Line Trend -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Tren Kehadiran Harian</h3>
                            <p class="text-[11px] text-slate-400">Hadir & Alpha per hari</p>
                        </div>
                        <button @click="downloadChart(chartTrend, 'tren-harian.png')"
                            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                            title="Download PNG">
                            <span class="material-symbols-rounded text-[16px]">download</span>
                        </button>
                    </div>
                    <div v-if="trend.length">
                        <Line ref="chartTrend" :data="trendData" :options="lineOptions" />
                    </div>
                    <EmptyState v-else icon="show_chart" title="Belum ada data tren" description="" />
                </div>

                <!-- Stacked WFO vs WFA -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">WFO vs WFA per Sekolah</h3>
                            <p class="text-[11px] text-slate-400">Perbandingan tipe kehadiran</p>
                        </div>
                        <button @click="downloadChart(chartWfoWfa, 'wfo-wfa-sekolah.png')"
                            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                            title="Download PNG">
                            <span class="material-symbols-rounded text-[16px]">download</span>
                        </button>
                    </div>
                    <div v-if="bySchool.length">
                        <Bar ref="chartWfoWfa" :data="wfoWfaData" :options="stackedOptions" />
                    </div>
                    <EmptyState v-else icon="home_work" title="Belum ada data WFO/WFA" description="" />
                </div>
            </div>

            <!-- Row 3: Top Terlambat + Distribusi Jam -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
                <!-- Late Ranking -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Top 10 Paling Sering Terlambat</h3>
                            <p class="text-[11px] text-slate-400">Berdasarkan jumlah keterlambatan</p>
                        </div>
                        <button @click="downloadChart(chartLate, 'ranking-terlambat.png')"
                            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                            title="Download PNG">
                            <span class="material-symbols-rounded text-[16px]">download</span>
                        </button>
                    </div>
                    <div v-if="lateRank.length">
                        <Bar ref="chartLate" :data="lateData" :options="lateOptions" />
                    </div>
                    <EmptyState v-else icon="schedule" title="Tidak ada data keterlambatan" description="" />
                </div>

                <!-- Check-in Distribution -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Distribusi Jam Check-in</h3>
                            <p class="text-[11px] text-slate-400">Pukul berapa paling banyak hadir</p>
                        </div>
                        <button @click="downloadChart(chartCheckIn, 'distribusi-checkin.png')"
                            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                            title="Download PNG">
                            <span class="material-symbols-rounded text-[16px]">download</span>
                        </button>
                    </div>
                    <Bar ref="chartCheckIn" :data="checkInData" :options="checkInOptions" />
                </div>
            </div>

            <!-- Tabel Detail per Sekolah -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-700">Detail per Sekolah</h3>
                    <p class="text-[11px] text-slate-400">Klik header untuk mengurutkan</p>
                </div>
                <div v-if="bySchool.length" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider px-4 py-2.5">Sekolah</th>
                                <th @click="setSort('total_siswa')"  class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 py-2.5 cursor-pointer hover:text-blue-500 select-none whitespace-nowrap">
                                    Siswa <span v-if="schoolSort.key === 'total_siswa'">{{ schoolSort.dir === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th @click="setSort('hadir_pct')"   class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 py-2.5 cursor-pointer hover:text-emerald-500 select-none whitespace-nowrap">
                                    Hadir % <span v-if="schoolSort.key === 'hadir_pct'">{{ schoolSort.dir === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th @click="setSort('alpha_pct')"   class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 py-2.5 cursor-pointer hover:text-red-400 select-none whitespace-nowrap">
                                    Alpha % <span v-if="schoolSort.key === 'alpha_pct'">{{ schoolSort.dir === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th @click="setSort('terlambat_pct')" class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 py-2.5 cursor-pointer hover:text-amber-500 select-none whitespace-nowrap">
                                    Terlambat % <span v-if="schoolSort.key === 'terlambat_pct'">{{ schoolSort.dir === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 py-2.5 whitespace-nowrap">WFO / WFA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in sortedBySchool" :key="s.sekolah"
                                class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-2.5">
                                    <p class="text-sm font-semibold text-slate-700 capitalize">{{ s.sekolah }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-center text-sm text-slate-600">{{ s.total_siswa }}</td>
                                <!-- Hadir % with bar -->
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-slate-100 rounded-full h-1.5 min-w-[60px]">
                                            <div class="h-1.5 rounded-full bg-emerald-500"
                                                :style="{ width: s.hadir_pct + '%' }"></div>
                                        </div>
                                        <span class="text-xs font-bold text-emerald-600 w-9 text-right">{{ s.hadir_pct }}%</span>
                                    </div>
                                </td>
                                <!-- Alpha % with bar -->
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-slate-100 rounded-full h-1.5 min-w-[60px]">
                                            <div class="h-1.5 rounded-full bg-red-500"
                                                :style="{ width: s.alpha_pct + '%' }"></div>
                                        </div>
                                        <span class="text-xs font-bold text-red-500 w-9 text-right">{{ s.alpha_pct }}%</span>
                                    </div>
                                </td>
                                <!-- Terlambat % -->
                                <td class="px-3 py-2.5 text-center">
                                    <span class="text-xs font-bold"
                                        :class="s.terlambat_pct > 20 ? 'text-amber-600' : 'text-slate-500'">
                                        {{ s.terlambat_pct }}%
                                    </span>
                                </td>
                                <!-- WFO / WFA -->
                                <td class="px-3 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 text-xs">
                                        <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded font-bold">{{ s.wfo }}</span>
                                        <span class="text-slate-300">/</span>
                                        <span class="px-1.5 py-0.5 bg-violet-50 text-violet-600 rounded font-bold">{{ s.wfa }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <EmptyState v-else icon="school" title="Tidak ada data sekolah" description="Belum ada data absensi dalam periode ini." />
            </div>
        </template>
    </div>
</template>
