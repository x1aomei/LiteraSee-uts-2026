<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api/axios';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useWishlistStore } from '@/stores/wishlist';
import BookCard from '@/components/book/BookCard.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const cart = useCartStore();
const wishlist = useWishlistStore();

const book = ref(null);
const relatedBooks = ref([]);
const sameAuthorBooks = ref([]);
const quantity = ref(1);
const loading = ref(true);
const activeImage = ref('');

const isWishlisted = computed(() => book.value ? wishlist.has(book.value.id) : false);

async function fetchBook() {
    loading.value = true;
    try {
        const { data } = await api.get(`/books/${route.params.slug}`);
        book.value = data.data.book;
        relatedBooks.value = data.data.related_books || [];
        sameAuthorBooks.value = data.data.same_author_books || [];
        activeImage.value = book.value.image_url;
    } catch (e) { if (e.response?.status === 404) router.push('/catalog'); }
    finally { loading.value = false; }
}

function incrementQty() { if (quantity.value < book.value.stock) quantity.value++; }
function decrementQty() { if (quantity.value > 1) quantity.value--; }

async function addToCart() {
    if (!auth.isAuthenticated) { router.push({ name: 'login', query: { redirect: route.fullPath } }); return; }
    const ok = await cart.addToCart(book.value.id, quantity.value);
    if (ok) quantity.value = 1;
}

async function toggleWishlist() {
    if (!auth.isAuthenticated) { router.push({ name: 'login', query: { redirect: route.fullPath } }); return; }
    await wishlist.toggle(book.value.id);
}

watch(() => route.params.slug, fetchBook);
onMounted(fetchBook);
</script>

