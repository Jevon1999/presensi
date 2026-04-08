<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormPanel from '@/Components/FormPanel.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
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
const startDate = ref(props.filters.start_date || '')
const endDate = ref(props.filters.end_date || '')

const applyFilters = () => {
    router.get('/progresses', {
        member_id: memberFilter.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, { preserveState: true, preserveScroll: true })
}

const clearFilters = () => {
    memberFilter.value = ''
    startDate.value = ''
    endDate.value = ''
    router.get('/progresses', {}, { preserveState: true })
}

const hasFilters = computed(() => memberFilter.value || startDate.value || endDate.value)

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
            <button
                @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
            >
                <span class="material-symbols-rounded text-[18px]">add_circle</span>
                Tambah Progress
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <select
                    v-model="memberFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white min-w-[160px]"
                >
                    <option value="">Semua Anggota</option>
                    <option v-for="m in members" :key="m.id" :value="m.id">{{ m.nama_lengkap }}</option>
                </select>
                <div class="flex items-center gap-2">
                    <VueDatePicker
                        v-model="startDate"
                        :enable-time-picker="false"
                        model-type="yyyy-MM-dd"
                        format="dd/MM/yyyy"
                        auto-apply
                        placeholder="Mulai"
                        input-class-name="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all w-32"
                    />
                    <span class="text-xs text-slate-400">s/d</span>
                    <VueDatePicker
                        v-model="endDate"
                        :enable-time-picker="false"
                        model-type="yyyy-MM-dd"
                        format="dd/MM/yyyy"
                        auto-apply
                        placeholder="Akhir"
                        input-class-name="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all w-32"
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
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hidden lg:block">
            <table v-if="list.length" class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Kantor</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Deskripsi</th>
                        <th class="text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in list" :key="p.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(p.tanggal) }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-slate-700">{{ p.member?.nama_lengkap || '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ p.member?.office?.name || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 max-w-sm">{{ truncate(p.description) }}</td>
                        <td class="px-4 py-3 text-right">
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
            <EmptyState v-else icon="trending_up" title="Belum ada progress" description="Tambahkan progress untuk mencatat aktivitas harian." />
            <div v-if="list.length" class="px-4 pb-4">
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
