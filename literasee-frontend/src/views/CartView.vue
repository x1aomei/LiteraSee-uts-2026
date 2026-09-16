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
async function removeItem(itemId) { if (!confirm('Hapus buku ini dari keranjang?')) return; await cart.removeItem(itemId); }
function checkout() { router.push('/checkout'); }

onMounted(fetchCart);
</script>

<template>
    <!-- Background utama Beige (#F2ECE4) dengan teks Cokelat Soft (#3B2E26) -->
    <div class="bg-[#F2ECE4] text-[#3B2E26] min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Judul Halaman -->
            <div class="mb-8 pb-4 border-b-2 border-[#D4C5B9]">
                <h1 class="text-3xl font-black tracking-wide uppercase flex items-center gap-2">
                    <span>🛒</span> Keranjang Belanja
                </h1>
                <p class="text-xs font-bold tracking-widest text-[#8C7A6B] mt-1">PERIKSA KEMBALI KOLEKSI PILIHANMU</p>
            </div>

            <LoadingSpinner v-if="loading" />

            <!-- Keranjang Kosong -->
            <div v-else-if="cart.isEmpty" class="bg-white border-2 border-[#D4C5B9] p-12 text-center shadow-sm">
                <div class="text-6xl mb-4">🛒</div>
                <h3 class="text-xl font-bold mb-2 text-[#3B2E26]">Keranjang Masih Kosong</h3>
                <p class="text-sm text-[#8C7A6B] mb-6">Belum ada buku pilihan di keranjang belanja kamu.</p>
                <RouterLink to="/catalog" class="inline-block bg-[#5C4A3F] text-white text-xs font-bold tracking-widest uppercase px-8 py-3.5 hover:bg-[#3B2E26] transition shadow-sm">
                    Mulai Belanja
                </RouterLink>
            </div>

            <!-- Keranjang Isi -->
            <div v-else class="grid lg:grid-cols-3 gap-8">
                
                <!-- Daftar Item Buku -->
                <div class="lg:col-span-2 space-y-4">
                    <div 
                        v-for="item in cart.items" 
                        :key="item.id" 
                        class="bg-white border-2 border-[#D4C5B9] p-5 flex gap-5 items-center shadow-sm hover:border-[#5C4A3F] transition-all duration-300"
                    >
                        <RouterLink :to="`/books/${item.book.slug}`" class="shrink-0">
                            <img :src="item.book.image_url" :alt="item.book.title" class="w-24 h-32 object-cover border border-[#D4C5B9]" />
                        </RouterLink>
                        
                        <div class="flex-1 flex flex-col justify-between h-full">
                            <div>
                                <RouterLink :to="`/books/${item.book.slug}`" class="font-bold text-[#3B2E26] hover:text-[#5C4A3F] line-clamp-1 text-base">
                                    {{ item.book.title }}
                                </RouterLink>
                                <p class="text-xs font-medium text-[#8C7A6B] mt-1">{{ item.book.author }}</p>
                            </div>

                            <div class="flex items-end justify-between pt-4 mt-2 border-t border-[#FAF7F2]">
                                <div>
                                    <div class="text-[10px] font-bold tracking-widest text-[#8C7A6B] uppercase">Harga</div>
                                    <div class="font-bold text-[#5C4A3F] text-sm md:text-base">{{ item.book.formatted_price }}</div>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <!-- Kontrol Jumlah Kuantitas -->
                                    <div class="flex items-center border border-[#D4C5B9] bg-[#FAF7F2]">
                                        <button @click="updateQuantity(item.id, item.quantity - 1)" class="px-3 py-1.5 text-sm font-bold text-[#3B2E26] hover:bg-[#E4DBD0] transition">−</button>
                                        <input 
                                            :value="item.quantity" 
                                            @change="e => updateQuantity(item.id, Number(e.target.value))"
                                            type="number" 
                                            min="1" 
                                            :max="item.book.stock" 
                                            class="w-12 text-center bg-transparent border-0 text-sm font-bold text-[#3B2E26] focus:outline-none" 
                                        />
                                        <button 
                                            @click="updateQuantity(item.id, item.quantity + 1)" 
                                            :disabled="item.quantity >= item.book.stock"
                                            class="px-3 py-1.5 text-sm font-bold text-[#3B2E26] hover:bg-[#E4DBD0] transition disabled:opacity-30"
                                        >+</button>
                                    </div>

                                    <!-- Tombol Hapus -->
                                    <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 p-2 transition" title="Hapus buku">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Belanja (Sidebar Kanan) -->
                <div class="lg:col-span-1">
                    <div class="bg-white border-2 border-[#D4C5B9] p-6 sticky top-20 shadow-sm">
                        <h2 class="font-black text-base uppercase tracking-wider mb-4 pb-3 border-b-2 border-[#D4C5B9] text-[#3B2E26]">
                            Ringkasan Belanja
                        </h2>
                        
                        <div class="flex justify-between items-center mb-3 text-sm">
                            <span class="text-[#8C7A6B] font-medium">Total Item</span>
                            <span class="font-bold text-[#3B2E26]">{{ cart.itemCount }} Buku</span>
                        </div>
                        
                        <hr class="border-[#D4C5B9] my-4" />
                        
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-black text-sm uppercase tracking-wider text-[#3B2E26]">Total Harga</span>
                            <span class="font-black text-lg text-[#5C4A3F]">Rp {{ Number(cart.subtotal).toLocaleString('id-ID') }}</span>
                        </div>
                        
                        <button @click="checkout" class="w-full bg-[#5C4A3F] text-white text-xs font-bold tracking-widest uppercase py-3.5 hover:bg-[#3B2E26] transition shadow-sm mb-3">
                            Lanjut ke Checkout
                        </button>
                        
                        <RouterLink to="/catalog" class="block text-center text-xs font-bold tracking-widest uppercase text-[#8C7A6B] hover:text-[#5C4A3F] transition mt-4">
                            ← Lanjut Belanja Buku Lain
                        </RouterLink>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>