<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useWishlistStore } from '@/stores/wishlist';

const router = useRouter();
const auth = useAuthStore();
const cart = useCartStore();
const wishlist = useWishlistStore();

const { user, isAuthenticated, isAdmin } = storeToRefs(auth);
const { itemCount } = storeToRefs(cart);
const { count: wishlistCount } = storeToRefs(wishlist);

const searchQuery = ref('');
const userMenuOpen = ref(false);

function handleSearch() {
    if (searchQuery.value.trim()) router.push({ name: 'catalog', query: { q: searchQuery.value } });
}

async function handleLogout() {
    await auth.logout();
    router.push('/login');
}

onMounted(async () => {
    if (isAuthenticated.value) await Promise.all([cart.fetchSummary(), wishlist.fetchWishlist()]);
});
</script>

<template>
    <nav class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <RouterLink to="/" class="flex items-center gap-2 text-xl font-bold text-primary-600 shrink-0">
                    📚 <span class="hidden sm:inline">LiteraSee</span>
                </RouterLink>

                <form @submit.prevent="handleSearch" class="hidden md:flex flex-1 max-w-xl mx-8">
                    <div class="relative w-full">
                        <input v-model="searchQuery" type="text" placeholder="Cari judul, penulis..."
                            class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20" />
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    <RouterLink to="/catalog" class="hidden lg:block px-3 py-2 text-gray-700 hover:text-primary-600 font-medium">Katalog</RouterLink>

                    <template v-if="isAuthenticated">
                        <RouterLink to="/wishlist" class="relative p-2 text-gray-700 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span v-if="wishlistCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ wishlistCount }}</span>
                        </RouterLink>

                        <RouterLink to="/cart" class="relative p-2 text-gray-700 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span v-if="itemCount > 0" class="absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ itemCount }}</span>
                        </RouterLink>

                        <div class="relative">
                            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100">
                                <img :src="user?.avatar_url" :alt="user?.name" class="w-8 h-8 rounded-full bg-gray-200" />
                                <span class="hidden lg:block text-sm font-medium">{{ user?.name }}</span>
                            </button>
                            <Transition name="fade">
                                <div v-if="userMenuOpen" @click="userMenuOpen = false"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50">
                                    <RouterLink to="/profile" class="block px-4 py-2 text-sm hover:bg-gray-50">👤 Profil Saya</RouterLink>
                                    <RouterLink to="/orders" class="block px-4 py-2 text-sm hover:bg-gray-50">📦 Pesanan Saya</RouterLink>
                                    <template v-if="isAdmin">
                                        <hr class="my-1" />
                                        <RouterLink to="/admin/dashboard" class="block px-4 py-2 text-sm text-primary-600 hover:bg-gray-50">🎛️ Admin Panel</RouterLink>
                                    </template>
                                    <hr class="my-1" />
                                    <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">🚪 Logout</button>
                                </div>
                            </Transition>
                        </div>
                    </template>

                    <template v-else>
                        <RouterLink to="/login" class="hidden sm:block px-4 py-2 text-gray-700 font-medium hover:text-primary-600">Login</RouterLink>
                        <RouterLink to="/register" class="btn-primary text-sm">Daftar</RouterLink>
                    </template>
                </div>
            </div>
        </div>
        <div v-if="userMenuOpen" @click="userMenuOpen = false" class="fixed inset-0 z-30"></div>
    </nav>
</template>