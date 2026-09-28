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
const isAvailable = computed(() => props.book.is_available !== false && props.book.stock > 0);

async function addToCart() {
    if (!auth.isAuthenticated) {
        window.location.href = '/login';
        return;
    }
    await cart.addToCart(props.book.id, 1);
}

async function toggleWishlist() {
    if (!auth.isAuthenticated) {
        window.location.href = '/login';
        return;
    }
    await wishlist.toggle(props.book.id);
}
</script>

<template>
    <div class="group flex flex-col h-full bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150">

        <!-- ============================================
             COVER IMAGE
             ============================================ -->
        <RouterLink
            :to="`/books/${book.slug}`"
            class="relative block aspect-[3/4] bg-[#f4f1ea] overflow-hidden border-b-2 border-[#0a0a0a]"
        >
            <img
                :src="book.image_url || 'https://placehold.co/300x400/5C4A3F/FFFFFF/png?text=No+Cover'"
                :alt="book.title"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />

            <!-- Badge Diskon -->
            <span
                v-if="book.has_discount"
                class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest px-2 py-1 border-2 border-[#0a0a0a] shadow-[2px_2px_0_0_#0a0a0a]"
            >
                -{{ book.discount_percentage }}%
            </span>

            <!-- Badge Stok Habis -->
            <div
                v-if="!isAvailable"
                class="absolute inset-0 bg-[#0a0a0a]/70 flex items-center justify-center"
            >
                <span class="bg-white text-[#0a0a0a] text-xs font-black uppercase tracking-widest px-3 py-1.5 border-2 border-[#0a0a0a]">
                    Stok Habis
                </span>
            </div>
        </RouterLink>

        <!-- ============================================
             CONTENT
             ============================================ -->
        <div class="p-4 flex flex-col flex-1">

            <!-- Category -->
            <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">
                {{ book.category?.name || 'Buku' }}
            </div>

            <!-- Title -->
            <RouterLink
                :to="`/books/${book.slug}`"
                class="font-black text-sm text-[#0a0a0a] hover:text-primary-600 line-clamp-2 mb-1 leading-snug"
            >
                {{ book.title }}
            </RouterLink>

            <!-- Author -->
            <div class="text-xs text-[#0a0a0a]/50 font-medium mb-3 line-clamp-1">
                {{ book.author }}
            </div>

            <!-- ============================================
                 PRICE + WISHLIST
                 ============================================ -->
            <div class="mt-auto">
                <div class="flex items-end justify-between mb-3 gap-2">

                    <!-- Harga -->
                    <div class="flex-1 min-w-0">
                        <div
                            v-if="book.has_discount"
                            class="text-[11px] text-[#0a0a0a]/40 line-through font-medium"
                        >
                            Rp {{ Number(book.price).toLocaleString('id-ID') }}
                        </div>
                        <div class="font-black text-primary-600 text-base leading-none">
                            {{ book.formatted_price || 'Rp ' + Number(book.price).toLocaleString('id-ID') }}
                        </div>
                    </div>

                    <!-- Wishlist Button -->
                    <button
                        @click="toggleWishlist"
                        class="flex-shrink-0 w-8 h-8 flex items-center justify-center border-2 border-[#0a0a0a] transition-all duration-100"
                        :class="isWishlisted
                            ? 'bg-red-600 text-white shadow-[2px_2px_0_0_#0a0a0a]'
                            : 'bg-white text-[#0a0a0a] hover:bg-red-50'"
                        :aria-label="isWishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist'"
                    >
                        <svg
                            class="w-4 h-4"
                            :fill="isWishlisted ? 'currentColor' : 'none'"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- ============================================
                     ADD TO CART
                     ============================================ -->
                <button
                    @click="addToCart"
                    :disabled="!isAvailable || cart.loading"
                    class="w-full flex items-center justify-center gap-2 bg-primary-600 text-white py-2.5 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all duration-100 disabled:opacity-40 disabled:cursor-not-allowed disabled:translate-x-0 disabled:translate-y-0 disabled:shadow-[3px_3px_0_0_#0a0a0a] disabled:hover:shadow-[3px_3px_0_0_#0a0a0a]"
                >
                    <template v-if="cart.loading">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Memproses
                    </template>
                    <template v-else-if="!isAvailable">
                        Stok Habis
                    </template>
                    <template v-else>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-linejoin="miter" d="M12 4v16m8-8H4"/>
                        </svg>
                        Keranjang
                    </template>
                </button>
            </div>
        </div>
    </div>
</template>