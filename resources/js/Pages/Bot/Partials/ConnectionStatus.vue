<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    initialStatus: { type: String, default: 'UNKNOWN' },
})

const status = ref(props.initialStatus)
const sessionInfo = ref(null)
const loading = ref(false)
const error = ref('')

const statusMap = {
    WORKING: { label: 'Terhubung', color: 'bg-emerald-100 text-emerald-700', icon: 'check_circle', dot: 'bg-emerald-500' },
    SCAN_QR_CODE: { label: 'Scan QR', color: 'bg-amber-100 text-amber-700', icon: 'qr_code_2', dot: 'bg-amber-500' },
    STARTING: { label: 'Memulai...', color: 'bg-blue-100 text-blue-700', icon: 'pending', dot: 'bg-blue-500' },
    STOPPED: { label: 'Berhenti', color: 'bg-slate-100 text-slate-500', icon: 'stop_circle', dot: 'bg-slate-400' },
    FAILED: { label: 'Gagal', color: 'bg-red-100 text-red-700', icon: 'error', dot: 'bg-red-500' },
    ERROR: { label: 'Error', color: 'bg-red-100 text-red-700', icon: 'error', dot: 'bg-red-500' },
    UNKNOWN: { label: 'Unknown', color: 'bg-slate-100 text-slate-500', icon: 'help', dot: 'bg-slate-400' },
}

const currentStatus = () => statusMap[status.value] || statusMap.UNKNOWN

const checkStatus = async () => {
    loading.value = true
    error.value = ''
    try {
        const resp = await fetch('/bot/status', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        const data = await resp.json()
        status.value = data.status || 'UNKNOWN'
        sessionInfo.value = data
        if (data.error) error.value = data.error
    } catch (e) {
        status.value = 'ERROR'
        error.value = 'Tidak dapat terhubung.'
    }
    loading.value = false
}

const startSession = async () => {
    loading.value = true
    try {
        await fetch('/bot/session/start', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' } })
        setTimeout(checkStatus, 2000)
    } catch (e) { loading.value = false }
}

const stopSession = async () => {
    loading.value = true
    try {
        await fetch('/bot/session/stop', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' } })
        setTimeout(checkStatus, 1500)
    } catch (e) { loading.value = false }
}

let pollInterval = null
onMounted(() => {
    checkStatus()
    pollInterval = setInterval(checkStatus, 30000) // poll every 30s
})
onUnmounted(() => clearInterval(pollInterval))

defineExpose({ checkStatus })
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                <span class="material-symbols-rounded text-[18px] text-slate-400">wifi</span>
                Status Koneksi
            </h3>
            <button @click="checkStatus" :disabled="loading" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                <span class="material-symbols-rounded text-[18px]" :class="{ 'animate-spin': loading }">refresh</span>
            </button>
        </div>

        <!-- Status Badge -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-3 h-3 rounded-full animate-pulse" :class="currentStatus().dot"></div>
            <span :class="currentStatus().color" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full">
                <span class="material-symbols-rounded text-[14px]">{{ currentStatus().icon }}</span>
                {{ currentStatus().label }}
            </span>
        </div>

        <!-- Session Info -->
        <div v-if="sessionInfo?.me" class="bg-slate-50 rounded-xl p-3 mb-4 text-xs space-y-1.5">
            <div class="flex justify-between">
                <span class="text-slate-400">Session</span>
                <span class="font-medium text-slate-700">{{ sessionInfo.name }}</span>
            </div>
            <div v-if="sessionInfo.me?.pushName" class="flex justify-between">
                <span class="text-slate-400">Nama</span>
                <span class="font-medium text-slate-700">{{ sessionInfo.me.pushName }}</span>
            </div>
            <div v-if="sessionInfo.me?.id" class="flex justify-between">
                <span class="text-slate-400">Nomor</span>
                <span class="font-medium text-slate-700">{{ sessionInfo.me.id.replace('@c.us', '') }}</span>
            </div>
        </div>

        <p v-if="error" class="text-xs text-red-500 mb-3">{{ error }}</p>

        <!-- QR Code -->
        <div v-if="status === 'SCAN_QR_CODE'" class="text-center mb-4">
            <p class="text-xs text-slate-500 mb-2">Scan QR Code dengan WhatsApp</p>
            <img :src="'/bot/qr-code?t=' + Date.now()" alt="QR Code" class="w-48 h-48 mx-auto rounded-xl border border-slate-200" @error="$event.target.style.display='none'" />
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <button
                v-if="status === 'STOPPED' || status === 'FAILED' || status === 'UNKNOWN'"
                @click="startSession"
                :disabled="loading"
                class="flex-1 py-2 text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-colors disabled:opacity-50"
            >
                Mulai Session
            </button>
            <button
                v-if="status === 'WORKING' || status === 'SCAN_QR_CODE'"
                @click="stopSession"
                :disabled="loading"
                class="flex-1 py-2 text-xs font-semibold bg-red-500 hover:bg-red-600 text-white rounded-xl transition-colors disabled:opacity-50"
            >
                Stop Session
            </button>
            <button
                @click="checkStatus"
                :disabled="loading"
                class="py-2 px-4 text-xs font-medium border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl transition-colors disabled:opacity-50"
            >
                Refresh
            </button>
        </div>
    </div>
</template>
