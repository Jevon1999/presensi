<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
    member: { type: Object, default: null },
    offices: { type: Array, default: () => [] },
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const isEdit = !!props.member

const form = useForm({
    nama_lengkap: props.member?.nama_lengkap || '',
    no_hp: props.member?.no_hp || '',
    jenis_kelamin: props.member?.jenis_kelamin || 'L',
    asal_sekolah: props.member?.asal_sekolah || '',
    office_id: props.member?.office_id || '',
    tanggal_mulai_magang: props.member?.tanggal_mulai_magang || '',
    tanggal_selesai_magang: props.member?.tanggal_selesai_magang || '',
    status_aktif: props.member?.status_aktif ?? true,
})

watch(() => props.member, (m) => {
    if (m) {
        form.nama_lengkap = m.nama_lengkap || ''
        form.no_hp = m.no_hp || ''
        form.jenis_kelamin = m.jenis_kelamin || 'L'
        form.asal_sekolah = m.asal_sekolah || ''
        form.office_id = m.office_id || ''
        form.tanggal_mulai_magang = m.tanggal_mulai_magang || ''
        form.tanggal_selesai_magang = m.tanggal_selesai_magang || ''
        form.status_aktif = m.status_aktif ?? true
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
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
            <input
                v-model="form.nama_lengkap"
                type="text"
                placeholder="Masukkan nama lengkap"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.nama_lengkap }"
            />
            <p v-if="form.errors.nama_lengkap" class="text-xs text-red-500 mt-1">{{ form.errors.nama_lengkap }}</p>
        </div>

        <!-- No HP -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. HP <span class="text-red-400">*</span></label>
            <input
                v-model="form.no_hp"
                type="text"
                placeholder="08xxxxxxxxxx"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.no_hp }"
            />
            <p v-if="form.errors.no_hp" class="text-xs text-red-500 mt-1">{{ form.errors.no_hp }}</p>
        </div>

        <!-- Jenis Kelamin -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Kelamin</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.jenis_kelamin" value="L" class="radio radio-sm radio-primary" />
                    <span class="text-sm">Laki-laki</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.jenis_kelamin" value="P" class="radio radio-sm radio-primary" />
                    <span class="text-sm">Perempuan</span>
                </label>
            </div>
        </div>

        <!-- Asal Sekolah -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Asal Sekolah</label>
            <input
                v-model="form.asal_sekolah"
                type="text"
                placeholder="Nama sekolah / kampus"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
            />
        </div>

        <!-- Kantor -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kantor <span class="text-red-400">*</span></label>
            <select
                v-model="form.office_id"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white"
                :class="{ 'border-red-300 bg-red-50': form.errors.office_id }"
            >
                <option value="">Pilih kantor</option>
                <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
            </select>
            <p v-if="form.errors.office_id" class="text-xs text-red-500 mt-1">{{ form.errors.office_id }}</p>
        </div>

        <!-- Tanggal Mulai -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Mulai Magang</label>
                <input
                    v-model="form.tanggal_mulai_magang"
                    type="date"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Selesai Magang</label>
                <input
                    v-model="form.tanggal_selesai_magang"
                    type="date"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                />
            </div>
        </div>

        <!-- Status Aktif (edit only) -->
        <div v-if="isEdit">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.status_aktif" class="toggle toggle-sm toggle-primary" />
                <span class="text-sm font-medium">{{ form.status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
            </label>
        </div>

        <!-- Submit -->
        <div class="pt-2">
            <button
                type="submit"
                :disabled="form.processing || processing"
                class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
            >
                {{ (form.processing || processing) ? 'Menyimpan...' : (isEdit ? 'Update' : 'Simpan') }}
            </button>
        </div>
    </form>
</template>
