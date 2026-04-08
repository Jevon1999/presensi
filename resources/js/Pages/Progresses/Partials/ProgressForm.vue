import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { ref, computed } from 'vue'

const props = defineProps({
    progress: { type: Object, default: null },
    members: { type: Array, default: () => [] },
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const isEdit = !!props.progress

const form = useForm({
    member_id: props.progress?.member_id || '',
    tanggal: props.progress?.tanggal || new Date().toISOString().slice(0, 10),
    description: props.progress?.description || '',
})

// Searchable member logic
const memberSearch = ref('')
const showMemberDropdown = ref(false)
const filteredMembers = computed(() => {
    if (!memberSearch.value) return props.members
    const s = memberSearch.value.toLowerCase()
    return props.members.filter(m => 
        m.nama_lengkap.toLowerCase().includes(s) || 
        (m.no_hp && m.no_hp.includes(s))
    )
})
const selectedMemberName = computed(() => {
    const m = props.members.find(m => m.id === form.member_id)
    return m ? m.nama_lengkap : ''
})
const selectMember = (m) => {
    form.member_id = m.id
    memberSearch.value = ''
    showMemberDropdown.value = false
}

watch(() => props.progress, (p) => {
    if (p) {
        form.member_id = p.member_id || ''
        form.tanggal = p.tanggal || new Date().toISOString().slice(0, 10)
        form.description = p.description || ''
    }
})

const submit = () => {
    emit('submit', form)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4" @click="showMemberDropdown = false">
        <!-- Anggota -->
        <div class="relative">
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Anggota <span class="text-red-400">*</span></label>
            
            <div v-if="!isEdit" class="relative">
                <input
                    type="text"
                    v-model="memberSearch"
                    @click.stop="showMemberDropdown = true"
                    placeholder="Cari nama anggota..."
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 outline-none transition-all bg-white"
                    :class="{ 'border-red-300 bg-red-50': form.errors.member_id }"
                />
                <div v-if="selectedMemberName && !memberSearch" class="absolute right-10 top-1/2 -translate-y-1/2 text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">
                    Selected: {{ selectedMemberName }}
                </div>
                <span class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                
                <!-- Dropdown -->
                <div v-if="showMemberDropdown" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                    <div 
                        v-for="m in filteredMembers" 
                        :key="m.id"
                        @click.stop="selectMember(m)"
                        class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-50 last:border-0"
                    >
                        <p class="text-sm font-semibold text-slate-700">{{ m.nama_lengkap }}</p>
                        <p class="text-[10px] text-slate-400">{{ m.no_hp || '-' }} &middot; {{ m.office?.name || '-' }}</p>
                    </div>
                    <div v-if="filteredMembers.length === 0" class="px-4 py-4 text-center text-sm text-slate-400">
                        Tidak ada anggota ditemukan
                    </div>
                </div>
            </div>
            <div v-else class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-100 bg-slate-50 text-slate-500 font-medium">
                {{ selectedMemberName }}
            </div>
            
            <input type="hidden" v-model="form.member_id" required />
            <p v-if="form.errors.member_id" class="text-xs text-red-500 mt-1">{{ form.errors.member_id }}</p>
        </div>

        <!-- Tanggal -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal <span class="text-red-400">*</span></label>
            <VueDatePicker
                v-model="form.tanggal"
                :enable-time-picker="false"
                model-type="yyyy-MM-dd"
                format="dd/MM/yyyy"
                auto-apply
                placeholder="Pilih tanggal"
                input-class-name="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300': form.errors.tanggal }"
            />
            <p v-if="form.errors.tanggal" class="text-xs text-red-500 mt-1">{{ form.errors.tanggal }}</p>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi Progress <span class="text-red-400">*</span></label>
            <textarea
                v-model="form.description"
                rows="5"
                placeholder="Jelaskan progress kerja hari ini..."
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"
                :class="{ 'border-red-300 bg-red-50': form.errors.description }"
            ></textarea>
            <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
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
