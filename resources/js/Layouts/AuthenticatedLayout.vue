<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'
import logo from '../../images/logo_global.png'
import axios from 'axios'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const flash = computed(() => page.props.flash)
const userName = computed(() => user.value?.name || user.value?.nama || user.value?.email || 'Admin')
const userInitials = computed(() => {
    const name = userName.value
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

// Sidebar state
const sidebarOpen = ref(false)
const userMenuOpen = ref(false)
const pendingMembersCount = ref(0)

const closeSidebar = () => { sidebarOpen.value = false }

const fetchPendingCount = () => {
    if (user.value?.role !== 'admin') return
    axios.get('/internal/pending-members-count')
        .then(res => {
            pendingMembersCount.value = Number(res.data?.count) || 0
        })
        .catch(() => {})
}

onMounted(() => {
    fetchPendingCount()
    // Re-fetch after each Inertia navigation so badge stays in sync
    router.on('finish', () => fetchPendingCount())
})

// Navigation items
const navItems = [
    { name: 'Dashboard',  href: '/dashboard',          icon: 'grid_view',       match: '/dashboard' },
    { name: 'Absensi',    href: '/attendances',         icon: 'fact_check',      match: '/attendances' },
    { name: 'Laporan',    href: '/attendances/report',  icon: 'assessment',      match: '/attendances/report' },
    { name: 'Statistik',  href: '/statistics',          icon: 'bar_chart',       match: '/statistics' },
    { name: 'Members',    href: '/members',             icon: 'group',           match: '/members' },
    { name: 'Progress',   href: '/progresses',          icon: 'trending_up',     match: '/progresses' },
    { name: 'Kantor',     href: '/offices',             icon: 'apartment',       match: '/offices' },
    { name: 'Users',      href: '/users',               icon: 'manage_accounts', match: '/users' },
    { name: 'Bot WA',     href: '/bot/config',          icon: 'smart_toy',       match: '/bot' },
]

const currentPath = computed(() => page.url.split('?')[0])
const activeMatch = computed(() => {
    const path = currentPath.value
    // Find the longest matching navItem prefix for the current path
    return navItems
        .filter(item => path === item.match || path.startsWith(item.match + '/'))
        .sort((a, b) => b.match.length - a.match.length)[0]?.match || null
})
const isActive = (match) => activeMatch.value === match

// Flash/toast state
const showSuccess = ref(false)
const showError = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

watch(() => flash.value, (f) => {
    if (f?.success) {
        successMessage.value = f.success
        showSuccess.value = true
    }
    if (f?.error) {
        errorMessage.value = f.error
        showError.value = true
    }
}, { immediate: true })

// Close user menu when clicking outside
const closeUserMenu = () => { userMenuOpen.value = false }

const logout = () => {
    userMenuOpen.value = false
    router.post('/logout')
}
</script>

<template>
    <div class="flex h-dvh overflow-hidden" @click="closeUserMenu">
        <!-- Mobile Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'lg:translate-x-0'
            ]"
            class="fixed lg:static inset-y-0 left-0 w-70 lg:w-64 shrink-0 border-r border-slate-200 bg-white flex flex-col z-50 transition-transform duration-300 ease-in-out"
        >
            <!-- Brand -->
            <div class="p-5 flex items-center gap-3 border-b border-slate-100">
                <img :src="logo" alt="Global Intermedia" class="w-9 h-9 rounded-xl object-contain" />
                <div>
                    <h1 class="text-base font-bold leading-none">Presensi GI</h1>
                    <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wider font-semibold">Attendance System</p>
                </div>
                <!-- Close button (mobile) -->
                <button @click="closeSidebar" class="lg:hidden ml-auto p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-rounded text-slate-400 text-[20px]">close</span>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-3">Menu</p>
                
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.href"
                    preserve-scroll
                    :class="[
                        isActive(item.match)
                            ? 'bg-blue-50 text-blue-600 font-semibold'
                            : 'text-slate-600 hover:bg-slate-50',
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200'
                    ]"
                    @click="closeSidebar"
                >
                    <span
                        class="material-symbols-rounded text-[20px]"
                        :class="isActive(item.match) ? 'text-blue-600' : 'text-slate-400'"
                    >{{ item.icon }}</span>
                    <div class="flex-1 flex items-center">
                        <div v-if="item.name === 'Members'" class="relative pr-1">
                            <span class="text-sm block">{{ item.name }}</span>
                            <!-- Notification Badge (Superscript style) -->
                            <span
                                v-if="pendingMembersCount > 0"
                                class="absolute -top-1.5 -right-2 min-w-[15px] h-4 px-1 rounded-full bg-red-500 flex items-center justify-center text-white text-[9px] font-bold shadow-sm transition-all animate-pulse"
                            >
                                {{ pendingMembersCount }}
                            </span>
                        </div>
                        <span v-else class="text-sm block">{{ item.name }}</span>
                    </div>
                </Link>
            </nav>

            <!-- User Section -->
            <div class="p-4 border-t border-slate-100">
                <div
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer"
                    @click.stop="userMenuOpen = !userMenuOpen"
                >
                    <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold">
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold truncate">{{ userName }}</h4>
                        <p class="text-[11px] text-slate-400 truncate">{{ user?.email || 'admin' }}</p>
                    </div>
                    <span class="material-symbols-rounded text-slate-400 text-[18px]">unfold_more</span>
                </div>
                <Transition
                    enter-active-class="transition duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-150"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="userMenuOpen" class="mt-2 bg-white border border-slate-100 rounded-xl shadow-lg overflow-hidden">
                        <button @click="logout" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <span class="material-symbols-rounded text-[18px]">logout</span>
                            Logout
                        </button>
                    </div>
                </Transition>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar (Mobile) -->
            <header class="lg:hidden flex items-center justify-between px-4 py-3 bg-white border-b border-slate-200 sticky top-0 z-30">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-rounded text-slate-600">menu</span>
                </button>
                <div class="flex items-center gap-2">
                    <img :src="logo" alt="Logo" class="w-7 h-7 rounded-lg object-contain" />
                    <span class="font-bold text-sm">Presensi GI</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 text-[10px] font-bold">
                    {{ userInitials }}
                </div>
            </header>

            <!-- Page Content (scrollable) -->
            <div class="flex-1 overflow-y-auto">
                <!-- Flash Toasts -->
                <div class="mx-4 lg:mx-6 mt-4 space-y-2">
                    <Toast
                        :message="successMessage"
                        type="success"
                        :show="showSuccess"
                        @close="showSuccess = false"
                    />
                    <Toast
                        :message="errorMessage"
                        type="error"
                        :show="showError"
                        @close="showError = false"
                    />
                </div>

                <div class="p-4 lg:p-6">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
