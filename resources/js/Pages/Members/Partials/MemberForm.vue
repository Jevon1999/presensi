<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch, ref, computed } from 'vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const props = defineProps({
    member: { type: Object, default: null },
    offices: { type: Array, default: () => [] },
    availableUsers: { type: Array, default: () => [] },
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const isEdit = !!props.member

const form = useForm({
    user_id: props.member?.user_id || '',
    nama_lengkap: props.member?.nama_lengkap || '',
    jenis_kelamin: props.member?.jenis_kelamin || '',
    asal_sekolah: props.member?.asal_sekolah || '',
    jurusan: props.member?.jurusan || '',
    no_hp: props.member?.no_hp || '',
    office_id: props.member?.office_id || '',
    tanggal_mulai_magang: props.member?.tanggal_mulai_magang || '',
    tanggal_selesai_magang: props.member?.tanggal_selesai_magang || '',
    status_aktif: props.member ? !!props.member.status_aktif : true,
})

// Searchable user logic
const userSearch = ref('')
const showUserDropdown = ref(false)
const filteredUsers = computed(() => {
    if (!userSearch.value) return props.availableUsers
    const s = userSearch.value.toLowerCase()
    return props.availableUsers.filter(u => 
        u.name.toLowerCase().includes(s) || 
        u.email.toLowerCase().includes(s)
    )
})
const selectedUserName = computed(() => {
    const u = props.availableUsers.find(u => u.id === form.user_id)
    return u ? `${u.name} (${u.email})` : ''
})
const selectUser = (u) => {
    form.user_id = u.id
    userSearch.value = ''
    showUserDropdown.value = false
    
    // Auto fill name if empty
    if (!form.nama_lengkap) {
        form.nama_lengkap = u.name
    }
}

watch(() => props.member, (m) => {
    if (m) {
        form.user_id = m.user_id || ''
        form.nama_lengkap = m.nama_lengkap || ''
        form.jenis_kelamin = m.jenis_kelamin || ''
        form.asal_sekolah = m.asal_sekolah || ''
        form.jurusan = m.jurusan || ''
        form.no_hp = m.no_hp || ''
        form.office_id = m.office_id || ''
        form.tanggal_mulai_magang = m.tanggal_mulai_magang || ''
        form.tanggal_selesai_magang = m.tanggal_selesai_magang || ''
        form.status_aktif = !!m.status_aktif
    }
}, { deep: true })

const onUserSelect = (e) => {
    const userId = e.target.value
    const user = props.availableUsers.find(u => u.id == userId)
    if (user && !form.nama_lengkap) {
        form.nama_lengkap = user.name
    }
}

const toTitleCase = (str) => {
    if (!str) return '';
    return str.replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
}

watch(() => form.asal_sekolah, (val) => {
    const formatted = val ? val.toUpperCase() : '';
    if (val !== formatted) form.asal_sekolah = formatted;
})

watch(() => form.jurusan, (val) => {
    const formatted = toTitleCase(val);
    if (val !== formatted) form.jurusan = formatted;
})

watch(() => form.nama_lengkap, (val) => {
    const formatted = toTitleCase(val);
    if (val !== formatted) form.nama_lengkap = formatted;
})

const submit = () => {
    emit('submit', form)
}

/**
 * Auto-format phone: 08xx → +628xx, 628xx → +628xx
 */
