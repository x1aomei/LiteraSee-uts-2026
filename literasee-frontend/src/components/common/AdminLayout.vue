<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const sidebarOpen = ref(true);
const mobileOpen = ref(false);
const userMenuOpen = ref(false);

const menu = [
    { name: 'Dashboard', icon: '📊', path: '/admin/dashboard' },
    { name: 'Buku', icon: '📚', path: '/admin/books' },
    { name: 'Kategori', icon: '📂', path: '/admin/categories' },
    { name: 'Pesanan', icon: '📦', path: '/admin/orders' },
    { name: 'Pengguna', icon: '👥', path: '/admin/users' },
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
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Mobile Overlay -->
        <div v-if="mobileOpen" @click="mobileOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <!-- SIDEBAR -->
        <aside
            :class="[
                'bg-gray-900 text-white flex flex-col fixed inset-y-0 left-0 z-50 transition-all duration-300',
                sidebarOpen ? 'w-64' : 'w-20',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 shrink-0">
                <RouterLink to="/admin/dashboard" class="flex items-center gap-2 overflow-hidden">
                    <span class="text-2xl shrink-0">📚</span>
                    <span v-if="sidebarOpen" class="font-bold text-lg whitespace-nowrap">LiteraSee</span>
                </RouterLink>
                <button
                    v-if="sidebarOpen"
                    @click="sidebarOpen = false"
                    class="hidden lg:block text-gray-400 hover:text-white p-1"
                    title="Kecilkan"
                >«</button>
                <button
                    @click="mobileOpen = false"
                    class="lg:hidden text-gray-400 hover:text-white text-xl"
                >×</button>
            </div>

            <!-- Expand button (saat collapsed) -->
            <div v-if="!sidebarOpen" class="hidden lg:flex p-2 justify-center">
                <button @click="sidebarOpen = true" class="text-gray-400 hover:text-white p-2 rounded hover:bg-gray-800" title="Besarkan">»</button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 py-4 overflow-y-auto">
                <ul class="space-y-1 px-2">
                    <li v-for="item in menu" :key="item.path">
                        <RouterLink
                            :to="item.path"
                            @click="mobileOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all',
                                isActive(item.path)
                                    ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/30'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white',
                                !sidebarOpen ? 'lg:justify-center' : '',
                            ]"
                            :title="!sidebarOpen ? item.name : ''"
                        >
                            <span class="text-xl shrink-0">{{ item.icon }}</span>
                            <span v-if="sidebarOpen" class="text-sm font-medium whitespace-nowrap">{{ item.name }}</span>
                        </RouterLink>
                    </li>
                </ul>
            </nav>

            <!-- User -->
            <div class="border-t border-gray-800 p-3 shrink-0">
                <div v-if="sidebarOpen" class="flex items-center gap-3 mb-3 p-2 rounded bg-gray-800/50">
                    <img :src="auth.user?.avatar_url" class="w-9 h-9 rounded-full shrink-0 bg-gray-700" />
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium truncate">{{ auth.user?.name }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ auth.user?.email }}</div>
                    </div>
                </div>
                <div v-else class="hidden lg:flex justify-center mb-3">
                    <img :src="auth.user?.avatar_url" class="w-9 h-9 rounded-full bg-gray-700" />
                </div>

                <button
                    @click="handleLogout"
                    :class="[
                        'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition text-sm',
                        !sidebarOpen ? 'lg:justify-center' : '',
                    ]"
                >
                    <span class="text-xl shrink-0">🚪</span>
                    <span v-if="sidebarOpen" class="font-medium">Logout</span>
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div
            :class="[
                'flex-1 flex flex-col transition-all duration-300 min-w-0',
                sidebarOpen ? 'lg:ml-64' : 'lg:ml-20',
            ]"
        >
            <!-- Topbar -->
            <header class="bg-white shadow-sm h-16 flex items-center px-4 lg:px-6 gap-4 sticky top-0 z-30">
                <!-- Hamburger (mobile) -->
                <button
                    @click="mobileOpen = true"
                    class="lg:hidden text-gray-600 hover:text-primary-600 p-2 -ml-2 rounded-lg hover:bg-gray-100"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Toggle collapse (desktop) -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="hidden lg:block text-gray-600 hover:text-primary-600 p-2 -ml-2 rounded-lg hover:bg-gray-100"
                    :title="sidebarOpen ? 'Kecilkan sidebar' : 'Besarkan sidebar'"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex-1">
                    <h1 class="font-bold text-lg text-gray-800">Admin Panel</h1>
                    <p class="text-xs text-gray-500 hidden sm:block">
                        Selamat datang kembali, {{ auth.user?.name }} 👋
                    </p>
                </div>

                <RouterLink to="/" target="_blank" class="btn-outline text-sm flex items-center gap-2">
                    🌐 <span class="hidden sm:inline">Lihat Toko</span>
                </RouterLink>
            </header>

            <!-- Page -->
            <main class="flex-1 p-4 lg:p-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>
