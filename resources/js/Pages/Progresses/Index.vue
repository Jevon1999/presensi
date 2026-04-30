<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormPanel from '@/Components/FormPanel.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import ProgressForm from './Partials/ProgressForm.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    progresses: Object,
    members: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

// Panel state
const showForm = ref(false)
const editingProgress = ref(null)
const formProcessing = ref(false)

// Confirm delete
const showDeleteConfirm = ref(false)
const deletingId = ref(null)
const deleteProcessing = ref(false)

// Filters
const memberFilter = ref(props.filters.member_id || '')
const tipeFilter   = ref(props.filters.tipe || '')
const startDate    = ref(props.filters.start_date || '')
const endDate      = ref(props.filters.end_date || '')

// Searchable member
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
    const m = props.members.find(m => String(m.id) === String(memberFilter.value))
    return m ? m.nama_lengkap : ''
})
const selectMember = (m) => {
    memberFilter.value = m.id
    memberSearch.value = ''
    showMemberDropdown.value = false
    applyFilters()
}
const clearMember = () => {
    memberFilter.value = ''
    memberSearch.value = ''
    showMemberDropdown.value = false
}

const activeTab = ref('today')

const applyFilters = () => {
    router.get('/progresses', {
        member_id: memberFilter.value || undefined,
        tipe: tipeFilter.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, { preserveState: true, preserveScroll: true })
}

const clearFilters = () => {
    memberFilter.value = ''
    memberSearch.value = ''
    tipeFilter.value   = ''
    startDate.value    = ''
    endDate.value      = ''
    router.get('/progresses', {}, { preserveState: true })
}

const hasFilters = computed(() => memberFilter.value || tipeFilter.value || startDate.value || endDate.value)

// CRUD
const openCreate = () => {
    editingProgress.value = null
    showForm.value = true
}

const openEdit = (p) => {
    editingProgress.value = { ...p }
    showForm.value = true
}

const handleSubmit = (form) => {
    formProcessing.value = true
    if (editingProgress.value) {
        form.put(`/progresses/${editingProgress.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; editingProgress.value = null },
            onFinish: () => { formProcessing.value = false },
        })
    } else {
        form.post('/progresses', {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
            onFinish: () => { formProcessing.value = false },
        })
    }
}

const confirmDelete = (id) => {
    deletingId.value = id
    showDeleteConfirm.value = true
}

const doDelete = () => {
    deleteProcessing.value = true
    router.delete(`/progresses/${deletingId.value}`, {
        preserveScroll: true,
        onSuccess: () => { showDeleteConfirm.value = false },
        onFinish: () => { deleteProcessing.value = false },
    })
}

const list = computed(() => props.progresses?.data || [])
const pagination = computed(() => ({
    links: props.progresses?.links || [],
    from: props.progresses?.from || 0,
    to: props.progresses?.to || 0,
    total: props.progresses?.total || 0,
}))

// ─── Group by date + member for batch display ───
const todayStr = new Date().toISOString().slice(0, 10)

const normalizeDate = (d) => {
    if (!d) return ''
    if (typeof d === 'string' && d.length >= 10) return d.slice(0, 10)
    return new Date(d).toISOString().slice(0, 10)
}

// Group: { "2026-04-30": { "member-id-1": { member: {...}, items: [...] }, ... }, ... }
const grouped = computed(() => {
    const groups = {}
    list.value.forEach(p => {
        const d = normalizeDate(p.tanggal)
        if (!groups[d]) groups[d] = {}
        const mId = p.member_id || 'unknown'
        if (!groups[d][mId]) {
            groups[d][mId] = {
                member: p.member,
                items: []
            }
        }
        groups[d][mId].items.push(p)
    })
    return groups
})

const todayGroup = computed(() => grouped.value[todayStr] || {})
const todayHasData = computed(() => Object.keys(todayGroup.value).length > 0)

const historyDates = computed(() => {
    return Object.keys(grouped.value)
        .filter(d => d !== todayStr)
        .sort((a, b) => b.localeCompare(a))
})

// Track which history dates are expanded
const expandedDates = ref(new Set())
const toggleDate = (d) => {
    if (expandedDates.value.has(d)) {
        expandedDates.value.delete(d)
    } else {
        expandedDates.value.add(d)
    }
    // Force reactivity
    expandedDates.value = new Set(expandedDates.value)
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

const tipeConfig = (tipe) => {
    const map = {
        hadir: { label: 'Hadir', icon: 'work', cls: 'bg-blue-50 text-blue-600 border-blue-100' },
        sakit: { label: 'Sakit', icon: 'medical_services', cls: 'bg-red-50 text-red-600 border-red-100' },
        izin:  { label: 'Izin', icon: 'assignment_late', cls: 'bg-amber-50 text-amber-600 border-amber-100' },
    }
    return map[tipe] || map.hadir
}

const countEntries = (dateGroup) => {
    return Object.values(dateGroup).reduce((sum, g) => sum + g.items.length, 0)
}
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Progress</h1>
                <p class="text-sm text-slate-400 mt-0.5">Pantau progres kerja anggota</p>
            </div>
            <button
                @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
            >
                <span class="material-symbols-rounded text-[18px]">add_circle</span>
                Tambah Progress
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4" @click="showMemberDropdown = false">
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Searchable Member Filter -->
                <div class="relative min-w-[200px]">
                    <input
                        type="text"
                        v-model="memberSearch"
                        @click.stop="showMemberDropdown = true"
                        @focus="showMemberDropdown = true"
                        placeholder="Cari anggota..."
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none transition-all pr-16"
                    />
                    <div v-if="selectedMemberName && !memberSearch" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-700 pointer-events-none truncate max-w-[130px]">
                        {{ selectedMemberName }}
                    </div>
                    <button v-if="memberFilter" @click.stop="clearMember" class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-rounded text-[16px]">close</span>
                    </button>
                    <span class="material-symbols-rounded absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>

                    <div v-if="showMemberDropdown" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto" @click.stop>
                        <div
                            class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer text-sm text-slate-500 border-b border-slate-50"
                            @click.stop="clearMember(); showMemberDropdown = false"
                        >
                            Semua Anggota
                        </div>
                        <div
                            v-for="m in filteredMembers"
                            :key="m.id"
                            @click.stop="selectMember(m)"
                            class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-50 last:border-0 text-sm font-medium text-slate-700"
                        >
                            {{ m.nama_lengkap }}
                        </div>
                        <div v-if="filteredMembers.length === 0" class="px-4 py-4 text-center text-sm text-slate-400">
                            Anggota tidak ditemukan
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="tipeFilter"
                        class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none transition-all cursor-pointer min-w-[120px]"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="hadir">Hadir</option>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                    </select>
                    <flat-pickr
                        v-model="startDate"
                        :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true }"
                        placeholder="Mulai"
                        class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all w-32"
                    />
                    <span class="text-xs text-slate-400">s/d</span>
                    <flat-pickr
                        v-model="endDate"
                        :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true }"
                        placeholder="Akhir"
                        class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all w-32"
                    />
                </div>
                <button
                    @click="applyFilters"
                    class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    Filter
                </button>
                <button
                    v-if="hasFilters"
                    @click="clearFilters"
                    class="px-3.5 py-2.5 text-sm text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-xl transition-colors border border-slate-200"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- TABS -->
        <div class="flex items-center justify-between border-b border-slate-200 mb-6">
            <div class="flex gap-6">
                <button @click="activeTab = 'today'" :class="['pb-3 text-sm font-bold border-b-2 transition-colors relative -mb-[1px]', activeTab === 'today' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700']">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-[18px]">today</span>
                        Hari Ini
                        <span v-if="todayHasData" :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-full', activeTab === 'today' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500']">{{ countEntries(todayGroup) }}</span>
                    </div>
                </button>
                <button @click="activeTab = 'history'" :class="['pb-3 text-sm font-bold border-b-2 transition-colors relative -mb-[1px]', activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700']">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-[18px]">history</span>
                        Riwayat
                        <span v-if="historyDates.length" :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-full', activeTab === 'history' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500']">{{ historyDates.length }} hari</span>
                    </div>
                </button>
            </div>
            <p v-if="activeTab === 'today'" class="text-xs text-slate-400 pb-3">{{ formatDate(todayStr) }}</p>
        </div>

        <!-- ════════════════════════════════════ -->
        <!-- TODAY'S PROGRESS                     -->
        <!-- ════════════════════════════════════ -->
        <div v-if="activeTab === 'today'" class="mb-6">
            <div v-if="todayHasData" class="space-y-3">
                <!-- Per member batch -->
                <div v-for="(group, memberId) in todayGroup" :key="memberId" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <!-- Member header -->
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-blue-500 flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                                {{ (group.member?.nama_lengkap || '?')[0].toUpperCase() }}
                            </div>
                            <span class="text-xs font-bold text-slate-700">{{ group.member?.nama_lengkap || '-' }}</span>
                            <span class="text-[10px] text-slate-400">{{ group.member?.office?.name || '' }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-600">{{ group.items.length }} progress</span>
                    </div>
                    <!-- Entries -->
                    <div v-for="(p, idx) in group.items" :key="p.id"
                        :class="['px-4 py-3', idx < group.items.length - 1 ? 'border-b border-slate-50' : '']">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0 flex items-start gap-2">
                                <span class="w-5 h-5 rounded-md bg-blue-50 flex items-center justify-center text-blue-500 text-[11px] font-bold shrink-0 mt-0.5">{{ idx + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <span :class="['inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold border mb-1', tipeConfig(p.tipe).cls]">
                                        <span class="material-symbols-rounded text-[11px]">{{ tipeConfig(p.tipe).icon }}</span>
                                        {{ tipeConfig(p.tipe).label }}
                                    </span>
                                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ p.description || '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
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
            </div>
            <div v-else class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                <span class="material-symbols-rounded text-slate-300 text-[32px] mb-2">edit_note</span>
                <p class="text-sm font-semibold text-slate-500">Belum ada progress hari ini</p>
            </div>
        </div>

        <!-- ════════════════════════════════════ -->
        <!-- HISTORY (click to expand per date)   -->
        <!-- ════════════════════════════════════ -->
        <div v-if="activeTab === 'history'">
            <div v-if="historyDates.length" class="space-y-3">
                <div v-for="date in historyDates" :key="date">
                    <!-- Date toggle header -->
                    <button @click="toggleDate(date)"
                        class="w-full flex items-center justify-between px-4 py-3 bg-white rounded-2xl border border-slate-200 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-slate-400 text-[16px]">calendar_today</span>
                            <span class="text-sm font-semibold text-slate-700">{{ formatDateShort(date) }}</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ countEntries(grouped[date]) }} progress</span>
                        </div>
                        <span class="material-symbols-rounded text-slate-400 text-[20px] transition-transform" :class="{ 'rotate-180': expandedDates.has(date) }">expand_more</span>
                    </button>

                    <!-- Expanded content -->
                    <div v-if="expandedDates.has(date)" class="mt-2 space-y-2 ml-2">
                        <div v-for="(group, memberId) in grouped[date]" :key="memberId" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                            <!-- Member header -->
                            <div class="px-4 py-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                                <div class="w-5 h-5 rounded-md bg-slate-300 flex items-center justify-center text-white text-[9px] font-bold shrink-0">
                                    {{ (group.member?.nama_lengkap || '?')[0].toUpperCase() }}
                                </div>
                                <span class="text-xs font-bold text-slate-600">{{ group.member?.nama_lengkap || '-' }}</span>
                                <span class="text-[10px] text-slate-400">{{ group.member?.office?.name || '' }}</span>
                            </div>
                            <!-- Entries with admin CRUD -->
                            <div v-for="(p, idx) in group.items" :key="p.id"
                                :class="['px-4 py-2.5', idx < group.items.length - 1 ? 'border-b border-slate-50' : '']">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0 flex items-start gap-2">
                                        <span class="w-4 h-4 rounded bg-slate-100 flex items-center justify-center text-slate-400 text-[10px] font-bold shrink-0 mt-0.5">{{ idx + 1 }}</span>
                                        <div class="flex-1 min-w-0">
                                            <span :class="['inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold border mb-0.5', tipeConfig(p.tipe).cls]">
                                                <span class="material-symbols-rounded text-[10px]">{{ tipeConfig(p.tipe).icon }}</span>
                                                {{ tipeConfig(p.tipe).label }}
                                            </span>
                                            <p class="text-sm text-slate-600 whitespace-pre-line leading-relaxed">{{ p.description || '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button @click="openEdit(p)" class="p-1 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors" title="Edit">
                                            <span class="material-symbols-rounded text-[15px]">edit</span>
                                        </button>
                                        <button @click="confirmDelete(p.id)" class="p-1 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                                            <span class="material-symbols-rounded text-[15px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div v-else class="bg-white rounded-2xl border border-slate-200 p-8 text-center mt-3">
                <span class="material-symbols-rounded text-slate-300 text-[32px] mb-2">history</span>
                <p class="text-sm font-semibold text-slate-500">Belum ada riwayat progress</p>
            </div>
        </div>

        <!-- Empty state (no data at all) -->
        <EmptyState v-if="!list.length" icon="trending_up" title="Belum ada progress" description="Tambahkan progress untuk mencatat aktivitas harian." />

        <!-- Pagination -->
        <div v-if="list.length" class="mt-4">
            <Pagination v-bind="pagination" />
        </div>

        <!-- Form Panel -->
        <FormPanel :show="showForm" :title="editingProgress ? 'Edit Progress' : 'Tambah Progress'" @close="showForm = false">
            <ProgressForm
                :progress="editingProgress"
                :members="members"
                :processing="formProcessing"
                @submit="handleSubmit"
            />
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
