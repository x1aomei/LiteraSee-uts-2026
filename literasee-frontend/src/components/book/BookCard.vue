<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useWishlistStore } from '@/stores/wishlist';

const props = defineProps({ book: { type: Object, required: true } });
const auth = useAuthStore();
const cart = useCartStore();
const wishlist = useWishlistStore();
const isWishlisted = computed(() => wishlist.has(props.book.id));

async function addToCart() {
    if (!auth.isAuthenticated) { window.location.href = '/login'; return; }
    await cart.addToCart(props.book.id, 1);
}
async function toggleWishlist() {
    if (!auth.isAuthenticated) { window.location.href = '/login'; return; }
    await wishlist.toggle(props.book.id);
}
</script>

<template>
    <div class="card overflow-hidden group hover:shadow-lg transition-all duration-300 flex flex-col">
        <RouterLink :to="`/books/${book.slug}`" class="relative block aspect-[3/4] bg-gray-100 overflow-hidden">
            <img :src="book.image_url" :alt="book.title" loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <span v-if="book.has_discount" class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">-{{ book.discount_percentage }}%</span>
            <span v-if="book.is_featured" class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded">⭐</span>
        </RouterLink>

        <div class="p-4 flex flex-col flex-1">
            <div class="text-xs text-gray-500 mb-1">{{ book.category?.name }}</div>
            <RouterLink :to="`/books/${book.slug}`" class="font-medium text-gray-900 hover:text-primary-600 line-clamp-2 mb-1">{{ book.title }}</RouterLink>
            <div class="text-xs text-gray-500 mb-3 line-clamp-1">{{ book.author }}</div>

            <div class="mt-auto">
                <div class="flex items-end justify-between mb-3">
                    <div>
                        <div v-if="book.has_discount" class="text-xs text-gray-400 line-through">Rp {{ Number(book.price).toLocaleString('id-ID') }}</div>
                        <div class="font-bold text-primary-600">{{ book.formatted_price }}</div>
                    </div>
                    <button @click="toggleWishlist" class="p-2 rounded-full hover:bg-red-50 transition"
                        :class="isWishlisted ? 'text-red-500' : 'text-gray-400'">
                        <svg class="w-5 h-5" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>
                <button @click="addToCart" :disabled="!book.is_available || cart.loading" class="btn-primary w-full text-sm py-2">
                    {{ !book.is_available ? 'Stok Habis' : '+ Keranjang' }}
                </button>
            </div>
        </div>
    </div>
</template>