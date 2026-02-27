<script setup>
import { useMediaQuery } from '@/composables/useMediaQuery'
import SlideOver from './SlideOver.vue'
import BottomSheet from './BottomSheet.vue'

defineProps({
    show: Boolean,
    title: { type: String, default: '' },
})

defineEmits(['close'])

const { isDesktop } = useMediaQuery()
</script>

<template>
    <component
        :is="isDesktop ? SlideOver : BottomSheet"
        :show="show"
        :title="title"
        @close="$emit('close')"
    >
        <slot />
        <template v-if="$slots.footer" #footer>
            <slot name="footer" />
        </template>
    </component>
</template>
