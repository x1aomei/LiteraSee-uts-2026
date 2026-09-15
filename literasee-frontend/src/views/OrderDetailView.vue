<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api/axios';
import { useToastStore } from '@/stores/toast';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();
const order = ref(null);
const loading = ref(true);
const paying = ref(false);

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

async function fetchOrder() {
    loading.value = true;
    try {
        const { data } = await api.get(`/orders/${route.params.id}`);
        order.value = data.data;
    } catch (e) {
        if ([403, 404].includes(e.response?.status)) router.push('/orders');
    } finally { loading.value = false; }
}

async function payNow() {
    paying.value = true;
    try {
        const { data } = await api.post(`/payments/${order.value.id}/snap-token`);
        const { snap_token, client_key, snap_url } = data.data;
        await loadSnapScript(snap_url, client_key);

        window.snap.pay(snap_token, {
            onSuccess: () => { toast.success('Pembayaran berhasil!'); setTimeout(fetchOrder, 2000); },
            onPending: () => { toast.info('Menunggu pembayaran'); setTimeout(fetchOrder, 2000); },
            onError: () => toast.error('Pembayaran gagal'),
            onClose: () => toast.warning('Popup ditutup'),
        });
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal proses pembayaran');
    } finally { paying.value = false; }
}

function loadSnapScript(url, clientKey) {
    return new Promise((resolve, reject) => {
        if (window.snap) return resolve();
        const script = document.createElement('script');
        script.src = url;
        script.setAttribute('data-client-key', clientKey);
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load Snap.js'));
        document.head.appendChild(script);
    });
}

async function cancelOrder() {
    if (!confirm('Yakin batalkan pesanan ini?')) return;
    try {
        await api.post(`/orders/${order.value.id}/cancel`);
        toast.success('Pesanan dibatalkan');
        fetchOrder();
    } catch (e) { toast.error('Gagal membatalkan'); }
}

async function checkStatus() {
    try {
        await api.post(`/payments/${order.value.id}/check-status`);
        toast.info('Cek status...');
        setTimeout(fetchOrder, 1500);
    } catch (e) { toast.error('Gagal cek status'); }
}

onMounted(fetchOrder);
</script>

<template>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <LoadingSpinner v-if="loading" />

        <div v-else-if="order">
            <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
                <div>
                    <RouterLink to="/orders" class="text-sm text-gray-500 hover:text-primary-600">← Kembali</RouterLink>
                    <h1 class="text-2xl font-bold mt-2">{{ order.order_number }}</h1>
                    <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleString('id-ID') }}</p>
                </div>
                <span :class="['badge text-sm px-3 py-1.5', statusColors[order.status]]">{{ order.status.toUpperCase() }}</span>
            </div>

            <div class="card p-6 mb-6">
                <h2 class="font-bold mb-4">Alamat Pengiriman</h2>
                <div class="text-sm space-y-1">
                    <div class="font-medium">{{ order.shipping_name }}</div>
                    <div>{{ order.shipping_phone }}</div>
                    <div class="text-gray-600">{{ order.shipping_address }}</div>
                </div>
            </div>

            <div class="card p-6 mb-6">
                <h2 class="font-bold mb-4">Item Pesanan</h2>
                <div class="space-y-3">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between py-2 border-b last:border-0">
                        <div>
                            <div class="font-medium">{{ item.book_title }}</div>
                            <div class="text-sm text-gray-500">{{ item.book_author }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ item.quantity }} × Rp {{ Number(item.price).toLocaleString('id-ID') }}</div>
                            <div class="font-semibold">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</div>
                        </div>
                    </div>
                </div>
                <hr class="my-4" />
                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span class="text-primary-600">{{ order.formatted_total }}</span>
                </div>
            </div>

            <div v-if="order.payment_status !== 'paid' && order.status !== 'cancelled'" class="card p-6 bg-primary-50 border-primary-200">
                <div class="text-center">
                    <div class="text-4xl mb-3">💳</div>
                    <h3 class="text-lg font-bold mb-2">Selesaikan Pembayaran</h3>
                    <p class="text-sm text-gray-600 mb-4">Pesanan akan dibatalkan jika tidak dibayar dalam 24 jam.</p>
                    <div class="flex justify-center gap-3 flex-wrap">
                        <button @click="payNow" :disabled="paying" class="btn-primary px-6 py-3">
                            {{ paying ? 'Memproses...' : 'Bayar Sekarang' }}
                        </button>
                        <button @click="checkStatus" class="btn-outline">Cek Status</button>
                    </div>
                </div>
            </div>

            <div v-else-if="order.payment_status === 'paid'" class="card p-6 bg-green-50 border-green-200">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">✅</div>
                    <div>
                        <div class="font-bold text-green-700">Pembayaran Berhasil</div>
                        <div class="text-sm text-green-600">Pesanan sedang diproses</div>
                    </div>
                </div>
            </div>

            <div v-if="['pending', 'processing'].includes(order.status) && order.payment_status !== 'paid'" class="mt-6 text-center">
                <button @click="cancelOrder" class="text-sm text-red-600 hover:underline">Batalkan Pesanan</button>
            </div>
        </div>
    </div>
</template>
