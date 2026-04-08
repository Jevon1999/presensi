<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormPanel from '@/Components/FormPanel.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Pagination from '@/Components/Pagination.vue'
import Badge from '@/Components/Badge.vue'
import EmptyState from '@/Components/EmptyState.vue'
import MemberForm from './Partials/MemberForm.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    members: Object,
    offices: { type: Array, default: () => [] },
    availableUsers: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

// Panel state
const showForm = ref(false)
const editingMember = ref(null)
const formProcessing = ref(false)

// Confirm delete
const showDeleteConfirm = ref(false)
const deletingId = ref(null)
const deleteProcessing = ref(false)

// Confirm reject
const showRejectDialog = ref(false)
const rejectingId = ref(null)
const rejectReason = ref('')
const rejectProcessing = ref(false)
const approveProcessing = ref(null)

// Filters
const search = ref(props.filters.search || '')
const officeFilter = ref(props.filters.office_id || '')
const statusFilter = ref(props.filters.status_aktif ?? '')
const memberStatusFilter = ref(props.filters.status || '')

let debounceTimer = null
const applyFilters = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        router.get('/members', {
            search: search.value || undefined,
            office_id: officeFilter.value || undefined,
            status_aktif: statusFilter.value !== '' ? statusFilter.value : undefined,
            status: memberStatusFilter.value || undefined,
        }, { preserveState: true, preserveScroll: true })
    }, 400)
}

const clearFilters = () => {
    search.value = ''
    officeFilter.value = ''
    statusFilter.value = ''
    memberStatusFilter.value = ''
    router.get('/members', {}, { preserveState: true })
}

const hasFilters = computed(() => search.value || officeFilter.value || statusFilter.value !== '' || memberStatusFilter.value)

// CRUD actions
const openCreate = () => {
    editingMember.value = null
    showForm.value = true
}

const openEdit = (member) => {
    editingMember.value = { ...member }
    showForm.value = true
}

