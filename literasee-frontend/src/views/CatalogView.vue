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
    <!-- Wrapper Utama dengan background Beige (#F2ECE4) dan teks Cokelat Soft (#3B2E26) -->
    <div class="bg-[#F2ECE4] text-[#3B2E26] min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid lg:grid-cols-4 gap-8">
                
                <!-- Sidebar Filter -->
                <aside class="lg:col-span-1">
                    <div class="bg-white border-2 border-[#D4C5B9] p-6 sticky top-20 shadow-sm">
                        <h3 class="font-black text-lg mb-4 text-[#3B2E26] pb-2 border-b-2 border-[#D4C5B9] flex items-center gap-2">
                            <span>🔍</span> Filter Katalog
                        </h3>

                        <!-- Kategori -->
                        <div class="mb-6">
                            <h4 class="text-xs font-black tracking-widest text-[#5C4A3F] uppercase mb-3">Kategori</h4>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="filters.category" value="" @change="applyFilters" class="text-[#5C4A3F] focus:ring-[#5C4A3F]" />
                                    <span class="text-sm font-medium">Semua</span>
                                </label>
                                <label v-for="cat in categories" :key="cat.id" class="flex items-center justify-between cursor-pointer">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" v-model="filters.category" :value="cat.slug" @change="applyFilters" class="text-[#5C4A3F] focus:ring-[#5C4A3F]" />
                                        <span class="text-sm font-medium">{{ cat.name }}</span>
                                    </div>
                                    <span class="text-xs text-[#8C7A6B] font-bold">({{ cat.active_books_count }})</span>
                                </label>
                            </div>
                        </div>

                        <hr class="border-[#D4C5B9] my-4" />

                        <!-- Rentang Harga -->
                        <div class="mb-6">
                            <h4 class="text-xs font-black tracking-widest text-[#5C4A3F] uppercase mb-3">Rentang Harga</h4>
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <input v-model="filters.min_price" type="number" placeholder="Min" class="bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2 px-3 focus:outline-none focus:border-[#5C4A3F]" />
                                <input v-model="filters.max_price" type="number" placeholder="Max" class="bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2 px-3 focus:outline-none focus:border-[#5C4A3F]" />
                            </div>
                            <button @click="applyFilters" class="w-full bg-[#5C4A3F] text-white text-xs font-bold tracking-widest uppercase py-2.5 hover:bg-[#3B2E26] transition shadow-sm">
                                Terapkan Harga
                            </button>
                        </div>

                        <hr class="border-[#D4C5B9] my-4" />

                        <!-- Bahasa -->
                        <div class="mb-6">
                            <h4 class="text-xs font-black tracking-widest text-[#5C4A3F] uppercase mb-3">Bahasa</h4>
                            <select v-model="filters.language" @change="applyFilters" class="w-full bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2 px-3 focus:outline-none focus:border-[#5C4A3F]">
                                <option value="">Semua Bahasa</option>
                                <option v-for="lang in languages" :key="lang" :value="lang">{{ lang }}</option>
                            </select>
                        </div>

                        <!-- Sedang Diskon -->
                        <div class="mb-6 bg-[#FAF7F2] p-3 border border-[#D4C5B9]">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="filters.on_sale" @change="applyFilters" class="text-[#5C4A3F] focus:ring-[#5C4A3F] rounded" />
                                <span class="text-sm font-bold text-[#3B2E26]">🔥 Sedang Diskon</span>
                            </label>
                        </div>

                        <button @click="resetFilters" class="w-full bg-transparent border-2 border-[#5C4A3F] text-[#5C4A3F] text-xs font-bold tracking-widest uppercase py-2.5 hover:bg-[#5C4A3F] hover:text-white transition">
                            Reset Filter
                        </button>
                    </div>
                </aside>

                <!-- Konten Utama Katalog -->
                <main class="lg:col-span-3">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 bg-white p-6 border-2 border-[#D4C5B9] shadow-sm">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-black text-[#3B2E26] uppercase tracking-wide">
                                {{ filters.category ? categories.find(c => c.slug === filters.category)?.name : 'Semua Buku' }}
                            </h1>
                            <p class="text-xs font-bold tracking-widest text-[#8C7A6B] mt-1">{{ pagination.total }} BUKU DITEMUKAN</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-widest text-[#5C4A3F]">Urutkan:</span>
                            <select v-model="filters.sort" @change="applyFilters" class="bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2 px-3 focus:outline-none focus:border-[#5C4A3F]">
                                <option value="newest">Terbaru</option>
                                <option value="oldest">Terlama</option>
                                <option value="price_asc">Harga Terendah</option>
                                <option value="price_desc">Harga Tertinggi</option>
                                <option value="title_asc">Judul A-Z</option>
                            </select>
                        </div>
                    </div>

                    <LoadingSpinner v-if="loading" />

                    <!-- Grid Buku -->
                    <div v-else-if="books.length" class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div 
                            v-for="book in books" 
                            :key="book.id" 
                            class="bg-[#FAF7F2] p-4 border border-[#D4C5B9] hover:border-[#5C4A3F] hover:shadow-md transition-all duration-300 flex flex-col justify-between"
                        >
                            <BookCard :book="book" />
                        </div>
                    </div>

                    <!-- Kosong / Tidak Ditemukan -->
                    <div v-else class="bg-white border-2 border-[#D4C5B9] p-12 text-center shadow-sm">
                        <div class="text-6xl mb-4">📖</div>
                        <h3 class="text-lg font-bold mb-2 text-[#3B2E26]">Buku Tidak Ditemukan</h3>
                        <p class="text-sm text-[#8C7A6B] mb-6">Coba ubah kata kunci pencarian atau sesuaikan kembali filter pilihanmu.</p>
                        <button @click="resetFilters" class="bg-[#5C4A3F] text-white text-xs font-bold tracking-widest uppercase px-6 py-3 hover:bg-[#3B2E26] transition shadow-sm">
                            Reset Filter
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        <Pagination :current-page="pagination.currentPage" :last-page="pagination.lastPage" @change="changePage" />
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>