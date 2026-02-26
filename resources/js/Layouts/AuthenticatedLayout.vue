<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth.user);

const menuItems = [
    { name: 'SYSTEM MONITOR', route: 'dashboard', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { name: 'UNIT REGISTRY', route: 'members.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { name: 'ATTENDANCE LOG', route: 'attendances.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { name: 'PROGRESS REPORTS', route: 'progresses.index', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
];

const adminMenuItems = [
    { name: 'BOT CONFIG', route: 'bot.config', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
    { name: 'USER ADMIN', route: 'users.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

const isAdmin = computed(() => user.value?.role === 'admin');

const isCurrentRoute = (routeName) => {
    return route().current(routeName) || route().current()?.startsWith(routeName);
};
</script>

<template>
    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" v-model="sidebarOpen" />
        
        <!-- Main Content -->
        <div class="drawer-content flex flex-col bg-base-100">
            <!-- Header -->
            <header class="h-14 bg-base-200 border-b border-neutral/30 flex items-center px-4 sticky top-0 z-30">
                <div class="flex-none lg:hidden mr-3">
                    <label for="sidebar-drawer" class="btn btn-ghost btn-sm btn-square">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </label>
                </div>
                
                <div class="flex-1">
                    <h1 class="text-xs font-mono font-semibold tracking-wider uppercase text-base-content/70">
                        <slot name="header">SYSTEM MONITOR</slot>
                    </h1>
                </div>
                
                <div class="flex-none flex items-center gap-2">
                    <!-- Status Indicator -->
                    <div class="hidden md:flex items-center gap-2 px-2">
                        <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                        <span class="text-xs font-mono text-success">ONLINE</span>
                    </div>

                    <!-- User Info -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm normal-case gap-2">
                            <div class="w-6 h-6 rounded bg-neutral flex items-center justify-center">
                                <span class="text-xs font-mono">{{ user?.name?.charAt(0).toUpperCase() }}</span>
                            </div>
                            <span class="hidden md:inline text-xs font-mono">{{ user?.name }}</span>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu menu-sm bg-base-200 border border-neutral/30 rounded-md w-52 p-2 mt-2">
                            <li class="menu-title">
                                <span class="text-xs font-mono">{{ user?.email }}</span>
                            </li>
                            <li><a class="text-xs font-mono">Profile</a></li>
                            <li><a class="text-xs font-mono">Settings</a></li>
                            <li class="border-t border-neutral/30 mt-1 pt-1">
                                <Link href="/logout" method="post" as="button" class="text-xs font-mono text-error">LOGOUT</Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 md:p-6">
                <slot />
            </main>
        </div>
        
        <!-- Sidebar -->
        <div class="drawer-side">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            
            <aside class="w-64 min-h-full bg-base-200 border-r border-neutral/30">
                <!-- Logo/Brand -->
                <div class="h-14 flex items-center justify-center border-b border-neutral/30">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-primary/20 border border-primary/50 rounded flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="font-mono text-sm font-bold tracking-wider">PRESENSI</span>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-3">
                    <div class="text-[10px] font-mono font-semibold tracking-widest text-base-content/40 px-3 py-2">
                        MAIN OPERATIONS
                    </div>
                    <ul class="space-y-1">
                        <li v-for="item in menuItems" :key="item.route">
                            <Link 
                                :href="route(item.route)" 
                                :class="[ 
                                    'flex items-center gap-3 px-3 py-2 rounded-md text-xs font-mono transition-colors',
                                    isCurrentRoute(item.route) 
                                        ? 'bg-primary/10 text-primary border-l-2 border-primary' 
                                        : 'text-base-content/70 hover:bg-base-300 hover:text-base-content border-l-2 border-transparent'
                                ]"
                            >
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                                </svg>
                                <span class="font-semibold tracking-wide">{{ item.name }}</span>
                            </Link>
                        </li>
                    </ul>

                    <template v-if="isAdmin">
                        <div class="text-[10px] font-mono font-semibold tracking-widest text-base-content/40 px-3 py-2 mt-4">
                            ADMINISTRATION
                        </div>
                        <ul class="space-y-1">
                            <li v-for="item in adminMenuItems" :key="item.route">
                                <Link 
                                    :href="route(item.route)" 
                                    :class="[ 
                                        'flex items-center gap-3 px-3 py-2 rounded-md text-xs font-mono transition-colors',
                                        isCurrentRoute(item.route) 
                                            ? 'bg-primary/10 text-primary border-l-2 border-primary' 
                                            : 'text-base-content/70 hover:bg-base-300 hover:text-base-content border-l-2 border-transparent'
                                    ]"
                                >
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                                    </svg>
                                    <span class="font-semibold tracking-wide">{{ item.name }}</span>
                                </Link>
                            </li>
                        </ul>
                    </template>
                </div>

                <!-- Footer Info -->
                <div class="absolute bottom-4 left-3 right-3">
                    <div class="bg-base-300 border border-neutral/30 rounded-md p-3">
                        <div class="text-xs font-mono font-semibold">{{ user?.name }}</div>
                        <div class="text-[10px] font-mono text-base-content/50 uppercase tracking-wider mt-0.5">
                            {{ user?.role === 'admin' ? 'Administrator' : 'User' }}
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
