<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Badge from '@/Components/Badge.vue'
import EmptyState from '@/Components/EmptyState.vue'

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    report: { type: Object, default: () => ({}) },
    offices: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const formatYMD = (d) => {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

const startDate = ref(props.filters.start_date || formatYMD(new Date(new Date().getFullYear(), new Date().getMonth(), 1)))
const endDate = ref(props.filters.end_date || formatYMD(new Date()))
const officeFilter = ref(props.filters.office_id || '')
const memberFilter = ref(props.filters.member_id || '')
const statusFilter = ref(props.filters.status || '')

// Searchable member logic
const memberSearch = ref('')
const showMemberDropdown = ref(false)
const filteredMembers = computed(() => {
    if (!memberSearch.value) return props.members
    const s = memberSearch.value.toLowerCase()
    return props.members.filter(m => 
        m.nama_lengkap.toLowerCase().includes(s)
    )
})
const selectedMemberName = computed(() => {
    if (!memberFilter.value) return ''
    const m = props.members.find(m => m.id == memberFilter.value)
    return m ? m.nama_lengkap : ''
})
const selectMember = (m) => {
    memberFilter.value = m.id
    memberSearch.value = ''
    showMemberDropdown.value = false
    applyFilters()
}

const applyFilters = () => {
    router.get('/attendances/report', {
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
        office_id: officeFilter.value || undefined,
        member_id: memberFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true })
}

const exportCsv = () => {
    const params = new URLSearchParams()
    if (startDate.value) params.set('start_date', startDate.value)
    if (endDate.value) params.set('end_date', endDate.value)
    if (officeFilter.value) params.set('office_id', officeFilter.value)
    if (memberFilter.value) params.set('member_id', memberFilter.value)
    if (statusFilter.value) params.set('status', statusFilter.value)
    window.open(`/attendances/export?${params.toString()}`, '_blank')
}

const exportPdf = () => {
    const params = new URLSearchParams()
    if (startDate.value) params.set('start_date', startDate.value)
    if (endDate.value) params.set('end_date', endDate.value)
    if (officeFilter.value) params.set('office_id', officeFilter.value)
    if (memberFilter.value) params.set('member_id', memberFilter.value)
    if (statusFilter.value) params.set('status', statusFilter.value)
    window.open(`/attendances/export/pdf?${params.toString()}`, '_blank')
}

const stats = computed(() => props.report?.statistics || {})
const attendances = computed(() => props.report?.attendances || [])

const statsCards = computed(() => [
    { label: 'Total Hari', value: stats.value.total_days || 0, icon: 'calendar_month', color: 'bg-slate-100 text-slate-600' },
    { label: 'Hadir', value: stats.value.hadir || 0, icon: 'check_circle', color: 'bg-emerald-50 text-emerald-600' },
    { label: 'Izin', value: stats.value.izin || 0, icon: 'event_busy', color: 'bg-blue-50 text-blue-600' },
    { label: 'Sakit', value: stats.value.sakit || 0, icon: 'local_hospital', color: 'bg-orange-50 text-orange-600' },
    { label: 'Alpha', value: stats.value.alpha || 0, icon: 'cancel', color: 'bg-red-50 text-red-600' },
])

const formatDate = (d) => {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
const formatTime = (t) => t || '-'
</script>

<template>
    <div @click="showMemberDropdown = false">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="/attendances" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-rounded text-[20px]">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-slate-800">Laporan Absensi</h1>
                </div>
                <p class="text-sm text-slate-400">Rekap kehadiran anggota berdasarkan periode</p>
            </div>
            <div class="flex gap-2">
                <button
                    @click="exportCsv"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
                >
                    <span class="material-symbols-rounded text-[18px]">table_chart</span>
                    Export Excel
                </button>
                <button
                    @click="exportPdf"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
                >
                    <span class="material-symbols-rounded text-[18px]">print</span>
                    Cetak / PDF
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 print:hidden">
            <div class="flex flex-col gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Mulai</label>
                        <flat-pickr
                            v-model="startDate"
                            :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true }"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Akhir</label>
                        <flat-pickr
                            v-model="endDate"
                            :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true }"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                        />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select
                        v-model="officeFilter"
                        @change="applyFilters"
                        class="w-full sm:w-auto px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all sm:min-w-[140px]"
                    >
                        <option value="">Semua Kantor</option>
                        <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>

                    <select
                        v-model="statusFilter"
                        @change="applyFilters"
                        class="w-full sm:w-auto px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all sm:min-w-[140px]"
                    >
                        <option value="">Semua Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="telat">Terlambat</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Alpha</option>
                    </select>
                    

                    <!-- Searchable Member Select -->
                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            v-model="memberSearch"
                            @click.stop="showMemberDropdown = true"
                            placeholder="Cari anggota..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none transition-all bg-white"
                        />
                        <div v-if="selectedMemberName && !memberSearch" class="absolute right-10 top-1/2 -translate-y-1/2 text-[10px] font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-lg truncate max-w-[120px]">
                            {{ selectedMemberName }}
                        </div>
                        <span class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        
                        <div v-if="showMemberDropdown" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                            <div 
                                class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer text-sm text-slate-600 border-b border-slate-50"
                                @click.stop="memberFilter = ''; showMemberDropdown = false; applyFilters()"
                            >
                                Semua Anggota
                            </div>
                            <div 
                                v-for="m in filteredMembers" 
                                :key="m.id"
                                @click.stop="selectMember(m)"
                                class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-50 last:border-0 text-sm"
                            >
                                {{ m.nama_lengkap }}
                            </div>
                        </div>
                    </div>

                    <button
                        @click="applyFilters"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm ml-auto"
                    >
                        Tampilkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4 print:hidden">
            <div v-for="s in statsCards" :key="s.label" class="bg-white rounded-2xl border border-slate-200 p-3.5">
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

        <!-- Print Formal Header (Hidden on Screen) -->
        <div class="hidden print:block mb-6 text-center text-slate-800 font-serif">
            <h1 class="text-xl font-black uppercase">PT. Global Intermedia Lintas Batas</h1>
            <h2 class="text-lg font-bold uppercase mt-1">Laporan Rekapitulasi Absensi Peserta Magang</h2>
            <div class="mt-4 text-sm font-semibold border-b-2 border-slate-800 pb-2 inline-block">
                Periode: {{ formatDate(startDate) }} s/d {{ formatDate(endDate) }}
            </div>
            <div class="mt-1 text-[10px] text-slate-500">
                Dicetak pada: {{ new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute:'2-digit'}) }} WIB
            </div>
        </div>

        <!-- Table (Desktop & Print) -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hidden lg:block print:block print:border-none print:shadow-none">
            <table v-if="attendances.length" class="w-full text-left border-collapse print:text-[10px]">
                <thead>
                    <tr class="border-b border-slate-100 print:border-slate-800 print:border-b-2">
                        <th class="text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1 w-10">No</th>
                        <th class="text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1 w-24">Tanggal</th>
                        <th class="text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1">Nama</th>
                        <th class="text-center text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1">Check In</th>
                        <th class="text-center text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1">Check Out</th>
                        <th class="text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1">Kantor</th>
                        <th class="text-center text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1">Tipe</th>
                        <th class="text-center text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1">Telat</th>
                        <th class="text-[11px] print:text-[10px] font-bold text-slate-400 print:text-slate-800 uppercase tracking-wider px-2.5 py-2 print:p-1">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(a, i) in attendances" :key="a.id || i" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors print:border-slate-300">
                        <td class="px-2.5 py-1.5 print:p-1 text-sm print:text-[10px] text-slate-500">{{ i + 1 }}</td>
                        <td class="px-2.5 py-1.5 print:p-1 text-sm print:text-[10px] font-medium text-slate-700 whitespace-nowrap">{{ formatDate(a.tanggal) }}</td>
                        <td class="px-2.5 py-1.5 print:p-1 align-top">
                            <p class="text-sm print:text-[10px] font-bold text-slate-800">{{ a.member?.nama_lengkap || '-' }}</p>
                            <p class="text-[11px] print:text-[9px] text-slate-500 truncate max-w-[200px] print:max-w-none">{{ a.member?.asal_sekolah || '-' }} • {{ a.member?.jurusan || '-' }}</p>
                        </td>
                        <td class="px-2.5 py-1.5 print:p-1 text-center text-sm print:text-[10px] font-medium text-slate-700 align-top pt-2">{{ formatTime(a.check_in_time) }}</td>
                        <td class="px-2.5 py-1.5 print:p-1 text-center text-sm print:text-[10px] font-medium text-slate-700 align-top pt-2">{{ formatTime(a.check_out_time) }}</td>
                        <td class="px-2.5 py-1.5 print:p-1 text-sm print:text-[10px] text-slate-600 align-top pt-2">{{ a.member?.office?.name || '-' }}</td>
                        <td class="px-2.5 py-1.5 print:p-1 text-center text-sm print:text-[10px] font-semibold text-slate-600 align-top pt-2 uppercase">{{ a.work_type || '-' }}</td>
                        <td class="px-2.5 py-1.5 print:p-1 text-center text-sm print:text-[10px] font-semibold text-slate-600 align-top pt-2">{{ a.is_late ? 'Ya' : '-' }}</td>
                        <td class="px-2.5 py-1.5 print:p-1 align-top pt-2">
                            <div class="flex items-center gap-1.5 relative group w-max">
                                <span class="font-bold uppercase text-[11px] print:text-[10px] print:!text-slate-800 w-12 inline-block" :class="{
                                    'text-emerald-600': a.status === 'hadir',
                                    'text-red-500': a.status === 'alpha',
                                    'text-blue-500': a.status === 'izin',
                                    'text-orange-500': a.status === 'sakit'
                                }">{{ a.status }}</span>
                                
                                <!-- Info Icon (tanda seru) -->
                                <span v-if="a.is_late || a.status === 'sakit' || a.status === 'izin' || a.status === 'alpha'" 
                                      class="material-symbols-rounded text-[14px] text-amber-500 hover:text-amber-600 cursor-help transition-colors print:!hidden hidden lg:block">info</span>

                                <!-- Tooltip -->
                                <div v-if="a.is_late || a.status === 'sakit' || a.status === 'izin' || a.status === 'alpha'" 
                                     class="absolute right-full mr-2 top-1/2 -translate-y-1/2 hidden group-hover:block z-50 w-52 p-3 bg-slate-800 text-white text-[10px] rounded-xl shadow-xl print:!hidden cursor-default">
                                    <div v-if="a.permissions && a.permissions.length">
                                        <span class="text-slate-400 font-medium block mb-1">Alasan:</span>
                                        <p class="text-[10px] text-slate-200 leading-snug">{{ a.permissions[0].keterangan || a.permissions[0].reason || '-' }}</p>
                                    </div>
                                    <div v-else-if="a.status !== 'hadir'">
                                        <span class="text-slate-400 font-medium block mb-1">Alasan:</span>
                                        <p class="text-[10px] text-slate-200 leading-snug text-red-300 italic">Tanpa Keterangan</p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <EmptyState v-else icon="assessment" title="Tidak ada data" description="Pilih filter dan klik Tampilkan untuk melihat laporan." />
        </div>

        <!-- Cards (Mobile, hidden on print) -->
        <div class="lg:hidden print:hidden space-y-3">
            <div
                v-for="(a, i) in attendances"
                :key="'rcard-' + (a.id || i)"
                class="bg-white rounded-2xl border border-slate-200 p-4"
            >
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="font-semibold text-sm text-slate-800">{{ a.member?.nama_lengkap || '-' }}</p>
                        <p class="text-[11px] text-slate-500">{{ a.member?.asal_sekolah || '-' }} • {{ a.member?.jurusan || '-' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ a.member?.office?.name || '-' }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1.5 mt-0.5">
                        <span class="font-bold uppercase text-[10px]" :class="{
                            'text-emerald-600': a.status === 'hadir',
                            'text-red-500': a.status === 'alpha',
                            'text-blue-500': a.status === 'izin',
                            'text-orange-500': a.status === 'sakit'
                        }">{{ a.status }}</span>
                        <span v-if="a.is_late" class="px-1.5 py-0.5 rounded bg-red-50 text-red-600 text-[9px] font-bold uppercase">Terlambat</span>
                    </div>
                </div>
                <div class="space-y-1.5 text-xs text-slate-500 mt-3">
                    <div class="flex justify-between items-center bg-slate-50 px-3 py-1.5 rounded-lg">
                        <span class="font-medium">Tanggal</span>
                        <span class="font-bold text-slate-700">{{ formatDate(a.tanggal) }}</span>
                    </div>
                    <div class="flex justify-between items-center px-3 py-1">
                        <span>Check In</span>
                        <span class="font-medium text-slate-700">{{ formatTime(a.check_in_time) }}</span>
                    </div>
                    <div class="flex justify-between items-center px-3 py-1">
                        <span>Check Out</span>
                        <span class="font-medium text-slate-700">{{ formatTime(a.check_out_time) }}</span>
                    </div>
                    
                    <div v-if="a.permissions && a.permissions.length" class="mt-2 mx-3 pt-2 border-t border-slate-100 flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Alasan {{ a.status }}</span>
                        <p class="text-xs text-slate-700 leading-snug">{{ a.permissions[0].keterangan || a.permissions[0].reason || '-' }}</p>
                    </div>
                    <div v-else-if="a.status !== 'hadir'" class="mt-2 mx-3 pt-2 border-t border-slate-100 flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Keterangan</span>
                        <p class="text-xs text-red-500 italic">Tanpa Keterangan</p>
                    </div>
                </div>
            </div>
            <EmptyState v-if="!attendances.length" icon="assessment" title="Tidak ada data" description="Pilih filter dan klik Tampilkan untuk melihat laporan." />
        </div>
    </div>
</template>
