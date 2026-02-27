<script setup>
import { ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'
import logo from '../../../images/logo_global.png'

const page = usePage()

const form = useForm({
    email: '',
    password: '',
})

const showPassword = ref(false)

const submit = () => {
    form.clearErrors()
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Login" />

    <div class="min-h-dvh flex items-center justify-center px-4 py-8 bg-linear-to-br from-slate-50 via-blue-50/30 to-slate-100">
        <!-- Login Card -->
        <div class="w-full max-w-sm">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <img :src="logo" alt="Global Intermedia" class="w-16 h-16 object-contain" />
                </div>
                <h1 class="text-2xl font-bold">Presensi GI</h1>
                <p class="text-sm text-slate-500 mt-1">Masuk ke dashboard anda</p>
            </div>

            <!-- Flash Success -->
            <Toast
                :message="page.props.flash?.success"
                type="success"
                :show="!!page.props.flash?.success"
                class="mb-4"
            />

            <!-- Flash Error -->
            <Toast
                :message="page.props.flash?.error"
                type="error"
                :show="!!page.props.flash?.error"
                class="mb-4"
            />

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-6 space-y-5">
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">mail</span>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            placeholder="nama@email.com"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all"
                            :class="{ 'border-red-300 bg-red-50/50': form.errors.email }"
                        />
                    </div>
                    <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">lock</span>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all"
                            :class="{ 'border-red-300 bg-red-50/50': form.errors.password }"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <span class="material-symbols-rounded text-[20px]">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white py-2.5 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-200 shadow-lg shadow-blue-500/25 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span>{{ form.processing ? 'Memproses...' : 'Masuk' }}</span>
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-6">
                &copy; {{ new Date().getFullYear() }} Global Intermedia
            </p>
        </div>
    </div>
</template>
