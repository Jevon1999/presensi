<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import { watch } from 'vue'
import Toast from '@/Components/Toast.vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'

const props = defineProps({
    config: { type: Object, default: () => ({}) },
})

const page = usePage()
const flash = computed(() => page.props.flash)

const form = useForm({
    is_active:                 props.config?.is_active ?? false,
    reminder_enabled:          props.config?.reminder_enabled ?? false,
    reminder_time:             props.config?.reminder_time || '07:00',
    checkout_reminder_enabled: props.config?.checkout_reminder_enabled ?? false,
    checkout_reminder_time:    props.config?.checkout_reminder_time || '17:00',
    check_in_late_threshold:   props.config?.check_in_late_threshold || '09:00',
    require_late_reason:       props.config?.require_late_reason ?? true,
    message_remind_check_in:   props.config?.message_remind_check_in || "Halo {nama}, jangan lupa absen check-in hari ini!",
    message_remind_check_out:  props.config?.message_remind_check_out || "Halo {nama}, waktu pulang telah tiba. Jangan lupa absen check-out!",
    message_remind_late:       props.config?.message_remind_late || "⚠️ *Notifikasi Keterlambatan*\n\nHalo {nama}! Kamu belum check-in hari ini dan melewati batas waktu telat/alpha.",
})

watch(() => props.config, (c) => {
    if (c) {
        form.is_active                 = c.is_active ?? false
        form.reminder_enabled          = c.reminder_enabled ?? false
        form.reminder_time             = c.reminder_time || '07:00'
        form.checkout_reminder_enabled = c.checkout_reminder_enabled ?? false
        form.checkout_reminder_time    = c.checkout_reminder_time || '17:00'
        form.check_in_late_threshold   = c.check_in_late_threshold || '09:00'
        form.require_late_reason       = c.require_late_reason ?? true
        if (c.message_remind_check_in) form.message_remind_check_in = c.message_remind_check_in
        if (c.message_remind_check_out) form.message_remind_check_out = c.message_remind_check_out
        if (c.message_remind_late) form.message_remind_late = c.message_remind_late
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

        <!-- Flash messages -->
        <Toast
            :message="flash?.success"
            type="success"
            :show="!!flash?.success"
            class="mb-4"
        />
        <Toast
            :message="flash?.error"
            type="error"
            :show="!!flash?.error"
            class="mb-4"
        />

        <form @submit.prevent="save" class="space-y-4">

            <!-- Status Bot -->
            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50">
                <div>
                    <p class="text-sm font-semibold text-slate-700">Status Bot</p>
                    <p class="text-xs text-slate-400">Bot aktif akan merespons pesan masuk</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold" :class="form.is_active ? 'text-green-600' : 'text-slate-400'">
                        {{ form.is_active ? 'ON' : 'OFF' }}
                    </span>
                    <input type="checkbox" v-model="form.is_active" @change="save" class="toggle toggle-primary" />
                </div>
            </div>

            <!-- Pengingat Check-In -->
            <div class="bg-slate-50 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Pengingat Check-In</p>
                        <p class="text-xs text-slate-400">Kirim pengingat absensi pagi ke anggota</p>
                    </div>
                    <input type="checkbox" v-model="form.reminder_enabled" @change="save" class="toggle toggle-primary" />
                </div>
                <div v-if="form.reminder_enabled">
                    <label class="block text-xs text-slate-500 mb-1">Jam Pengingat Check-In</label>
                    <VueDatePicker
                        v-model="form.reminder_time"
                        time-picker
                        model-type="HH:mm"
                        :is-24="true"
                        input-class-name="px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none bg-white w-32"
                        @update:model-value="save"
                    />
                </div>
            </div>

            <!-- Pengingat Check-Out -->
            <div class="bg-slate-50 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Pengingat Check-Out</p>
                        <p class="text-xs text-slate-400">Kirim pengingat absensi pulang ke anggota</p>
                    </div>
                    <input type="checkbox" v-model="form.checkout_reminder_enabled" @change="save" class="toggle toggle-primary" />
                </div>
                <div v-if="form.checkout_reminder_enabled">
                    <label class="block text-xs text-slate-500 mb-1">Jam Pengingat Check-Out</label>
                    <VueDatePicker
                        v-model="form.checkout_reminder_time"
                        time-picker
                        model-type="HH:mm"
                        :is-24="true"
                        input-class-name="px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none bg-white w-32"
                        @update:model-value="save"
                    />
                </div>
            </div>

            <!-- Batas Jam Terlambat -->
            <div class="bg-amber-50 rounded-xl p-4 space-y-3 border border-amber-100">
                <div class="flex items-center gap-1.5 mb-1">
                    <span class="material-symbols-rounded text-[16px] text-amber-500">schedule</span>
                    <p class="text-sm font-semibold text-slate-700">Batas Jam Terlambat</p>
                </div>
                <p class="text-xs text-slate-500">Check-in setelah jam ini dianggap <strong>terlambat</strong></p>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Jam Batas Telat</label>
                    <VueDatePicker
                        v-model="form.check_in_late_threshold"
                        time-picker
                        model-type="HH:mm"
                        :is-24="true"
                        input-class-name="px-3.5 py-2 text-sm rounded-xl border border-amber-200 focus:border-amber-400 outline-none bg-white w-32"
                        @update:model-value="save"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Wajib Isi Alasan</p>
                        <p class="text-xs text-slate-400">Harus kirim alasan jika terlambat</p>
                    </div>
                    <input type="checkbox" v-model="form.require_late_reason" @change="save" class="toggle toggle-warning" />
                </div>
            </div>

            <!-- Template Pesan -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="material-symbols-rounded text-[18px] text-blue-500">edit_note</span>
                        <p class="text-sm font-semibold text-slate-700">Pengaturan Redaksi & Template Pesan</p>
                    </div>
                    <p class="text-[11px] text-slate-500 mb-3 bg-blue-50 p-2 rounded-lg inline-block">
                        Gunakan tanda <code class="font-bold text-blue-700">{nama}</code> untuk menyisipkan nama pengguna secara otomatis.
                    </p>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Template Pengingat Check-In</label>
                    <textarea v-model="form.message_remind_check_in" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-blue-400 outline-none" @blur="save"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Template Pengingat Check-Out</label>
                    <textarea v-model="form.message_remind_check_out" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-blue-400 outline-none" @blur="save"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Template Notifikasi Alpha / Telat</label>
                    <textarea v-model="form.message_remind_late" rows="3" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-blue-400 outline-none" @blur="save"></textarea>
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
            >
                <span v-if="form.processing" class="material-symbols-rounded text-[16px] animate-spin">refresh</span>
                {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
            </button>
        </form>
    </div>
</template>