const handleSubmit = (form) => {
    formProcessing.value = true
    if (editingMember.value) {
        form.put(`/members/${editingMember.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; editingMember.value = null },
            onFinish: () => { formProcessing.value = false },
        })
    } else {
        form.post('/members', {
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
    router.delete(`/members/${deletingId.value}`, {
        preserveScroll: true,
        onSuccess: () => { showDeleteConfirm.value = false },
        onFinish: () => { deleteProcessing.value = false },
    })
}

const membersList = computed(() => props.members?.data || [])
const pagination = computed(() => ({
    links: props.members?.links || [],
    from: props.members?.from || 0,
    to: props.members?.to || 0,
    total: props.members?.total || 0,
}))

const formatDate = (d) => {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const handleApprove = (id) => {
    approveProcessing.value = id
    router.put(`/members/${id}/approve`, {}, {
        preserveScroll: true,
        onFinish: () => { approveProcessing.value = null },
    })
}

const openRejectDialog = (id) => {
    rejectingId.value = id
    rejectReason.value = ''
    showRejectDialog.value = true
}

const doReject = () => {
    if (!rejectReason.value || !rejectReason.value.trim()) {
        alert('Alasan penolakan harus diisi!');
        return;
    }
    rejectProcessing.value = true;
    router.put(`/members/${rejectingId.value}/reject`, { rejection_reason: rejectReason.value }, {
        preserveScroll: true,
        onSuccess: () => { showRejectDialog.value = false },
        onFinish: () => { rejectProcessing.value = false },
    })
}

const statusBadgeClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-amber-50 text-amber-700 border-amber-200'
        case 'approved': return 'bg-green-50 text-green-700 border-green-200'
        case 'rejected': return 'bg-red-50 text-red-700 border-red-200'
        default: return 'bg-slate-50 text-slate-600 border-slate-200'
    }
}

const statusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Pending'
        case 'approved': return 'Disetujui'
        case 'rejected': return 'Ditolak'
        default: return '-'
    }
}
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Anggota</h1>
                <p class="text-sm text-slate-400 mt-0.5">Kelola data anggota magang</p>
            </div>
            <button
                @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
            >
                <span class="material-symbols-rounded text-[18px]">person_add</span>
                Tambah Anggota
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[18px]">search</span>
                    <input
                        v-model="search"
                        @input="applyFilters"
                        type="text"
                        placeholder="Cari nama atau no. HP..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                    />
                </div>
                <select
                    v-model="officeFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white min-w-[140px]"
                >
                    <option value="">Semua Kantor</option>
                    <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
                </select>
                <select
                    v-model="statusFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white min-w-[120px]"
                >
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
                <select
                    v-model="memberStatusFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white min-w-[130px]"
                >
                    <option value="">Semua Pengajuan</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
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
            <table v-if="membersList.length" class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">No. HP</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Kantor</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Asal Sekolah</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Periode</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Pengajuan</th>
                        <th class="text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="m in membersList" :key="m.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-xs font-bold">
                                    {{ (m.nama_lengkap || '?')[0].toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">{{ m.nama_lengkap }}</p>
                                    <p class="text-[11px] text-slate-400">{{ m.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ m.no_hp || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ m.office?.name || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ m.asal_sekolah || '-' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ formatDate(m.tanggal_mulai_magang) }} — {{ formatDate(m.tanggal_selesai_magang) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <Badge :status="String(m.status_aktif)" type="member" />
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span v-if="m.status" :class="[statusBadgeClass(m.status), 'text-[10px] font-semibold px-2 py-0.5 rounded-full border']">
                                {{ statusLabel(m.status) }}
                            </span>
                            <span v-else class="text-[10px] text-slate-400">-</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <template v-if="m.status === 'pending'">
                                    <button @click="handleApprove(m.id)" :disabled="approveProcessing === m.id" class="p-1.5 rounded-lg hover:bg-green-50 text-slate-400 hover:text-green-600 transition-colors" title="Setujui">
                                        <span class="material-symbols-rounded text-[18px]">check_circle</span>
                                    </button>
                                    <button @click="openRejectDialog(m.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Tolak">
                                        <span class="material-symbols-rounded text-[18px]">cancel</span>
                                    </button>
                                </template>
                                <button @click="openEdit(m)" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors" title="Edit">
                                    <span class="material-symbols-rounded text-[18px]">edit</span>
                                </button>
                                <button @click="confirmDelete(m.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                                    <span class="material-symbols-rounded text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <EmptyState v-else icon="group" title="Belum ada anggota" description="Tambahkan anggota pertama untuk memulai." />
            <div v-if="membersList.length" class="px-4 pb-4">
                <Pagination v-bind="pagination" />
            </div>
        </div>

        <!-- Cards (Mobile) -->
        <div class="lg:hidden space-y-3">
            <div
                v-for="m in membersList"
                :key="'card-' + m.id"
                class="bg-white rounded-2xl border border-slate-200 p-4"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-sm font-bold">
                            {{ (m.nama_lengkap || '?')[0].toUpperCase() }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-slate-800">{{ m.nama_lengkap }}</p>
                            <p class="text-xs text-slate-400">{{ m.no_hp || '-' }}</p>
                        </div>
                    </div>
                    <Badge :status="String(m.status_aktif)" type="member" />
                </div>
                <div class="space-y-1.5 text-xs text-slate-500 mb-3">
                    <div class="flex justify-between">
                        <span>Kantor</span>
                        <span class="font-medium text-slate-700">{{ m.office?.name || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Sekolah</span>
                        <span class="font-medium text-slate-700">{{ m.asal_sekolah || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Periode</span>
                        <span class="font-medium text-slate-700">{{ formatDate(m.tanggal_mulai_magang) }} — {{ formatDate(m.tanggal_selesai_magang) }}</span>
                    </div>
                </div>
                <div v-if="m.status === 'pending'" class="flex gap-2 pt-2 border-t border-slate-100 mb-2">
                    <button @click="handleApprove(m.id)" :disabled="approveProcessing === m.id" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">check_circle</span>
                        Setujui
                    </button>
                    <button @click="openRejectDialog(m.id)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">cancel</span>
                        Tolak
                    </button>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <button @click="openEdit(m)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">edit</span>
                        Edit
                    </button>
                    <button @click="confirmDelete(m.id)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">delete</span>
                        Hapus
                    </button>
                </div>
            </div>
            <EmptyState v-if="!membersList.length" icon="group" title="Belum ada anggota" description="Tambahkan anggota pertama untuk memulai." />
            <div v-if="membersList.length">
                <Pagination v-bind="pagination" />
            </div>
        </div>

        <!-- Form Panel -->
        <FormPanel :show="showForm" :title="editingMember ? 'Edit Anggota' : 'Tambah Anggota'" @close="showForm = false">
            <MemberForm
                :member="editingMember"
                :offices="offices"
                :available-users="availableUsers"
                :processing="formProcessing"
                @submit="handleSubmit"
            />
        </FormPanel>

        <!-- Confirm Delete -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="Hapus Anggota"
            message="Anggota yang dihapus tidak dapat dikembalikan. Lanjutkan?"
            :processing="deleteProcessing"
            @confirm="doDelete"
            @cancel="showDeleteConfirm = false"
        />

        <!-- Reject Dialog -->
        <ConfirmDialog
            :show="showRejectDialog"
            title="Tolak Pengajuan"
            message="Berikan alasan penolakan (Wajib):"
            :processing="rejectProcessing"
            confirmText="Tolak"
            confirmClass="bg-red-500 hover:bg-red-600"
            @confirm="doReject"
            @cancel="showRejectDialog = false"
        >
            <template #content>
                <textarea
                    v-model="rejectReason"
                    rows="3"
                    placeholder="Alasan penolakan..."
                    class="w-full mt-2 px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-red-400 outline-none resize-none"
                />
            </template>
        </ConfirmDialog>
    </div>
</template>
