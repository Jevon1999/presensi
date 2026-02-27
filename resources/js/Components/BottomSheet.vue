<script setup>
import { ref, watch, onUnmounted } from 'vue'

const props = defineProps({
    show: Boolean,
    title: { type: String, default: '' },
    snapPoints: { type: Array, default: () => [0.5, 0.92] },
})

const emit = defineEmits(['close'])

const sheetRef = ref(null)
const currentSnap = ref(1)
const dragging = ref(false)
const startY = ref(0)
const currentY = ref(0)
const sheetHeight = ref(0)

const close = () => emit('close')

const getMaxHeight = () => {
    const snap = props.snapPoints[currentSnap.value] || 0.92
    return window.innerHeight * snap
}

const onTouchStart = (e) => {
    dragging.value = true
    startY.value = e.touches[0].clientY
    sheetHeight.value = sheetRef.value?.offsetHeight || 0
}

const onTouchMove = (e) => {
    if (!dragging.value) return
    const dy = e.touches[0].clientY - startY.value
    currentY.value = Math.max(0, dy)
}

const onTouchEnd = () => {
    dragging.value = false
    if (currentY.value > 100) {
        if (currentSnap.value > 0) {
            currentSnap.value--
            currentY.value = 0
        } else {
            close()
        }
    } else if (currentY.value < -50 && currentSnap.value < props.snapPoints.length - 1) {
        currentSnap.value++
        currentY.value = 0
    } else {
        currentY.value = 0
    }
}

watch(() => props.show, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
    if (val) {
        currentSnap.value = props.snapPoints.length - 1
        currentY.value = 0
    }
})

onUnmounted(() => { document.body.style.overflow = '' })
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
            <div v-if="show" class="fixed inset-0 z-[60]" @mousedown.self="close">
                <div class="absolute inset-0 bg-black/40" @click="close" />
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-y-full"
                    enter-to-class="translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-y-0"
                    leave-to-class="translate-y-full"
                >
                    <div
                        v-if="show"
                        ref="sheetRef"
                        class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-2xl flex flex-col"
                        :class="{ 'transition-all duration-300': !dragging }"
                        :style="{
                            maxHeight: `${(snapPoints[currentSnap] || 0.92) * 100}vh`,
                            transform: `translateY(${currentY}px)`,
                        }"
                    >
                        <!-- Drag handle -->
                        <div
                            class="flex justify-center pt-3 pb-2 cursor-grab active:cursor-grabbing"
                            @touchstart="onTouchStart"
                            @touchmove="onTouchMove"
                            @touchend="onTouchEnd"
                        >
                            <div class="w-10 h-1 rounded-full bg-slate-300" />
                        </div>
                        <!-- Header -->
                        <div v-if="title" class="flex items-center justify-between px-5 pb-3 border-b border-slate-100">
                            <h3 class="text-base font-bold">{{ title }}</h3>
                            <button @click="close" class="p-1 rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="material-symbols-rounded text-slate-400 text-[18px]">close</span>
                            </button>
                        </div>
                        <!-- Body -->
                        <div class="flex-1 overflow-y-auto p-5">
                            <slot />
                        </div>
                        <!-- Footer -->
                        <div v-if="$slots.footer" class="border-t border-slate-100 px-5 py-3 pb-safe">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
