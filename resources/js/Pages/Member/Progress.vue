<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'
import FormPanel from '@/Components/FormPanel.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

defineOptions({ layout: MemberLayout })

const props = defineProps({
    progresses: Object,
    today_attendance: Object,
    member: Object,
    error: String,
})

const allProgress = computed(() => props.progresses?.data || [])

// ─── Separate today vs history ───
const todayStr = new Date().toISOString().slice(0, 10)

const todayProgress = computed(() =>
    allProgress.value.filter(p => normalizeDate(p.tanggal) === todayStr)
)

const historyProgress = computed(() =>
    allProgress.value.filter(p => normalizeDate(p.tanggal) !== todayStr)
)

// Group history by date
const historyGrouped = computed(() => {
    const groups = {}
    historyProgress.value.forEach(p => {
        const d = normalizeDate(p.tanggal)
        if (!groups[d]) groups[d] = []
        groups[d].push(p)
    })
    // Sort dates descending
    return Object.entries(groups).sort((a, b) => b[0].localeCompare(a[0]))
})

// Toggle history visibility
const showHistory = ref(false)

// ─── Lock Logic ───
const canManageProgress = computed(() => {
    const att = props.today_attendance
    return att && att.check_in_time && !att.check_out_time
})

const lockReason = computed(() => {
    const att = props.today_attendance
    if (!att || !att.check_in_time) return 'Kamu belum check-in hari ini.'
    if (att.check_out_time) return 'Kamu sudah checkout. Progress terkunci.'
    return ''
})

const attendanceStatus = computed(() => {
    const att = props.today_attendance
    if (!att || !att.check_in_time) return { label: 'Belum Check-in', color: 'slate', icon: 'remove_circle_outline' }
    if (att.check_out_time) return { label: 'Sudah Checkout', color: 'green', icon: 'check_circle' }
    return { label: 'Sedang Bekerja', color: 'blue', icon: 'work' }
})

// ─── Form State ───
const showForm = ref(false)
const editingProgress = ref(null)
const formProcessing = ref(false)

const form = useForm({
    description: '',
})

const openCreate = () => {
    editingProgress.value = null
    form.description = ''
    form.clearErrors()
    showForm.value = true
}

