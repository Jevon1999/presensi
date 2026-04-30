<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import FormPanel from '@/Components/FormPanel.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

defineOptions({ layout: MemberLayout })

const props = defineProps({
    progresses: Object,
    today_attendance: Object,
    member: Object,
    error: String,
})

const progressList = computed(() => props.progresses?.data || [])

// ─── Lock Logic ───
const canManageProgress = computed(() => {
    const att = props.today_attendance
    return att && att.check_in_time && !att.check_out_time
})

const lockReason = computed(() => {
    const att = props.today_attendance
    if (!att || !att.check_in_time) return 'Kamu belum check-in hari ini.'
    if (att.check_out_time) return 'Kamu sudah checkout hari ini. Progress terkunci.'
    return ''
})

const attendanceStatus = computed(() => {
    const att = props.today_attendance
    if (!att || !att.check_in_time) return { label: 'Belum Check-in', color: 'slate', icon: 'remove_circle_outline' }
    if (att.check_out_time) return { label: 'Sudah Checkout', color: 'green', icon: 'check_circle' }
    return { label: 'Sedang Bekerja', color: 'blue', icon: 'work' }
})

// Check if a progress entry is for today
const isToday = (dateStr) => {
    if (!dateStr) return false
    const d = new Date(typeof dateStr === 'string' && dateStr.length === 10 ? dateStr + 'T00:00:00' : dateStr)
    const now = new Date()
    return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate()
}

// ─── Form State ───
const showForm = ref(false)
const editingProgress = ref(null)
const formProcessing = ref(false)

const tipeOptions = [
    { value: 'hadir', label: 'Hadir', desc: 'Laporan kegiatan kerja', icon: 'work', color: 'blue' },
    { value: 'sakit', label: 'Sakit', desc: 'Tidak masuk karena sakit', icon: 'medical_services', color: 'red' },
    { value: 'izin', label: 'Izin', desc: 'Izin tidak hadir', icon: 'assignment_late', color: 'amber' },
]

const form = useForm({
    tipe: 'hadir',
    description: '',
})

const openCreate = () => {
    editingProgress.value = null
    form.tipe = 'hadir'
    form.description = ''
    form.clearErrors()
    showForm.value = true
}

const openEdit = (p) => {
    editingProgress.value = { ...p }
    form.tipe = p.tipe || 'hadir'
    form.description = p.description || ''
    form.clearErrors()
    showForm.value = true
}

