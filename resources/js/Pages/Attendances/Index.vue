<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormPanel from '@/Components/FormPanel.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'
import EmptyState from '@/Components/EmptyState.vue'
import AttendanceDetail from './Partials/AttendanceDetail.vue'
import ResetForm from './Partials/ResetForm.vue'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    attendances: Object,
    offices: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
})

// Panel state
const showDetail = ref(false)
const showReset = ref(false)
const selectedAttendance = ref(null)
const detailLoading = ref(false)
const resetProcessing = ref(false)

const formatYMD = (d) => {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

// Filters
const dateFilter = ref(props.filters.date || formatYMD(new Date()))
const officeFilter = ref(props.filters.office_id || '')
const statusFilter = ref(props.filters.status || '')
const workTypeFilter = ref(props.filters.work_type || '')

const applyFilters = () => {
    router.get('/attendances', {
        date: dateFilter.value || undefined,
        office_id: officeFilter.value || undefined,
        status: statusFilter.value || undefined,
        work_type: workTypeFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true })
}

const clearFilters = () => {
    dateFilter.value = formatYMD(new Date())
    officeFilter.value = ''
    statusFilter.value = ''
    workTypeFilter.value = ''
    router.get('/attendances', { date: dateFilter.value }, { preserveState: true })
}

// Open detail
const openDetail = async (att) => {
    selectedAttendance.value = att
    showDetail.value = true
    detailLoading.value = true
    try {
        const resp = await fetch(`/attendances/${att.id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        if (resp.ok) {
            const data = await resp.json()
            selectedAttendance.value = data.data || data
        }
    } catch (e) { /* keep existing data */ }
    detailLoading.value = false
}

// Reset attendance
const openReset = (att) => {
    selectedAttendance.value = att
    showReset.value = true
}

const handleReset = (form) => {
    resetProcessing.value = true
    form.post(`/attendances/${selectedAttendance.value.id}/reset`, {
        preserveScroll: true,
        onSuccess: () => { showReset.value = false },
        onFinish: () => { resetProcessing.value = false },
    })
}

const attList = computed(() => props.attendances?.data || [])
const pagination = computed(() => ({
    links: props.attendances?.links || [],
    from: props.attendances?.from || 0,
    to: props.attendances?.to || 0,
    total: props.attendances?.total || 0,
}))

const formatTime = (t) => t || '-'

const summaryCards = computed(() => [
    { label: 'Total',  value: props.summary?.total || 0, icon: 'groups',         color: 'bg-slate-100 text-slate-600' },
    { label: 'Hadir',  value: props.summary?.hadir || 0, icon: 'check_circle',   color: 'bg-emerald-50 text-emerald-600' },
    // { label: 'WFO',    value: props.summary?.wfo   || 0, icon: 'business',       color: 'bg-blue-50 text-blue-600' },
    // { label: 'WFA',    value: props.summary?.wfa   || 0, icon: 'home_work',      color: 'bg-violet-50 text-violet-600' },
    { label: 'Izin',   value: props.summary?.izin  || 0, icon: 'event_busy',     color: 'bg-sky-50 text-sky-600' },
    { label: 'Sakit',  value: props.summary?.sakit || 0, icon: 'local_hospital', color: 'bg-orange-50 text-orange-600' },
    { label: 'Alpha',  value: props.summary?.alpha || 0, icon: 'cancel',         color: 'bg-red-50 text-red-600' },
])
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Absensi</h1>
                <p class="text-sm text-slate-400 mt-0.5">Data kehadiran anggota</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 lg-grid-cols-5 gap-4 mb-5">
            <div v-for="s in summaryCards" :key="s.label" class="bg-white rounded-2xl border border-slate-200 p-3.5">
                <div class="flex items-center gap-2.5">
                    <div :class="s.color" class="w-9 h-9 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-rounded text-[18px]">{{ s.icon }}</span>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-800 leading-none">{{ s.value }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-semibold uppercase">{{ s.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <flat-pickr
                    v-model="dateFilter"
                    :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true }"
                    @update:model-value="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all w-40"
                />
                <select
                    v-model="officeFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white min-w-[140px]"
                >
                    <option value="">Semua Kantor</option>
                    <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
                </select>
                <select
                    v-model="statusFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white min-w-[120px]"
                >
                    <option value="">Semua Status</option>
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpha">Alpha</option>
                </select>
                <select
                    v-model="workTypeFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white min-w-[120px]"
                >
                    <option value="">Semua Kehadiran</option>
                    <option value="wfo">WFO</option>
                    <option value="wfa">WFA</option>
                </select>
                <button
                    @click="clearFilters"
                    class="px-3.5 py-2.5 text-sm text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-xl transition-colors border border-slate-200"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Table (Desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hidden lg:block">
            <table v-if="attList.length" class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Kantor</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Check In</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Check Out</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Kehadiran</th>
                        <th class="text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in attList" :key="a.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-xs font-bold">
                                    {{ (a.member?.nama_lengkap || '?')[0].toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">{{ a.member?.nama_lengkap || '-' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ a.member?.no_hp || '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ a.member?.office?.name || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-slate-600">{{ formatTime(a.check_in_time) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-slate-600">{{ formatTime(a.check_out_time) }}</td>
                        <td class="px-4 py-3 text-center"><Badge :status="a.status" type="attendance" /></td>
                        <td class="px-4 py-3 text-center">
                            <span v-if="a.work_type"
                                :class="a.work_type === 'wfo'
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'bg-violet-50 text-violet-600'"
                                class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full uppercase"
                            >
                                <span class="material-symbols-rounded text-[12px]">{{ a.work_type === 'wfo' ? 'business' : 'home_work' }}</span>
                                {{ a.work_type.toUpperCase() }}
                            </span>
                            <span v-else class="text-slate-300 text-xs">-</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Tombol lihat detail absensi -->
                                <button
                                    @click="openDetail(a)"
                                    class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors"
                                    title="Lihat detail absensi"
                                >
                                    <span class="material-symbols-rounded text-[18px]">visibility</span>
                                    <span class="text-[9px] font-semibold uppercase tracking-wide">Detail</span>
                                </button>
                                <!-- Tombol reset / koreksi data absensi
                                <button
                                    @click="openReset(a)"
                                    class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-lg hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-colors"
                                    title="Reset / koreksi data absensi ini"
                                >
                                    <span class="material-symbols-rounded text-[18px]">restart_alt</span>
                                    <span class="text-[9px] font-semibold uppercase tracking-wide">Reset</span>
                                </button> -->
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <EmptyState v-else icon="fact_check" title="Tidak ada data absensi" description="Belum ada data kehadiran untuk filter yang dipilih." />
            <div v-if="attList.length" class="px-4 pb-4">
                <Pagination v-bind="pagination" />
            </div>
        </div>

        <!-- Cards (Mobile) -->
        <div class="lg:hidden space-y-3">
            <div
                v-for="a in attList"
                :key="'card-' + a.id"
                class="bg-white rounded-2xl border border-slate-200 p-4"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-sm font-bold">
                            {{ (a.member?.nama_lengkap || '?')[0].toUpperCase() }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-slate-800">{{ a.member?.nama_lengkap || '-' }}</p>
                            <p class="text-xs text-slate-400">{{ a.member?.office?.name || '-' }}</p>
                        </div>
                    </div>
                    <Badge :status="a.status" type="attendance" />
                        <span v-if="a.work_type"
                            :class="a.work_type === 'wfo'
                                ? 'bg-blue-50 text-blue-600'
                                : 'bg-violet-50 text-violet-600'"
                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full uppercase"
                        >
                            <span class="material-symbols-rounded text-[12px]">{{ a.work_type === 'wfo' ? 'business' : 'home_work' }}</span>
                            {{ a.work_type.toUpperCase() }}
                        </span>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="bg-slate-50 rounded-xl p-2.5 text-center">
                        <p class="text-[10px] text-slate-400 mb-0.5">Check In</p>
                        <p class="text-sm font-semibold text-slate-700">{{ formatTime(a.check_in_time) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-2.5 text-center">
                        <p class="text-[10px] text-slate-400 mb-0.5">Check Out</p>
                        <p class="text-sm font-semibold text-slate-700">{{ formatTime(a.check_out_time) }}</p>
                    </div>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <button @click="openDetail(a)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">visibility</span>
                        Detail
                    </button>
                    <button @click="openReset(a)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">restart_alt</span>
                        Reset
                    </button>
                </div>
            </div>
            <EmptyState v-if="!attList.length" icon="fact_check" title="Tidak ada data absensi" description="Belum ada data kehadiran untuk filter yang dipilih." />
            <div v-if="attList.length">
                <Pagination v-bind="pagination" />
            </div>
        </div>

        <!-- Detail Panel -->
        <FormPanel :show="showDetail" title="Detail Absensi" @close="showDetail = false">
            <AttendanceDetail :attendance="selectedAttendance" :loading="detailLoading" />
        </FormPanel>

        <!-- Reset Panel -->
        <FormPanel :show="showReset" title="Reset Absensi" @close="showReset = false">
            <ResetForm
                :attendance-id="selectedAttendance?.id"
                :processing="resetProcessing"
                @submit="handleReset"
            />
        </FormPanel>
    </div>
</template>
