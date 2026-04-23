<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    holidays:     { type: Array,  default: () => [] },
    total:        { type: Number, default: 0 },
    selectedYear: { type: Number, default: new Date().getFullYear() },
})

// ─── Year selector ───
const currentYear = new Date().getFullYear()
const yearOptions = [currentYear - 1, currentYear, currentYear + 1]
const goToYear = (y) => router.get('/holidays', { year: y }, { preserveState: false })

// ─── Sync ───
const syncing    = ref(false)
const syncResult = ref(null)
const syncError  = ref(null)

const doSync = async () => {
    syncing.value    = true
    syncResult.value = null
    syncError.value  = null

    try {
        const res = await axios.post('/holidays/sync', { year: props.selectedYear })
        syncResult.value = res.data?.message ?? 'Sync berhasil.'
        // Reload halaman agar list diperbarui
        router.reload({ only: ['holidays', 'total'] })
    } catch (e) {
        syncError.value = e.response?.data?.message ?? 'Gagal sync. Coba lagi.'
    } finally {
        syncing.value = false
    }
}

// ─── Add manual ───
const showForm   = ref(false)
const addForm    = useForm({ tanggal: '', nama: '' })

const submitAdd = () => {
    addForm.post('/holidays', {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false
            addForm.reset()
        },
    })
}

// ─── Delete ───
const deleting = ref(null)

const doDelete = (id) => {
    if (!confirm('Hapus hari libur ini?')) return
    deleting.value = id
    router.delete(`/holidays/${id}`, {
        preserveScroll: true,
        onFinish: () => { deleting.value = null },
    })
}

