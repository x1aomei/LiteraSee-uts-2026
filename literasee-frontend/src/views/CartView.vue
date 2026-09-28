<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const router = useRouter();
const cart = useCartStore();
const loading = ref(true);

async function fetchCart() {
    loading.value = true;
    await cart.fetchCart();
    loading.value = false;
}
async function updateQuantity(itemId, qty) {
    if (qty < 1) return;
    await cart.updateQuantity(itemId, qty);
}
async function removeItem(itemId) {
    if (!confirm('Hapus buku ini dari keranjang?')) return;
    await cart.removeItem(itemId);
}
function checkout() {
    router.push('/checkout');
}

onMounted(fetchCart);
</script>

<template>
    <div class="bg-[#F2ECE4] text-[#3B2E26] min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4">

            <!-- ============================================
                 PAGE HEADER
                 ============================================ -->
            <div class="flex items-center gap-3 mb-8 pb-5 border-b-2 border-[#0a0a0a]">
                <div class="w-12 h-12 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center shadow-[3px_3px_0_0_#0a0a0a]">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM7.16 14.26l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49L19.13 4h-.01l-.9 1.63-2.9 5.24H8.53L4.27 2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7.42c-.14 0-.25-.11-.26-.24z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        Keranjang Belanja
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                        Periksa kembali koleksi pilihanmu
                    </p>
                </div>
            </div>

            <LoadingSpinner v-if="loading" />

            <!-- ============================================
                 KERANJANG KOSONG
                 ============================================ -->
            <div v-else-if="cart.isEmpty" class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-12 text-center max-w-lg mx-auto">

                <div class="w-20 h-20 mx-auto mb-5 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                    <svg class="w-10 h-10 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>

                <div class="flex items-center justify-center gap-2 mb-3">
                    <span class="w-6 h-[2px] bg-[#0a0a0a]"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[#0a0a0a]/60">
                        Belum Ada Item
                    </span>
                    <span class="w-6 h-[2px] bg-[#0a0a0a]"></span>
                </div>

                <h3 class="text-xl font-black uppercase tracking-tight text-[#0a0a0a] mb-2">
                    Keranjang Masih Kosong
                </h3>
                <p class="text-xs text-[#0a0a0a]/60 font-medium mb-6 leading-relaxed max-w-xs mx-auto">
                    Belum ada buku pilihan di keranjang belanja kamu
                </p>

                <RouterLink
                    to="/catalog"
                    class="inline-flex items-center gap-2 bg-[#8C5830] text-white px-6 py-3.5 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Mulai Belanja
                </RouterLink>
            </div>

            <!-- ============================================
                 KERANJANG ISI
                 ============================================ -->
            <div v-else class="grid lg:grid-cols-3 gap-8">

                <!-- ============================================
                     DAFTAR ITEM
                     ============================================ -->
                <div class="lg:col-span-2 space-y-4">

                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <span class="text-[11px] font-black uppercase tracking-[0.25em]">Daftar Item</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-white/60">
                            {{ cart.itemCount }} Buku
                        </span>
                    </div>

                    <div
                        v-for="item in cart.items"
                        :key="item.id"
                        class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 p-5 flex gap-5 items-center"
                    >
                        <!-- Cover -->
                        <RouterLink :to="`/books/${item.book.slug}`" class="shrink-0">
                            <div class="w-24 h-32 border-2 border-[#0a0a0a] overflow-hidden bg-[#F2ECE4]">
                                <img
                                    :src="item.book.image_url"
                                    :alt="item.book.title"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </RouterLink>

                        <!-- Content -->
                        <div class="flex-1 flex flex-col justify-between h-full min-w-0">
                            <div>
                                <RouterLink
                                    :to="`/books/${item.book.slug}`"
                                    class="font-black uppercase tracking-tight text-[#0a0a0a] hover:text-[#8C5830] line-clamp-1 text-base transition-colors"
                                >
                                    {{ item.book.title }}
                                </RouterLink>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                                    {{ item.book.author }}
                                </p>
                            </div>

                            <div class="flex items-end justify-between pt-4 mt-2 border-t-2 border-dashed border-[#0a0a0a]/15">
                                <!-- Harga -->
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50">Harga</div>
                                    <div class="font-black text-[#8C5830] text-sm md:text-base mt-0.5">
                                        {{ item.book.formatted_price }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <!-- Quantity Control -->
                                    <div class="flex items-center border-2 border-[#0a0a0a] bg-white shadow-[2px_2px_0_0_#0a0a0a]">
                                        <button
                                            @click="updateQuantity(item.id, item.quantity - 1)"
                                            class="px-3 py-1.5 text-sm font-black text-[#0a0a0a] hover:bg-[#F2ECE4] transition-colors"
                                        >
                                            −
                                        </button>
                                        <input
                                            :value="item.quantity"
                                            @change="e => updateQuantity(item.id, Number(e.target.value))"
                                            type="number"
                                            min="1"
                                            :max="item.book.stock"
                                            class="w-12 text-center bg-transparent border-0 text-sm font-black text-[#0a0a0a] focus:outline-none"
                                        />
                                        <button
                                            @click="updateQuantity(item.id, item.quantity + 1)"
                                            :disabled="item.quantity >= item.book.stock"
                                            class="px-3 py-1.5 text-sm font-black text-[#0a0a0a] hover:bg-[#F2ECE4] transition-colors disabled:opacity-30"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <!-- Delete -->
                                    <button
                                        @click="removeItem(item.id)"
                                        class="inline-flex items-center justify-center w-9 h-9 bg-white border-2 border-[#0a0a0a] text-[#0a0a0a] hover:bg-red-600 hover:text-white hover:border-red-600 shadow-[2px_2px_0_0_#0a0a0a] hover:shadow-[2px_2px_0_0_#dc2626] transition-all"
                                        title="Hapus buku"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================
                     RINGKASAN BELANJA
                     ============================================ -->
                <div class="lg:col-span-1">
                    <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden sticky top-20">

                        <!-- Header -->
                        <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-[11px] font-black uppercase tracking-[0.25em]">Ringkasan Belanja</span>
                        </div>

                        <div class="p-5">

                            <!-- Total Item -->
                            <div class="flex justify-between items-center mb-3 text-xs">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/60">Total Item</span>
                                <span class="font-black text-[#0a0a0a]">{{ cart.itemCount }} Buku</span>
                            </div>

                            <hr class="border-t-2 border-dashed border-[#0a0a0a]/15 my-4" />

                            <!-- Total Harga -->
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-[#0a0a0a]">Total Harga</span>
                                <span class="font-black text-lg text-[#8C5830]">
                                    Rp {{ Number(cart.subtotal).toLocaleString('id-ID') }}
                                </span>
                            </div>

                            <!-- Checkout Button -->
                            <button
                                @click="checkout"
                                class="w-full flex items-center justify-center gap-2 bg-[#8C5830] text-white py-3.5 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all mb-4"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                                Lanjut ke Checkout
                            </button>

                            <!-- Back to Catalog -->
                            <RouterLink
                                to="/catalog"
                                class="flex items-center justify-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/60 hover:text-[#8C5830] transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="square" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Lanjut Belanja
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>