<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BotSettings from './Partials/BotSettings.vue'
import MessageTemplates from './Partials/MessageTemplates.vue'
import SendMessage from './Partials/SendMessage.vue'
import { ref } from 'vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    config: { type: Object, default: () => ({}) },
})

// Shared ref: when admin clicks a template, its text gets injected here
// and SendMessage watches this to fill the textarea
const injectedMessage = ref('')

const onTemplateSelect = (text) => {
    injectedMessage.value = text
}
</script>

<template>
    <div>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-800">Bot WhatsApp</h1>
            <p class="text-sm text-slate-400 mt-0.5">Kelola pengaturan bot &amp; kirim pesan via WhatsApp</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Left Column -->
            <div class="space-y-4">
                <BotSettings :config="config" />
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <SendMessage :injected-message="injectedMessage" />
                <MessageTemplates :config="config" @select-template="onTemplateSelect" />
            </div>
        </div>
    </div>
</template>
