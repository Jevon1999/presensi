<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
    message: String,
    type: { type: String, default: 'info' }, // success, error, warning, info
    duration: { type: Number, default: 5000 },
    show: Boolean,
})

const emit = defineEmits(['close'])

const visible = ref(false)

const config = {
    success: { icon: 'check_circle', bg: 'bg-emerald-50 border-emerald-200', text: 'text-emerald-700', iconColor: 'text-emerald-500' },
    error:   { icon: 'error',        bg: 'bg-red-50 border-red-200',         text: 'text-red-700',     iconColor: 'text-red-500' },
    warning: { icon: 'warning',      bg: 'bg-amber-50 border-amber-200',     text: 'text-amber-700',   iconColor: 'text-amber-500' },
    info:    { icon: 'info',         bg: 'bg-blue-50 border-blue-200',       text: 'text-blue-700',    iconColor: 'text-blue-500' },
}

const style = () => config[props.type] || config.info

let timeout = null

const dismiss = () => {
    visible.value = false
    clearTimeout(timeout)
    setTimeout(() => emit('close'), 300)
}

watch(() => props.show, (val) => {
    if (val && props.message) {
        visible.value = true
        clearTimeout(timeout)
        if (props.duration > 0) {
            timeout = setTimeout(dismiss, props.duration)
        }
    } else {
        visible.value = false
    }
}, { immediate: true })
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-2 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-2 scale-95"
    >
        <div
            v-if="visible && message"
            :class="[style().bg, style().text]"
            class="flex items-start gap-3 px-4 py-3 rounded-xl border shadow-sm text-sm"
        >
            <span :class="style().iconColor" class="material-symbols-rounded text-[20px] mt-0.5 shrink-0">{{ style().icon }}</span>
            <p class="flex-1 min-w-0">{{ message }}</p>
            <button
                @click="dismiss"
                class="shrink-0 p-0.5 rounded-lg hover:bg-black/5 transition-colors"
            >
                <span class="material-symbols-rounded text-[16px] opacity-60">close</span>
            </button>
        </div>
    </Transition>
</template>
