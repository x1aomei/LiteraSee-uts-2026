<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import Pagination from '@/components/common/Pagination.vue';

const orders = ref([]);
const loading = ref(true);
const pagination = ref({ current: 1, last: 1 });

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

async function fetchOrders(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/orders', { params: { page } });
        orders.value = data.data.data || [];
        pagination.value = { current: data.data.current_page, last: data.data.last_page };
    } finally { loading.value = false; }
}

function changePage(page) { fetchOrders(page); window.scrollTo({ top: 0, behavior: 'smooth' }); }
onMounted(() => fetchOrders(1));
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">📦 Pesanan Saya</h1>
        <LoadingSpinner v-if="loading" />

        <div v-else-if="orders.length" class="space-y-4">
            <RouterLink v-for="order in orders" :key="order.id" :to="`/orders/${order.id}`"
                class="card p-4 flex flex-wrap items-center justify-between gap-4 hover:shadow-md transition">
                <div>
                    <div class="font-bold text-primary-600">{{ order.order_number }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ new Date(order.created_at).toLocaleString('id-ID') }}</div>
                </div>
                <div class="text-sm">
                    <div class="text-gray-500">{{ order.items?.length || 0 }} item</div>
                    <div class="font-bold">Rp {{ Number(order.total_amount).toLocaleString('id-ID') }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="['badge', statusColors[order.status]]">{{ order.status.toUpperCase() }}</span>
                    <span :class="['badge', order.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700']">
                        {{ order.payment_status.toUpperCase() }}
                    </span>
                </div>
            </RouterLink>
            <Pagination :current-page="pagination.current" :last-page="pagination.last" @change="changePage" />
        </div>

        <div v-else class="card p-12 text-center">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-xl font-semibold mb-2">Belum Ada Pesanan</h3>
            <p class="text-gray-500 mb-6">Mulai belanja buku pertamamu</p>
            <RouterLink to="/catalog" class="btn-primary">Belanja Sekarang</RouterLink>
        </div>
    </div>
</template>
