<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth.user);

const menuItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Data Member', route: 'members.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
    { name: 'Data Absensi', route: 'attendances.index', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { name: 'Laporan Progress', route: 'progresses.index', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
];

const adminMenuItems = [
    { name: 'Konfigurasi Bot WA', route: 'bot.config', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z' },
    { name: 'Manajemen User', route: 'users.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
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
        <div class="drawer-content flex flex-col">
            <!-- Header -->
            <header class="navbar bg-neutral text-neutral-content sticky top-0 z-30 shadow-lg">
                <div class="flex-none lg:hidden">
                    <label for="sidebar-drawer" class="btn btn-square btn-ghost">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </label>
                </div>
                
                <div class="flex-1 px-2 mx-2">
                    <h1 class="text-lg font-bold hidden lg:block">
                        <slot name="header">Dashboard</slot>
                    </h1>
                </div>
                
                <div class="flex-none gap-2">
                    <!-- Notifications -->
                    <button class="btn btn-ghost btn-circle">
                        <div class="indicator">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                    </button>

                    <!-- User Dropdown -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-10">
                                <span class="text-sm">{{ user?.name?.charAt(0).toUpperCase() }}</span>
                            </div>
                        </label>
                        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52 text-base-content">
                            <li class="menu-title">{{ user?.name }}</li>
                            <li><a>Profile</a></li>
                            <li><a>Settings</a></li>
                            <li><Link href="/logout" method="post" as="button">Logout</Link></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6 bg-base-200 min-h-screen">
                <slot />
            </main>
        </div>
        
        <!-- Sidebar -->
        <div class="drawer-side">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            
            <aside class="bg-neutral text-neutral-content w-64 min-h-full">
                <!-- Logo -->
                <div class="bg-neutral-focus p-4 flex items-center justify-center border-b border-neutral-content/10">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📋</span>
                        </div>
                        <span class="font-bold text-xl">Presensi GI</span>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <ul class="menu p-4 gap-1">
                    <li v-for="item in menuItems" :key="item.route">
                        <Link 
                            :href="route(item.route)" 
                            :class="{ 'active bg-primary': isCurrentRoute(item.route) }"
                            class="flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                            </svg>
                            {{ item.name }}
                        </Link>
                    </li>

                    <template v-if="isAdmin">
                        <li class="menu-title mt-4">Admin</li>
                        <li v-for="item in adminMenuItems" :key="item.route">
                            <Link 
                                :href="route(item.route)" 
                                :class="{ 'active bg-primary': isCurrentRoute(item.route) }"
                                class="flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                                </svg>
                                {{ item.name }}
                            </Link>
                        </li>
                    </template>
                </ul>

                <!-- Footer Info -->
                <div class="absolute bottom-4 left-4 right-4">
                    <div class="bg-base-100 text-base-content rounded-lg p-3 text-sm">
                        <div class="font-semibold">{{ user?.name }}</div>
                        <div class="text-xs opacity-70">{{ user?.role === 'admin' ? 'Administrator' : 'Member' }}</div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
