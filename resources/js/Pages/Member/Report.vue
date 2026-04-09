<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

defineOptions({ layout: MemberLayout })

const props = defineProps({
    member: Object,
    period: Object,
    stats: Object,
    attendances: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    error: String,
})

const startDate = ref(props.filters?.start_date || '')
const endDate = ref(props.filters?.end_date || '')

const applyFilter = () => {
    router.get('/member/report', {
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, { preserveState: true })
}

const printReport = () => {
    window.print()
}

const formatDate = (d) => {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}

const formatTime = (t) => {
    if (!t) return '-'
    // Handle time-only string "HH:mm:ss"
    if (typeof t === 'string' && /^\d{2}:\d{2}(:\d{2})?$/.test(t)) {
        return t.slice(0, 5)
    }
    const d = new Date(t)
    if (isNaN(d.getTime())) return t
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
    <Head title="Laporan Saya" />

    <div>
        <!-- Screen Header (hidden on print) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Laporan Kehadiran</h1>
                <p class="text-sm text-slate-400 mt-0.5">Cetak laporan absensi Anda</p>
            </div>
            <button @click="printReport" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors">
                <span class="material-symbols-rounded text-[18px]">print</span>
                Cetak Laporan
            </button>
        </div>

        <!-- Filter (hidden on print) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 print:hidden">
            <div class="flex flex-col sm:flex-row gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Mulai</label>
                    <VueDatePicker
                        v-model="startDate"
                        :enable-time-picker="false"
                        model-type="yyyy-MM-dd"
                        format="dd/MM/yyyy"
                        auto-apply
                        placeholder="Mulai"
                        input-class-name="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none transition-all"
                    />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Akhir</label>
                    <VueDatePicker
                        v-model="endDate"
                        :enable-time-picker="false"
                        model-type="yyyy-MM-dd"
                        format="dd/MM/yyyy"
                        auto-apply
                        placeholder="Akhir"
                        input-class-name="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none transition-all"
                    />
                </div>
                <button @click="applyFilter" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                    Tampilkan
                </button>
            </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 print:hidden">
            <span class="material-symbols-rounded text-red-500 text-[20px]">error</span>
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <!-- Report Content (visible on print) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 print:border-0 print:shadow-none print:p-0">
            <!-- Print Header -->
            <div class="hidden print:block text-center mb-6">
                <h1 class="text-lg font-bold">LAPORAN KEHADIRAN</h1>
                <p class="text-sm text-gray-600">Sistem Presensi - Global Intermedia</p>
                <hr class="my-3" />
            </div>

            <!-- Member Info -->
            <div class="mb-6" v-if="member">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 print:text-gray-600">Data Member</h3>
                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div><span class="text-slate-500">Nama:</span> <strong>{{ member.nama_lengkap }}</strong></div>
                    <div><span class="text-slate-500">Kantor:</span> <strong>{{ member.office?.name || '-' }}</strong></div>
                    <div><span class="text-slate-500">Periode:</span> <strong>{{ period?.start_date }} — {{ period?.end_date }}</strong></div>
                </div>
            </div>

            <!-- Stats Summary -->
            <div v-if="stats" class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
                <div class="bg-slate-50 rounded-xl p-3 text-center print:border print:bg-white">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Hari Kerja</p>
                    <p class="text-lg font-bold text-slate-800">{{ stats.working_days }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-3 text-center print:border print:bg-white">
                    <p class="text-[10px] font-bold text-green-600 uppercase">Hadir</p>
                    <p class="text-lg font-bold text-green-700">{{ stats.total_hadir }}</p>
                </div>
                <div class="bg-red-50 rounded-xl p-3 text-center print:border print:bg-white">
                    <p class="text-[10px] font-bold text-red-600 uppercase">Absen</p>
                    <p class="text-lg font-bold text-red-700">{{ stats.total_absen }}</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-3 text-center print:border print:bg-white">
                    <p class="text-[10px] font-bold text-amber-600 uppercase">Terlambat</p>
                    <p class="text-lg font-bold text-amber-700">{{ stats.total_terlambat }}</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-3 text-center print:border print:bg-white">
                    <p class="text-[10px] font-bold text-blue-600 uppercase">Persentase</p>
                    <p class="text-lg font-bold text-blue-700">{{ stats.persentase }}%</p>
                </div>
            </div>

            <!-- Attendance Table -->
            <table v-if="attendances.length" class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-2 px-2 text-[11px] font-bold text-slate-400 uppercase">No</th>
                        <th class="text-left py-2 px-2 text-[11px] font-bold text-slate-400 uppercase">Tanggal</th>
                        <th class="text-center py-2 px-2 text-[11px] font-bold text-slate-400 uppercase">Check In</th>
                        <th class="text-center py-2 px-2 text-[11px] font-bold text-slate-400 uppercase">Check Out</th>
                        <th class="text-center py-2 px-2 text-[11px] font-bold text-slate-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(att, i) in attendances" :key="att.id" class="border-b border-slate-50">
                        <td class="py-2 px-2 text-slate-600">{{ i + 1 }}</td>
                        <td class="py-2 px-2 text-slate-700 font-medium">{{ formatDate(att.tanggal) }}</td>
                        <td class="py-2 px-2 text-center text-slate-700">{{ formatTime(att.check_in_time) }}</td>
                        <td class="py-2 px-2 text-center text-slate-700">{{ formatTime(att.check_out_time) }}</td>
                        <td class="py-2 px-2 text-center">
                            <span v-if="att.is_late" class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded print:bg-white">Terlambat</span>
                            <span v-else class="text-[10px] font-semibold text-green-600 bg-green-50 px-1.5 py-0.5 rounded print:bg-white">Hadir</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="py-8 text-center">
                <span class="material-symbols-rounded text-slate-300 text-[32px]">event_busy</span>
                <p class="text-sm text-slate-400 mt-2">Tidak ada data absensi dalam periode ini.</p>
            </div>
        </div>
    </div>
</template>
