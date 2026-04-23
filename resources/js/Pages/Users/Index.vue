<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormPanel from '@/Components/FormPanel.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import UserForm from './Partials/UserForm.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    users: Object,
    filters: { type: Object, default: () => ({}) },
    error: { type: String, default: null },
    currentUser: Object,
})

// Panel state
const showForm = ref(false)
const editingUser = ref(null)
const formProcessing = ref(false)

// Confirm delete
const showDeleteConfirm = ref(false)
const deletingId = ref(null)
const deleteProcessing = ref(false)

// Filters
const search = ref(props.filters.search || '')
const roleFilter = ref(props.filters.role || '')

let debounceTimer = null
const applyFilters = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        router.get('/users', {
            search: search.value || undefined,
            role: roleFilter.value || undefined,
        }, { preserveState: true, preserveScroll: true })
    }, 400)
}

const clearFilters = () => {
    search.value = ''
    roleFilter.value = ''
    router.get('/users', {}, { preserveState: true })
}

const hasFilters = computed(() => search.value || roleFilter.value)

// Loading
const usersList = computed(() => props.users?.data || [])
const isLoading = computed(() => !usersList.value.length && !props.error)

// CRUD
const openCreate = () => {
    editingUser.value = null
    showForm.value = true
}

const openEdit = (user) => {
    editingUser.value = { ...user }
    showForm.value = true
}

const handleSubmit = (form) => {
    formProcessing.value = true
    if (editingUser.value) {
        form.put(`/users/${editingUser.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; editingUser.value = null },
            onFinish: () => { formProcessing.value = false },
        })
    } else {
        form.post('/users', {
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
    router.delete(`/users/${deletingId.value}`, {
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
                <h1 class="text-xl font-bold text-slate-800">Users</h1>
                <p class="text-sm text-slate-400 mt-0.5">Kelola akun admin & operator</p>
            </div>
            <button
                @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
            >
                <span class="material-symbols-rounded text-[18px]">person_add</span>
                Tambah User
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        <input
                            v-model="search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Cari nama atau email..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none"
                        />
                    </div>
                </div>
                <select
                    v-model="roleFilter"
                    @change="applyFilters"
                    class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none bg-white"
                >
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <button
                    v-if="hasFilters"
                    @click="clearFilters"
                    class="px-3.5 py-2.5 text-sm text-slate-500 hover:text-slate-700 rounded-xl border border-slate-200 hover:bg-slate-50 transition"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Table (Desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200 hidden lg:block">
            <!-- Loading Skeleton -->
            <div v-if="isLoading" class="p-8">
                <div class="animate-pulse space-y-4">
                    <div v-for="i in 3" :key="'skeleton-' + i" class="flex items-center gap-4">
                        <div class="h-8 bg-slate-200 rounded w-48"></div>
                        <div class="h-8 bg-slate-200 rounded flex-1"></div>
                        <div class="h-8 bg-slate-200 rounded w-20"></div>
                        <div class="h-8 bg-slate-200 rounded w-20"></div>
                        <div class="h-8 bg-slate-200 rounded w-24"></div>
                    </div>
                </div>
            </div>

            <div v-else-if="usersList.length" class="overflow-auto max-h-[calc(100vh-22rem)]">
                <table class="w-full min-w-[600px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-white">
                            <th class="sticky left-0 z-20 bg-white text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3 shadow-[1px_0_0_0_#f1f5f9]">Nama</th>
                            <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Email</th>
                            <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Role</th>
                            <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Status</th>
                            <th class="sticky right-0 z-20 bg-white text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3 shadow-[-1px_0_0_0_#f1f5f9]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in usersList" :key="u.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <td class="sticky left-0 z-[5] bg-white hover:bg-slate-50/50 px-4 py-3 shadow-[1px_0_0_0_#f1f5f9]">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold shrink-0">
                                        {{ (u.name || '').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) }}
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">{{ u.name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ u.email }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                    class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                                >
                                    {{ u.role === 'admin' ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="u.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                                    class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                                >
                                    {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="sticky right-0 z-[5] bg-white hover:bg-slate-50/50 px-4 py-3 text-right shadow-[-1px_0_0_0_#f1f5f9]">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(u)" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors" title="Edit">
                                        <span class="material-symbols-rounded text-[18px]">edit</span>
                                    </button>
                                    <button
                                        v-if="u.id !== currentUser?.id"
                                        @click="confirmDelete(u.id)"
                                        class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors"
                                        title="Hapus"
                                    >
                                        <span class="material-symbols-rounded text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState v-else-if="!isLoading" icon="manage_accounts" title="Belum ada user" description="Tambahkan user pertama untuk memulai." />
        </div>


        <!-- Cards (Mobile) -->
        <div class="lg:hidden space-y-3">
            <!-- Loading Skeleton (Mobile) -->
            <div v-if="isLoading" class="space-y-3">
                <div v-for="i in 3" :key="'mobile-skeleton-' + i" class="bg-white rounded-2xl border border-slate-200 p-4 animate-pulse">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="h-5 bg-slate-200 rounded w-32 mb-2"></div>
                            <div class="h-4 bg-slate-200 rounded w-48"></div>
                        </div>
                        <div class="h-6 bg-slate-200 rounded w-16"></div>
                    </div>
                    <div class="h-10 bg-slate-200 rounded"></div>
                </div>
            </div>

            <div
                v-else
                v-for="u in usersList"
                :key="'card-' + u.id"
                class="bg-white rounded-2xl border border-slate-200 p-4"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold">
                            {{ (u.name || '').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-slate-800">{{ u.name }}</p>
                            <p class="text-xs text-slate-400">{{ u.email }}</p>
                        </div>
                    </div>
                    <span
                        :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                        class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                    >
                        {{ u.role === 'admin' ? 'Admin' : 'User' }}
                    </span>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <span
                        :class="u.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                        class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                    >
                        {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <button @click="openEdit(u)" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                        <span class="material-symbols-rounded text-[16px]">edit</span>
                        Edit
                    </button>
                    <button 
                        v-if="u.id !== currentUser?.id"
                        @click="confirmDelete(u.id)" 
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors"
                        title="Hapus"
                    >
                        <span class="material-symbols-rounded text-[16px]">delete</span>
                        Hapus
                    </button>
                </div>
            </div>
            <EmptyState v-if="!usersList.length && !isLoading" icon="manage_accounts" title="Belum ada user" description="Tambahkan user pertama untuk memulai." />
        </div>

        <!-- Pagination -->
        <Pagination v-if="users?.last_page > 1" :data="users" class="mt-4" />

        <!-- Form Panel -->
        <FormPanel :show="showForm" :title="editingUser ? 'Edit User' : 'Tambah User'" @close="showForm = false">
            <UserForm
                :user="editingUser"
                :processing="formProcessing"
                @submit="handleSubmit"
            />
        </FormPanel>

        <!-- Confirm Delete -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="Hapus User"
            message="User yang dihapus tidak dapat dikembalikan. Lanjutkan?"
            :processing="deleteProcessing"
            @confirm="doDelete"
            @cancel="showDeleteConfirm = false"
        />
    </div>
</template>
