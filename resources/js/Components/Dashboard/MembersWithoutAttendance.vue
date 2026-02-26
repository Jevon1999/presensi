<script setup>
const props = defineProps({
    members: Array
});
</script>

<template>
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm h-full">
        <h3 class="text-xl font-bold mb-2">Not Checked In</h3>
        <p class="text-sm text-gray-400 mb-6">Members who haven't checked in today</p>
        
        <div v-if="members && members.length > 0" class="space-y-3 max-h-[400px] overflow-y-auto scrollbar-thin">
            <div 
                v-for="member in members" 
                :key="member.id"
                class="flex items-center gap-3 p-3 bg-orange-50 border border-orange-100 rounded-xl hover:bg-orange-100 transition-colors"
            >
                <div class="w-10 h-10 bg-orange-100 border-2 border-orange-200 rounded-full flex items-center justify-center shrink-0">
                    <span class="text-sm font-bold text-orange-600">{{ member.nama_lengkap.charAt(0).toUpperCase() }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm truncate">{{ member.nama_lengkap }}</div>
                    <div class="text-xs text-gray-400 truncate">{{ member.office?.name }}</div>
                </div>
                <div>
                    <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider bg-orange-100 text-orange-600 rounded-full uppercase">
                        Pending
                    </span>
                </div>
            </div>
        </div>
        
        <div v-else class="text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-bold text-emerald-600">All members checked in!</p>
        </div>
    </div>
</template>
