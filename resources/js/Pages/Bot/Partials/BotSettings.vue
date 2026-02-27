<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
    config: { type: Object, default: () => ({}) },
})

const form = useForm({
    waha_api_key: props.config?.waha_api_key || '',
    waha_session_name: props.config?.waha_session_name || 'default',
    is_active: props.config?.is_active ?? true,
    reminder_enabled: props.config?.reminder_enabled ?? false,
    reminder_time: props.config?.reminder_time || '07:00',
    checkout_reminder_enabled: props.config?.checkout_reminder_enabled ?? false,
    checkout_reminder_time: props.config?.checkout_reminder_time || '17:00',
})

watch(() => props.config, (c) => {
    if (c) {
        form.waha_api_key = c.waha_api_key || ''
        form.waha_session_name = c.waha_session_name || 'default'
        form.is_active = c.is_active ?? true
        form.reminder_enabled = c.reminder_enabled ?? false
        form.reminder_time = c.reminder_time || '07:00'
        form.checkout_reminder_enabled = c.checkout_reminder_enabled ?? false
        form.checkout_reminder_time = c.checkout_reminder_time || '17:00'
    }
}, { immediate: true })

const save = () => {
    form.put('/bot/config', { preserveScroll: true })
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2 mb-4">
            <span class="material-symbols-rounded text-[18px] text-slate-400">settings</span>
            Pengaturan Bot
        </h3>

        <form @submit.prevent="save" class="space-y-4">
            <!-- WAHA API Key -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">WAHA API Key</label>
                <input
                    v-model="form.waha_api_key"
                    type="password"
                    placeholder="Masukkan API key WAHA"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                />
            </div>

            <!-- Session Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Session Name</label>
                <input
                    v-model="form.waha_session_name"
                    type="text"
                    placeholder="default"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                />
            </div>

            <!-- Bot Active -->
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="text-sm font-medium text-slate-700">Bot Aktif</p>
                    <p class="text-xs text-slate-400">Aktifkan/Nonaktifkan bot</p>
                </div>
                <input type="checkbox" v-model="form.is_active" class="toggle toggle-sm toggle-primary" />
            </div>

            <!-- Reminder Check-In -->
            <div class="bg-slate-50 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Pengingat Check-In</p>
                        <p class="text-xs text-slate-400">Kirim pengingat absensi pagi</p>
                    </div>
                    <input type="checkbox" v-model="form.reminder_enabled" class="toggle toggle-sm toggle-primary" />
                </div>
                <div v-if="form.reminder_enabled">
                    <label class="block text-xs text-slate-500 mb-1">Jam Pengingat</label>
                    <input
                        v-model="form.reminder_time"
                        type="time"
                        class="px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none"
                    />
                </div>
            </div>

            <!-- Reminder Check-Out -->
            <div class="bg-slate-50 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Pengingat Check-Out</p>
                        <p class="text-xs text-slate-400">Kirim pengingat absensi pulang</p>
                    </div>
                    <input type="checkbox" v-model="form.checkout_reminder_enabled" class="toggle toggle-sm toggle-primary" />
                </div>
                <div v-if="form.checkout_reminder_enabled">
                    <label class="block text-xs text-slate-500 mb-1">Jam Pengingat</label>
                    <input
                        v-model="form.checkout_reminder_time"
                        type="time"
                        class="px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none"
                    />
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
            >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
            </button>
        </form>
    </div>
</template>
