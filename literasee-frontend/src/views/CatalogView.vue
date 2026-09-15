<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api/axios';
import BookCard from '@/components/book/BookCard.vue';
import Pagination from '@/components/common/Pagination.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const route = useRoute();
const router = useRouter();
const books = ref([]);
const categories = ref([]);
const languages = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });

const filters = reactive({
    q: route.query.q || '', category: route.query.category || '',
    min_price: route.query.min_price || '', max_price: route.query.max_price || '',
    language: route.query.language || '', on_sale: route.query.on_sale === '1',
    sort: route.query.sort || 'newest',
});

async function fetchCatalog() {
    loading.value = true;
    try {
        const { data } = await api.get('/catalog', {
            params: { ...filters, on_sale: filters.on_sale ? 1 : undefined, page: pagination.currentPage, per_page: 12 },
        });
        books.value = data.data.books.data || [];
        pagination.currentPage = data.data.books.current_page;
        pagination.lastPage = data.data.books.last_page;
        pagination.total = data.data.books.total;
        if (data.data.filters) {
            categories.value = data.data.filters.categories || [];
            languages.value = data.data.filters.languages || [];
        }
    } catch (e) { console.error(e); }
    finally { loading.value = false; }
}

function applyFilters() {
    const query = { ...filters };
    if (!query.on_sale) delete query.on_sale;
    Object.keys(query).forEach(k => { if (!query[k]) delete query[k]; });
    router.push({ name: 'catalog', query });
    pagination.currentPage = 1;
    fetchCatalog();
}

function resetFilters() {
    Object.assign(filters, { q: '', category: '', min_price: '', max_price: '', language: '', on_sale: false, sort: 'newest' });
    router.push({ name: 'catalog' });
    fetchCatalog();
}

function changePage(page) {
    pagination.currentPage = page;
    fetchCatalog();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

watch(() => route.query, () => {
    Object.assign(filters, {
        q: route.query.q || '', category: route.query.category || '',
        min_price: route.query.min_price || '', max_price: route.query.max_price || '',
        language: route.query.language || '', on_sale: route.query.on_sale === '1',
        sort: route.query.sort || 'newest',
    });
    fetchCatalog();
});

onMounted(fetchCatalog);
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid lg:grid-cols-4 gap-8">
            <aside class="lg:col-span-1">
                <div class="card p-4 sticky top-20">
                    <h3 class="font-bold mb-4">🔍 Filter</h3>

                    <div class="mb-6">
                        <h4 class="text-sm font-semibold mb-2">Kategori</h4>
                        <label class="flex items-center gap-2 cursor-pointer mb-1">
                            <input type="radio" v-model="filters.category" value="" @change="applyFilters" class="text-primary-600" />
                            <span class="text-sm">Semua</span>
                        </label>
                        <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 cursor-pointer mb-1">
                            <input type="radio" v-model="filters.category" :value="cat.slug" @change="applyFilters" class="text-primary-600" />
                            <span class="text-sm">{{ cat.name }}</span>
                            <span class="text-xs text-gray-400">({{ cat.active_books_count }})</span>
                        </label>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-semibold mb-2">Rentang Harga</h4>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <input v-model="filters.min_price" type="number" placeholder="Min" class="input text-sm py-1.5" />
                            <input v-model="filters.max_price" type="number" placeholder="Max" class="input text-sm py-1.5" />
                        </div>
                        <button @click="applyFilters" class="btn-primary w-full text-sm py-1.5">Terapkan</button>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-semibold mb-2">Bahasa</h4>
                        <select v-model="filters.language" @change="applyFilters" class="input text-sm">
                            <option value="">Semua Bahasa</option>
                            <option v-for="lang in languages" :key="lang" :value="lang">{{ lang }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="filters.on_sale" @change="applyFilters" class="text-primary-600" />
                            <span class="text-sm">Sedang Diskon</span>
                        </label>
                    </div>

                    <button @click="resetFilters" class="btn-outline w-full text-sm">Reset Filter</button>
                </div>
            </aside>

            <main class="lg:col-span-3">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ filters.category ? categories.find(c => c.slug === filters.category)?.name : 'Semua Buku' }}
                        </h1>
                        <p class="text-sm text-gray-500">{{ pagination.total }} buku ditemukan</p>
                    </div>
                    <select v-model="filters.sort" @change="applyFilters" class="input w-auto text-sm">
                        <option value="newest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="price_asc">Harga Terendah</option>
                        <option value="price_desc">Harga Tertinggi</option>
                        <option value="title_asc">Judul A-Z</option>
                    </select>
                </div>

                <LoadingSpinner v-if="loading" />

                <div v-else-if="books.length" class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <BookCard v-for="book in books" :key="book.id" :book="book" />
                </div>

                <div v-else class="card p-12 text-center">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-lg font-semibold mb-2">Buku tidak ditemukan</h3>
                    <p class="text-gray-500 mb-4">Coba ubah filter atau kata kunci</p>
                    <button @click="resetFilters" class="btn-primary">Reset Filter</button>
                </div>

                <Pagination :current-page="pagination.currentPage" :last-page="pagination.lastPage" @change="changePage" />
            </main>
        </div>
    </div>
</template>
