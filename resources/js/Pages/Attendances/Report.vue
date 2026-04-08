<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Badge from '@/Components/Badge.vue'
import EmptyState from '@/Components/EmptyState.vue'

import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    report: { type: Object, default: () => ({}) },
    offices: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const startDate = ref(props.filters.start_date || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10))
const endDate = ref(props.filters.end_date || new Date().toISOString().slice(0, 10))
const officeFilter = ref(props.filters.office_id || '')
const memberFilter = ref(props.filters.member_id || '')

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
    }, { preserveState: true, preserveScroll: true })
}

const exportCsv = () => {
    const params = new URLSearchParams()
    if (startDate.value) params.set('start_date', startDate.value)
    if (endDate.value) params.set('end_date', endDate.value)
    if (officeFilter.value) params.set('office_id', officeFilter.value)
    if (memberFilter.value) params.set('member_id', memberFilter.value)
    window.open(`/attendances/export?${params.toString()}`, '_blank')
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="/attendances" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-rounded text-[20px]">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-slate-800">Laporan Absensi</h1>
                </div>
                <p class="text-sm text-slate-400">Rekap kehadiran anggota berdasarkan periode</p>
            </div>
            <button
                @click="exportCsv"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
            >
                <span class="material-symbols-rounded text-[18px]">download</span>
                Export CSV
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Mulai</label>
                        <VueDatePicker
                            v-model="startDate"
                            :enable-time-picker="false"
                            model-type="yyyy-MM-dd"
                            format="dd/MM/yyyy"
                            auto-apply
                            input-class-name="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Akhir</label>
                        <VueDatePicker
                            v-model="endDate"
                            :enable-time-picker="false"
                            model-type="yyyy-MM-dd"
                            format="dd/MM/yyyy"
                            auto-apply
                            input-class-name="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                        />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select
                        v-model="officeFilter"
                        @change="applyFilters"
                        class="w-full sm:w-auto px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white sm:min-w-[140px]"
                    >
                        <option value="">Semua Kantor</option>
                        <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>
                    
                    <!-- Searchable Member Select -->
                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            v-model="memberSearch"
                            @click.stop="showMemberDropdown = true"
                            placeholder="Cari anggota..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none transition-all bg-white"
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
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
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

        <!-- Table (Desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hidden lg:block">
            <table v-if="attendances.length" class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">No</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Kantor</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Check In</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Check Out</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(a, i) in attendances" :key="a.id || i" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-slate-500">{{ i + 1 }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(a.tanggal) }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-700">{{ a.member?.nama_lengkap || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ a.member?.office?.name || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-slate-600">{{ formatTime(a.check_in_time) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-slate-600">{{ formatTime(a.check_out_time) }}</td>
                        <td class="px-4 py-3 text-center"><Badge :status="a.status" type="attendance" /></td>
                    </tr>
                </tbody>
            </table>
            <EmptyState v-else icon="assessment" title="Tidak ada data" description="Pilih filter dan klik Tampilkan untuk melihat laporan." />
        </div>

        <!-- Cards (Mobile) -->
        <div class="lg:hidden space-y-3">
            <div
                v-for="(a, i) in attendances"
                :key="'rcard-' + (a.id || i)"
                class="bg-white rounded-2xl border border-slate-200 p-4"
            >
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="font-semibold text-sm text-slate-800">{{ a.member?.nama_lengkap || '-' }}</p>
                        <p class="text-xs text-slate-400">{{ a.member?.office?.name || '-' }}</p>
                    </div>
                    <Badge :status="a.status" type="attendance" />
                </div>
                <div class="space-y-1.5 text-xs text-slate-500">
                    <div class="flex justify-between">
                        <span>Tanggal</span>
                        <span class="font-medium text-slate-700">{{ formatDate(a.tanggal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Check In</span>
                        <span class="font-medium text-slate-700">{{ formatTime(a.check_in_time) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Check Out</span>
                        <span class="font-medium text-slate-700">{{ formatTime(a.check_out_time) }}</span>
                    </div>
                </div>
            </div>
            <EmptyState v-if="!attendances.length" icon="assessment" title="Tidak ada data" description="Pilih filter dan klik Tampilkan untuk melihat laporan." />
        </div>
    </div>
</template>
