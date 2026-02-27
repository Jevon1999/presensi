<script setup>
import { ref } from 'vue'

const props = defineProps({
    config: { type: Object, default: () => ({}) },
})

// Display-only templates from config
const templates = ref([
    {
        key: 'checkin_reminder',
        label: 'Pengingat Check-In',
        icon: 'alarm',
        default: 'Selamat pagi {nama}! Jangan lupa untuk melakukan absensi check-in hari ini. Terima kasih.',
    },
    {
        key: 'checkout_reminder',
        label: 'Pengingat Check-Out',
        icon: 'alarm_off',
        default: 'Halo {nama}, jangan lupa untuk melakukan check-out sebelum pulang. Terima kasih.',
    },
    {
        key: 'absence_notification',
        label: 'Notifikasi Alpha',
        icon: 'warning',
        default: '{nama}, Anda tercatat tidak hadir (alpha) pada tanggal {tanggal}. Jika ada alasan, silakan hubungi admin.',
    },
])
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2 mb-4">
            <span class="material-symbols-rounded text-[18px] text-slate-400">article</span>
            Template Pesan
        </h3>

        <div class="space-y-3">
            <div v-for="t in templates" :key="t.key" class="bg-slate-50 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-rounded text-[16px] text-slate-400">{{ t.icon }}</span>
                    <span class="text-xs font-bold text-slate-600">{{ t.label }}</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed bg-white rounded-lg p-3 border border-slate-100">
                    {{ config?.[t.key + '_template'] || t.default }}
                </p>
                <p class="text-[10px] text-slate-400 mt-2">
                    Variabel: <code class="bg-slate-100 px-1 rounded">{nama}</code>
                    <code class="bg-slate-100 px-1 rounded ml-1">{tanggal}</code>
                    <code class="bg-slate-100 px-1 rounded ml-1">{kantor}</code>
                </p>
            </div>
        </div>
    </div>
</template>
