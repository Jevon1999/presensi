<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BotSettings from './Partials/BotSettings.vue'
import MessageTemplates from './Partials/MessageTemplates.vue'
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
            <p class="text-sm text-slate-400 mt-0.5">Kelola pengaturan bot & kirim pesan via WhatsApp</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Left Column -->
            <div class="space-y-4">
                <BotSettings :config="config" />
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <SendMessage />
                <MessageTemplates :config="config" />
            </div>
        </div>
    </div>
</template>
