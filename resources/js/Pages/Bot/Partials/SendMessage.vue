<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const activeTab = ref('single') // single | broadcast

const singleForm = useForm({
    phone: '',
    message: '',
})

const broadcastForm = useForm({
    message: '',
})

const sendSingle = () => {
    singleForm.post('/bot/send-message', {
        preserveScroll: true,
        onSuccess: () => { singleForm.reset() },
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
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. HP Tujuan</label>
                <input
                    v-model="singleForm.phone"
                    type="text"
                    placeholder="08xxxxxxxxxx"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                    :class="{ 'border-red-300': singleForm.errors.phone }"
                />
                <p v-if="singleForm.errors.phone" class="text-xs text-red-500 mt-1">{{ singleForm.errors.phone }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pesan</label>
                <textarea
                    v-model="singleForm.message"
                    rows="3"
                    placeholder="Tulis pesan..."
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                    :class="{ 'border-red-300': singleForm.errors.message }"
                ></textarea>
                <p v-if="singleForm.errors.message" class="text-xs text-red-500 mt-1">{{ singleForm.errors.message }}</p>
            </div>
            <button
                type="submit"
                :disabled="singleForm.processing || !singleForm.phone || !singleForm.message"
                class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
            >
                {{ singleForm.processing ? 'Mengirim...' : 'Kirim Pesan' }}
            </button>
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
                    placeholder="Tulis pesan untuk semua anggota..."
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
