<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BotSettings from './Partials/BotSettings.vue'
import SendMessage from './Partials/SendMessage.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    config: { type: Object, default: () => ({}) },
})
</script>

<template>
    <div>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-800">Bot WhatsApp</h1>
            <p class="text-sm text-slate-400 mt-0.5">Kelola pengaturan bot &amp; kirim pesan via WhatsApp</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Left Column: Bot Settings + Templates (merged) -->
            <BotSettings :config="config" />

            <!-- Right Column: Send Message -->
            <div class="space-y-4">
                <SendMessage />
            </div>
        </div>
    </div>
</template>
