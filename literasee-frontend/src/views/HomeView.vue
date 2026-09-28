<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/api/axios';
import BookCard from '@/components/book/BookCard.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useWishlistStore } from '@/stores/wishlist';

const route = useRoute();
const auth = useAuthStore();
const cart = useCartStore();
const wishlist = useWishlistStore();

const menus = computed(() => [
    {
        name: 'Home',
        to: '/',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
    },
    {
        name: 'Katalog',
        to: '/catalog',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
    },
    {
        name: 'Wishlist',
        to: '/wishlist',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
        badge: wishlist.items?.length || 0,
    },
    {
        name: 'Keranjang',
        to: '/cart',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        badge: cart.items?.length || 0,
    },
    {
        name: 'Pesanan',
        to: '/orders',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
    },
    {
        name: 'Profil',
        to: '/profile',
        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
    },
]);

function isActive(path) {
    if (path === '/') return route.path === '/';
    return route.path.startsWith(path);
}

async function handleLogout() {
    await auth.logout();
    window.location.href = '/';
}

const categories = ref([]);
const featuredBooks = ref([]);
const latestBooks = ref([]);
const onSaleBooks = ref([]);
const loading = ref(true);

// ============================================
// STATE UNTUK MODAL "SEMUA KATEGORI"
// ============================================
const showAllCategoriesModal = ref(false);

// 6 kategori pertama untuk grid
const mainCategories = computed(() => categories.value.slice(0, 6));

// Sisa kategori untuk modal
const otherCategories = computed(() => categories.value.slice(6));

// Apakah ada lebih dari 6 kategori?
const hasMoreCategories = computed(() => categories.value.length > 6);

const featuredScrollRef = ref(null);
const latestScrollRef = ref(null);

function scrollLeft(el) {
    if (!el) return;
    el.scrollBy({ left: -el.clientWidth * 0.8, behavior: 'smooth' });
}
function scrollRight(el) {
    if (!el) return;
    el.scrollBy({ left: el.clientWidth * 0.8, behavior: 'smooth' });
}

const categoryIcons = [
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.121 2.121 0 0114.1 24.15L8.27 18.32M11.42 15.17l-4.65-4.65a4.242 4.242 0 116-6L17.25 9.75M11.42 15.17l4.65 4.65"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.875 2.122L7 21h10l-1.125-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12H3V5.25"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h16.5V18H3.75V3zM6 16.5h12.75A2.25 2.25 0 0021 14.25V3"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 10.100V18.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 18.75v3.35"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
];

