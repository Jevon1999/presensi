<script setup>
defineProps({
    label: String,
    value: [String, Number],
    trend: String,
    trendUp: {
        type: Boolean,
        default: true
    },
    icon: String,
    colorClass: {
        type: String,
        default: 'text-blue-600'
    },
    bgClass: {
        type: String,
        default: 'bg-blue-50'
    }
});
</script>

<template>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div :class="['p-3 rounded-xl', bgClass, colorClass]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon"/>
                </svg>
            </div>
            <div v-if="trend" :class="[
                'flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-lg',
                trendUp ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'
            ]">
                <svg v-if="trendUp" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
                {{ trend }}
            </div>
        </div>
        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">{{ label }}</p>
        <h3 class="text-2xl font-black mt-1">{{ value }}</h3>
    </div>
</template>
