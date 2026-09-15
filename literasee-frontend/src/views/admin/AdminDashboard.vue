<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const stats = ref(null);
const recentOrders = ref([]);
const recentCustomers = ref([]);
const topProducts = ref([]);
const loading = ref(true);
const error = ref('');

async function fetchDashboard() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/admin/dashboard');
        stats.value = data.data.stats;
        recentOrders.value = data.data.recent_orders || [];
        recentCustomers.value = data.data.recent_customers || [];
        topProducts.value = data.data.top_products || [];
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal memuat data dashboard';
        console.error('Dashboard error:', e);
    } finally {
        loading.value = false;
    }
}
onMounted(fetchDashboard);
</script>

<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">📊 Dashboard</h1>
            <button @click="fetchDashboard" class="btn-outline text-sm">🔄 Refresh</button>
        </div>

        <LoadingSpinner v-if="loading" />

        <div v-else-if="error" class="card p-8 text-center border-red-200">
            <div class="text-5xl mb-3">⚠️</div>
            <h3 class="font-bold text-red-600 mb-2">Gagal Memuat Data</h3>
            <p class="text-gray-600 mb-4">{{ error }}</p>
            <button @click="fetchDashboard" class="btn-primary">Coba Lagi</button>
        </div>

        <template v-else-if="stats">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="card p-5 border-l-4 border-green-500">
                    <div class="text-sm text-gray-500">Total Pendapatan</div>
                    <div class="text-2xl font-bold text-green-600">Rp {{ Number(stats.total_revenue || 0).toLocaleString('id-ID') }}</div>
                </div>
                <div class="card p-5 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-500">Total Pesanan</div>
                    <div class="text-2xl font-bold">{{ stats.total_orders || 0 }}</div>
                </div>
                <div class="card p-5 border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-500">Perlu Diproses</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ stats.pending_orders || 0 }}</div>
                </div>
                <div class="card p-5 border-l-4 border-red-500">
                    <div class="text-sm text-gray-500">Stok Menipis</div>
                    <div class="text-2xl font-bold text-red-600">{{ stats.low_stock || 0 }}</div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="card p-5">
                    <div class="text-sm text-gray-500">Total Customer</div>
                    <div class="text-2xl font-bold">{{ stats.total_customers || 0 }}</div>
                </div>
                <div class="card p-5">
                    <div class="text-sm text-gray-500">Baru (Bulan Ini)</div>
                    <div class="text-2xl font-bold text-primary-600">{{ stats.new_customers_this_month || 0 }}</div>
                </div>
                <div class="card p-5">
                    <div class="text-sm text-gray-500">Pembeli Aktif</div>
                    <div class="text-2xl font-bold text-green-600">{{ stats.active_buyers || 0 }}</div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 card p-6">
                    <h2 class="font-bold mb-4">Pesanan Terbaru</h2>
                    <div v-if="recentOrders.length" class="space-y-3">
                        <RouterLink v-for="order in recentOrders" :key="order.id" to="/admin/orders"
                            class="flex justify-between items-center py-2 border-b last:border-0 hover:bg-gray-50 px-2 -mx-2 rounded">
                            <div>
                                <div class="font-medium text-sm text-primary-600">{{ order.order_number }}</div>
                                <div class="text-xs text-gray-500">{{ order.user?.name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-sm">{{ order.formatted_total }}</div>
                                <span class="text-xs">{{ order.status }}</span>
                            </div>
                        </RouterLink>
                    </div>
                    <p v-else class="text-gray-500 text-center py-6">Belum ada pesanan</p>
                </div>

                <div class="card p-6">
                    <h2 class="font-bold mb-4">👥 Customer Terbaru</h2>
                    <div v-if="recentCustomers.length" class="space-y-3">
                        <RouterLink v-for="c in recentCustomers" :key="c.id" :to="`/admin/users/${c.id}`"
                            class="flex items-center gap-3 py-1 hover:bg-gray-50 px-2 -mx-2 rounded">
                            <img :src="c.avatar_url" class="w-8 h-8 rounded-full" />
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ c.name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ c.email }}</div>
                            </div>
                        </RouterLink>
                    </div>
                    <p v-else class="text-gray-500 text-center py-6">Belum ada customer</p>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="font-bold mb-4">🔥 Produk Terlaris</h2>
                <div v-if="topProducts.length" class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div v-for="p in topProducts" :key="p.id" class="text-center">
                        <img :src="p.image_url" class="w-full aspect-[3/4] object-cover rounded mb-2" />
                        <div class="text-sm font-medium line-clamp-2">{{ p.title }}</div>
                        <div class="text-xs text-gray-500">{{ p.sold }} terjual</div>
                    </div>
                </div>
                <p v-else class="text-gray-500 text-center py-6">Belum ada penjualan</p>
            </div>
        </template>
    </div>
</template>
