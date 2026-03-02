<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'
import logo from '../../../images/logo_global.png'

const page = usePage()
const user = computed(() => page.props.auth?.user || page.props.user)

const props = defineProps({
    offices: { type: Array, default: () => [] },
    existingMember: { type: Object, default: null },
    error: { type: String, default: null },
})

// If there's a rejected application, show it
const isRejected = computed(() => props.existingMember?.status === 'rejected')
const rejectionReason = computed(() => props.existingMember?.rejection_reason || '')

const form = useForm({
    no_hp: '',
    office_id: '',
    jenis_kelamin: '',
    asal_sekolah: '',
    tanggal_mulai_magang: '',
    tanggal_selesai_magang: '',
})

const submit = () => {
    form.post('/member/apply', {
        preserveScroll: true,
    })
}

const logout = () => {
    router.post('/logout')
}
</script>

<template>
    <Head title="Pendaftaran Magang" />

    <div class="min-h-dvh bg-slate-50">
        <!-- Top Bar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img :src="logo" alt="Logo" class="w-8 h-8 rounded-lg object-contain" />
                    <span class="font-bold text-sm">Presensi GI</span>
                </div>
                <button @click="logout" class="text-sm text-red-500 hover:text-red-600 font-medium flex items-center gap-1">
                    <span class="material-symbols-rounded text-[18px]">logout</span>
                    Logout
                </button>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 py-8">
            <!-- Flash Messages -->
            <Toast :message="page.props.flash?.success" type="success" :show="!!page.props.flash?.success" class="mb-4" />
            <Toast :message="page.props.flash?.error" type="error" :show="!!page.props.flash?.error" class="mb-4" />

            <!-- Rejection Warning -->
            <div v-if="isRejected" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-rounded text-red-500 text-[22px] mt-0.5">block</span>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800">Pengajuan Sebelumnya Ditolak</h3>
                        <p class="text-xs text-red-600 mt-1" v-if="rejectionReason">Alasan: {{ rejectionReason }}</p>
                        <p class="text-xs text-red-600 mt-1">Anda dapat mengajukan ulang dengan melengkapi formulir di bawah.</p>
                    </div>
                </div>
            </div>

            <!-- Existing pending/approved -->
            <div v-if="existingMember && existingMember.status === 'pending'" class="text-center py-16">
                <div class="w-16 h-16 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-rounded text-amber-500 text-[28px]">hourglass_top</span>
                </div>
                <h2 class="text-lg font-bold text-slate-800 mb-2">Pengajuan Sedang Diproses</h2>
                <p class="text-sm text-slate-500 max-w-md mx-auto">Pengajuan pendaftaran magang Anda sedang menunggu persetujuan admin. Silakan cek kembali nanti.</p>
                <Link href="/member/pending" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors">
                    <span class="material-symbols-rounded text-[18px]">visibility</span>
                    Lihat Status
                </Link>
            </div>

            <div v-else-if="existingMember && existingMember.status === 'approved'" class="text-center py-16">
                <div class="w-16 h-16 bg-green-50 border border-green-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-rounded text-green-500 text-[28px]">check_circle</span>
                </div>
                <h2 class="text-lg font-bold text-slate-800 mb-2">Anda Sudah Terdaftar</h2>
                <p class="text-sm text-slate-500">Akun magang Anda sudah aktif.</p>
                <Link href="/member/dashboard" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors">
                    <span class="material-symbols-rounded text-[18px]">dashboard</span>
                    Buka Dashboard
                </Link>
            </div>

            <!-- Application Form -->
            <div v-else>
                <div class="mb-6">
                    <h1 class="text-xl font-bold text-slate-800">Pendaftaran Magang</h1>
                    <p class="text-sm text-slate-400 mt-1">Lengkapi data berikut untuk mengajukan pendaftaran magang di Global Intermedia.</p>
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                    <!-- Info Name -->
                    <div class="flex items-center gap-3 bg-blue-50 rounded-xl px-4 py-3 border border-blue-100">
                        <span class="material-symbols-rounded text-blue-500 text-[18px]">info</span>
                        <p class="text-sm text-blue-700">Nama terdaftar: <strong>{{ user?.name || '-' }}</strong></p>
                    </div>

                    <!-- No HP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor HP (WhatsApp)</label>
                        <div class="relative">
                            <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">phone</span>
                            <input v-model="form.no_hp" type="text" required placeholder="+628xxxxxxxxxx"
                                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                                :class="{ 'border-red-300 bg-red-50': form.errors.no_hp }" />
                        </div>
                        <p v-if="form.errors.no_hp" class="text-xs text-red-500 mt-1">{{ form.errors.no_hp }}</p>
                    </div>

                    <!-- Kantor -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kantor Penempatan</label>
                        <select v-model="form.office_id" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none bg-white"
                            :class="{ 'border-red-300 bg-red-50': form.errors.office_id }">
                            <option value="">Pilih kantor...</option>
                            <option v-for="office in offices" :key="office.id" :value="office.id">{{ office.name }}</option>
                        </select>
                        <p v-if="form.errors.office_id" class="text-xs text-red-500 mt-1">{{ form.errors.office_id }}</p>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Kelamin</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.jenis_kelamin" value="L" class="text-blue-500 focus:ring-blue-400" />
                                <span class="text-sm text-slate-700">Laki-laki</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.jenis_kelamin" value="P" class="text-blue-500 focus:ring-blue-400" />
                                <span class="text-sm text-slate-700">Perempuan</span>
                            </label>
                        </div>
                        <p v-if="form.errors.jenis_kelamin" class="text-xs text-red-500 mt-1">{{ form.errors.jenis_kelamin }}</p>
                    </div>

                    <!-- Asal Sekolah -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Asal Sekolah / Universitas</label>
                        <div class="relative">
                            <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">school</span>
                            <input v-model="form.asal_sekolah" type="text" required placeholder="Nama sekolah atau universitas"
                                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                                :class="{ 'border-red-300 bg-red-50': form.errors.asal_sekolah }" />
                        </div>
                        <p v-if="form.errors.asal_sekolah" class="text-xs text-red-500 mt-1">{{ form.errors.asal_sekolah }}</p>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Mulai Magang</label>
                            <input v-model="form.tanggal_mulai_magang" type="date" required
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none"
                                :class="{ 'border-red-300 bg-red-50': form.errors.tanggal_mulai_magang }" />
                            <p v-if="form.errors.tanggal_mulai_magang" class="text-xs text-red-500 mt-1">{{ form.errors.tanggal_mulai_magang }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Selesai Magang</label>
                            <input v-model="form.tanggal_selesai_magang" type="date"
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none"
                                :class="{ 'border-red-300 bg-red-50': form.errors.tanggal_selesai_magang }" />
                            <p v-if="form.errors.tanggal_selesai_magang" class="text-xs text-red-500 mt-1">{{ form.errors.tanggal_selesai_magang }}</p>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white py-2.5 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-200 shadow-lg shadow-blue-500/25 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            {{ form.processing ? 'Mengirim...' : 'Ajukan Pendaftaran' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
