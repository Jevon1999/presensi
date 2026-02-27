<script setup>
const props = defineProps({
    show: Boolean,
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, default: 'Apakah Anda yakin?' },
    confirmText: { type: String, default: 'Hapus' },
    confirmClass: { type: String, default: 'bg-red-500 hover:bg-red-600 text-white' },
    processing: Boolean,
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('cancel')" />
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="show" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-rounded text-red-500 text-[24px]">warning</span>
                        </div>
                        <h3 class="text-center font-bold text-lg mb-2">{{ title }}</h3>
                        <p class="text-center text-sm text-slate-500 mb-6">{{ message }}</p>
                        <div class="flex gap-3">
                            <button
                                @click="$emit('cancel')"
                                class="flex-1 py-2.5 text-sm font-medium rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                @click="$emit('confirm')"
                                :disabled="processing"
                                :class="confirmClass"
                                class="flex-1 py-2.5 text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
                            >
                                {{ processing ? 'Memproses...' : confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
