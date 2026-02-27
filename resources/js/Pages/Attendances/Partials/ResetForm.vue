<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    attendanceId: [Number, String],
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const form = useForm({
    reason: '',
    reset_check_in: true,
    reset_check_out: true,
})

const submit = () => {
    emit('submit', form)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="bg-amber-50 rounded-xl p-3 flex items-start gap-3">
            <span class="material-symbols-rounded text-amber-500 text-[20px] mt-0.5">info</span>
            <p class="text-xs text-amber-700">Reset akan menghapus data check-in/check-out untuk absensi ini. Pastikan alasan reset tercatat.</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alasan Reset <span class="text-red-400">*</span></label>
            <textarea
                v-model="form.reason"
                rows="3"
                placeholder="Jelaskan alasan reset absensi..."
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                :class="{ 'border-red-300 bg-red-50': form.errors.reason }"
            ></textarea>
            <p v-if="form.errors.reason" class="text-xs text-red-500 mt-1">{{ form.errors.reason }}</p>
        </div>

        <div class="space-y-2">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.reset_check_in" class="checkbox checkbox-sm checkbox-primary" />
                <span class="text-sm">Reset Check-In</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.reset_check_out" class="checkbox checkbox-sm checkbox-primary" />
                <span class="text-sm">Reset Check-Out</span>
            </label>
        </div>

        <div class="pt-2">
            <button
                type="submit"
                :disabled="form.processing || processing || !form.reason.trim()"
                class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
            >
                {{ (form.processing || processing) ? 'Memproses...' : 'Reset Absensi' }}
            </button>
        </div>
    </form>
</template>