const submitForm = () => {
    formProcessing.value = true
    if (editingProgress.value) {
        form.put(`/member/progress/${editingProgress.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; editingProgress.value = null },
            onFinish: () => { formProcessing.value = false },
        })
    } else {
        form.post('/member/progress', {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset() },
            onFinish: () => { formProcessing.value = false },
        })
    }
}

// ─── Delete ───
const showDeleteConfirm = ref(false)
const deletingId = ref(null)
const deleteProcessing = ref(false)

const confirmDelete = (id) => {
    deletingId.value = id
    showDeleteConfirm.value = true
}

const doDelete = () => {
    deleteProcessing.value = true
    router.delete(`/member/progress/${deletingId.value}`, {
        preserveScroll: true,
        onSuccess: () => { showDeleteConfirm.value = false },
        onFinish: () => { deleteProcessing.value = false },
    })
}

// ─── Helpers ───
const formatDate = (d) => {
    if (!d) return '-'
    const parsed = new Date(typeof d === 'string' && d.length === 10 ? d + 'T00:00:00' : d)
    if (isNaN(parsed.getTime())) return d
    return parsed.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}

const formatTime = (t) => {
    if (!t) return '--:--'
    if (typeof t === 'string' && /^\d{2}:\d{2}(:\d{2})?$/.test(t)) return t.slice(0, 5)
    const d = new Date(t)
    if (isNaN(d.getTime())) return t
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const truncate = (str, len = 120) => {
    if (!str) return '-'
    return str.length > len ? str.slice(0, len) + '...' : str
}

const tipeConfig = (tipe) => {
    const map = {
        hadir: { label: 'Hadir', icon: 'work', bgClass: 'bg-blue-50 text-blue-600 border-blue-100' },
        sakit: { label: 'Sakit', icon: 'medical_services', bgClass: 'bg-red-50 text-red-600 border-red-100' },
        izin:  { label: 'Izin', icon: 'assignment_late', bgClass: 'bg-amber-50 text-amber-600 border-amber-100' },
    }
    return map[tipe] || map.hadir
}

const isRefreshing = ref(false)
const refreshData = () => {
    isRefreshing.value = true
    router.reload({ onFinish: () => { isRefreshing.value = false } })
}
</script>

<template>
    <Head title="Progress Saya" />

    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Progress Saya</h1>
                <p class="text-sm text-slate-400 mt-0.5">Catatan kegiatan harian</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="refreshData" :disabled="isRefreshing"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-600 rounded-xl transition-colors">
                    <span class="material-symbols-rounded text-[16px]" :class="{ 'animate-spin': isRefreshing }">refresh</span>
                </button>
                <button
                    v-if="canManageProgress"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
                >
                    <span class="material-symbols-rounded text-[18px]">add_circle</span>
                    Tambah Progress
                </button>
            </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
            <span class="material-symbols-rounded text-red-500 text-[20px]">error</span>
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <!-- Status Banner -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                        :class="{
                            'bg-blue-50': attendanceStatus.color === 'blue',
                            'bg-green-50': attendanceStatus.color === 'green',
                            'bg-slate-100': attendanceStatus.color === 'slate',
                        }">
                        <span class="material-symbols-rounded text-[20px]"
                            :class="{
                                'text-blue-500': attendanceStatus.color === 'blue',
                                'text-green-500': attendanceStatus.color === 'green',
                                'text-slate-400': attendanceStatus.color === 'slate',
                            }">{{ attendanceStatus.icon }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ attendanceStatus.label }}</p>
                        <p v-if="today_attendance?.check_in_time" class="text-xs text-slate-400">
                            Check-in: {{ formatTime(today_attendance.check_in_time) }}
                            <template v-if="today_attendance.check_out_time">
                                · Checkout: {{ formatTime(today_attendance.check_out_time) }}
                            </template>
                        </p>
                    </div>
                </div>
                <!-- Lock indicator -->
                <div v-if="!canManageProgress" class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-500">
                    <span class="material-symbols-rounded text-[14px]">lock</span>
                    Terkunci
                </div>
                <div v-else class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-600">
                    <span class="material-symbols-rounded text-[14px]">lock_open</span>
                    Bisa Input
                </div>
            </div>
            <!-- Lock reason message -->
            <div v-if="!canManageProgress && lockReason" class="mt-3 flex items-start gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                <span class="material-symbols-rounded text-[15px] shrink-0 mt-0.5">info</span>
                <span>{{ lockReason }}</span>
            </div>
        </div>

        <!-- Progress List -->
        <div class="space-y-3">
            <div v-for="p in progressList" :key="p.id" class="bg-white rounded-2xl border border-slate-200 p-4 transition-all hover:border-slate-300">
                <!-- Header -->
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <p class="text-xs font-semibold text-slate-400">{{ formatDate(p.tanggal) }}</p>
                        <span v-if="isToday(p.tanggal)" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-600 uppercase">Hari Ini</span>
                    </div>
                    <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold border', tipeConfig(p.tipe).bgClass]">
                        <span class="material-symbols-rounded text-[12px]">{{ tipeConfig(p.tipe).icon }}</span>
                        {{ tipeConfig(p.tipe).label }}
                    </span>
                </div>

                <!-- Description -->
                <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed mb-3">{{ p.description || '-' }}</p>

                <!-- Actions (only for today + unlocked) -->
                <div v-if="isToday(p.tanggal) && canManageProgress" class="flex gap-2 pt-2 border-t border-slate-100">
                    <button @click="openEdit(p)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">edit</span>
                        Edit
                    </button>
                    <button @click="confirmDelete(p.id)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">delete</span>
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!progressList.length" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <span class="material-symbols-rounded text-slate-300 text-[40px] mb-3">description</span>
            <p class="text-sm font-semibold text-slate-500">Belum ada progress</p>
            <p class="text-xs text-slate-400 mt-1">
                <template v-if="canManageProgress">
                    Klik tombol "Tambah Progress" untuk mencatat kegiatan hari ini.
                </template>
                <template v-else>
                    {{ lockReason || 'Progress harian Anda akan muncul di sini.' }}
                </template>
            </p>
        </div>

        <!-- Pagination -->
        <Pagination v-if="progresses?.last_page > 1" :data="progresses" class="mt-4" />

        <!-- Form Panel -->
        <FormPanel :show="showForm" :title="editingProgress ? 'Edit Progress' : 'Tambah Progress'" @close="showForm = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- Tipe Laporan -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Tipe Laporan <span class="text-red-400">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="t in tipeOptions"
                            :key="t.value"
                            type="button"
                            @click="form.tipe = t.value"
                            :class="[
                                form.tipe === t.value
                                    ? t.value === 'hadir'
                                        ? 'border-blue-400 bg-blue-50 text-blue-700'
                                        : t.value === 'sakit'
                                            ? 'border-red-400 bg-red-50 text-red-700'
                                            : 'border-amber-400 bg-amber-50 text-amber-700'
                                    : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300 hover:bg-slate-50',
                                'flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all cursor-pointer'
                            ]"
                        >
                            <span class="material-symbols-rounded text-[22px]">{{ t.icon }}</span>
                            <span class="text-xs font-bold">{{ t.label }}</span>
                            <span class="text-[10px] text-center leading-tight opacity-70">{{ t.desc }}</span>
                        </button>
                    </div>
                    <p v-if="form.errors.tipe" class="text-xs text-red-500 mt-1.5">{{ form.errors.tipe }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        {{ form.tipe === 'hadir' ? 'Laporan Kegiatan' : 'Alasan / Keterangan' }}
                        <span class="text-red-400">*</span>
                    </label>

                    <!-- Context banners -->
                    <div v-if="form.tipe === 'sakit'" class="mb-2 flex items-start gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                        <span class="material-symbols-rounded text-[15px] shrink-0 mt-0.5">info</span>
                        <span>Jelaskan keluhan / jenis sakit Anda.</span>
                    </div>
                    <div v-else-if="form.tipe === 'izin'" class="mb-2 flex items-start gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                        <span class="material-symbols-rounded text-[15px] shrink-0 mt-0.5">info</span>
                        <span>Tuliskan alasan izin Anda.</span>
                    </div>

                    <textarea
                        v-model="form.description"
                        :rows="form.tipe === 'hadir' ? 5 : 3"
                        :placeholder="form.tipe === 'hadir' ? 'Deskripsikan kegiatan / pekerjaan hari ini...' : 'Alasan / keterangan...'"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                        :class="{ 'border-red-300 bg-red-50': form.errors.description }"
                    ></textarea>
                    <p v-if="form.errors.description" class="text-xs text-red-500 mt-1.5">{{ form.errors.description }}</p>
                </div>

                <!-- Submit -->
                <div class="pt-1">
                    <button
                        type="submit"
                        :disabled="form.processing || formProcessing"
                        :class="[
                            form.tipe === 'hadir'
                                ? 'bg-blue-500 hover:bg-blue-600'
                                : form.tipe === 'sakit'
                                    ? 'bg-red-500 hover:bg-red-600'
                                    : 'bg-amber-500 hover:bg-amber-600',
                            'w-full py-2.5 text-white text-sm font-bold rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center gap-2'
                        ]"
                    >
                        <span class="material-symbols-rounded text-[18px]">{{ editingProgress ? 'save' : 'send' }}</span>
                        {{ (form.processing || formProcessing) ? 'Menyimpan...' : (editingProgress ? 'Update Laporan' : 'Kirim Laporan') }}
                    </button>
                </div>
            </form>
        </FormPanel>

        <!-- Confirm Delete -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="Hapus Progress"
            message="Progress yang dihapus tidak dapat dikembalikan. Lanjutkan?"
            :processing="deleteProcessing"
            @confirm="doDelete"
            @cancel="showDeleteConfirm = false"
        />
    </div>
</template>
