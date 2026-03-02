<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
    user: { type: Object, default: null },
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const isEdit = !!props.user

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    role: props.user?.role || 'admin',
    is_active: props.user?.is_active ?? true,
})

watch(() => props.user, (u) => {
    if (u) {
        form.name = u.name || ''
        form.email = u.email || ''
        form.password = ''
        form.role = u.role || 'admin'
        form.is_active = u.is_active ?? true
    }
})

const submit = () => {
    emit('submit', form)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Nama -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama <span class="text-red-400">*</span></label>
            <input
                v-model="form.name"
                type="text"
                placeholder="Masukkan nama"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.name }"
            />
            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email <span class="text-red-400">*</span></label>
            <input
                v-model="form.email"
                type="email"
                placeholder="email@example.com"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
        </div>

        <!-- Password -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                Password
                <span v-if="!isEdit" class="text-red-400">*</span>
                <span v-else class="text-slate-400 font-normal">(kosongkan jika tidak ubah)</span>
            </label>
            <input
                v-model="form.password"
                type="password"
                placeholder="Minimal 8 karakter"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.password }"
            />
            <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
        </div>

        <!-- Role -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Role</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.role" value="admin" class="radio radio-sm radio-primary" />
                    <span class="text-sm">Admin</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.role" value="user" class="radio radio-sm radio-primary" />
                    <span class="text-sm">User</span>
                </label>
            </div>
        </div>

        <!-- Status Aktif -->
        <div class="flex items-center justify-between py-2 px-3.5 bg-slate-50 rounded-xl">
            <div>
                <p class="text-sm font-medium text-slate-700">Status Aktif</p>
                <p class="text-xs text-slate-400">User aktif bisa login ke dashboard</p>
            </div>
            <input type="checkbox" v-model="form.is_active" class="toggle toggle-sm toggle-primary" />
        </div>

        <!-- Submit -->
        <button
            type="submit"
            :disabled="processing"
            class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
        >
            {{ processing ? 'Menyimpan...' : (isEdit ? 'Update User' : 'Tambah User') }}
        </button>
    </form>
</template>
