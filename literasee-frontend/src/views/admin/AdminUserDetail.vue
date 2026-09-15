<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/api/axios';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const route = useRoute();
const data = ref(null);
const loading = ref(true);

async function fetchUser() {
    loading.value = true;
    try {
        const res = await api.get(`/admin/users/${route.params.id}`);
        data.value = res.data.data;
    } finally { loading.value = false; }
}
onMounted(fetchUser);
</script>

<template>
    <div>
        <RouterLink to="/admin/users" class="text-sm text-gray-500 hover:text-primary-600">← Kembali</RouterLink>
        <LoadingSpinner v-if="loading" />

        <div v-else-if="data" class="mt-4">
            <div class="card p-6 mb-6">
                <div class="flex items-center gap-4">
                    <img :src="data.user.avatar_url" class="w-20 h-20 rounded-full" />
                    <div>
                        <h1 class="text-2xl font-bold">{{ data.user.name }}</h1>
                        <p class="text-gray-500">{{ data.user.email }}</p>
                        <span :class="['badge mt-2', data.user.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700']">
                            {{ data.user.role.toUpperCase() }}
                        </span>
                    </div>
                </div>
                <hr class="my-4" />
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">No. HP:</span> <span class="font-medium ml-2">{{ data.user.phone || '-' }}</span></div>
                    <div><span class="text-gray-500">Terdaftar:</span> <span class="font-medium ml-2">{{ new Date(data.user.created_at).toLocaleString('id-ID') }}</span></div>
                    <div class="md:col-span-2"><span class="text-gray-500">Alamat:</span><div class="font-medium mt-1">{{ data.user.address || '-' }}</div></div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="card p-4"><div class="text-sm text-gray-500">Total Order</div><div class="text-2xl font-bold">{{ data.stats.total_orders }}</div></div>
                <div class="card p-4"><div class="text-sm text-gray-500">Paid</div><div class="text-2xl font-bold text-green-600">{{ data.stats.paid_orders }}</div></div>
                <div class="card p-4"><div class="text-sm text-gray-500">Cancelled</div><div class="text-2xl font-bold text-red-600">{{ data.stats.cancelled_orders }}</div></div>
                <div class="card p-4"><div class="text-sm text-gray-500">Total Belanja</div><div class="text-lg font-bold text-primary-600">{{ data.stats.total_spent_formatted }}</div></div>
            </div>

            <div class="card p-6">
                <h2 class="font-bold mb-4">Pesanan Terakhir</h2>
                <div v-if="data.recent_orders?.length" class="space-y-3">
                    <RouterLink v-for="order in data.recent_orders" :key="order.id" to="/admin/orders"
                        class="flex justify-between items-center py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <div class="font-medium">{{ order.order_number }}</div>
                            <div class="text-xs text-gray-500">{{ new Date(order.created_at).toLocaleString('id-ID') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold">{{ order.formatted_total }}</div>
                            <span class="text-xs">{{ order.status }}</span>
                        </div>
                    </RouterLink>
                </div>
                <p v-else class="text-gray-500 text-center py-8">Belum ada pesanan</p>
            </div>
        </div>
    </div>
</template>