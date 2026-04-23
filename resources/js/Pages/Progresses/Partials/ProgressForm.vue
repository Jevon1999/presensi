<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch, ref, computed } from 'vue'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

const formatYMD = (d) => {
    const y   = d.getFullYear()
    const m   = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

const props = defineProps({
    progress: { type: Object, default: null },
    members:  { type: Array,  default: () => [] },
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const isEdit = !!props.progress

// ─── Tipe config ───
const tipeOptions = [
    {
        value: 'hadir',
        label: 'Hadir',
        desc: 'Laporan kegiatan kerja hari ini',
        icon: 'work',
        color: 'blue',
        placeholder: 'Deskripsikan kegiatan / pekerjaan yang diselesaikan hari ini...',
        required: true,
    },
    {
        value: 'sakit',
        label: 'Sakit',
        desc: 'Tidak masuk karena sakit',
        icon: 'medical_services',
        color: 'red',
        placeholder: 'Sebutkan alasan / keluhan sakit Anda...',
        required: true,
    },
    {
        value: 'izin',
        label: 'Izin',
        desc: 'Izin pulang / tidak hadir',
        icon: 'assignment_late',
        color: 'amber',
        placeholder: 'Alasan izin (contoh: keperluan keluarga, janji dokter...)',
        required: false,
    },
]

const selectedTipe = computed(() => tipeOptions.find(t => t.value === form.tipe) ?? tipeOptions[0])

const form = useForm({
    member_id:   props.progress?.member_id   || '',
    tanggal:     props.progress?.tanggal     || formatYMD(new Date()),
    tipe:        props.progress?.tipe        || 'hadir',
    description: props.progress?.description || '',
})

// Saat tipe berubah, atur default description untuk izin
watch(() => form.tipe, (newTipe) => {
    if (newTipe === 'izin' && !form.description) {
        form.description = 'Pulang'
    }
    if (newTipe === 'hadir' && form.description === 'Pulang') {
        form.description = ''
    }
})

// ─── Searchable member ───
const memberSearch        = ref('')
const showMemberDropdown  = ref(false)

const filteredMembers = computed(() => {
    if (!memberSearch.value) return props.members
    const s = memberSearch.value.toLowerCase()
    return props.members.filter(m =>
        m.nama_lengkap.toLowerCase().includes(s) ||
        (m.no_hp && m.no_hp.includes(s))
    )
})
const selectedMember = computed(() => props.members.find(m => m.id === form.member_id))
const selectMember = (m) => {
    form.member_id        = m.id
    memberSearch.value    = ''
    showMemberDropdown.value = false
}

watch(() => props.progress, (p) => {
    if (p) {
        form.member_id   = p.member_id   || ''
        form.tanggal     = p.tanggal     || formatYMD(new Date())
        form.tipe        = p.tipe        || 'hadir'
        form.description = p.description || ''
    }
})

const submit = () => emit('submit', form)
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5" @click="showMemberDropdown = false">

        <!-- ─── Anggota ─── -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                Anggota <span class="text-red-400">*</span>
            </label>

            <div v-if="!isEdit" class="relative">
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">person_search</span>
                    <input
                        type="text"
                        v-model="memberSearch"
                        @click.stop="showMemberDropdown = true"
                        placeholder="Cari nama anggota..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                        :class="{ 'border-red-300 bg-red-50': form.errors.member_id }"
                    />
                </div>

                <!-- Selected pill -->
                <div v-if="selectedMember && !memberSearch"
                    class="mt-2 flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-xl"
                >
                    <div class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                        {{ (selectedMember.nama_lengkap || '?')[0].toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-800 truncate">{{ selectedMember.nama_lengkap }}</p>
                        <p class="text-[10px] text-blue-500">{{ selectedMember.office?.name || '-' }}</p>
                    </div>
                    <button type="button" @click.stop="form.member_id = ''" class="text-blue-400 hover:text-blue-600">
                        <span class="material-symbols-rounded text-[16px]">close</span>
                    </button>
                </div>

                <!-- Dropdown -->
                <div v-if="showMemberDropdown" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto" @click.stop>
                    <div v-for="m in filteredMembers" :key="m.id"
                        @click.stop="selectMember(m)"
                        class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-50 last:border-0"
                    >
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 text-xs font-bold shrink-0">
                            {{ (m.nama_lengkap || '?')[0].toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ m.nama_lengkap }}</p>
                            <p class="text-[10px] text-slate-400">{{ m.no_hp || '-' }} &middot; {{ m.office?.name || '-' }}</p>
                        </div>
                    </div>
                    <div v-if="filteredMembers.length === 0" class="px-4 py-4 text-center text-sm text-slate-400">
                        Tidak ada anggota ditemukan
                    </div>
                </div>
            </div>

            <!-- Edit mode: readonly -->
            <div v-else class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-100 bg-slate-50">
                <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600 text-xs font-bold shrink-0">
                    {{ (selectedMember?.nama_lengkap || '?')[0].toUpperCase() }}
                </div>
                <span class="text-sm font-medium text-slate-600">{{ selectedMember?.nama_lengkap || '-' }}</span>
            </div>
            <input type="hidden" v-model="form.member_id" required />
            <p v-if="form.errors.member_id" class="text-xs text-red-500 mt-1.5">{{ form.errors.member_id }}</p>
        </div>

        <!-- ─── Tanggal ─── -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                Tanggal <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px] pointer-events-none z-10">calendar_today</span>
                <flat-pickr
                    v-model="form.tanggal"
                    :config="{ altInput: true, altFormat: 'd M Y', dateFormat: 'Y-m-d', disableMobile: true }"
                    placeholder="Pilih tanggal"
                    class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                    :class="{ 'border-red-300': form.errors.tanggal }"
                />
            </div>
            <p v-if="form.errors.tanggal" class="text-xs text-red-500 mt-1.5">{{ form.errors.tanggal }}</p>
        </div>

        <!-- ─── Tipe Laporan ─── -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                Tipe Laporan <span class="text-red-400">*</span>
            </label>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="t in tipeOptions"
                    :key="t.value"
                    type="button"
                    @click="form.tipe = t.value"
                    :class="[
                        form.tipe === t.value
                            ? t.value === 'hadir'
                                ? 'border-blue-400 bg-blue-50 text-blue-700'
                                : t.value === 'sakit'
                                    ? 'border-red-400 bg-red-50 text-red-700'
                                    : 'border-amber-400 bg-amber-50 text-amber-700'
                            : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300 hover:bg-slate-50',
                        'flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all cursor-pointer'
                    ]"
                >
                    <span class="material-symbols-rounded text-[22px]">{{ t.icon }}</span>
                    <span class="text-xs font-bold">{{ t.label }}</span>
                    <span class="text-[10px] text-center leading-tight opacity-70">{{ t.desc }}</span>
                </button>
            </div>
            <p v-if="form.errors.tipe" class="text-xs text-red-500 mt-1.5">{{ form.errors.tipe }}</p>
        </div>

        <!-- ─── Keterangan / Deskripsi ─── -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                    {{ form.tipe === 'hadir' ? 'Laporan Kegiatan' : 'Alasan / Keterangan' }}
                    <span class="text-red-400">*</span>
                </label>
                <!-- Hint badge -->
                <span
                    v-if="form.tipe === 'sakit'"
                    class="text-[10px] font-semibold px-2 py-0.5 bg-red-100 text-red-600 rounded-full"
                >
                    Wajib diisi
                </span>
                <span
                    v-else-if="form.tipe === 'izin'"
                    class="text-[10px] font-semibold px-2 py-0.5 bg-amber-100 text-amber-600 rounded-full"
                >
                    Default: Pulang
                </span>
            </div>

            <!-- Context banner -->
            <div
                v-if="form.tipe === 'sakit'"
                class="mb-2 flex items-start gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700"
            >
                <span class="material-symbols-rounded text-[15px] shrink-0 mt-0.5">info</span>
                <span>Jelaskan keluhan / jenis sakit Anda. Surat dokter wajib diserahkan ke kantor jika lebih dari 2 hari.</span>
            </div>
            <div
                v-else-if="form.tipe === 'izin'"
                class="mb-2 flex items-start gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700"
            >
                <span class="material-symbols-rounded text-[15px] shrink-0 mt-0.5">info</span>
                <span>Izin pulang lebih awal atau tidak hadir. Alasan otomatis diisi "Pulang" jika tidak diubah.</span>
            </div>

            <textarea
                v-model="form.description"
                :rows="form.tipe === 'hadir' ? 5 : 3"
                :placeholder="selectedTipe.placeholder"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                :class="{ 'border-red-300 bg-red-50': form.errors.description }"
            ></textarea>
            <p v-if="form.errors.description" class="text-xs text-red-500 mt-1.5">{{ form.errors.description }}</p>
        </div>

        <!-- ─── Submit ─── -->
        <div class="pt-1">
            <button
                type="submit"
                :disabled="form.processing || processing"
                :class="[
                    form.tipe === 'hadir'
                        ? 'bg-blue-500 hover:bg-blue-600'
                        : form.tipe === 'sakit'
                            ? 'bg-red-500 hover:bg-red-600'
                            : 'bg-amber-500 hover:bg-amber-600',
                    'w-full py-2.5 text-white text-sm font-bold rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center gap-2'
                ]"
            >
                <span class="material-symbols-rounded text-[18px]">{{ isEdit ? 'save' : 'send' }}</span>
                {{ (form.processing || processing) ? 'Menyimpan...' : (isEdit ? 'Update Laporan' : 'Kirim Laporan') }}
            </button>
        </div>
    </form>
</template>