// ─── Hari Indonesia ───
const dayNames = { '0': 'Minggu', '1': 'Senin', '2': 'Selasa', '3': 'Rabu', '4': 'Kamis', '5': 'Jumat', '6': 'Sabtu' }
const formatDate = (str) => {
    const d = new Date(str + 'T00:00:00')
    const day = dayNames[d.getDay()]
    return `${day}, ${d.getDate().toString().padStart(2, '0')}/${(d.getMonth()+1).toString().padStart(2, '0')}/${d.getFullYear()}`
}
const monthName = (str) => {
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
    return months[new Date(str + 'T00:00:00').getMonth()]
}
const dayNum = (str) => new Date(str + 'T00:00:00').getDate()
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Hari Libur Nasional</h1>
                <p class="text-sm text-slate-400 mt-0.5">
                    Master tanggal merah · Sumber: libur.deno.dev
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Year tabs -->
                <div class="flex items-center bg-slate-100 rounded-xl p-1 gap-1">
                    <button
                        v-for="y in yearOptions" :key="y"
                        @click="goToYear(y)"
                        :class="[
                            selectedYear === y
                                ? 'bg-white text-blue-600 font-semibold shadow-sm'
                                : 'text-slate-500 hover:text-slate-700',
                            'px-3 py-1.5 rounded-lg text-sm transition-all'
                        ]"
                    >{{ y }}</button>
                </div>

                <!-- Sync button -->
                <button
                    @click="doSync"
                    :disabled="syncing"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <span class="material-symbols-rounded text-[18px]" :class="{ 'animate-spin': syncing }">sync</span>
                    {{ syncing ? 'Mengambil data...' : 'Sync dari Internet' }}
                </button>

                <!-- Add manual button -->
                <button
                    @click="showForm = !showForm"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <span class="material-symbols-rounded text-[18px]">add</span>
                    Tambah Manual
                </button>
            </div>
        </div>

        <!-- Sync feedback -->
        <div v-if="syncResult" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-center gap-2">
            <span class="material-symbols-rounded text-[18px]">check_circle</span>
            {{ syncResult }}
        </div>
        <div v-if="syncError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-center gap-2">
            <span class="material-symbols-rounded text-[18px]">error</span>
            {{ syncError }}
        </div>

        <!-- Add form (inline) -->
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="showForm" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-2xl">
                <p class="text-sm font-semibold text-blue-800 mb-3">Tambah Hari Libur Manual</p>
                <form @submit.prevent="submitAdd" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-40">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                        <input
                            v-model="addForm.tanggal"
                            type="date"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white focus:border-blue-400 outline-none"
                        />
                        <p v-if="addForm.errors.tanggal" class="text-xs text-red-500 mt-1">{{ addForm.errors.tanggal }}</p>
                    </div>
                    <div class="flex-[2] min-w-52">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Hari Libur</label>
                        <input
                            v-model="addForm.nama"
                            type="text"
                            required
                            placeholder="Contoh: Hari Kemerdekaan"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white focus:border-blue-400 outline-none"
                        />
                        <p v-if="addForm.errors.nama" class="text-xs text-red-500 mt-1">{{ addForm.errors.nama }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            :disabled="addForm.processing"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors"
                        >
                            {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                        <button
                            type="button"
                            @click="showForm = false; addForm.reset()"
                            class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-600 text-sm rounded-xl border border-slate-200 transition-colors"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </Transition>

        <!-- Stats card -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                    <span class="material-symbols-rounded text-red-500 text-[20px]">calendar_today</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ total }}</p>
                    <p class="text-xs text-slate-400">Total hari libur {{ selectedYear }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <span class="material-symbols-rounded text-blue-500 text-[20px]">public</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">
                        {{ holidays.filter(h => h.source === 'libur.deno.dev').length }}
                    </p>
                    <p class="text-xs text-slate-400">Dari internet</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <span class="material-symbols-rounded text-amber-500 text-[20px]">edit_note</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">
                        {{ holidays.filter(h => h.source === 'manual').length }}
                    </p>
                    <p class="text-xs text-slate-400">Input manual</p>
                </div>
            </div>
        </div>

        <!-- Holiday list -->
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
            <!-- Empty state -->
            <div v-if="holidays.length === 0" class="py-16 flex flex-col items-center text-center gap-3">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <span class="material-symbols-rounded text-slate-400 text-[32px]">event_busy</span>
                </div>
                <p class="text-slate-700 font-semibold">Belum ada data hari libur</p>
                <p class="text-sm text-slate-400">Klik "Sync dari Internet" untuk mengambil data tahun {{ selectedYear }}</p>
            </div>

            <!-- Table header -->
            <div v-else class="hidden sm:grid grid-cols-12 gap-4 px-5 py-3 bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <div class="col-span-2">Tanggal</div>
                <div class="col-span-7">Nama Hari Libur</div>
                <div class="col-span-2">Sumber</div>
                <div class="col-span-1 text-right">Aksi</div>
            </div>

            <!-- Rows -->
            <div
                v-for="(h, i) in holidays"
                :key="h.id"
                :class="[i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50', 'border-b border-slate-50 last:border-0']"
                class="grid grid-cols-12 gap-4 px-5 py-3.5 items-center hover:bg-blue-50/30 transition-colors"
            >
                <!-- Date badge -->
                <div class="col-span-12 sm:col-span-2">
                    <div class="flex items-center gap-2 sm:block">
                        <div class="w-11 h-11 rounded-xl bg-red-50 border border-red-100 flex flex-col items-center justify-center shrink-0">
                            <span class="text-[10px] font-bold text-red-400 uppercase leading-none">{{ monthName(h.tanggal) }}</span>
                            <span class="text-lg font-black text-red-600 leading-none">{{ dayNum(h.tanggal) }}</span>
                        </div>
                        <span class="sm:hidden text-sm font-medium text-slate-700">{{ h.nama }}</span>
                    </div>
                </div>

                <!-- Name -->
                <div class="hidden sm:block col-span-7">
                    <p class="text-sm font-semibold text-slate-700">{{ h.nama }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ formatDate(h.tanggal) }}</p>
                </div>

                <!-- Source badge -->
                <div class="col-span-10 sm:col-span-2">
                    <span
                        :class="[
                            h.source === 'manual'
                                ? 'bg-amber-50 text-amber-600 border-amber-200'
                                : 'bg-blue-50 text-blue-600 border-blue-200',
                            'px-2 py-0.5 rounded-full text-[10px] font-semibold border'
                        ]"
                    >
                        {{ h.source === 'manual' ? '✏ Manual' : '🌐 Internet' }}
                    </span>
                </div>

                <!-- Actions -->
                <div class="col-span-2 sm:col-span-1 flex justify-end">
                    <button
                        @click="doDelete(h.id)"
                        :disabled="deleting === h.id"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors disabled:opacity-40"
                        title="Hapus"
                    >
                        <span class="material-symbols-rounded text-[18px]">delete</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
