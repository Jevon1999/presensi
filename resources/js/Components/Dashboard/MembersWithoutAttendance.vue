<script setup>
const props = defineProps({
    members: Array
});
</script>

<template>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">Belum Absen Hari Ini</h3>
            
            <div v-if="members && members.length > 0" class="space-y-2 max-h-64 overflow-y-auto">
                <div 
                    v-for="member in members" 
                    :key="member.id"
                    class="flex items-center gap-3 p-2 hover:bg-base-200 rounded-lg transition-colors"
                >
                    <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                            <span class="text-xs">{{ member.nama_lengkap.charAt(0).toUpperCase() }}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-sm">{{ member.nama_lengkap }}</div>
                        <div class="text-xs opacity-60">{{ member.office?.name }}</div>
                    </div>
                    <div>
                        <span class="badge badge-sm badge-warning">Belum Absen</span>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-8 text-success">
                <svg class=" w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-medium">Semua member sudah absen!</p>
            </div>
        </div>
    </div>
</template>
