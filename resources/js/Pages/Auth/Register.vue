<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'
import logo from '../../../images/logo_global.png'

const page = usePage()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const registerError = ref('')

const errorMessage = computed(() => {
    return registerError.value || page.props.flash?.error || ''
})

const submit = () => {
    form.clearErrors()
    registerError.value = ''
    form.post('/register', {
        onFinish: () => {
            if (form.errors.name) registerError.value = form.errors.name
            else if (form.errors.email) registerError.value = form.errors.email
            else if (form.errors.password) registerError.value = form.errors.password
        },
    })
}
</script>

<template>
    <Head title="Register" />

    <div class="min-h-dvh flex bg-slate-50">
        <!-- Left Panel — branding (hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-[55%] relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-600 items-center justify-center p-12">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/5 rounded-full"></div>
                <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] bg-white/5 rounded-full"></div>
                <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-white/5 rounded-full"></div>
                <div class="absolute top-20 right-20 w-32 h-32 bg-white/10 rounded-2xl rotate-12"></div>
                <div class="absolute bottom-32 left-16 w-20 h-20 bg-white/10 rounded-xl -rotate-12"></div>
            </div>

            <div class="relative z-10 max-w-md text-center">
                <div class="w-20 h-20 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-8 ring-1 ring-white/20">
                    <img :src="logo" alt="Global Intermedia" class="w-14 h-14 object-contain" />
                </div>
                <h1 class="text-3xl xl:text-4xl font-bold text-white mb-3 leading-tight">Presensi GI</h1>
                <p class="text-blue-100/80 text-sm leading-relaxed">Daftar akun untuk mengajukan pendaftaran magang di Global Intermedia.</p>

                <div class="mt-10 space-y-3 text-left">
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 ring-1 ring-white/10">
                        <span class="material-symbols-rounded text-white/90 text-[20px]">person_add</span>
                        <span class="text-sm text-white/90">Buat akun dengan nama & email</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 ring-1 ring-white/10">
                        <span class="material-symbols-rounded text-white/90 text-[20px]">assignment</span>
                        <span class="text-sm text-white/90">Ajukan pendaftaran magang</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 ring-1 ring-white/10">
                        <span class="material-symbols-rounded text-white/90 text-[20px]">check_circle</span>
                        <span class="text-sm text-white/90">Tunggu persetujuan admin</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel — register form -->
        <div class="flex-1 flex items-center justify-center px-5 py-8">
            <div class="w-full max-w-sm">
                <!-- Mobile logo -->
                <div class="text-center mb-8 lg:mb-10">
                    <div class="lg:hidden w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center mx-auto mb-4">
                        <img :src="logo" alt="Global Intermedia" class="w-10 h-10 object-contain" />
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        <span class="lg:hidden">Presensi GI</span>
                        <span class="hidden lg:inline">Buat Akun</span>
                    </h2>
                    <p class="text-sm text-slate-400 mt-1.5">Daftar akun baru untuk sistem presensi</p>
                </div>

                <!-- Flash -->
                <Toast
                    :message="page.props.flash?.success"
                    type="success"
                    :show="!!page.props.flash?.success"
                    class="mb-4"
                />

                <!-- Form Card -->
                <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                    <!-- Error Alert -->
                    <div v-if="errorMessage" class="flex items-center gap-2.5 px-3.5 py-3 bg-red-50 border border-red-200 rounded-xl">
                        <span class="material-symbols-rounded text-red-500 text-[18px] shrink-0">error</span>
                        <p class="text-sm text-red-700">{{ errorMessage }}</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Lengkap</label>
                        <div class="relative">
                            <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">person</span>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                placeholder="Nama lengkap Anda"
                                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                                :class="{ 'border-red-300 bg-red-50': form.errors.name }"
                            />
                        </div>
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                        <div class="relative">
                            <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">mail</span>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="nama@email.com"
                                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                                :class="{ 'border-red-300 bg-red-50': form.errors.email }"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password</label>
                        <div class="relative">
                            <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">lock</span>
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Minimal 8 karakter"
                                class="w-full pl-10 pr-11 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                                :class="{ 'border-red-300 bg-red-50': form.errors.password }"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <span class="material-symbols-rounded text-[18px]">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">lock</span>
                            <input
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Ulangi password"
                                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                            />
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="pt-1">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white py-2.5 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-200 shadow-lg shadow-blue-500/25 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            {{ form.processing ? 'Mendaftar...' : 'Daftar' }}
                        </button>
                    </div>
                </form>

                <!-- Login link -->
                <p class="mt-6 text-center text-sm text-slate-400">
                    Sudah punya akun?
                    <Link href="/" class="font-semibold text-blue-500 hover:text-blue-600 transition-colors">Masuk</Link>
                </p>
            </div>
        </div>
    </div>
</template>
