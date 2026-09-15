<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '@/api/axios';
import { useToastStore } from '@/stores/toast';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import Pagination from '@/components/common/Pagination.vue';

const toast = useToastStore();

const orders = ref([]);
const loading = ref(true);
const pagination = ref({ current: 1, last: 1, total: 0 });
const selectedOrder = ref(null);
const showDetailModal = ref(false);
const updatingStatus = ref(false);

const filters = ref({
    status: '',
    payment_status: '',
    q: '',
});

let searchTimeout = null;

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

const paymentColors = {
    unpaid: 'bg-gray-100 text-gray-700',
    paid: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    expired: 'bg-orange-100 text-orange-700',
    refunded: 'bg-purple-100 text-purple-700',
};

async function fetchOrders(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/orders', {
            params: { ...filters.value, page, per_page: 20 },
        });
        orders.value = data.data.data || [];
        pagination.value = {
            current: data.data.current_page,
            last: data.data.last_page,
            total: data.data.total,
        };
    } finally {
        loading.value = false;
    }
}

function changePage(page) {
    fetchOrders(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

watch(() => filters.value.q, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchOrders(1), 400);
});

watch([() => filters.value.status, () => filters.value.payment_status], () => fetchOrders(1));

async function viewDetail(order) {
    try {
        const { data } = await api.get(`/admin/orders/${order.id}`);
        selectedOrder.value = data.data;
        showDetailModal.value = true;
    } catch (e) {
        toast.error('Gagal memuat detail');
    }
}

async function updateStatus(orderId, newStatus) {
    if (!confirm(`Ubah status pesanan menjadi "${newStatus}"?`)) return;

    updatingStatus.value = true;
    try {
        const { data } = await api.patch(`/admin/orders/${orderId}/status`, {
            status: newStatus,
        });
        toast.success(data.message || 'Status diubah');

        fetchOrders(pagination.value.current);
        if (selectedOrder.value?.id === orderId) {
            selectedOrder.value.status = newStatus;
        }
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal update status');
    } finally {
        updatingStatus.value = false;
    }
}

function closeModal() {
    showDetailModal.value = false;
    selectedOrder.value = null;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    toast.success('Disalin ke clipboard');
}

onMounted(() => fetchOrders(1));
</script>

<template>
    <div>
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold">📦 Manajemen Pesanan</h1>
                <p class="text-sm text-gray-500">Total: {{ pagination.total }} pesanan</p>
            </div>
        </div>

        <div class="card p-4 mb-6">
            <div class="grid md:grid-cols-4 gap-3">
                <input
                    v-model="filters.q"
                    type="text"
                    placeholder="🔍 Cari no. order..."
                    class="input"
                />
                <select v-model="filters.status" class="input">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select v-model="filters.payment_status" class="input">
                    <option value="">Semua Pembayaran</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="expired">Expired</option>
                    <option value="refunded">Refunded</option>
                </select>
                <button @click="fetchOrders(1)" class="btn-outline">
                    🔄 Refresh
                </button>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />

        <div v-else class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-left text-sm">
                        <tr>
                            <th class="px-4 py-3">No. Order</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Pembayaran</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="border-t hover:bg-gray-50"
                        >
                            <td class="px-4 py-3">
                                <button
                                    @click="copyToClipboard(order.order_number)"
                                    class="font-mono text-sm font-bold text-primary-600 hover:underline"
                                    title="Klik untuk copy"
                                >
                                    {{ order.order_number }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium">{{ order.user?.name }}</div>
                                <div class="text-xs text-gray-500">{{ order.user?.email }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                {{ order.formatted_total }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['badge', paymentColors[order.payment_status]]">
                                    {{ order.payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['badge', statusColors[order.status]]">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ new Date(order.created_at).toLocaleDateString('id-ID') }}
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    @click="viewDetail(order)"
                                    class="text-primary-600 hover:underline text-sm"
                                >
                                    Detail
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!orders.length" class="p-12 text-center text-gray-500">
                    Tidak ada pesanan ditemukan
                </div>
            </div>

            <div v-if="orders.length" class="p-4">
                <Pagination
                    :current-page="pagination.current"
                    :last-page="pagination.last"
                    @change="changePage"
                />
            </div>
        </div>

        <!-- Detail Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showDetailModal && selectedOrder"
                    class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                    @click.self="closeModal"
                >
                    <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white p-6 border-b flex justify-between items-start z-10">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Order Number</div>
                                <h2 class="text-xl font-bold font-mono">{{ selectedOrder.order_number }}</h2>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ new Date(selectedOrder.created_at).toLocaleString('id-ID') }}
                                </p>
                            </div>
                            <button
                                @click="closeModal"
                                class="text-gray-400 hover:text-gray-600 text-3xl leading-none"
                            >
                                ×
                            </button>
                        </div>

                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-lg bg-gray-50">
                                    <div class="text-xs text-gray-500 mb-1">Status Pesanan</div>
                                    <span :class="['badge', statusColors[selectedOrder.status]]">
                                        {{ selectedOrder.status.toUpperCase() }}
                                    </span>
                                </div>
                                <div class="p-3 rounded-lg bg-gray-50">
                                    <div class="text-xs text-gray-500 mb-1">Status Pembayaran</div>
                                    <span :class="['badge', paymentColors[selectedOrder.payment_status]]">
                                        {{ selectedOrder.payment_status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-bold mb-3">👤 Customer</h3>
                                <div class="p-4 bg-gray-50 rounded-lg text-sm space-y-1">
                                    <div><strong>{{ selectedOrder.shipping_name }}</strong></div>
                                    <div>{{ selectedOrder.shipping_phone }}</div>
                                    <div class="text-gray-600">{{ selectedOrder.shipping_address }}</div>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-bold mb-3">📚 Item Pesanan</h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="item in selectedOrder.items"
                                        :key="item.id"
                                        class="flex justify-between p-3 border rounded-lg"
                                    >
                                        <div>
                                            <div class="font-medium text-sm">{{ item.book_title }}</div>
                                            <div class="text-xs text-gray-500">{{ item.book_author }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ item.quantity }} × Rp {{ Number(item.price).toLocaleString('id-ID') }}
                                            </div>
                                        </div>
                                        <div class="font-bold text-sm">
                                            Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between font-bold text-lg mt-3 pt-3 border-t">
                                    <span>Total</span>
                                    <span class="text-primary-600">{{ selectedOrder.formatted_total }}</span>
                                </div>
                            </div>

                            <div v-if="selectedOrder.notes">
                                <h3 class="font-bold mb-3">📝 Catatan Customer</h3>
                                <div class="p-4 bg-yellow-50 rounded-lg text-sm">
                                    {{ selectedOrder.notes }}
                                </div>
                            </div>

                            <div class="border-t pt-6">
                                <h3 class="font-bold mb-3">⚙️ Update Status Pesanan</h3>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="s in ['pending', 'processing', 'shipped', 'delivered', 'cancelled']"
                                        :key="s"
                                        @click="updateStatus(selectedOrder.id, s)"
                                        :disabled="updatingStatus || selectedOrder.status === s"
                                        :class="[
                                            'px-4 py-2 rounded-lg text-sm font-medium transition',
                                            selectedOrder.status === s
                                                ? 'bg-primary-600 text-white cursor-not-allowed'
                                                : 'bg-gray-100 hover:bg-gray-200',
                                        ]"
                                    >
                                        {{ s.charAt(0).toUpperCase() + s.slice(1) }}
                                    </button>
                                </div>

                                <p class="text-xs text-gray-500 mt-3">
                                    ⚠️ Status "cancelled" akan mengembalikan stok buku otomatis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>