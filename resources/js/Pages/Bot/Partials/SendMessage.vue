<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    injectedMessage: { type: String, default: '' },
})

const activeTab = ref('single') // single | broadcast

const singleForm = useForm({
    phone: '',
    message: '',
})

const broadcastForm = useForm({
    message: '',
})

// Member lookup state
const memberLookup = ref(null)      // { found, member } | null
const lookupLoading = ref(false)
const lookupTimer = ref(null)

// Auto-fill message textarea when a template is selected from parent
watch(() => props.injectedMessage, (val) => {
    if (val) {
        if (activeTab.value === 'single') {
            singleForm.message = val
        } else {
            broadcastForm.message = val
        }
    }
})

// When tab changes and injectedMessage is set, also fill the new tab
watch(activeTab, () => {
    if (props.injectedMessage) {
        if (activeTab.value === 'single') {
            singleForm.message = props.injectedMessage
        } else {
            broadcastForm.message = props.injectedMessage
        }
    }
})

// Debounced member lookup on phone input
const onPhoneInput = () => {
    memberLookup.value = null
    singleForm.clearErrors('phone')

    if (lookupTimer.value) clearTimeout(lookupTimer.value)

    const raw = singleForm.phone.replace(/[^0-9]/g, '')
    if (raw.length < 8) return

    lookupLoading.value = true
    lookupTimer.value = setTimeout(async () => {
        try {
            const res = await fetch(`/bot/lookup-member?phone=${encodeURIComponent(singleForm.phone)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            const data = await res.json()
            memberLookup.value = data
        } catch {
            memberLookup.value = { found: false, member: null }
        } finally {
            lookupLoading.value = false
        }
    }, 600)
}

const canSendSingle = () => {
    return memberLookup.value?.found && singleForm.message && !singleForm.processing
}

const sendSingle = () => {
    if (!canSendSingle()) return
    singleForm.post('/bot/send-message', {
        preserveScroll: true,
        onSuccess: () => {
            singleForm.reset()
            memberLookup.value = null
        },
    })
}

const sendBroadcast = () => {
    broadcastForm.post('/bot/broadcast', {
        preserveScroll: true,
        onSuccess: () => { broadcastForm.reset() },
    })
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2 mb-4">
            <span class="material-symbols-rounded text-[18px] text-slate-400">send</span>
            Kirim Pesan
        </h3>

        <!-- Tabs -->
        <div class="flex gap-1 p-1 bg-slate-100 rounded-xl mb-4">
            <button
                @click="activeTab = 'single'"
                :class="activeTab === 'single' ? 'bg-white shadow-sm text-slate-700' : 'text-slate-500'"
                class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all"
            >
                Pesan Tunggal
            </button>
            <button
                @click="activeTab = 'broadcast'"
                :class="activeTab === 'broadcast' ? 'bg-white shadow-sm text-slate-700' : 'text-slate-500'"
                class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all"
            >
                Broadcast
            </button>
        </div>

        <!-- Single Message -->
        <form v-if="activeTab === 'single'" @submit.prevent="sendSingle" class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. HP Member Tujuan</label>
                <div class="relative">
                    <input
                        v-model="singleForm.phone"
                        @input="onPhoneInput"
                        type="text"
                        placeholder="08xxxxxxxxxx"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border focus:ring-2 outline-none transition-all pr-9"
                        :class="{
                            'border-red-300 focus:border-red-400 focus:ring-red-100': singleForm.errors.phone || (memberLookup && !memberLookup.found && singleForm.phone),
                            'border-emerald-300 focus:border-emerald-400 focus:ring-emerald-100': memberLookup && memberLookup.found,
                            'border-slate-200 focus:border-blue-400 focus:ring-blue-100': !singleForm.errors.phone && !(memberLookup && !memberLookup.found && singleForm.phone) && !(memberLookup && memberLookup.found),
                        }"
                    />
                    <!-- loading spinner -->
                    <span v-if="lookupLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                    </span>
                    <!-- found icon -->
                    <span v-else-if="memberLookup && memberLookup.found" class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-emerald-500 text-[18px]">check_circle</span>
                    <!-- not found icon -->
                    <span v-else-if="memberLookup && !memberLookup.found && singleForm.phone" class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-red-400 text-[18px]">cancel</span>
                </div>

                <!-- Member found info badge -->
                <div v-if="memberLookup && memberLookup.found" class="mt-2 flex items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                    <span class="material-symbols-rounded text-emerald-500 text-[16px]">person</span>
                    <div>
                        <p class="text-xs font-bold text-emerald-700">{{ memberLookup.member.nama_lengkap }}</p>
                        <p class="text-[10px] text-emerald-500">
                            {{ memberLookup.member.status_aktif ? 'Anggota Aktif' : 'Anggota Tidak Aktif' }}
                        </p>
                    </div>
                </div>

                <!-- Not a member warning -->
                <div v-else-if="memberLookup && !memberLookup.found && singleForm.phone" class="mt-2 flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                    <span class="material-symbols-rounded text-red-400 text-[16px]">person_off</span>
                    <p class="text-xs text-red-600 font-medium">Nomor ini tidak terdaftar sebagai member. Pesan tidak dapat dikirim.</p>
                </div>

                <p v-if="singleForm.errors.phone" class="text-xs text-red-500 mt-1">{{ singleForm.errors.phone }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pesan</label>
                <textarea
                    v-model="singleForm.message"
                    rows="3"
                    placeholder="Tulis pesan atau klik template di bawah..."
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                    :class="{ 'border-red-300': singleForm.errors.message }"
                ></textarea>
                <p v-if="singleForm.errors.message" class="text-xs text-red-500 mt-1">{{ singleForm.errors.message }}</p>
            </div>
            <button
                type="submit"
                :disabled="!canSendSingle()"
                class="w-full py-2.5 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
                :class="canSendSingle() ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-slate-300 cursor-not-allowed'"
            >
                {{ singleForm.processing ? 'Mengirim...' : 'Kirim Pesan' }}
            </button>
            <p v-if="!memberLookup?.found && singleForm.phone" class="text-[11px] text-center text-slate-400">
                Masukkan nomor HP member untuk melanjutkan
            </p>
        </form>

        <!-- Broadcast -->
        <form v-if="activeTab === 'broadcast'" @submit.prevent="sendBroadcast" class="space-y-3">
            <div class="bg-amber-50 rounded-xl p-3 flex items-start gap-3">
                <span class="material-symbols-rounded text-amber-500 text-[20px] mt-0.5">info</span>
                <p class="text-xs text-amber-700">Broadcast akan mengirim pesan ke semua anggota aktif yang memiliki nomor HP. Proses mungkin memakan waktu.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pesan Broadcast</label>
                <textarea
                    v-model="broadcastForm.message"
                    rows="4"
                    placeholder="Tulis pesan untuk semua anggota atau klik template di bawah..."
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                    :class="{ 'border-red-300': broadcastForm.errors.message }"
                ></textarea>
                <p v-if="broadcastForm.errors.message" class="text-xs text-red-500 mt-1">{{ broadcastForm.errors.message }}</p>
            </div>
            <button
                type="submit"
                :disabled="broadcastForm.processing || !broadcastForm.message"
                class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
            >
                {{ broadcastForm.processing ? 'Mengirim broadcast...' : 'Kirim Broadcast' }}
            </button>
        </form>
    </div>
</template>
