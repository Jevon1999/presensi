<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
    show: Boolean,
    title: { type: String, default: '' },
    maxWidth: { type: String, default: 'max-w-lg' },
})

const emit = defineEmits(['close'])

const panelRef = ref(null)

const close = () => emit('close')

const onKeydown = (e) => {
    if (e.key === 'Escape') close()
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))

watch(() => props.show, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[60] flex justify-end" @mousedown.self="close">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-x-0"
                    leave-to-class="translate-x-full"
                >
                    <div
                        v-if="show"
                        ref="panelRef"
                        :class="maxWidth"
                        class="relative w-full bg-white shadow-2xl flex flex-col h-full"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                            <h3 class="text-lg font-bold">{{ title }}</h3>
                            <button @click="close" class="p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="material-symbols-rounded text-slate-400 text-[20px]">close</span>
                            </button>
                        </div>
                        <!-- Body -->
                        <div class="flex-1 overflow-y-auto p-6">
                            <slot />
                        </div>
                        <!-- Footer -->
                        <div v-if="$slots.footer" class="border-t border-slate-100 px-6 py-4">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
