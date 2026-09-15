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

    <div v-else-if="book" class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <RouterLink to="/" class="hover:text-primary-600">Home</RouterLink>
            <span class="mx-2">/</span>
            <RouterLink to="/catalog" class="hover:text-primary-600">Katalog</RouterLink>
            <span class="mx-2">/</span>
            <RouterLink :to="{ name: 'catalog', query: { category: book.category.slug } }" class="hover:text-primary-600">{{ book.category.name }}</RouterLink>
        </nav>

        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <div>
                <div class="card p-4 mb-4">
                    <img :src="activeImage" :alt="book.title" class="w-full aspect-[3/4] object-contain" />
                </div>
                <div v-if="book.images?.length > 1" class="flex gap-2 overflow-x-auto">
                    <button v-for="img in book.images" :key="img.id" @click="activeImage = img.image_url"
                        :class="['border-2 rounded-lg overflow-hidden w-20 h-24 shrink-0', activeImage === img.image_url ? 'border-primary-500' : 'border-transparent']">
                        <img :src="img.image_url" class="w-full h-full object-cover" />
                    </button>
                </div>
            </div>

            <div>
                <RouterLink :to="{ name: 'catalog', query: { category: book.category.slug } }" class="badge bg-primary-100 text-primary-700 mb-3">{{ book.category.name }}</RouterLink>
                <h1 class="text-3xl font-bold mb-3">{{ book.title }}</h1>
                <div class="flex items-center gap-4 text-sm text-gray-600 mb-6">
                    <span>✍️ {{ book.author }}</span>
                    <span>🏢 {{ book.publisher }}</span>
                    <span v-if="book.year">📅 {{ book.year }}</span>
                </div>

                <div class="mb-6">
                    <div v-if="book.has_discount" class="flex items-center gap-3">
                        <span class="text-lg text-gray-400 line-through">Rp {{ Number(book.price).toLocaleString('id-ID') }}</span>
                        <span class="badge bg-red-500 text-white">-{{ book.discount_percentage }}%</span>
                    </div>
                    <div class="text-3xl font-bold text-primary-600">{{ book.formatted_price }}</div>
                </div>

                <div class="mb-6">
                    <span :class="['badge', book.stock > 5 ? 'bg-green-100 text-green-700' : book.stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700']">
                        {{ book.stock > 0 ? `Stok: ${book.stock}` : 'Stok Habis' }}
                    </span>
                </div>

                <div v-if="book.is_available" class="flex gap-3 mb-6">
                    <div class="flex items-center border rounded-lg">
                        <button @click="decrementQty" class="px-4 py-3 hover:bg-gray-100">−</button>
                        <input v-model.number="quantity" type="number" min="1" :max="book.stock" class="w-16 text-center border-0 focus:ring-0" />
                        <button @click="incrementQty" class="px-4 py-3 hover:bg-gray-100">+</button>
                    </div>
                    <button @click="addToCart" :disabled="cart.loading" class="btn-primary flex-1">
                        🛒 Tambah ke Keranjang
                    </button>
                    <button @click="toggleWishlist"
                        :class="['p-3 rounded-lg border transition', isWishlisted ? 'border-red-500 text-red-500' : 'border-gray-300 hover:border-red-300']">
                        <svg class="w-6 h-6" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>

                <div class="card p-4 space-y-3 mb-6">
                    <h3 class="font-semibold mb-2">Detail Buku</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-gray-500">ISBN:</span> {{ book.isbn || '-' }}</div>
                        <div><span class="text-gray-500">Bahasa:</span> {{ book.language }}</div>
                        <div><span class="text-gray-500">Halaman:</span> {{ book.pages || '-' }}</div>
                        <div><span class="text-gray-500">Berat:</span> {{ book.weight }}g</div>
                    </div>
                </div>

                <div v-if="book.description" class="card p-4">
                    <h3 class="font-semibold mb-3">Deskripsi</h3>
                    <p class="text-gray-600 whitespace-pre-line text-sm leading-relaxed">{{ book.description }}</p>
                </div>
            </div>
        </div>

        <section v-if="relatedBooks.length" class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Buku Terkait</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <BookCard v-for="b in relatedBooks" :key="b.id" :book="b" />
            </div>
        </section>

        <section v-if="sameAuthorBooks.length">
            <h2 class="text-2xl font-bold mb-6">Dari Penulis yang Sama</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <BookCard v-for="b in sameAuthorBooks" :key="b.id" :book="b" />
            </div>
        </section>
    </div>
</template>