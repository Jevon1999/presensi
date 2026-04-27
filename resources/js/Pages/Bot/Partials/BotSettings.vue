<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import { watch } from 'vue'
import Toast from '@/Components/Toast.vue'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

const props = defineProps({
    config: { type: Object, default: () => ({}) },
})

const page = usePage()
const flash = computed(() => page.props.flash)

// Edit mode for template section
const editingTemplates = ref(false)

const form = useForm({
    waha_session_name:         props.config?.waha_session_name || 'default',
    waha_api_key:              props.config?.waha_api_key || '',
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

const showApiKey = ref(false)

watch(() => props.config, (c) => {
    if (c) {
        form.waha_session_name         = c.waha_session_name || 'default'
        form.waha_api_key              = c.waha_api_key || ''
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

// Toggle save: wait for nextTick so v-model has updated
const toggleSave = () => {
    nextTick(() => {
        save()
    })
}

// Auto-resize textarea helper
const autoResize = (event) => {
    const el = event.target
    el.style.height = 'auto'
    el.style.height = el.scrollHeight + 'px'
}

// Init auto-resize on mount for existing content
onMounted(() => {
    nextTick(() => {
        document.querySelectorAll('.auto-resize-textarea').forEach(el => {
            el.style.height = 'auto'
            el.style.height = el.scrollHeight + 'px'
        })
    })
})

const startEditing = () => {
    editingTemplates.value = true
    nextTick(() => {
        document.querySelectorAll('.auto-resize-textarea').forEach(el => {
            el.style.height = 'auto'
            el.style.height = el.scrollHeight + 'px'
        })
    })
}

const saveTemplates = () => {
    editingTemplates.value = false
    save()
}
</script>

<template>
    <div class="space-y-4">
        <!-- Pengaturan Bot -->
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

            <form @submit.prevent="save" class="space-y-3">

                <!-- Status Bot -->
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Status Bot</p>
                        <p class="text-xs text-slate-400">Bot aktif akan merespons pesan masuk</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold" :class="form.is_active ? 'text-blue-600' : 'text-slate-400'">
                            {{ form.is_active ? 'ON' : 'OFF' }}
                        </span>
                        <input type="checkbox" v-model="form.is_active" @change="toggleSave" class="toggle toggle-sm toggle-primary" />
                    </div>
                </div>

                <!-- Pengingat Check-In -->
                <div class="bg-slate-50 rounded-xl p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Pengingat Check-In</p>
                            <p class="text-xs text-slate-400">Kirim pengingat absensi pagi ke anggota</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold" :class="form.reminder_enabled ? 'text-blue-600' : 'text-slate-400'">
                                {{ form.reminder_enabled ? 'ON' : 'OFF' }}
                            </span>
                            <input type="checkbox" v-model="form.reminder_enabled" @change="toggleSave" class="toggle toggle-sm toggle-primary" />
                        </div>
                    </div>
                    <div v-if="form.reminder_enabled">
                        <label class="block text-xs text-slate-500 mb-1">Jam Pengingat</label>
                        <flat-pickr
                            v-model="form.reminder_time"
                            :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, disableMobile: true }"
                            class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none w-28"
                            @on-change="save"
                        />
                    </div>
                </div>

                <!-- Pengingat Check-Out -->
                <div class="bg-slate-50 rounded-xl p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Pengingat Check-Out</p>
                            <p class="text-xs text-slate-400">Kirim pengingat absensi pulang ke anggota</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold" :class="form.checkout_reminder_enabled ? 'text-blue-600' : 'text-slate-400'">
                                {{ form.checkout_reminder_enabled ? 'ON' : 'OFF' }}
                            </span>
                            <input type="checkbox" v-model="form.checkout_reminder_enabled" @change="toggleSave" class="toggle toggle-sm toggle-primary" />
                        </div>
                    </div>
                    <div v-if="form.checkout_reminder_enabled">
                        <label class="block text-xs text-slate-500 mb-1">Jam Pengingat</label>
                        <flat-pickr
                            v-model="form.checkout_reminder_time"
                            :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, disableMobile: true }"
                            class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 bg-white text-slate-700 focus:border-blue-400 outline-none w-28"
                            @on-change="save"
                        />
                    </div>
                </div>

                <!-- Batas Jam Terlambat -->
                <div class="bg-amber-50 rounded-xl p-3 space-y-2 border border-amber-100">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-[16px] text-amber-500">schedule</span>
                        <p class="text-sm font-semibold text-slate-700">Batas Jam Terlambat</p>
                    </div>
                    <p class="text-xs text-slate-500">Check-in setelah jam ini dianggap <strong>terlambat</strong></p>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Jam Batas Telat</label>
                        <flat-pickr
                            v-model="form.check_in_late_threshold"
                            :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, disableMobile: true }"
                            class="px-3 py-1.5 text-sm rounded-lg border border-amber-200 bg-white text-slate-700 focus:border-amber-400 outline-none w-28"
                            @on-change="save"
                        />
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Wajib Isi Alasan</p>
                            <p class="text-xs text-slate-400">Harus kirim alasan jika terlambat</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold" :class="form.require_late_reason ? 'text-amber-600' : 'text-slate-400'">
                                {{ form.require_late_reason ? 'ON' : 'OFF' }}
                            </span>
                            <input type="checkbox" v-model="form.require_late_reason" @change="toggleSave" class="toggle toggle-sm toggle-warning" />
                        </div>
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

        <!-- Redaksi & Template Pesan (merged, compact) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <span class="material-symbols-rounded text-[18px] text-blue-500">edit_note</span>
                    Redaksi & Template Pesan
                </h3>
                <button
                    v-if="!editingTemplates"
                    @click="startEditing"
                    type="button"
                    class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                >
                    <span class="material-symbols-rounded text-[14px]">edit</span>
                    Edit
                </button>
                <button
                    v-else
                    @click="saveTemplates"
                    type="button"
                    :disabled="form.processing"
                    class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors disabled:opacity-50"
                >
                    <span v-if="form.processing" class="material-symbols-rounded text-[14px] animate-spin">refresh</span>
                    <span v-else class="material-symbols-rounded text-[14px]">save</span>
                    Simpan
                </button>
            </div>

            <p class="text-[11px] text-slate-500 mb-3 bg-blue-50 p-2 rounded-lg inline-block">
                Variabel: <code class="font-bold text-blue-700">{nama}</code>
                <code class="font-bold text-blue-700 ml-1">{tanggal}</code>
                <code class="font-bold text-blue-700 ml-1">{kantor}</code>
            </p>

            <div class="space-y-3">
                <!-- Pengingat Check-In -->
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 mb-1">
                        <span class="material-symbols-rounded text-[14px] text-slate-400">alarm</span>
                        Pengingat Check-In
                    </label>
                    <textarea
                        v-if="editingTemplates"
                        v-model="form.message_remind_check_in"
                        @input="autoResize"
                        class="auto-resize-textarea w-full px-3 py-2 text-sm rounded-lg border border-blue-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none resize-none overflow-hidden transition-all"
                    ></textarea>
                    <p v-else class="text-xs text-slate-600 leading-relaxed bg-slate-50 rounded-lg p-3 border border-slate-100 whitespace-pre-wrap">{{ form.message_remind_check_in }}</p>
                </div>

                <!-- Pengingat Check-Out -->
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 mb-1">
                        <span class="material-symbols-rounded text-[14px] text-slate-400">alarm_off</span>
                        Pengingat Check-Out
                    </label>
                    <textarea
                        v-if="editingTemplates"
                        v-model="form.message_remind_check_out"
                        @input="autoResize"
                        class="auto-resize-textarea w-full px-3 py-2 text-sm rounded-lg border border-blue-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none resize-none overflow-hidden transition-all"
                    ></textarea>
                    <p v-else class="text-xs text-slate-600 leading-relaxed bg-slate-50 rounded-lg p-3 border border-slate-100 whitespace-pre-wrap">{{ form.message_remind_check_out }}</p>
                </div>

                <!-- Notifikasi Alpha / Telat -->
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 mb-1">
                        <span class="material-symbols-rounded text-[14px] text-amber-500">warning</span>
                        Notifikasi Alpha / Telat
                    </label>
                    <textarea
                        v-if="editingTemplates"
                        v-model="form.message_remind_late"
                        @input="autoResize"
                        class="auto-resize-textarea w-full px-3 py-2 text-sm rounded-lg border border-blue-200 bg-white text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none resize-none overflow-hidden transition-all"
                    ></textarea>
                    <p v-else class="text-xs text-slate-600 leading-relaxed bg-slate-50 rounded-lg p-3 border border-slate-100 whitespace-pre-wrap">{{ form.message_remind_late }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
