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
    // Prevent double submit
    if (submitting.value) return;
    submitting.value = true;

    try {
        const response = await api.post('/checkout', form.value);
        console.log('Checkout response:', response.data);

        // Ambil order ID dari berbagai kemungkinan format
        const root = response.data;
        const orderId = root?.data?.id
            || root?.data?.order?.id
            || root?.id
            || null;

        console.log('Order ID:', orderId);

        toast.success('Pesanan berhasil dibuat!');

        // Update cart count
        await cart.fetchSummary();

        // Redirect
        if (orderId) {
            router.push(`/orders/${orderId}`);
        } else {
            router.push('/orders');
        }
    } catch (e) {
        console.error('Checkout error:', e);
        console.error('Response:', e.response?.data);
        toast.error(e.response?.data?.message || 'Gagal membuat pesanan');
        submitting.value = false;
    }
    // Jangan reset submitting kalau sukses — biar gak bisa double click
}

onMounted(fetchPreview);
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Checkout</h1>
        <LoadingSpinner v-if="loading" />

        <form v-else-if="preview" @submit.prevent="submitOrder" class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="card p-6">
                    <h2 class="font-bold text-lg mb-4">Informasi Pengiriman</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Penerima *</label>
                            <input v-model="form.name" type="text" required class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">No. Telepon *</label>
                            <input v-model="form.phone" type="tel" required class="input" placeholder="08xxxxxxxxxx" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Alamat Lengkap *</label>
                            <textarea v-model="form.address" required rows="3" class="input"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
                            <textarea v-model="form.notes" rows="2" class="input"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="card p-6 sticky top-20">
                    <h2 class="font-bold text-lg mb-4">Ringkasan Pesanan</h2>
                    <div class="space-y-3 max-h-64 overflow-y-auto mb-4">
                        <div v-for="(item, i) in preview.items" :key="i" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ item.book_title }} × {{ item.quantity }}</span>
                            <span class="font-medium">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                    <hr class="my-4" />
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span>Rp {{ Number(preview.subtotal).toLocaleString('id-ID') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Ongkir</span>
                            <span>Rp {{ Number(preview.shipping_cost).toLocaleString('id-ID') }}</span>
                        </div>
                        <hr />
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary-600">Rp {{ Number(preview.total).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                    <button type="submit" :disabled="submitting" class="btn-primary w-full py-3">
                        {{ submitting ? 'Memproses...' : 'Buat Pesanan' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
