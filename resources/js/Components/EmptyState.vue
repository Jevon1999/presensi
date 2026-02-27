<script setup>
defineProps({
    icon: { type: String, default: 'inbox' },
    title: { type: String, default: 'Tidak ada data' },
    description: { type: String, default: '' },
})
</script>

<template>
    <div class="text-center py-12">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-rounded text-slate-300 text-[32px]">{{ icon }}</span>
        </div>
        <h3 class="text-sm font-semibold text-slate-500 mb-1">{{ title }}</h3>
        <p v-if="description" class="text-xs text-slate-400 max-w-xs mx-auto">{{ description }}</p>
    </div>
</template>
