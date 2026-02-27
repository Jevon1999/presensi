<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    office: { type: Object, default: null },
    processing: Boolean,
})

const emit = defineEmits(['submit'])

const isEdit = !!props.office

const form = useForm({
    code: props.office?.code || '',
    name: props.office?.name || '',
})

// Locations management
const locations = ref(
    props.office?.locations?.map(l => ({ ...l })) || [{ alamat: '', latitude: '', longitude: '', radius_meters: 100, is_active: true }]
)

watch(() => props.office, (o) => {
    if (o) {
        form.code = o.code || ''
        form.name = o.name || ''
        locations.value = o.locations?.map(l => ({ ...l })) || [{ alamat: '', latitude: '', longitude: '', radius_meters: 100, is_active: true }]
    }
})

const addLocation = () => {
    locations.value.push({ alamat: '', latitude: '', longitude: '', radius_meters: 100, is_active: true })
}

const removeLocation = (i) => {
    if (locations.value.length > 1) locations.value.splice(i, 1)
}

const submit = () => {
    // Merge locations into form data
    form.transform((data) => ({
        ...data,
        locations: locations.value,
    }))
    emit('submit', form)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Kode Kantor -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kode Kantor <span class="text-red-400">*</span></label>
            <input
                v-model="form.code"
                type="text"
                placeholder="GI-JKT"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.code }"
            />
            <p v-if="form.errors.code" class="text-xs text-red-500 mt-1">{{ form.errors.code }}</p>
        </div>

        <!-- Nama Kantor -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Kantor <span class="text-red-400">*</span></label>
            <input
                v-model="form.name"
                type="text"
                placeholder="Kantor Pusat Jakarta"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50': form.errors.name }"
            />
            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
        </div>

        <!-- Locations -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-semibold text-slate-600">Lokasi</label>
                <button
                    type="button"
                    @click="addLocation"
                    class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1"
                >
                    <span class="material-symbols-rounded text-[14px]">add</span> Tambah Lokasi
                </button>
            </div>
            <div v-for="(loc, i) in locations" :key="i" class="bg-slate-50 rounded-xl p-3 mb-2 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Lokasi {{ i + 1 }}</span>
                    <button
                        v-if="locations.length > 1"
                        type="button"
                        @click="removeLocation(i)"
                        class="text-red-400 hover:text-red-500"
                    >
                        <span class="material-symbols-rounded text-[16px]">close</span>
                    </button>
                </div>
                <input
                    v-model="loc.alamat"
                    type="text"
                    placeholder="Alamat"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-blue-400 outline-none"
                />
                <div class="grid grid-cols-3 gap-2">
                    <input
                        v-model="loc.latitude"
                        type="text"
                        placeholder="Latitude"
                        class="px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-blue-400 outline-none"
                    />
                    <input
                        v-model="loc.longitude"
                        type="text"
                        placeholder="Longitude"
                        class="px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-blue-400 outline-none"
                    />
                    <input
                        v-model.number="loc.radius_meters"
                        type="number"
                        placeholder="Radius (m)"
                        class="px-3 py-2 text-sm rounded-lg border border-slate-200 focus:border-blue-400 outline-none"
                    />
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="loc.is_active" class="toggle toggle-xs toggle-primary" />
                    <span class="text-xs">{{ loc.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </label>
            </div>
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