<template>
    <LoadingSpinner v-if="loading" />

    <!-- Background Utama Beige (#F2ECE4) dengan Teks Cokelat Soft (#3B2E26) -->
    <div v-else-if="book" class="bg-[#F2ECE4] text-[#3B2E26] min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Breadcrumb Navigasi -->
            <nav class="text-xs font-bold tracking-widest text-[#8C7A6B] uppercase mb-8 flex items-center gap-2">
                <RouterLink to="/" class="hover:text-[#5C4A3F]">Home</RouterLink>
                <span>/</span>
                <RouterLink to="/catalog" class="hover:text-[#5C4A3F]">Katalog</RouterLink>
                <span>/</span>
                <RouterLink :to="{ name: 'catalog', query: { category: book.category.slug } }" class="hover:text-[#5C4A3F]">{{ book.category.name }}</RouterLink>
            </nav>

            <!-- Bagian Utama Detail Buku -->
            <div class="grid md:grid-cols-2 gap-10 mb-16">
                
                <!-- Kolom Gambar Buku -->
                <div>
                    <div class="bg-white border-2 border-[#D4C5B9] p-6 mb-4 shadow-sm flex items-center justify-center">
                        <img :src="activeImage" :alt="book.title" class="max-h-[450px] object-contain drop-shadow-md" />
                    </div>
                    
                    <!-- Galeri Gambar Thumbnail -->
                    <div v-if="book.images?.length > 1" class="flex gap-3 overflow-x-auto pb-2">
                        <button v-for="img in book.images" :key="img.id" @click="activeImage = img.image_url"
                            :class="['border-2 bg-white overflow-w-hidden w-20 h-24 shrink-0 transition', activeImage === img.image_url ? 'border-[#5C4A3F]' : 'border-[#D4C5B9] opacity-70 hover:opacity-100']">
                            <img :src="img.image_url" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Kolom Informasi Buku -->
                <div class="flex flex-col justify-between">
                    <div>
                        <!-- Kategori Badge -->
                        <RouterLink :to="{ name: 'catalog', query: { category: book.category.slug } }" class="inline-block bg-[#5C4A3F] text-white text-[10px] font-bold tracking-widest uppercase px-3 py-1 mb-4">
                            {{ book.category.name }}
                        </RouterLink>
                        
                        <h1 class="text-3xl md:text-4xl font-black tracking-wide text-[#3B2E26] mb-4">{{ book.title }}</h1>
                        
                        <!-- Metadata Penulis, Penerbit, Tahun -->
                        <div class="flex flex-wrap items-center gap-4 text-xs font-bold tracking-wider text-[#8C7A6B] mb-6 pb-6 border-b-2 border-[#D4C5B9]">
                            <span>✍️ {{ book.author }}</span>
                            <span>🏢 {{ book.publisher }}</span>
                            <span v-if="book.year">📅 {{ book.year }}</span>
                        </div>

                        <!-- Harga & Diskon -->
                        <div class="mb-6 bg-white p-4 border border-[#D4C5B9]">
                            <div v-if="book.has_discount" class="flex items-center gap-3 mb-1">
                                <span class="text-sm text-[#8C7A6B] line-through">Rp {{ Number(book.price).toLocaleString('id-ID') }}</span>
                                <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 tracking-wider uppercase">-{{ book.discount_percentage }}%</span>
                            </div>
                            <div class="text-3xl font-black text-[#5C4A3F]">{{ book.formatted_price }}</div>
                        </div>

                        <!-- Ketersediaan Stok -->
                        <div class="mb-6">
                            <span :class="['inline-block px-3 py-1 text-xs font-bold tracking-widest uppercase border', book.stock > 5 ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : book.stock > 0 ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-red-50 text-red-800 border-red-300']">
                                {{ book.stock > 0 ? `Stok Tersedia: ${book.stock}` : 'Stok Habis' }}
                            </span>
                        </div>

                        <!-- Tombol Aksi (Kuantitas, Keranjang, Wishlist) -->
                        <div v-if="book.is_available" class="flex flex-wrap gap-3 mb-8">
                            <div class="flex items-center border border-[#D4C5B9] bg-white">
                                <button @click="decrementQty" class="px-4 py-3 font-bold text-[#3B2E26] hover:bg-[#FAF7F2] transition">−</button>
                                <input v-model.number="quantity" type="number" min="1" :max="book.stock" class="w-16 text-center bg-transparent border-0 font-bold text-[#3B2E26] focus:outline-none" />
                                <button @click="incrementQty" class="px-4 py-3 font-bold text-[#3B2E26] hover:bg-[#FAF7F2] transition">+</button>
                            </div>
                            
                            <button @click="addToCart" :disabled="cart.loading" class="flex-1 bg-[#5C4A3F] text-white text-xs font-bold tracking-widest uppercase px-6 py-3 hover:bg-[#3B2E26] transition shadow-sm disabled:opacity-50">
                                🛒 Tambah ke Keranjang
                            </button>
                            
                            <button @click="toggleWishlist"
                                :class="['p-3 bg-white border-2 transition flex items-center justify-center', isWishlisted ? 'border-red-600 text-red-600 bg-red-50' : 'border-[#D4C5B9] text-[#3B2E26] hover:border-[#5C4A3F]']" title="Wishlist">
                                <svg class="w-6 h-6" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Informasi Detail Fisik Buku -->
                        <div class="bg-white border-2 border-[#D4C5B9] p-5 mb-6 shadow-sm">
                            <h3 class="text-xs font-black tracking-widest uppercase text-[#5C4A3F] mb-3 pb-2 border-b border-[#D4C5B9]">Detail Spesifikasi Buku</h3>
                            <div class="grid grid-cols-2 gap-3 text-xs font-medium text-[#3B2E26]">
                                <div><span class="text-[#8C7A6B]">ISBN:</span> {{ book.isbn || '-' }}</div>
                                <div><span class="text-[#8C7A6B]">Bahasa:</span> {{ book.language }}</div>
                                <div><span class="text-[#8C7A6B]">Halaman:</span> {{ book.pages || '-' }} Halaman</div>
                                <div><span class="text-[#8C7A6B]">Berat:</span> {{ book.weight }} gram</div>
                            </div>
                        </div>

                        <!-- Deskripsi Buku -->
                        <div v-if="book.description" class="bg-white border-2 border-[#D4C5B9] p-5 shadow-sm">
                            <h3 class="text-xs font-black tracking-widest uppercase text-[#5C4A3F] mb-3 pb-2 border-b border-[#D4C5B9]">Deskripsi Buku</h3>
                            <p class="text-[#3B2E26] whitespace-pre-line text-sm leading-relaxed">{{ book.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buku Terkait -->
            <section v-if="relatedBooks.length" class="mb-16 pt-8 border-t-2 border-[#D4C5B9]">
                <div class="mb-8">
                    <span class="text-xs font-black tracking-[0.2em] text-[#8C5830] uppercase">Rekomendasi Serupa</span>
                    <h2 class="text-2xl md:text-3xl font-black tracking-wide text-[#3B2E26]">📚 Buku Terkait</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="b in relatedBooks" :key="b.id" class="bg-[#FAF7F2] p-4 border border-[#D4C5B9] hover:border-[#5C4A3F] hover:shadow-md transition-all duration-300">
                        <BookCard :book="b" />
                    </div>
                </div>
            </section>

            <!-- Dari Penulis yang Sama -->
            <section v-if="sameAuthorBooks.length" class="pt-8 border-t-2 border-[#D4C5B9]">
                <div class="mb-8">
                    <span class="text-xs font-black tracking-[0.2em] text-[#4A6B82] uppercase">Koleksi Penulis</span>
                    <h2 class="text-2xl md:text-3xl font-black tracking-wide text-[#3B2E26]">✍️ Dari Penulis yang Sama</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="b in sameAuthorBooks" :key="b.id" class="bg-[#FAF7F2] p-4 border border-[#D4C5B9] hover:border-[#5C4A3F] hover:shadow-md transition-all duration-300">
                        <BookCard :book="b" />
                    </div>
                </div>
            </section>

        </div>
    </div>
</template>