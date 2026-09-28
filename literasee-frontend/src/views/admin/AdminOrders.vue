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
    pending:    'bg-yellow-200 text-yellow-900',
    processing: 'bg-blue-200 text-blue-900',
    shipped:    'bg-purple-200 text-purple-900',
    delivered:  'bg-green-300 text-green-900',
    cancelled:  'bg-red-200 text-red-900',
};

const paymentColors = {
    unpaid:   'bg-gray-200 text-gray-800',
    paid:     'bg-green-300 text-green-900',
    failed:   'bg-red-200 text-red-900',
    expired:  'bg-orange-200 text-orange-900',
    refunded: 'bg-purple-200 text-purple-900',
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
        <!-- ============================================
             PAGE HEADER
             ============================================ -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center shadow-[3px_3px_0_0_#0a0a0a]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        Manajemen Pesanan
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                        Total {{ pagination.total }} pesanan
                    </p>
                </div>
            </div>
        </div>

        <!-- ============================================
             FILTER CARD
             ============================================ -->
        <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden mb-6">
            <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-[11px] font-black uppercase tracking-[0.25em]">Filter & Pencarian</span>
            </div>

            <div class="p-5 grid md:grid-cols-4 gap-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#0a0a0a]/40 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        v-model="filters.q"
                        type="text"
                        placeholder="Cari no. order..."
                        class="input pl-10"
                    />
                </div>

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

                <button
                    @click="fetchOrders(1)"
                    class="inline-flex items-center justify-center gap-2 bg-white text-[#0a0a0a] px-4 py-2.5 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />

        <!-- ============================================
             TABLE CARD
             ============================================ -->
        <div v-else class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">

            <!-- Header -->
            <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span class="text-[11px] font-black uppercase tracking-[0.25em]">Daftar Pesanan</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-white/60">
                    {{ orders.length }} dari {{ pagination.total }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#F2ECE4] border-b-2 border-[#0a0a0a]">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">No. Order</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Customer</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Total</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Pembayaran</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Tanggal</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="border-b border-[#0a0a0a]/10 hover:bg-[#F2ECE4]/50 transition-colors"
                        >
                            <!-- Order Number -->
                            <td class="px-4 py-3">
                                <button
                                    @click="copyToClipboard(order.order_number)"
                                    class="font-mono text-xs font-black text-[#8C5830] hover:text-[#0a0a0a] transition-colors"
                                    title="Klik untuk copy"
                                >
                                    {{ order.order_number }}
                                </button>
                            </td>

                            <!-- Customer -->
                            <td class="px-4 py-3">
                                <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a] truncate">{{ order.user?.name }}</div>
                                <div class="text-[10px] font-bold text-[#0a0a0a]/50 truncate">{{ order.user?.email }}</div>
                            </td>

                            <!-- Total -->
                            <td class="px-4 py-3">
                                <span class="font-black text-sm text-[#0a0a0a]">{{ order.formatted_total }}</span>
                            </td>

                            <!-- Payment Status -->
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-1 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a]',
                                        paymentColors[order.payment_status] || 'bg-gray-200 text-gray-800'
                                    ]"
                                >
                                    {{ order.payment_status }}
                                </span>
                            </td>

                            <!-- Order Status -->
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-1 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a]',
                                        statusColors[order.status] || 'bg-gray-200 text-gray-800'
                                    ]"
                                >
                                    {{ order.status }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#0a0a0a]/60">
                                    {{ new Date(order.created_at).toLocaleDateString('id-ID') }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <button
                                        @click="viewDetail(order)"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-white border-2 border-[#0a0a0a] text-[#0a0a0a] hover:bg-[#8C5830] hover:text-white hover:border-[#8C5830] shadow-[2px_2px_0_0_#0a0a0a] hover:shadow-[2px_2px_0_0_#8C5830] transition-all"
                                        title="Lihat Detail"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty state -->
                <div v-if="!orders.length" class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-black uppercase tracking-wider text-[#0a0a0a]">Tidak Ada Pesanan</div>
                    <p class="text-xs text-[#0a0a0a]/50 font-medium mt-1">Belum ada pesanan yang sesuai filter</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="orders.length" class="p-4 border-t-2 border-[#0a0a0a]/10">
                <Pagination
                    :current-page="pagination.current"
                    :last-page="pagination.last"
                    @change="changePage"
                />
            </div>
        </div>

        <!-- ============================================
             DETAIL MODAL
             ============================================ -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showDetailModal && selectedOrder"
                    class="fixed inset-0 bg-[#0a0a0a]/70 z-50 flex items-center justify-center p-4"
                    @click.self="closeModal"
                >
                    <div class="bg-white border-2 border-[#0a0a0a] shadow-[8px_8px_0_0_#0a0a0a] max-w-3xl w-full max-h-[90vh] overflow-y-auto">

                        <!-- Modal Header -->
                        <div class="sticky top-0 bg-[#0a0a0a] text-white px-5 py-4 flex justify-between items-start z-10">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-white/60 mb-1">
                                    Order Number
                                </div>
                                <h2 class="text-lg font-black uppercase tracking-wider font-mono">
                                    {{ selectedOrder.order_number }}
                                </h2>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-white/50 mt-1">
                                    {{ new Date(selectedOrder.created_at).toLocaleString('id-ID') }}
                                </p>
                            </div>
                            <button
                                @click="closeModal"
                                class="w-7 h-7 flex items-center justify-center border-2 border-white/30 text-white/70 hover:text-white hover:border-white transition-colors shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="p-5 space-y-5">

                            <!-- Status Cards -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="border-2 border-[#0a0a0a] bg-[#F2ECE4] p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/60 mb-2">
                                        Status Pesanan
                                    </div>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-1 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a]',
                                            statusColors[selectedOrder.status] || 'bg-gray-200 text-gray-800'
                                        ]"
                                    >
                                        {{ selectedOrder.status }}
                                    </span>
                                </div>
                                <div class="border-2 border-[#0a0a0a] bg-[#F2ECE4] p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/60 mb-2">
                                        Status Pembayaran
                                    </div>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-1 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a]',
                                            paymentColors[selectedOrder.payment_status] || 'bg-gray-200 text-gray-800'
                                        ]"
                                    >
                                        {{ selectedOrder.payment_status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="bg-white border-2 border-[#0a0a0a] overflow-hidden">
                                <div class="bg-[#0a0a0a] text-white px-5 py-2.5 flex items-center gap-2.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase tracking-[0.25em]">Data Customer</span>
                                </div>
                                <div class="p-4 text-sm space-y-1.5">
                                    <div class="font-black text-xs uppercase tracking-wider text-[#0a0a0a]">
                                        {{ selectedOrder.shipping_name }}
                                    </div>
                                    <div class="text-xs font-bold text-[#0a0a0a]/70">{{ selectedOrder.shipping_phone }}</div>
                                    <div class="text-xs font-medium text-[#0a0a0a]/60 leading-relaxed">
                                        {{ selectedOrder.shipping_address }}
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="bg-white border-2 border-[#0a0a0a] overflow-hidden">
                                <div class="bg-[#0a0a0a] text-white px-5 py-2.5 flex items-center gap-2.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase tracking-[0.25em]">Item Pesanan</span>
                                </div>

                                <div class="p-4 space-y-2">
                                    <div
                                        v-for="item in selectedOrder.items"
                                        :key="item.id"
                                        class="flex justify-between items-start p-3 border-2 border-[#0a0a0a]/10 hover:border-[#0a0a0a] transition-colors"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a]">
                                                {{ item.book_title }}
                                            </div>
                                            <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-0.5">
                                                {{ item.book_author }}
                                            </div>
                                            <div class="text-[10px] font-bold text-[#0a0a0a]/60 mt-1">
                                                {{ item.quantity }} × Rp {{ Number(item.price).toLocaleString('id-ID') }}
                                            </div>
                                        </div>
                                        <div class="font-black text-sm text-[#8C5830] ml-3">
                                            Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center pt-3 mt-2 border-t-2 border-dashed border-[#0a0a0a]/20">
                                        <span class="text-[11px] font-black uppercase tracking-[0.25em] text-[#0a0a0a]">
                                            Total
                                        </span>
                                        <span class="text-lg font-black text-[#8C5830]">
                                            {{ selectedOrder.formatted_total }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="selectedOrder.notes" class="bg-white border-2 border-[#0a0a0a] overflow-hidden">
                                <div class="bg-yellow-400 border-b-2 border-[#0a0a0a] px-5 py-2.5 flex items-center gap-2.5">
                                    <svg class="w-3.5 h-3.5 text-[#0a0a0a]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[#0a0a0a]">
                                        Catatan Customer
                                    </span>
                                </div>
                                <div class="p-4 text-xs font-medium text-[#0a0a0a] leading-relaxed">
                                    {{ selectedOrder.notes }}
                                </div>
                            </div>

                            <!-- Update Status -->
                            <div class="bg-white border-2 border-[#0a0a0a] overflow-hidden">
                                <div class="bg-[#0a0a0a] text-white px-5 py-2.5 flex items-center gap-2.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase tracking-[0.25em]">Update Status Pesanan</span>
                                </div>

                                <div class="p-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="s in ['pending', 'processing', 'shipped', 'delivered', 'cancelled']"
                                            :key="s"
                                            @click="updateStatus(selectedOrder.id, s)"
                                            :disabled="updatingStatus || selectedOrder.status === s"
                                            :class="[
                                                'px-4 py-2.5 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a] transition-all',
                                                selectedOrder.status === s
                                                    ? 'bg-[#8C5830] text-white cursor-not-allowed shadow-[2px_2px_0_0_#0a0a0a]'
                                                    : 'bg-white text-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none'
                                            ]"
                                        >
                                            {{ s }}
                                        </button>
                                    </div>

                                    <div class="mt-4 flex items-start gap-2 bg-red-50 border-2 border-red-600 p-3">
                                        <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-red-800 leading-relaxed">
                                            Status <span class="font-black">cancelled</span> akan mengembalikan stok buku otomatis.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>