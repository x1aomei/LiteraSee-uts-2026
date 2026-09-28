<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import BookCard from '@/components/book/BookCard.vue';
import Pagination from '@/components/common/Pagination.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import { useWishlistStore } from '@/stores/wishlist';

const wishlist = useWishlistStore();
const books = ref([]);
const loading = ref(true);
const pagination = ref({ current: 1, last: 1 });

async function fetchWishlist(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/wishlist', { params: { page } });
        books.value = data.data.books.data || [];
        pagination.value = { current: data.data.books.current_page, last: data.data.books.last_page };
        await wishlist.fetchWishlist();
    } finally { loading.value = false; }
}

function changePage(page) {
    fetchWishlist(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
onMounted(() => fetchWishlist(1));
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- ============================================
             PAGE HEADER
             ============================================ -->
        <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 flex-shrink-0 border-2 border-[#0a0a0a] bg-red-600 flex items-center justify-center shadow-[3px_3px_0_0_#0a0a0a]">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                    Wishlist Saya
                </h1>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                    {{ wishlist.count }} buku tersimpan
                </p>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />

        <!-- ============================================
             GRID BUKU
             ============================================ -->
        <div v-else-if="books.length">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                <BookCard
                    v-for="book in books"
                    :key="book.id"
                    :book="book"
                />
            </div>

            <Pagination
                v-if="pagination.last > 1"
                :current-page="pagination.current"
                :last-page="pagination.last"
                class="mt-8"
                @change="changePage"
            />
        </div>

        <!-- ============================================
             EMPTY STATE
             ============================================ -->
        <div v-else class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-12 text-center max-w-lg mx-auto">

            <!-- Icon -->
            <div class="w-20 h-20 mx-auto mb-5 border-2 border-[#0a0a0a] bg-red-100 flex items-center justify-center">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                </svg>
            </div>

            <!-- Text -->
            <div class="flex items-center justify-center gap-2 mb-3">
                <span class="w-6 h-[2px] bg-[#0a0a0a]"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[#0a0a0a]/60">
                    Belum Ada Buku
                </span>
                <span class="w-6 h-[2px] bg-[#0a0a0a]"></span>
            </div>

            <h3 class="text-xl font-black uppercase tracking-tight text-[#0a0a0a] mb-2">
                Wishlist Kosong
            </h3>
            <p class="text-xs text-[#0a0a0a]/60 font-medium mb-6 leading-relaxed max-w-xs mx-auto">
                Simpan buku favoritmu di sini agar mudah ditemukan nanti
            </p>

            <!-- CTA -->
            <RouterLink
                to="/catalog"
                class="inline-flex items-center gap-2 bg-[#8C5830] text-white px-6 py-3.5 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Jelajahi Katalog
            </RouterLink>
        </div>
    </div>
</template>