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

function changePage(page) { fetchWishlist(page); window.scrollTo({ top: 0, behavior: 'smooth' }); }
onMounted(() => fetchWishlist(1));
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">❤️ Wishlist Saya ({{ wishlist.count }})</h1>

        <LoadingSpinner v-if="loading" />

        <div v-else-if="books.length" class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <BookCard v-for="book in books" :key="book.id" :book="book" />
        </div>

        <div v-else class="card p-12 text-center">
            <div class="text-6xl mb-4">💔</div>
            <h3 class="text-xl font-semibold mb-2">Wishlist Kosong</h3>
            <p class="text-gray-500 mb-6">Simpan buku favoritmu di sini</p>
            <RouterLink to="/catalog" class="btn-primary">Jelajahi Katalog</RouterLink>
        </div>

        <Pagination v-if="!loading && books.length" :current-page="pagination.current" :last-page="pagination.last" @change="changePage" />
    </div>
</template>