const openEdit = (p) => {
    editingProgress.value = { ...p }
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
const normalizeDate = (d) => {
    if (!d) return ''
    if (typeof d === 'string' && d.length >= 10) return d.slice(0, 10)
    return new Date(d).toISOString().slice(0, 10)
}

const formatDate = (d) => {
    if (!d) return '-'
    const parsed = new Date(typeof d === 'string' && d.length === 10 ? d + 'T00:00:00' : d)
    if (isNaN(parsed.getTime())) return d
    return parsed.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

const formatDateShort = (d) => {
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
                <div v-if="!canManageProgress" class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-500">
                    <span class="material-symbols-rounded text-[14px]">lock</span>
                    Terkunci
                </div>
                <div v-else class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-600">
                    <span class="material-symbols-rounded text-[14px]">lock_open</span>
                    Bisa Input
                </div>
            </div>
            <div v-if="!canManageProgress && lockReason" class="mt-3 flex items-start gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                <span class="material-symbols-rounded text-[15px] shrink-0 mt-0.5">info</span>
                <span>{{ lockReason }}</span>
            </div>
        </div>

        <!-- ════════════════════════════════════ -->
        <!-- TODAY'S PROGRESS                     -->
        <!-- ════════════════════════════════════ -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <span class="material-symbols-rounded text-blue-500 text-[18px]">today</span>
                    Progress Hari Ini
                    <span v-if="todayProgress.length" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-600">{{ todayProgress.length }}</span>
                </h2>
                <p class="text-xs text-slate-400">{{ formatDate(todayStr) }}</p>
            </div>

            <!-- Today's entries -->
            <div v-if="todayProgress.length" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div v-for="(p, idx) in todayProgress" :key="p.id"
                    :class="['p-4 transition-all', idx < todayProgress.length - 1 ? 'border-b border-slate-100' : '']">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-5 h-5 rounded-md bg-blue-50 flex items-center justify-center text-blue-500 text-[11px] font-bold shrink-0">{{ idx + 1 }}</span>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100">
                                    <span class="material-symbols-rounded text-[11px]">work</span>
                                    Kegiatan
                                </span>
                            </div>
                            <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ p.description || '-' }}</p>
                        </div>
                        <!-- Actions (only when unlocked) -->
                        <div v-if="canManageProgress" class="flex items-center gap-1 shrink-0">
                            <button @click="openEdit(p)" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors" title="Edit">
                                <span class="material-symbols-rounded text-[16px]">edit</span>
                            </button>
                            <button @click="confirmDelete(p.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                                <span class="material-symbols-rounded text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty today -->
            <div v-else class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                <span class="material-symbols-rounded text-slate-300 text-[32px] mb-2">edit_note</span>
                <p class="text-sm font-semibold text-slate-500">Belum ada progress hari ini</p>
                <p class="text-xs text-slate-400 mt-1">
                    <template v-if="canManageProgress">Klik "Tambah Progress" untuk mencatat kegiatan.</template>
                    <template v-else>{{ lockReason }}</template>
                </p>
            </div>
        </div>

        <!-- ════════════════════════════════════ -->
        <!-- HISTORY (collapsed by default)       -->
        <!-- ════════════════════════════════════ -->
        <div v-if="historyGrouped.length">
            <button @click="showHistory = !showHistory"
                class="w-full flex items-center justify-between px-4 py-3 bg-white rounded-2xl border border-slate-200 hover:bg-slate-50 transition-colors mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-slate-400 text-[18px]">history</span>
                    <span class="text-sm font-bold text-slate-700">Riwayat Progress</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ historyGrouped.length }} hari</span>
                </div>
                <span class="material-symbols-rounded text-slate-400 text-[20px] transition-transform" :class="{ 'rotate-180': showHistory }">expand_more</span>
            </button>

            <transition name="slide">
                <div v-if="showHistory" class="space-y-3">
                    <div v-for="[date, entries] in historyGrouped" :key="date" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <!-- Date header -->
                        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-rounded text-slate-400 text-[16px]">calendar_today</span>
                                <span class="text-xs font-bold text-slate-600">{{ formatDateShort(date) }}</span>
                            </div>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-slate-200 text-slate-500">{{ entries.length }} progress</span>
                        </div>
                        <!-- Entries (read-only for member) -->
                        <div v-for="(p, idx) in entries" :key="p.id"
                            :class="['px-4 py-3', idx < entries.length - 1 ? 'border-b border-slate-50' : '']">
                            <div class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-md bg-slate-100 flex items-center justify-center text-slate-500 text-[11px] font-bold shrink-0 mt-0.5">{{ idx + 1 }}</span>
                                <p class="text-sm text-slate-600 whitespace-pre-line leading-relaxed">{{ p.description || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Form Panel -->
        <FormPanel :show="showForm" :title="editingProgress ? 'Edit Progress' : 'Tambah Progress'" @close="showForm = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- Description only (member = hadir only) -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Laporan Kegiatan <span class="text-red-400">*</span>
                    </label>
                    <div class="mb-2 flex items-start gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-700">
                        <span class="material-symbols-rounded text-[15px] shrink-0 mt-0.5">info</span>
                        <span>Deskripsikan satu kegiatan / pekerjaan yang dilakukan hari ini. Anda bisa menambah beberapa progress.</span>
                    </div>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        placeholder="Contoh: Membuat halaman login dan register..."
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
                        class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-rounded text-[18px]">{{ editingProgress ? 'save' : 'send' }}</span>
                        {{ (form.processing || formProcessing) ? 'Menyimpan...' : (editingProgress ? 'Update Progress' : 'Kirim Progress') }}
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

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}
.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    max-height: 0;
}
.slide-enter-to,
.slide-leave-from {
    opacity: 1;
    max-height: 2000px;
}
</style>