const formatPhone = () => {
    let v = form.no_hp.replace(/[^\d+]/g, '')
    if (v.startsWith('08')) {
        v = '+62' + v.slice(1)
    } else if (v.startsWith('628')) {
        v = '+' + v
    } else if (v.startsWith('8') && v.length >= 9) {
        v = '+62' + v
    }
    form.no_hp = v
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Nama Lengkap / Pilih User -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">User Terkait (Nama) <span class="text-red-400">*</span></label>
            <div v-if="!isEdit">
                <select
                    v-model="form.user_id"
                    @change="onUserSelect"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none transition-all bg-white"
                    :class="{ 'border-red-300 bg-red-50': form.errors.user_id || form.errors.nama_lengkap }"
                >
                    <option value="">Pilih user...</option>
                    <option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                </select>
                <p v-if="form.errors.user_id" class="text-xs text-red-500 mt-1">{{ form.errors.user_id }}</p>
                <p v-if="form.errors.nama_lengkap" class="text-xs text-red-500 mt-1">{{ form.errors.nama_lengkap }}</p>
            </div>
            <div v-else>
                <input
                    v-model="form.nama_lengkap"
                    disabled
                    type="text"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 text-slate-500 outline-none"
                />
            </div>
        </div>

        <!-- No HP -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. HP <span class="text-red-400">*</span></label>
            <input
                v-model="form.no_hp"
                @blur="formatPhone"
                type="text"
                placeholder="+628xxxxxxxxxx"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.no_hp }"
            />
            <p v-if="form.errors.no_hp" class="text-xs text-red-500 mt-1">{{ form.errors.no_hp }}</p>
        </div>

        <!-- Jenis Kelamin -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Kelamin</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.jenis_kelamin" value="L" class="radio radio-sm radio-primary" />
                    <span class="text-sm">Laki-laki</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" v-model="form.jenis_kelamin" value="P" class="radio radio-sm radio-primary" />
                    <span class="text-sm">Perempuan</span>
                </label>
            </div>
        </div>

        <!-- Asal Sekolah -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Asal Sekolah <span class="text-red-400">*</span></label>
            <input
                v-model="form.asal_sekolah"
                type="text"
                placeholder="Nama sekolah / kampus"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.asal_sekolah }"
            />
            <p v-if="form.errors.asal_sekolah" class="text-xs text-red-500 mt-1">{{ form.errors.asal_sekolah }}</p>
        </div>

        <!-- Jurusan -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jurusan <span class="text-red-400">*</span></label>
            <input
                v-model="form.jurusan"
                type="text"
                required
                placeholder="Contoh: Teknik Informatika"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.jurusan }"
            />
            <p v-if="form.errors.jurusan" class="text-xs text-red-500 mt-1">{{ form.errors.jurusan }}</p>
        </div>

        <!-- Kantor -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kantor <span class="text-red-400">*</span></label>
            <select
                v-model="form.office_id"
                required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all bg-white"
                :class="{ 'border-red-300 bg-red-50': form.errors.office_id }"
            >
                <option value="">Pilih kantor</option>
                <option v-for="o in offices" :key="o.id" :value="o.id">{{ o.name }}</option>
            </select>
            <p v-if="form.errors.office_id" class="text-xs text-red-500 mt-1">{{ form.errors.office_id }}</p>
        </div>

        <!-- Tanggal Mulai -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Mulai Magang <span class="text-red-400">*</span></label>
                <VueDatePicker 
                    v-model="form.tanggal_mulai_magang"
                    :enable-time-picker="false"
                    model-type="yyyy-MM-dd"
                    format="dd MMM yyyy"
                    auto-apply
                    input-class-name="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Selesai Magang</label>
                <VueDatePicker 
                    v-model="form.tanggal_selesai_magang"
                    :enable-time-picker="false"
                    model-type="yyyy-MM-dd"
                    format="dd MMM yyyy"
                    auto-apply
                    input-class-name="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                />
            </div>
        </div>

        <!-- Status Aktif (edit only) -->
        <div v-if="isEdit">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.status_aktif" class="toggle toggle-sm toggle-primary" />
                <span class="text-sm font-medium">{{ form.status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
            </label>
        </div>

        <!-- Submit -->
        <div class="pt-2">
            <button
                type="submit"
                :disabled="form.processing || processing"
                class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50"
            >
                {{ (form.processing || processing) ? 'Menyimpan...' : (isEdit ? 'Update' : 'Simpan') }}
            </button>
        </div>
    </form>
</template>
