<script setup>
const props = defineProps({
    status: String,
    type: { type: String, default: 'attendance' }, // attendance | member
})

const attendanceMap = {
    'hadir': { label: 'Hadir', class: 'bg-emerald-100 text-emerald-700' },
    'terlambat': { label: 'Terlambat', class: 'bg-amber-100 text-amber-700' },
    'izin': { label: 'Izin', class: 'bg-blue-100 text-blue-700' },
    'sakit': { label: 'Sakit', class: 'bg-orange-100 text-orange-700' },
    'alpha': { label: 'Alpha', class: 'bg-red-100 text-red-700' },
}

const memberMap = {
    'true': { label: 'Aktif', class: 'bg-emerald-100 text-emerald-700' },
    'false': { label: 'Nonaktif', class: 'bg-slate-100 text-slate-500' },
    '1': { label: 'Aktif', class: 'bg-emerald-100 text-emerald-700' },
    '0': { label: 'Nonaktif', class: 'bg-slate-100 text-slate-500' },
}

const getStyle = () => {
    const map = props.type === 'member' ? memberMap : attendanceMap
    return map[String(props.status)] || { label: props.status || '-', class: 'bg-slate-100 text-slate-600' }
}
</script>

<template>
    <span :class="getStyle().class" class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
        {{ getStyle().label }}
    </span>
</template>
