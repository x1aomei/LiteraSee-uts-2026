<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const router = useRouter();
const cart = useCartStore();
const loading = ref(true);

async function fetchCart() { loading.value = true; await cart.fetchCart(); loading.value = false; }
async function updateQuantity(itemId, qty) { if (qty < 1) return; await cart.updateQuantity(itemId, qty); }
async function removeItem(itemId) { if (!confirm('Hapus buku ini?')) return; await cart.removeItem(itemId); }
function checkout() { router.push('/checkout'); }

onMounted(fetchCart);
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">🛒 Keranjang Belanja</h1>

        <LoadingSpinner v-if="loading" />

        <div v-else-if="cart.isEmpty" class="card p-12 text-center">
            <div class="text-6xl mb-4">🛒</div>
            <h3 class="text-xl font-semibold mb-2">Keranjang Kosong</h3>
            <p class="text-gray-500 mb-6">Belum ada buku di keranjang kamu</p>
            <RouterLink to="/catalog" class="btn-primary">Mulai Belanja</RouterLink>
        </div>

        <div v-else class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <div v-for="item in cart.items" :key="item.id" class="card p-4 flex gap-4">
                    <RouterLink :to="`/books/${item.book.slug}`" class="shrink-0">
                        <img :src="item.book.image_url" :alt="item.book.title" class="w-24 h-32 object-cover rounded-lg" />
                    </RouterLink>
                    <div class="flex-1 flex flex-col">
                        <RouterLink :to="`/books/${item.book.slug}`" class="font-medium hover:text-primary-600 line-clamp-2">{{ item.book.title }}</RouterLink>
                        <p class="text-sm text-gray-500 mt-1">{{ item.book.author }}</p>

                        <div class="mt-auto flex items-end justify-between pt-3">
                            <div>
                                <div class="text-xs text-gray-500">Harga</div>
                                <div class="font-semibold text-primary-600">{{ item.book.formatted_price }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center border rounded-lg">
                                    <button @click="updateQuantity(item.id, item.quantity - 1)" class="px-3 py-1.5 hover:bg-gray-100">−</button>
                                    <input :value="item.quantity" @change="e => updateQuantity(item.id, Number(e.target.value))"
                                        type="number" min="1" :max="item.book.stock" class="w-12 text-center border-0 focus:ring-0" />
                                    <button @click="updateQuantity(item.id, item.quantity + 1)" :disabled="item.quantity >= item.book.stock"
                                        class="px-3 py-1.5 hover:bg-gray-100 disabled:opacity-50">+</button>
                                </div>
                                <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="card p-6 sticky top-20">
                    <h2 class="font-bold text-lg mb-4">Ringkasan Belanja</h2>
                    <div class="flex justify-between mb-3">
                        <span class="text-gray-600">Total Item</span>
                        <span class="font-medium">{{ cart.itemCount }} buku</span>
                    </div>
                    <hr class="my-4" />
                    <div class="flex justify-between mb-6">
                        <span class="font-bold">Total</span>
                        <span class="font-bold text-primary-600 text-xl">Rp {{ Number(cart.subtotal).toLocaleString('id-ID') }}</span>
                    </div>
                    <button @click="checkout" class="btn-primary w-full py-3">Lanjut ke Checkout</button>
                    <RouterLink to="/catalog" class="block text-center text-sm text-gray-500 hover:text-primary-600 mt-4">← Lanjut Belanja</RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>