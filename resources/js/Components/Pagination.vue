<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    links: { type: Array, default: () => [] }, // Laravel pagination links array
    from: { type: Number, default: 0 },
    to: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
})
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4">
        <p class="text-xs text-slate-400">
            Menampilkan <span class="font-semibold text-slate-600">{{ from }}</span>
            - <span class="font-semibold text-slate-600">{{ to }}</span>
            dari <span class="font-semibold text-slate-600">{{ total }}</span>
        </p>
        <div class="flex items-center gap-1">
            <template v-for="(link, i) in links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                    class="min-w-[32px] h-8 px-2 flex items-center justify-center text-xs font-medium rounded-lg transition-colors"
                    :class="link.active ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="min-w-[32px] h-8 px-2 flex items-center justify-center text-xs text-slate-300"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
