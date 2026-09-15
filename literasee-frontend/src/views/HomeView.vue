<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import BookCard from '@/components/book/BookCard.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const categories = ref([]);
const featuredBooks = ref([]);
const latestBooks = ref([]);
const onSaleBooks = ref([]);
const loading = ref(true);

async function fetchHome() {
    loading.value = true;
    try {
        const { data } = await api.get('/home');
        categories.value = data.data.categories || [];
        featuredBooks.value = data.data.featured_books || [];
        latestBooks.value = data.data.latest_books || [];
        onSaleBooks.value = data.data.on_sale_books || [];
    } catch (e) { console.error(e); }
    finally { loading.value = false; }
}
onMounted(fetchHome);
</script>

<template>
    <section class="bg-gradient-to-br from-primary-600 to-primary-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-20">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">Temukan Buku Favoritmu di LiteraSee</h1>
                    <p class="text-lg text-primary-100 mb-8">Ribuan koleksi buku dari berbagai genre. Belanja mudah, harga bersahabat!</p>
                    <RouterLink to="/catalog" class="bg-white text-primary-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition inline-block">Mulai Belanja</RouterLink>
                </div>
                <div class="hidden md:block text-center text-9xl">📚</div>
            </div>
        </div>
    </section>

    <LoadingSpinner v-if="loading" />

    <div v-else>
        <section class="max-w-7xl mx-auto px-4 py-12">
            <h2 class="text-2xl font-bold mb-6">Kategori Populer</h2>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                <RouterLink v-for="cat in categories" :key="cat.id" :to="{ name: 'catalog', query: { category: cat.slug } }"
                    class="card p-4 text-center hover:shadow-md transition group">
                    <div class="text-3xl mb-2 group-hover:scale-110 transition">📖</div>
                    <div class="text-sm font-medium">{{ cat.name }}</div>
                    <div class="text-xs text-gray-500">{{ cat.active_books_count }} buku</div>
                </RouterLink>
            </div>
        </section>

        <section v-if="onSaleBooks.length" class="bg-red-50 py-12">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-2xl font-bold flex items-center gap-2 mb-6">🔥 Sedang Diskon</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <BookCard v-for="book in onSaleBooks" :key="book.id" :book="book" />
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">⭐ Buku Unggulan</h2>
                <RouterLink to="/catalog" class="text-primary-600 font-medium hover:underline">Lihat Semua →</RouterLink>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <BookCard v-for="book in featuredBooks" :key="book.id" :book="book" />
            </div>
        </section>

        <section class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">🆕 Buku Terbaru</h2>
                    <RouterLink to="/catalog" class="text-primary-600 font-medium hover:underline">Lihat Semua →</RouterLink>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <BookCard v-for="book in latestBooks" :key="book.id" :book="book" />
                </div>
            </div>
        </section>
    </div>
</template>
