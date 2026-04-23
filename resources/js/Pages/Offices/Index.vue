<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormPanel from '@/Components/FormPanel.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import EmptyState from '@/Components/EmptyState.vue'
import OfficeForm from './Partials/OfficeForm.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    offices: { type: Array, default: () => [] },
    error: { type: String, default: null },
})

// Panel state
const showForm = ref(false)
const editingOffice = ref(null)
const formProcessing = ref(false)

// Confirm delete
const showDeleteConfirm = ref(false)
const deletingId = ref(null)
const deleteProcessing = ref(false)

// Expanded locations
const expandedId = ref(null)
const toggleExpand = (id) => {
    expandedId.value = expandedId.value === id ? null : id
}

// Loading state - show loading if no data and no error (initial load)
const isLoading = computed(() => !props.offices.length && !props.error)

// CRUD
const openCreate = () => {
    editingOffice.value = null
    showForm.value = true
}

const openEdit = async (office) => {
    // Fetch full details including locations
    try {
        const resp = await fetch(`/offices/${office.id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        if (resp.ok) {
            const data = await resp.json()
            editingOffice.value = data.data || data
        } else {
            editingOffice.value = { ...office }
        }
    } catch (e) {
        editingOffice.value = { ...office }
    }
    showForm.value = true
}

const handleSubmit = (form) => {
    formProcessing.value = true
    if (editingOffice.value) {
        form.put(`/offices/${editingOffice.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; editingOffice.value = null },
            onFinish: () => { formProcessing.value = false },
        })
    } else {
        form.post('/offices', {
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
    router.delete(`/offices/${deletingId.value}`, {
        preserveScroll: true,
        onSuccess: () => { showDeleteConfirm.value = false },
        onFinish: () => { deleteProcessing.value = false },
    })
}
</script>

<template>
    <div>
        <!-- Error Alert -->
        <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
            <span class="material-symbols-rounded text-red-500 text-[20px]">error</span>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-800">Gagal Memuat Data</p>
                <p class="text-xs text-red-600 mt-1">{{ error }}</p>
            </div>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Kantor</h1>
                <p class="text-sm text-slate-400 mt-0.5">Kelola data kantor & lokasi</p>
            </div>
            <button
                @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
            >
                <span class="material-symbols-rounded text-[18px]">add_business</span>
                Tambah Kantor
            </button>
        </div>

        <!-- Table (Desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200 hidden lg:block">
            <!-- Loading Skeleton -->
            <div v-if="isLoading" class="p-8">
                <div class="animate-pulse space-y-4">
                    <div v-for="i in 3" :key="'skeleton-' + i" class="flex items-center gap-4">
                        <div class="h-8 bg-slate-200 rounded w-20"></div>
                        <div class="h-8 bg-slate-200 rounded flex-1"></div>
                        <div class="h-8 bg-slate-200 rounded w-16"></div>
                        <div class="h-8 bg-slate-200 rounded w-16"></div>
                        <div class="h-8 bg-slate-200 rounded w-24"></div>
                    </div>
                </div>
            </div>

            <div v-else-if="offices.length" class="overflow-auto max-h-[calc(100vh-20rem)]">
                <table class="w-full min-w-[560px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-white">
                            <th class="sticky left-0 z-20 bg-white text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3 shadow-[1px_0_0_0_#f1f5f9]">Kode</th>
                            <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama Kantor</th>
                            <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Anggota</th>
                            <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Lokasi</th>
                            <th class="sticky right-0 z-20 bg-white text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3 shadow-[-1px_0_0_0_#f1f5f9]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="o in offices" :key="o.id">
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="sticky left-0 z-[5] bg-white hover:bg-slate-50/50 px-4 py-3 shadow-[1px_0_0_0_#f1f5f9]">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-600 rounded-lg">{{ o.code }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ o.name }}</td>
                                <td class="px-4 py-3 text-sm text-center text-slate-600">{{ o.members_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        v-if="o.locations?.length"
                                        @click="toggleExpand(o.id)"
                                        class="text-xs text-blue-500 hover:text-blue-600 font-medium"
                                    >
                                        {{ o.locations.length }} lokasi
                                        <span class="material-symbols-rounded text-[14px] align-middle">{{ expandedId === o.id ? 'expand_less' : 'expand_more' }}</span>
                                    </button>
                                    <span v-else class="text-xs text-slate-400">-</span>
                                </td>
                                <td class="sticky right-0 z-[5] bg-white hover:bg-slate-50/50 px-4 py-3 text-right shadow-[-1px_0_0_0_#f1f5f9]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEdit(o)" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors" title="Edit">
                                            <span class="material-symbols-rounded text-[18px]">edit</span>
                                        </button>
                                        <button @click="confirmDelete(o.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                                            <span class="material-symbols-rounded text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expanded locations row -->
                            <tr v-if="expandedId === o.id && o.locations?.length">
                                <td colspan="5" class="bg-slate-50 px-6 py-3">
                                    <div class="space-y-2">
                                        <div v-for="loc in o.locations" :key="loc.id" class="flex items-start gap-3 text-xs">
                                            <span class="material-symbols-rounded text-[14px] mt-0.5" :class="loc.is_active ? 'text-emerald-500' : 'text-slate-300'">
                                                {{ loc.is_active ? 'location_on' : 'location_off' }}
                                            </span>
                                            <div>
                                                <p class="font-medium text-slate-700">{{ loc.alamat || 'Tanpa alamat' }}</p>
                                                <p class="text-slate-400">{{ loc.latitude }}, {{ loc.longitude }} &middot; radius {{ loc.radius_meters }}m</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <EmptyState v-else-if="!isLoading" icon="apartment" title="Belum ada kantor" description="Tambahkan kantor pertama untuk memulai." />
        </div>


        <!-- Cards (Mobile) -->
        <div class="lg:hidden space-y-3">
            <!-- Loading Skeleton (Mobile) -->
            <div v-if="isLoading" class="space-y-3">
                <div v-for="i in 3" :key="'mobile-skeleton-' + i" class="bg-white rounded-2xl border border-slate-200 p-4 animate-pulse">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="h-5 bg-slate-200 rounded w-16 mb-2"></div>
                            <div class="h-4 bg-slate-200 rounded w-32"></div>
                        </div>
                        <div class="h-8 bg-slate-200 rounded w-12"></div>
                    </div>
                    <div class="h-10 bg-slate-200 rounded"></div>
                </div>
            </div>
            
            <div
                v-else
                v-for="o in offices"
                :key="'card-' + o.id"
                class="bg-white rounded-2xl border border-slate-200 p-4"
            >
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 rounded-md">{{ o.code }}</span>
                        </div>
                        <p class="font-semibold text-sm text-slate-800">{{ o.name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-slate-700">{{ o.members_count ?? 0 }}</p>
                        <p class="text-[10px] text-slate-400">anggota</p>
                    </div>
                </div>

                <!-- Locations -->
                <div v-if="o.locations?.length" class="space-y-1.5 mb-3">
                    <div v-for="loc in o.locations" :key="loc.id" class="flex items-start gap-2 text-xs">
                        <span class="material-symbols-rounded text-[14px] mt-0.5" :class="loc.is_active ? 'text-emerald-500' : 'text-slate-300'">location_on</span>
                        <p class="text-slate-500">{{ loc.alamat || loc.latitude + ', ' + loc.longitude }}</p>
                    </div>
                </div>

                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <button @click="openEdit(o)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">edit</span>
                        Edit
                    </button>
                    <button @click="confirmDelete(o.id)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">delete</span>
                        Hapus
                    </button>
                </div>
            </div>
            <EmptyState v-if="!offices.length && !isLoading" icon="apartment" title="Belum ada kantor" description="Tambahkan kantor pertama untuk memulai." />
        </div>

        <!-- Form Panel -->
        <FormPanel :show="showForm" :title="editingOffice ? 'Edit Kantor' : 'Tambah Kantor'" @close="showForm = false">
            <OfficeForm
                :office="editingOffice"
                :processing="formProcessing"
                @submit="handleSubmit"
            />
        </FormPanel>

        <!-- Confirm Delete -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="Hapus Kantor"
            message="Kantor yang dihapus tidak dapat dikembalikan. Semua lokasi terkait juga akan dihapus. Lanjutkan?"
            :processing="deleteProcessing"
            @confirm="doDelete"
            @cancel="showDeleteConfirm = false"
        />
    </div>
</template>
