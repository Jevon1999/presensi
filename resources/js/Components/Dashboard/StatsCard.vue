<script setup>
defineProps({
    title: String,
    value: [String, Number],
    subtitle: String,
    icon: String,
    color: {
        type: String,
        default: 'primary'
    }
});
</script>

<template>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm opacity-70 mb-1">{{ title }}</p>
                    <h2 class="text-3xl font-bold" :class="`text-${color}`">{{ value }}</h2>
                    <p class="text-xs opacity-60 mt-1">{{ subtitle }}</p>
                </div>
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" :class="`bg-${color} bg-opacity-10`">
                        <svg class="w-6 h-6" :class="`text-${color}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
