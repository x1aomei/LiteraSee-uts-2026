<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import api from '@/api/axios';
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

// ============================================
// KATEGORI DINAMIS DARI API
// ============================================
const categories = ref([]);

async function fetchCategories() {
    try {
        const { data } = await api.get('/categories');
        categories.value = (data.data || []).slice(0, 8); // max 8 chip
    } catch (e) {
        console.error('Gagal load kategori:', e);
        categories.value = [];
    }
}

function handleSearch() {
    if (searchQuery.value.trim()) {
        router.push({ name: 'catalog', query: { q: searchQuery.value } });
    }
}

function searchByCategory(slug) {
    router.push({ name: 'catalog', query: { category: slug } });
}

async function handleLogout() {
    await auth.logout();
    router.push('/login');
    userMenuOpen.value = false;
}

onMounted(async () => {
    fetchCategories();
    if (isAuthenticated.value) {
        await Promise.all([cart.fetchSummary(), wishlist.fetchWishlist()]);
    }
});
</script>

<template>
    <nav class="sticky top-0 z-40 w-full bg-white border-b-2 border-[#0a0a0a]">

        <!-- ============ TOP BAR ============ -->
        <div class="hidden md:block bg-[#0a0a0a] text-white">
            <div class="max-w-7xl mx-auto px-4 h-9 flex items-center justify-between">

                <div class="flex items-center gap-5">
                    <RouterLink to="/catalog?category=fiksi" class="text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">Fiksi</RouterLink>
                    <RouterLink to="/catalog?category=non-fiksi" class="text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">Non-Fiksi</RouterLink>
                    <RouterLink to="/catalog?category=anak-anak" class="text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">Buku Anak</RouterLink>
                    <RouterLink to="/catalog?category=komik-manga" class="text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">Komik</RouterLink>
                    <span class="text-white/20">|</span>
                    <RouterLink to="/promo" class="text-[10px] font-black uppercase tracking-widest text-yellow-300 hover:text-yellow-200 transition-colors">Promo Hari Ini</RouterLink>
                </div>

                <div class="flex items-center gap-5">
                    <RouterLink to="/orders" class="text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">Pesanan Saya</RouterLink>
                    <a href="#" class="text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">Bantuan</a>
                    <span class="text-white/20">|</span>

                    <template v-if="!isAuthenticated">
                        <RouterLink to="/register" class="text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">Daftar</RouterLink>
                        <RouterLink to="/login" class="text-[10px] font-black uppercase tracking-widest bg-[#8C5830] text-white px-3 py-1 border-2 border-[#8C5830] hover:bg-white hover:text-[#8C5830] transition-colors">Masuk</RouterLink>
                    </template>

                    <template v-else>
                        <span class="text-[10px] font-black uppercase tracking-widest text-white/60">
                            Hai, <span class="text-white">{{ user?.name }}</span>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- ============ MAIN BAR ============ -->
        <div class="bg-[#F2ECE4]">
            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4 md:gap-6">

                <!-- Logo -->
                <RouterLink to="/" class="flex items-center gap-2.5 shrink-0 group">
                    <img
                        src="/logo3.jpg"
                        alt="LiteraSee"
                        style="height: 40px; width: auto;"
                        class="object-contain block border-2 border-[#0a0a0a] p-0.5 bg-white shadow-[2px_2px_0_0_#0a0a0a] group-hover:translate-x-[1px] group-hover:translate-y-[1px] group-hover:shadow-none transition-all"
                    />
                    <span class="text-2xl font-black uppercase tracking-tight hidden sm:flex">
                        <span class="text-[#0a0a0a]">Litera</span>
                        <span class="text-[#8C5830]">Store</span>
                    </span>
                </RouterLink>

                <!-- Search + Kategori Chips -->
                <div class="flex-1 max-w-2xl hidden md:block">
                    <form @submit.prevent="handleSearch" class="flex border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a]">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari judul buku, penulis, atau ISBN..."
                            class="flex-1 bg-white px-4 py-2.5 text-sm font-medium text-[#0a0a0a] placeholder:text-[#0a0a0a]/40 focus:outline-none border-0"
                        />
                        <button
                            type="submit"
                            class="bg-[#8C5830] text-white px-6 hover:bg-[#0a0a0a] transition-colors flex items-center justify-center border-l-2 border-[#0a0a0a]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </form>

                    <!-- ============================================
                         KATEGORI CHIPS — DINAMIS DARI API
                         ============================================ -->
                    <div
                        v-if="categories.length"
                        class="flex gap-2 mt-2.5 overflow-x-auto whitespace-nowrap pb-1"
                        style="scrollbar-width: none; -ms-overflow-style: none;"
                    >
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            @click="searchByCategory(cat.slug)"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 bg-white text-[#0a0a0a] text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[2px_2px_0_0_#0a0a0a] hover:bg-[#8C5830] hover:text-white hover:border-[#8C5830] hover:shadow-[2px_2px_0_0_#8C5830] hover:translate-x-[1px] hover:translate-y-[1px] transition-all duration-100"
                        >
                            {{ cat.name }}
                            <span
                                v-if="cat.active_books_count > 0"
                                class="min-w-[16px] h-4 flex items-center justify-center px-1 text-[8px] font-black border border-current"
                            >
                                {{ cat.active_books_count }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Icons Kanan -->
                <div class="flex items-center gap-1 md:gap-2 shrink-0">

                    <!-- Search Mobile -->
                    <button class="md:hidden w-10 h-10 flex items-center justify-center border-2 border-[#0a0a0a] bg-white text-[#0a0a0a] hover:bg-[#0a0a0a] hover:text-white shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- WISHLIST -->
                    <RouterLink
                        to="/wishlist"
                        class="relative w-10 h-10 flex items-center justify-center border-2 border-[#0a0a0a] bg-white text-[#0a0a0a] hover:bg-red-600 hover:text-white hover:border-red-600 shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span
                            v-if="wishlistCount > 0"
                            class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center bg-red-600 text-white text-[10px] font-black border-2 border-[#0a0a0a]"
                        >
                            {{ wishlistCount }}
                        </span>
                    </RouterLink>

                    <!-- CART -->
                    <RouterLink
                        to="/cart"
                        class="relative w-10 h-10 flex items-center justify-center border-2 border-[#0a0a0a] bg-white text-[#0a0a0a] hover:bg-[#8C5830] hover:text-white hover:border-[#8C5830] shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span
                            v-if="itemCount > 0"
                            class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center bg-[#8C5830] text-white text-[10px] font-black border-2 border-[#0a0a0a]"
                        >
                            {{ itemCount }}
                        </span>
                    </RouterLink>

                    <!-- PROFILE -->
                    <RouterLink
                        v-if="!isAuthenticated"
                        to="/login"
                        class="flex items-center gap-2 px-3 h-10 border-2 border-[#0a0a0a] bg-white text-[#0a0a0a] hover:bg-[#0a0a0a] hover:text-white shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden lg:block text-[10px] font-black uppercase tracking-widest">Masuk</span>
                    </RouterLink>

                    <div v-else class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2 pl-1 pr-3 h-10 border-2 border-[#0a0a0a] bg-white shadow-[2px_2px_0_0_#0a0a0a] hover:bg-[#F2ECE4] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all"
                        >
                            <div class="w-7 h-7 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] overflow-hidden">
                                <img v-if="user?.avatar_url" :src="user.avatar_url" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-[#8C5830] text-white text-xs font-black">
                                    {{ user?.name?.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <span class="hidden lg:block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] max-w-[100px] truncate">
                                {{ user?.name }}
                            </span>
                            <svg class="w-3 h-3 text-[#0a0a0a] hidden lg:block transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <Transition name="fade">
                            <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-64 bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] z-50 overflow-hidden">
                                <div class="bg-[#0a0a0a] text-white px-4 py-3 border-b-2 border-[#0a0a0a]">
                                    <div class="text-xs font-black uppercase tracking-wider truncate">{{ user?.name }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-white/50 truncate mt-0.5">{{ user?.email }}</div>
                                </div>

                                <RouterLink to="/profile" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] hover:bg-[#F2ECE4] transition-colors">
                                    <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Profil Saya
                                </RouterLink>

                                <RouterLink to="/orders" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] hover:bg-[#F2ECE4] transition-colors">
                                    <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    Riwayat Pesanan
                                </RouterLink>

                                <RouterLink to="/wishlist" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] hover:bg-[#F2ECE4] transition-colors">
                                    <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    Wishlist
                                </RouterLink>

                                <template v-if="isAdmin">
                                    <div class="border-t-2 border-dashed border-[#0a0a0a]/15 my-1"></div>
                                    <RouterLink to="/admin/dashboard" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-[#8C5830] hover:bg-[#F2ECE4] transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Admin Panel
                                    </RouterLink>
                                </template>

                                <div class="border-t-2 border-[#0a0a0a]">
                                    <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="userMenuOpen" @click="userMenuOpen = false" class="fixed inset-0 z-30"></div>
    </nav>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>