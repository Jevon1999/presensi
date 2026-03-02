<script setup>
import { Head } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: MemberLayout })

const props = defineProps({
    progresses: Object,
    error: String,
})

const progressList = props.progresses?.data || []

const formatDate = (d) => {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <Head title="Progress Saya" />

    <div>
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-800">Progress Saya</h1>
            <p class="text-sm text-slate-400 mt-0.5">Riwayat progres harian yang tercatat</p>
        </div>

        <!-- Error -->
        <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
            <span class="material-symbols-rounded text-red-500 text-[20px]">error</span>
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <!-- Progress List -->
        <div v-if="progressList.length" class="space-y-3">
            <div v-for="p in progressList" :key="p.id" class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="flex items-start justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-400">{{ formatDate(p.tanggal) }}</p>
                </div>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ p.deskripsi || p.keterangan || '-' }}</p>
            </div>
        </div>

        <div v-else class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <span class="material-symbols-rounded text-slate-300 text-[40px] mb-3">description</span>
            <p class="text-sm font-semibold text-slate-500">Belum ada progress</p>
            <p class="text-xs text-slate-400 mt-1">Progress harian Anda akan muncul di sini.</p>
        </div>

        <!-- Pagination -->
        <Pagination v-if="progresses?.last_page > 1" :data="progresses" class="mt-4" />
    </div>
</template>
