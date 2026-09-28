<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const sidebarOpen = ref(true);
const mobileOpen = ref(false);
const userMenuOpen = ref(false);

const menu = [
    {
        name: 'Dashboard',
        path: '/admin/dashboard',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
    },
    {
        name: 'Buku',
        path: '/admin/books',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
    },
    {
        name: 'Kategori',
        path: '/admin/categories',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>',
    },
    {
        name: 'Pesanan',
        path: '/admin/orders',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
    },
    {
        name: 'Pengguna',
        path: '/admin/users',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
    },
];

const isActive = (path) => route.path === path || route.path.startsWith(path + '/');

async function handleLogout() {
    await auth.logout();
    router.push('/login');
}

onMounted(() => {
    if (window.innerWidth < 1024) sidebarOpen.value = false;
});
</script>

<template>
    <div class="min-h-screen bg-[#F2ECE4] flex">

        <!-- Mobile Overlay -->
        <div
            v-if="mobileOpen"
            @click="mobileOpen = false"
            class="fixed inset-0 bg-[#0a0a0a]/70 z-40 lg:hidden"
        ></div>

        <!-- ============================================
             SIDEBAR ADMIN
             ============================================ -->
        <aside
            :class="[
                'bg-[#0a0a0a] text-white flex flex-col fixed inset-y-0 left-0 z-50 transition-all duration-300 border-r-2 border-[#0a0a0a]',
                sidebarOpen ? 'w-64' : 'w-20',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-between px-4 border-b-2 border-white/10 shrink-0">
                <RouterLink to="/admin/dashboard" class="flex items-center gap-3 overflow-hidden">
                    <img
                        src="/logo3.jpg"
                        alt="LiteraSee"
                        style="height: 32px; width: auto;"
                        class="object-contain shrink-0 block border-2 border-white bg-white"
                    />
                    <span v-if="sidebarOpen" class="font-black uppercase text-lg tracking-tight whitespace-nowrap flex items-center">
                        <span class="text-white">Litera</span><span class="text-primary-400">See</span>
                    </span>
                </RouterLink>
                <button
                    v-if="sidebarOpen"
                    @click="sidebarOpen = false"
                    class="hidden lg:flex w-7 h-7 items-center justify-center border-2 border-white/30 text-white/70 hover:text-white hover:border-white transition-colors"
                    title="Kecilkan"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="square" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button
                    @click="mobileOpen = false"
                    class="lg:hidden text-white/70 hover:text-white text-2xl leading-none"
                >
                    
                </button>
            </div>

            <!-- Expand button (saat collapsed) -->
            <div v-if="!sidebarOpen" class="hidden lg:flex p-2 justify-center">
                <button
                    @click="sidebarOpen = true"
                    class="w-10 h-10 flex items-center justify-center border-2 border-white/30 text-white/70 hover:text-white hover:border-white transition-colors"
                    title="Besarkan"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="square" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Label -->
            <div v-if="sidebarOpen" class="px-4 pt-4 pb-2">
                <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white/40">
                    Admin Menu
                </span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 py-4 overflow-y-auto">
                <ul class="space-y-1 px-2">
                    <li v-for="item in menu" :key="item.path">
                        <RouterLink
                            :to="item.path"
                            @click="mobileOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 text-sm font-bold uppercase tracking-wider transition-all duration-100',
                                isActive(item.path)
                                    ? 'bg-[#8C5830] text-white border-2 border-white/60 shadow-[3px_3px_0_0_#8C5830]'
                                    : 'text-white/70 hover:bg-white/10 hover:text-white border-2 border-transparent',
                                !sidebarOpen ? 'lg:justify-center' : '',
                            ]"
                            :title="!sidebarOpen ? item.name : ''"
                        >
                            <span class="shrink-0" v-html="item.icon"></span>
                            <span v-if="sidebarOpen" class="whitespace-nowrap">{{ item.name }}</span>
                        </RouterLink>
                    </li>
                </ul>
            </nav>

            <!-- User & Logout -->
            <div class="border-t-2 border-white/10 p-3 shrink-0">

                <!-- User Info -->
                <div v-if="sidebarOpen" class="flex items-center gap-3 mb-3 p-2 bg-white/5 border-2 border-white/10">
                    <div class="w-9 h-9 shrink-0 border-2 border-white bg-[#F2ECE4] flex items-center justify-center overflow-hidden">
                        <img
                            v-if="auth.user?.avatar"
                            :src="`/storage/${auth.user.avatar}`"
                            :alt="auth.user.name"
                            class="w-full h-full object-cover"
                        />
                        <svg v-else class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-black uppercase tracking-wider truncate">{{ auth.user?.name }}</div>
                        <div class="text-[10px] text-white/50 truncate">{{ auth.user?.email }}</div>
                    </div>
                </div>
                <div v-else class="hidden lg:flex justify-center mb-3">
                    <div class="w-9 h-9 border-2 border-white bg-[#F2ECE4] flex items-center justify-center overflow-hidden">
                        <img
                            v-if="auth.user?.avatar"
                            :src="`/storage/${auth.user.avatar}`"
                            class="w-full h-full object-cover"
                        />
                        <svg v-else class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>

                <!-- Logout Button -->
                <button
                    @click="handleLogout"
                    :class="[
                        'w-full flex items-center gap-2 px-3 py-2.5 text-sm font-bold uppercase tracking-wider text-red-400 hover:bg-red-600 hover:text-white transition-all border-2 border-transparent hover:border-red-700',
                        !sidebarOpen ? 'lg:justify-center' : '',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span v-if="sidebarOpen">Logout</span>
                </button>
            </div>
        </aside>

        <!-- ============================================
             MAIN CONTENT
             ============================================ -->
        <div
            :class="[
                'flex-1 flex flex-col transition-all duration-300 min-w-0',
                sidebarOpen ? 'lg:ml-64' : 'lg:ml-20',
            ]"
        >
            <!-- Topbar -->
            <header class="bg-white border-b-2 border-[#0a0a0a] h-16 flex items-center px-4 lg:px-6 gap-4 sticky top-0 z-30">

                <!-- Hamburger (mobile) -->
                <button
                    @click="mobileOpen = true"
                    class="lg:hidden w-9 h-9 flex items-center justify-center border-2 border-[#0a0a0a] bg-white text-[#0a0a0a] hover:bg-[#0a0a0a] hover:text-white transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Toggle collapse (desktop) -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="hidden lg:flex w-9 h-9 items-center justify-center border-2 border-[#0a0a0a] bg-white text-[#0a0a0a] hover:bg-[#0a0a0a] hover:text-white transition-all"
                    :title="sidebarOpen ? 'Kecilkan sidebar' : 'Besarkan sidebar'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex-1 min-w-0">
                    <h1 class="font-black uppercase tracking-tight text-lg text-[#0a0a0a] leading-none">Admin Panel</h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-0.5 hidden sm:block">
                        Selamat datang, {{ auth.user?.name }}
                    </p>
                </div>

                <RouterLink
                    to="/"
                    target="_blank"
                    class="hidden sm:flex items-center gap-2 bg-white text-[#0a0a0a] px-4 py-2 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] transition-all"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Lihat Toko
                </RouterLink>
            </header>

            <!-- Page -->
            <main class="flex-1 p-4 lg:p-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>