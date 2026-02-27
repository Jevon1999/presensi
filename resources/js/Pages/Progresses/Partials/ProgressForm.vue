<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
    progress: { type: Object, default: null },
    members: { type: Array, default: () => [] },
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const isEdit = !!props.progress

const form = useForm({
    member_id: props.progress?.member_id || '',
    tanggal: props.progress?.tanggal || new Date().toISOString().slice(0, 10),
    description: props.progress?.description || '',
})

watch(() => props.progress, (p) => {
    if (p) {
        form.member_id = p.member_id || ''
        form.tanggal = p.tanggal || new Date().toISOString().slice(0, 10)
        form.description = p.description || ''
    }
})

const submit = () => {
    emit('submit', form)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Anggota -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Anggota <span class="text-red-400">*</span></label>
            <select
                v-model="form.member_id"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white"
                :class="{ 'border-red-300 bg-red-50': form.errors.member_id }"
                :disabled="isEdit"
            >
                <option value="">Pilih anggota</option>
                <option v-for="m in members" :key="m.id" :value="m.id">{{ m.nama_lengkap }}</option>
            </select>
            <p v-if="form.errors.member_id" class="text-xs text-red-500 mt-1">{{ form.errors.member_id }}</p>
        </div>

        <!-- Tanggal -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal <span class="text-red-400">*</span></label>
            <input
                v-model="form.tanggal"
                type="date"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.tanggal }"
            />
            <p v-if="form.errors.tanggal" class="text-xs text-red-500 mt-1">{{ form.errors.tanggal }}</p>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi Progress <span class="text-red-400">*</span></label>
            <textarea
                v-model="form.description"
                rows="5"
                placeholder="Jelaskan progress kerja hari ini..."
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                :class="{ 'border-red-300 bg-red-50': form.errors.description }"
            ></textarea>
            <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
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
