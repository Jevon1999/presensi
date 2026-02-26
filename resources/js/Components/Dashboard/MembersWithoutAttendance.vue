<script setup>
const props = defineProps({
    members: Array
});
</script>

<template>
    <div class="bg-base-200 border border-neutral/30 rounded-md">
        <div class="p-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 bg-warning rounded"></div>
                <h3 class="text-xs font-mono font-semibold tracking-wider uppercase">ALERT: PENDING CHECK-IN</h3>
            </div>
            
            <div v-if="members && members.length > 0" class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                <div 
                    v-for="member in members" 
                    :key="member.id"
                    class="flex items-center gap-3 p-2 bg-warning/5 border border-warning/20 rounded hover:bg-warning/10 transition-colors"
                >
                    <div class="w-8 h-8 bg-warning/20 border border-warning/30 rounded flex items-center justify-center shrink-0">
                        <span class="text-xs font-mono font-bold text-warning">{{ member.nama_lengkap.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-mono text-xs font-semibold truncate">{{ member.nama_lengkap }}</div>
                        <div class="font-mono text-[10px] text-base-content/40 uppercase truncate">{{ member.office?.name }}</div>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 text-[9px] font-mono font-semibold tracking-wider bg-warning/10 text-warning border border-warning/30 rounded uppercase">
                            PENDING
                        </span>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-8">
                <svg class="w-10 h-10 mx-auto mb-2 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs font-mono font-semibold text-success uppercase tracking-wider">ALL UNITS CHECKED IN</p>
            </div>
        </div>
    </div>
</template>
