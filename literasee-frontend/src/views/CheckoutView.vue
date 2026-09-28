<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const router = useRouter();
const auth = useAuthStore();
const cart = useCartStore();
const toast = useToastStore();
const preview = ref(null);
const loading = ref(true);
const submitting = ref(false);
const form = ref({ name: '', phone: '', address: '', notes: '' });

async function fetchPreview() {
    loading.value = true;
    try {
        const { data } = await api.get('/checkout');
        preview.value = data.data;
        form.value.name = auth.user?.name || '';
        form.value.phone = auth.user?.phone || '';
        form.value.address = auth.user?.address || '';
    } catch (e) {
        if (e.response?.status === 422) {
            toast.error('Keranjang kosong');
            router.push('/cart');
        }
    } finally {
        loading.value = false;
    }
}

async function submitOrder() {
    if (submitting.value) return;
    submitting.value = true;

    try {
        const response = await api.post('/checkout', form.value);
        console.log('Checkout response:', response.data);

        const root = response.data;
        const orderId = root?.data?.id
            || root?.data?.order?.id
            || root?.id
            || null;

        toast.success('Pesanan berhasil dibuat!');
        await cart.fetchSummary();

        if (orderId) {
            router.push(`/orders/${orderId}`);
        } else {
            router.push('/orders');
        }
    } catch (e) {
        console.error('Checkout error:', e);
        toast.error(e.response?.data?.message || 'Gagal membuat pesanan');
        submitting.value = false;
    }
}

onMounted(fetchPreview);
</script>

<template>
    <!-- Background utama Beige (#F2ECE4) dengan teks Cokelat Soft (#3B2E26) -->
    <div class="bg-[#F2ECE4] text-[#3B2E26] min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Judul Halaman -->
            <div class="mb-8 pb-4 border-b-2 border-[#D4C5B9]">
                <h1 class="text-3xl font-black tracking-wide uppercase flex items-center gap-2">
                    <span>📦</span> Checkout Pesanan
                </h1>
                <p class="text-xs font-bold tracking-widest text-[#8C7A6B] mt-1">LENGKAPI INFORMASI PENGIRIMAN BUKU ANDA</p>
            </div>

            <LoadingSpinner v-if="loading" />

            <form v-else-if="preview" @submit.prevent="submitOrder" class="grid lg:grid-cols-3 gap-8">
                
                <!-- Informasi Pengiriman -->
                <div class="lg:col-span-2">
                    <div class="bg-white border-2 border-[#D4C5B9] p-6 shadow-sm">
                        <h2 class="font-black text-base uppercase tracking-wider mb-5 pb-3 border-b-2 border-[#D4C5B9] text-[#3B2E26]">
                            Informasi Alamat Pengiriman
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold tracking-widest text-[#5C4A3F] uppercase mb-2">Nama Penerima *</label>
                                <input v-model="form.name" type="text" required class="w-full bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2.5 px-3 focus:outline-none focus:border-[#5C4A3F]" />
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold tracking-widest text-[#5C4A3F] uppercase mb-2">No. Telepon / WhatsApp *</label>
                                <input v-model="form.phone" type="tel" required class="w-full bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2.5 px-3 focus:outline-none focus:border-[#5C4A3F]" placeholder="08xxxxxxxxxx" />
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold tracking-widest text-[#5C4A3F] uppercase mb-2">Alamat Lengkap *</label>
                                <textarea v-model="form.address" required rows="3" class="w-full bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2.5 px-3 focus:outline-none focus:border-[#5C4A3F]" placeholder="Nama jalan, nomor rumah, RT/RW, kecamatan, kota, kode pos"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold tracking-widest text-[#5C4A3F] uppercase mb-2">Catatan Kurir (Opsional)</label>
                                <textarea v-model="form.notes" rows="2" class="w-full bg-[#FAF7F2] border border-[#D4C5B9] text-[#3B2E26] text-sm py-2.5 px-3 focus:outline-none focus:border-[#5C4A3F]" placeholder="Contoh: Tolong titip di satpam jika rumah kosong"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Pesanan (Sidebar Kanan) -->
                <div class="lg:col-span-1">
                    <div class="bg-white border-2 border-[#D4C5B9] p-6 sticky top-20 shadow-sm">
                        <h2 class="font-black text-base uppercase tracking-wider mb-4 pb-3 border-b-2 border-[#D4C5B9] text-[#3B2E26]">
                            Ringkasan Pesanan
                        </h2>
                        
                        <div class="space-y-3 max-h-64 overflow-y-auto mb-4 pr-1">
                            <div v-for="(item, i) in preview.items" :key="i" class="flex justify-between items-start text-xs border-b border-[#FAF7F2] pb-2">
                                <span class="text-[#3B2E26] font-medium line-clamp-1 pr-2">{{ item.book_title }} <span class="text-[#8C7A6B]">× {{ item.quantity }}</span></span>
                                <span class="font-bold text-[#5C4A3F] shrink-0">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>
                        
                        <hr class="border-[#D4C5B9] my-4" />
                        
                        <div class="space-y-2 mb-6 text-xs font-medium">
                            <div class="flex justify-between text-[#8C7A6B]">
                                <span>Subtotal Buku</span>
                                <span class="text-[#3B2E26] font-bold">Rp {{ Number(preview.subtotal).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-[#8C7A6B]">
                                <span>Biaya Pengiriman (Ongkir)</span>
                                <span class="text-[#3B2E26] font-bold">Rp {{ Number(preview.shipping_cost).toLocaleString('id-ID') }}</span>
                            </div>
                            <hr class="border-[#D4C5B9] my-2" />
                            <div class="flex justify-between items-center text-sm font-black text-[#3B2E26]">
                                <span class="uppercase tracking-wider">Total Pembayaran</span>
                                <span class="text-base text-[#5C4A3F]">Rp {{ Number(preview.total).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>
                        
                        <button type="submit" :disabled="submitting" class="w-full bg-[#5C4A3F] text-white text-xs font-bold tracking-widest uppercase py-3.5 hover:bg-[#3B2E26] transition shadow-sm disabled:opacity-50">
                            {{ submitting ? 'Memproses Pesanan...' : 'Buat Pesanan Sekarang' }}
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</template>