const dummyBooks = [
    { id: 1, title: 'Filosofi Teras', author: 'Henry Manampiring', price: 98000, cover_image: 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=600&auto=format&fit=crop' },
    { id: 2, title: 'Atomic Habits', author: 'James Clear', price: 125000, cover_image: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=600&auto=format&fit=crop' },
    { id: 3, title: 'Bumi Manusia', author: 'Pramoedya Ananta Toer', price: 85000, cover_image: 'https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=600&auto=format&fit=crop' },
    { id: 4, title: 'Pulang', author: 'Leila S. Chudori', price: 95000, cover_image: 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=600&auto=format&fit=crop' }
];

async function fetchHome() {
    loading.value = true;
    try {
        const { data } = await api.get('/home');

        // Ambil SEMUA kategori untuk modal — pakai /categories endpoint
        let allCats = [];
        try {
            const catRes = await api.get('/categories');
            allCats = catRes.data.data || [];
        } catch (e) {
            // fallback ke categories dari home
            allCats = data.data.categories || [];
        }

        categories.value = allCats.map((cat, index) => ({
            ...cat,
            svgIcon: categoryIcons[index % categoryIcons.length]
        }));

        featuredBooks.value = data.data.featured_books?.length ? data.data.featured_books : dummyBooks;
        latestBooks.value = data.data.latest_books?.length ? data.data.latest_books : dummyBooks;
        onSaleBooks.value = data.data.on_sale_books || [];
    } catch (e) {
        console.error(e);
        categories.value = [
            { id: 1, name: 'Fiksi', slug: 'fiksi', active_books_count: 12, svgIcon: categoryIcons[0] },
            { id: 2, name: 'Novel', slug: 'novel', active_books_count: 8, svgIcon: categoryIcons[1] },
            { id: 3, name: 'Self Improvement', slug: 'self-improvement', active_books_count: 15, svgIcon: categoryIcons[2] },
            { id: 4, name: 'Teknologi', slug: 'teknologi', active_books_count: 6, svgIcon: categoryIcons[3] },
            { id: 5, name: 'Bisnis', slug: 'bisnis', active_books_count: 10, svgIcon: categoryIcons[4] },
            { id: 6, name: 'Sejarah', slug: 'sejarah', active_books_count: 5, svgIcon: categoryIcons[5] },
        ];
        featuredBooks.value = dummyBooks;
        latestBooks.value = dummyBooks;
    } finally {
        loading.value = false;
    }
}

function openAllCategories() {
    showAllCategoriesModal.value = true;
}

function closeAllCategories() {
    showAllCategoriesModal.value = false;
}

onMounted(fetchHome);
</script>

<template>
    <section class="relative bg-cover bg-center text-white overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=1920&auto=format&fit=crop');">
        <div class="absolute inset-0 bg-gradient-to-r from-[#3B2E26]/95 via-[#5C4A3F]/85 to-[#3B2E26]/70 backdrop-blur-[2px]"></div>

        <div class="relative max-w-7xl mx-auto px-4 py-20 md:py-28">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-center">

                <div class="lg:col-span-7">
                    <span class="inline-flex items-center gap-2 bg-[#7A6355]/60 text-[#F2ECE4] text-xs font-bold px-3 py-1.5 mb-4 tracking-widest uppercase border border-[#9A7D6B]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                        Toko Buku Digital Terpercaya
                    </span>
                    <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight drop-shadow-md tracking-tight">
                        Temukan Buku Favoritmu di <span class="text-[#E4DBD0] underline decoration-[#9A7D6B] decoration-wavy underline-offset-8">LiteraStore</span>
                    </h1>
                    <p class="text-lg md:text-xl text-neutral-200 mb-8 drop-shadow font-light leading-relaxed">
                        Ribuan koleksi buku pilihan dari berbagai genre menantimu. Belanja mudah, cepat, dan harga bersahabat!
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <RouterLink
                            to="/catalog"
                            class="bg-[#8C5830] text-white px-8 py-4 font-bold shadow-xl hover:bg-[#A36A3D] transition-all duration-300 transform hover:-translate-y-0.5 inline-flex items-center gap-2 border border-[#B87A4A] tracking-wider uppercase text-sm"
                        >
                            <span>Mulai Belanja</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </RouterLink>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="relative bg-white text-[#3B2E26] border-2 border-[#0a0a0a] shadow-[8px_8px_0_0_#0a0a0a] overflow-hidden">
                        <div class="absolute top-0 right-0 bg-[#8C5830] text-white px-4 py-2 z-10 border-l-2 border-b-2 border-[#0a0a0a]">
                            <div class="text-[10px] font-bold uppercase tracking-widest">Diskon</div>
                            <div class="text-2xl font-black leading-none">50%</div>
                        </div>

                        <div class="bg-[#F2ECE4] border-b-2 border-[#0a0a0a] px-6 py-4">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-[2px] bg-[#8C5830]"></span>
                                <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[#8C5830]">
                                    Promo Hari Ini
                                </span>
                            </div>
                            <h3 class="text-xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                                Flash Sale<br/>Buku Pilihan
                            </h3>
                        </div>

                        <div class="p-6 space-y-4">
                            <ul class="space-y-2.5">
                                <li class="flex items-start gap-3">
                                    <div class="w-5 h-5 flex-shrink-0 border-2 border-[#0a0a0a] flex items-center justify-center bg-[#8C5830]">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="square" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-black uppercase tracking-wider">Diskon hingga 50%</div>
                                        <div class="text-[11px] text-[#3B2E26]/60 font-medium">Untuk 100 buku pilihan</div>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-5 h-5 flex-shrink-0 border-2 border-[#0a0a0a] flex items-center justify-center bg-[#8C5830]">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="square" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-black uppercase tracking-wider">Gratis Ongkir</div>
                                        <div class="text-[11px] text-[#3B2E26]/60 font-medium">Min. belanja Rp 150.000</div>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-5 h-5 flex-shrink-0 border-2 border-[#0a0a0a] flex items-center justify-center bg-[#8C5830]">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="square" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-black uppercase tracking-wider">Voucher Rp 20.000</div>
                                        <div class="text-[11px] text-[#3B2E26]/60 font-medium">Khusus member baru</div>
                                    </div>
                                </li>
                            </ul>

                            <div class="border-t-2 border-dashed border-[#0a0a0a]/15 pt-4">
                                <div class="text-[10px] font-bold uppercase tracking-widest text-[#3B2E26]/50 mb-2 text-center">
                                    Berakhir Dalam
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-[#0a0a0a] text-white text-center py-2 border-2 border-[#0a0a0a]">
                                        <div class="text-lg font-black leading-none">02</div>
                                        <div class="text-[9px] font-bold uppercase tracking-widest text-white/60 mt-0.5">Hari</div>
                                    </div>
                                    <div class="bg-[#0a0a0a] text-white text-center py-2 border-2 border-[#0a0a0a]">
                                        <div class="text-lg font-black leading-none">12</div>
                                        <div class="text-[9px] font-bold uppercase tracking-widest text-white/60 mt-0.5">Jam</div>
                                    </div>
                                    <div class="bg-[#0a0a0a] text-white text-center py-2 border-2 border-[#0a0a0a]">
                                        <div class="text-lg font-black leading-none">45</div>
                                        <div class="text-[9px] font-bold uppercase tracking-widest text-white/60 mt-0.5">Menit</div>
                                    </div>
                                </div>
                            </div>

                            <RouterLink
                                to="/catalog"
                                class="flex items-center justify-center gap-2 w-full bg-[#8C5830] text-white py-3 font-black uppercase tracking-widest text-xs border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_0_#0a0a0a] transition-all duration-100"
                            >
                                Ambil Promo
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="square" stroke-linejoin="miter" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <LoadingSpinner v-if="loading" />

    <div v-else class="bg-[#F2ECE4] text-[#3B2E26] min-h-screen">
        <div class="max-w-[1600px] mx-auto px-4 py-12">

            <div class="grid lg:grid-cols-[260px_1fr] gap-8 items-start">

                <aside class="hidden lg:block">
                    <div class="sticky top-[7rem] max-h-[calc(100vh-8rem)] overflow-y-auto space-y-4 pr-1">
                        <!-- ... sidebar content sama seperti sebelumnya ... -->
                        <div v-if="auth.isAuthenticated" class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center overflow-hidden">
                                    <img v-if="auth.user?.avatar" :src="`/storage/${auth.user.avatar}`" class="w-full h-full object-cover" />
                                    <svg v-else class="w-5 h-5 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a] truncate">{{ auth.user?.name || 'Guest' }}</div>
                                    <div class="text-[10px] text-[#0a0a0a]/50 font-medium truncate">{{ auth.user?.email }}</div>
                                </div>
                            </div>
                        </div>

                        <nav class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                            <div class="bg-[#0a0a0a] text-white px-4 py-2.5">
                                <span class="text-[10px] font-black uppercase tracking-[0.25em]">Menu Navigasi</span>
                            </div>
                            <ul class="py-2">
                                <li v-for="menu in menus" :key="menu.to">
                                    <RouterLink
                                        :to="menu.to"
                                        class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm font-bold uppercase tracking-wider transition-all duration-100"
                                        :class="isActive(menu.to)
                                            ? 'bg-[#8C5830] text-white border-2 border-[#0a0a0a] shadow-[2px_2px_0_0_#0a0a0a]'
                                            : 'text-[#0a0a0a] hover:bg-[#F2ECE4]'"
                                    >
                                        <span class="flex-shrink-0" :class="isActive(menu.to) ? 'text-white' : 'text-[#5C4A3F]'" v-html="menu.icon" />
                                        <span class="flex-1">{{ menu.name }}</span>
                                        <span v-if="menu.badge" class="flex-shrink-0 min-w-[20px] h-5 flex items-center justify-center px-1.5 text-[10px] font-black border-2 border-[#0a0a0a]" :class="isActive(menu.to) ? 'bg-white text-[#0a0a0a]' : 'bg-[#8C5830] text-white'">{{ menu.badge }}</span>
                                    </RouterLink>
                                </li>
                            </ul>
                            <div v-if="auth.isAuthenticated" class="border-t-2 border-[#0a0a0a]">
                                <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-2.5 mx-2 my-1 text-sm font-bold uppercase tracking-wider text-red-600 hover:bg-red-50 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Logout</span>
                                </button>
                            </div>
                        </nav>

                        <div class="bg-[#8C5830] text-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-4">
                            <div class="text-[10px] font-black uppercase tracking-[0.25em] mb-1 text-white/70">Member Baru?</div>
                            <div class="text-sm font-black uppercase tracking-wider mb-2">Dapatkan Voucher Rp 20.000</div>
                            <p class="text-[11px] text-white/80 mb-3 font-medium leading-relaxed">Daftar sekarang dan nikmati promo eksklusif member.</p>
                            <RouterLink v-if="!auth.isAuthenticated" to="/register" class="inline-flex items-center justify-center gap-2 w-full bg-white text-[#0a0a0a] py-2 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                                Daftar
                            </RouterLink>
                        </div>
                    </div>
                </aside>

                <!-- ============================================
                     MAIN CONTENT
                     ============================================ -->
                <main class="min-w-0">

                    <!-- ============================================
                         KATEGORI POPULER — DENGAN CARD "..." 
                         ============================================ -->
                    <section class="mb-16">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-8 h-[2px] bg-[#5C4A3F]"></span>
                                    <span class="text-[10px] font-black tracking-[0.25em] text-[#5C4A3F] uppercase">Jelajahi Genre</span>
                                </div>
                                <h2 class="text-2xl md:text-3xl font-black tracking-wider uppercase text-[#3B2E26]">
                                    Kategori Populer
                                </h2>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
                            <!-- 6 kategori utama -->
                            <RouterLink
                                v-for="cat in mainCategories"
                                :key="cat.id"
                                :to="{ path: '/catalog', query: { category: cat.slug } }"
                                class="bg-white border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] group hover:shadow-[5px_5px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 flex flex-col overflow-hidden"
                            >
                                <div class="bg-[#5C4A3F] text-white py-1 px-3 text-[10px] font-bold tracking-widest uppercase text-center group-hover:bg-[#3B2E26] transition-colors">
                                    GENRE
                                </div>
                                <div class="p-4 text-center flex-1 flex flex-col items-center justify-center bg-white group-hover:bg-[#FAF7F2] transition-colors">
                                    <div class="w-12 h-12 rounded-full bg-[#F2ECE4] flex items-center justify-center mb-2 group-hover:scale-110 transition-all duration-300" v-html="cat.svgIcon"></div>
                                    <div class="text-xs font-bold text-[#3B2E26] mb-1 line-clamp-1">{{ cat.name }}</div>
                                    <div class="text-[10px] text-[#8C7A6B] font-bold tracking-wider">{{ cat.active_books_count || 0 }} BUKU</div>
                                </div>
                            </RouterLink>

                            <!-- Card "Lainnya" kalau kategori > 6 -->
                            <button
                                v-if="hasMoreCategories"
                                @click="openAllCategories"
                                class="bg-[#0a0a0a] border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#8C5830] group hover:shadow-[5px_5px_0_0_#8C5830] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 flex flex-col overflow-hidden text-left"
                            >
                                <div class="bg-[#8C5830] text-white py-1 px-3 text-[10px] font-bold tracking-widest uppercase text-center">
                                    +{{ otherCategories.length }}
                                </div>
                                <div class="p-4 text-center flex-1 flex flex-col items-center justify-center bg-[#0a0a0a] text-white">
                                    <div class="w-12 h-12 rounded-full bg-[#8C5830] flex items-center justify-center mb-2 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </div>
                                    <div class="text-xs font-black uppercase tracking-wider mb-1">Lainnya</div>
                                    <div class="text-[10px] text-white/60 font-bold tracking-wider">Lihat Semua</div>
                                </div>
                            </button>
                        </div>
                    </section>

                    <!-- Buku Unggulan, Buku Terbaru, Diskon — sama seperti sebelumnya -->
                    <section class="mb-16">
                        <div class="flex items-end justify-between mb-6 pb-4 border-b-2 border-[#0a0a0a] gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-8 h-[2px] bg-[#8C5830]"></span>
                                    <span class="text-[10px] font-black tracking-[0.25em] text-[#8C5830] uppercase">Pilihan Kurator</span>
                                </div>
                                <h2 class="text-2xl md:text-3xl font-black tracking-wide text-[#3B2E26] flex items-center gap-2">
                                    <svg class="w-6 h-6 text-[#8C5830]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Buku Unggulan
                                </h2>
                            </div>
                            <RouterLink to="/catalog" class="inline-flex items-center gap-2 bg-[#5C4A3F] text-white px-4 py-2 text-[10px] font-black tracking-widest uppercase hover:bg-[#3B2E26] transition border-2 border-[#0a0a0a] shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none">
                                <span>LIHAT SEMUA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </RouterLink>
                        </div>

                        <div class="relative">
                            <div ref="featuredScrollRef" class="flex gap-4 overflow-x-auto scroll-smooth pb-2 snap-x snap-mandatory" style="scrollbar-width: none; -ms-overflow-style: none;">
                                <div v-for="book in featuredBooks" :key="book.id" class="flex-shrink-0 w-[calc(50%-8px)] sm:w-[calc(33.333%-11px)] xl:w-[calc(25%-12px)] snap-start">
                                    <BookCard :book="book" />
                                </div>
                            </div>
                            <button @click="scrollLeft(featuredScrollRef)" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 w-9 h-9 flex items-center justify-center bg-white border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[-4px] hover:shadow-[4px_4px_0_0_#0a0a0a] active:translate-x-[-1px] active:translate-y-[3px] active:shadow-none transition-all duration-100 z-10 hidden md:flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="scrollRight(featuredScrollRef)" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 w-9 h-9 flex items-center justify-center bg-white border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[4px] hover:shadow-[4px_4px_0_0_#0a0a0a] active:translate-x-[1px] active:translate-y-[3px] active:shadow-none transition-all duration-100 z-10 hidden md:flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </section>

                    <section class="mb-16">
                        <div class="flex items-end justify-between mb-6 pb-4 border-b-2 border-[#0a0a0a] gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-8 h-[2px] bg-[#4A6B82]"></span>
                                    <span class="text-[10px] font-black tracking-[0.25em] text-[#4A6B82] uppercase">Koleksi Segar</span>
                                </div>
                                <h2 class="text-2xl md:text-3xl font-black tracking-wide text-[#3B2E26] flex items-center gap-2">
                                    <svg class="w-6 h-6 text-[#4A6B82]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Buku Terbaru
                                </h2>
                            </div>
                            <RouterLink to="/catalog" class="inline-flex items-center gap-2 bg-[#5C4A3F] text-white px-4 py-2 text-[10px] font-black tracking-widest uppercase hover:bg-[#3B2E26] transition border-2 border-[#0a0a0a] shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none">
                                <span>LIHAT SEMUA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </RouterLink>
                        </div>
                        <div class="relative">
                            <div ref="latestScrollRef" class="flex gap-4 overflow-x-auto scroll-smooth pb-2 snap-x snap-mandatory" style="scrollbar-width: none; -ms-overflow-style: none;">
                                <div v-for="book in latestBooks" :key="book.id" class="flex-shrink-0 w-[calc(50%-8px)] sm:w-[calc(33.333%-11px)] xl:w-[calc(25%-12px)] snap-start">
                                    <BookCard :book="book" />
                                </div>
                            </div>
                            <button @click="scrollLeft(latestScrollRef)" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 w-9 h-9 flex items-center justify-center bg-white border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[-4px] hover:shadow-[4px_4px_0_0_#0a0a0a] active:translate-x-[-1px] active:translate-y-[3px] active:shadow-none transition-all duration-100 z-10 hidden md:flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="scrollRight(latestScrollRef)" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 w-9 h-9 flex items-center justify-center bg-white border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[4px] hover:shadow-[4px_4px_0_0_#0a0a0a] active:translate-x-[1px] active:translate-y-[3px] active:shadow-none transition-all duration-100 z-10 hidden md:flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </section>

                    <section v-if="onSaleBooks.length">
                        <div class="flex items-end justify-between mb-6 pb-4 border-b-2 border-[#0a0a0a] gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-8 h-[2px] bg-[#8C5830]"></span>
                                    <span class="text-[10px] font-black tracking-[0.25em] text-[#8C5830] uppercase">Hemat Lebih</span>
                                </div>
                                <h2 class="text-2xl md:text-3xl font-black tracking-wide text-[#3B2E26] flex items-center gap-2">
                                    <svg class="w-6 h-6 text-[#8C5830]" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67z"/></svg>
                                    Sedang Diskon
                                </h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                            <BookCard v-for="book in onSaleBooks" :key="book.id" :book="book" />
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- ============================================
         MODAL SEMUA KATEGORI
         ============================================ -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="showAllCategoriesModal"
                class="fixed inset-0 bg-[#0a0a0a]/70 z-50 flex items-center justify-center p-4"
                @click.self="closeAllCategories"
            >
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[8px_8px_0_0_#0a0a0a] max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">

                    <!-- Header -->
                    <div class="bg-[#0a0a0a] text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <div>
                                <div class="text-[11px] font-black uppercase tracking-[0.25em]">Semua Kategori</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-white/50 mt-0.5">
                                    {{ categories.length }} Kategori Tersedia
                                </div>
                            </div>
                        </div>
                        <button
                            @click="closeAllCategories"
                            class="w-7 h-7 flex items-center justify-center border-2 border-white/30 text-white/70 hover:text-white hover:border-white transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-5 overflow-y-auto">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <RouterLink
                                v-for="cat in categories"
                                :key="cat.id"
                                :to="{ path: '/catalog', query: { category: cat.slug } }"
                                @click="closeAllCategories"
                                class="bg-white border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] group hover:shadow-[5px_5px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 flex flex-col overflow-hidden"
                            >
                                <div class="bg-[#5C4A3F] text-white py-1 px-3 text-[10px] font-bold tracking-widest uppercase text-center group-hover:bg-[#3B2E26] transition-colors">
                                    GENRE
                                </div>
                                <div class="p-4 text-center flex-1 flex flex-col items-center justify-center bg-white group-hover:bg-[#FAF7F2] transition-colors">
                                    <div class="w-12 h-12 rounded-full bg-[#F2ECE4] flex items-center justify-center mb-2 group-hover:scale-110 transition-all duration-300" v-html="cat.svgIcon"></div>
                                    <div class="text-xs font-bold text-[#3B2E26] mb-1 line-clamp-1">{{ cat.name }}</div>
                                    <div class="text-[10px] text-[#8C7A6B] font-bold tracking-wider">
                                        {{ cat.active_books_count || 0 }} BUKU
                                    </div>
                                </div>
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>