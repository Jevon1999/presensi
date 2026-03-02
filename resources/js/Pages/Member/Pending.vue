<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import logo from '../../../images/logo_global.png'

const page = usePage()

defineProps({
    member: { type: Object, default: null },
})

const refresh = () => {
    router.reload()
}

const logout = () => {
    router.post('/logout')
}
</script>

<template>
    <Head title="Menunggu Persetujuan" />

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

        <div class="max-w-lg mx-auto px-4 py-16 text-center">
            <!-- Pending Icon -->
            <div class="w-20 h-20 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-rounded text-amber-500 text-[36px]">hourglass_top</span>
            </div>

            <h1 class="text-2xl font-bold text-slate-800 mb-3">Menunggu Persetujuan</h1>
            <p class="text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                Pengajuan pendaftaran magang Anda sedang diproses oleh admin. Anda akan mendapatkan akses setelah pengajuan disetujui.
            </p>

            <!-- Member Info Card -->
            <div v-if="member" class="mt-8 bg-white rounded-2xl border border-slate-200 p-5 text-left max-w-sm mx-auto">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Data Pengajuan</h3>
                <div class="space-y-2.5">
                    <div class="flex justify-between">
                        <span class="text-xs text-slate-500">Nama</span>
                        <span class="text-xs font-semibold text-slate-700">{{ member.nama_lengkap }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-slate-500">Kantor</span>
                        <span class="text-xs font-semibold text-slate-700">{{ member.office?.name || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-slate-500">Status</span>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg">
                            <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                            Pending
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button @click="refresh"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors">
                    <span class="material-symbols-rounded text-[18px]">refresh</span>
                    Cek Status
                </button>
            </div>

            <p class="mt-6 text-xs text-slate-400">Jika ada pertanyaan, hubungi admin/pengurus magang.</p>
        </div>
    </div>
</template>
