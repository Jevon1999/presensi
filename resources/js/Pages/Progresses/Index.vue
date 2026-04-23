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

const formatDate = (d) => {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const truncate = (str, len = 80) => {
    if (!str) return '-'
    return str.length > len ? str.slice(0, len) + '...' : str
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
            <!-- <button
                @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
            >
                <span class="material-symbols-rounded text-[18px]">add_circle</span>
                Tambah Progress
            </button> -->
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
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none transition-all bg-white pr-16"
                    />
                    <!-- Selected label overlay -->
                    <div v-if="selectedMemberName && !memberSearch" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-700 pointer-events-none truncate max-w-[130px]">
                        {{ selectedMemberName }}
                    </div>
                    <!-- Clear button -->
                    <button v-if="memberFilter" @click.stop="clearMember" class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-rounded text-[16px]">close</span>
                    </button>
                    <span class="material-symbols-rounded absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>

                    <!-- Dropdown -->
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

        <!-- Table (Desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200 hidden lg:block">
            <div v-if="list.length" class="overflow-auto max-h-[calc(100vh-22rem)]">
                <table class="w-full min-w-[700px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-white">
                            <th class="sticky left-0 z-20 bg-white text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3 shadow-[1px_0_0_0_#f1f5f9] whitespace-nowrap">Tanggal</th>
                            <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama</th>
                            <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Kantor</th>
                            <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Tipe</th>
                            <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Deskripsi</th>
                            <th class="sticky right-0 z-20 bg-white text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3 shadow-[-1px_0_0_0_#f1f5f9]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in list" :key="p.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <td class="sticky left-0 z-[5] bg-white hover:bg-slate-50/50 px-4 py-3 text-sm text-slate-600 whitespace-nowrap shadow-[1px_0_0_0_#f1f5f9]">{{ formatDate(p.tanggal) }}</td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-slate-700 whitespace-nowrap">{{ p.member?.nama_lengkap || '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{ p.member?.office?.name || '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span v-if="p.tipe === 'sakit'" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-red-50 text-red-600 text-[11px] font-bold border border-red-100">
                                    <span class="material-symbols-rounded text-[14px]">medical_services</span> Sakit
                                </span>
                                <span v-else-if="p.tipe === 'izin'" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-amber-50 text-amber-600 text-[11px] font-bold border border-amber-100">
                                    <span class="material-symbols-rounded text-[14px]">assignment_late</span> Izin
                                </span>
                                <span v-else class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-50 text-blue-600 text-[11px] font-bold border border-blue-100">
                                    <span class="material-symbols-rounded text-[14px]">work</span> Hadir
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 max-w-sm">{{ truncate(p.description) }}</td>
                            <td class="sticky right-0 z-[5] bg-white hover:bg-slate-50/50 px-4 py-3 text-right shadow-[-1px_0_0_0_#f1f5f9]">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(p)" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors" title="Edit">
                                        <span class="material-symbols-rounded text-[18px]">edit</span>
                                    </button>
                                    <button @click="confirmDelete(p.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                                        <span class="material-symbols-rounded text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState v-if="!list.length" icon="trending_up" title="Belum ada progress" description="Tambahkan progress untuk mencatat aktivitas harian." />
            <div v-if="list.length" class="px-4 pb-4 border-t border-slate-100">
                <Pagination v-bind="pagination" />
            </div>
        </div>


        <!-- Cards (Mobile) -->
        <div class="lg:hidden space-y-3">
            <div
                v-for="p in list"
                :key="'card-' + p.id"
                class="bg-white rounded-2xl border border-slate-200 p-4"
            >
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="font-semibold text-sm text-slate-800">{{ p.member?.nama_lengkap || '-' }}</p>
                        <p class="text-xs text-slate-400">{{ p.member?.office?.name || '-' }} &middot; {{ formatDate(p.tanggal) }}</p>
                    </div>
                    <span v-if="p.tipe === 'sakit'" class="px-2 py-1 rounded-lg bg-red-50 text-red-600 text-[10px] font-bold border border-red-100 flex items-center gap-1">Sakit</span>
                    <span v-else-if="p.tipe === 'izin'" class="px-2 py-1 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-100 flex items-center gap-1">Izin</span>
                    <span v-else class="px-2 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100 flex items-center gap-1">Hadir</span>
                </div>
                <p class="text-sm text-slate-600 mb-3 leading-relaxed">{{ truncate(p.description, 120) }}</p>
                <div class="flex gap-2 pt-2 border-t border-slate-100">
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
            <EmptyState v-if="!list.length" icon="trending_up" title="Belum ada progress" description="Tambahkan progress untuk mencatat aktivitas harian." />
            <div v-if="list.length">
                <Pagination v-bind="pagination" />
            </div>
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
