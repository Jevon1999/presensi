<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth.user);

const menuItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Absensi', route: 'attendances.index', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { name: 'Members', route: 'members.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { name: 'Progress', route: 'progresses.index', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { name: 'Reports', route: 'reports.index', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
];

const adminMenuItems = [
    { name: 'Bot Config', route: 'bot.config', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
    { name: 'User Management', route: 'users.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

const isAdmin = computed(() => user.value?.role === 'admin');

const isCurrentRoute = (routeName) => {
    return route().current(routeName) || route().current()?.startsWith(routeName);
};
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col shrink-0 hidden lg:flex">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-xl tracking-tight">Presensi</h1>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Enterprise Suite</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    <Link 
                        v-for="item in menuItems" 
                        :key="item.route"
                        :href="route(item.route)"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group',
                            isCurrentRoute(item.route)
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                        </svg>
                        <span class="text-sm font-semibold capitalize">{{ item.name }}</span>
                        <svg v-if="isCurrentRoute(item.route)" class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>

                    <template v-if="isAdmin">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-4 py-2 mt-4">
                            Admin
                        </div>
                        <Link 
                            v-for="item in adminMenuItems" 
                            :key="item.route"
                            :href="route(item.route)"
                            :class="[
                                'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group',
                                isCurrentRoute(item.route)
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                    : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900'
                            ]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                            </svg>
                            <span class="text-sm font-semibold">{{ item.name }}</span>
                            <svg v-if="isCurrentRoute(item.route)" class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </template>
                </nav>
            </div>

            <div class="mt-auto p-6 space-y-4">
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <span class="text-sm font-bold">{{ user?.name?.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ user?.name }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">{{ user?.role }}</p>
                        </div>
                    </div>
                </div>

                <Link 
                    href="/logout" 
                    method="post" 
                    as="button"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 transition-all font-semibold"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Logout</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="h-20 bg-white border-b border-gray-200 px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Mobile Menu Button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <div class="relative w-96 hidden md:block">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input 
                            type="text" 
                            placeholder="Search anything..." 
                            class="w-full bg-gray-50 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-blue-500/20 transition-all"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <button class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <div class="h-8 w-px bg-gray-200"></div>
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold">{{ user?.name }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">{{ user?.role }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 ring-2 ring-gray-50">
                            <span class="text-sm font-bold">{{ user?.name?.charAt(0).toUpperCase() }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <slot />
            </div>
        </main>
    </div>
</template>
