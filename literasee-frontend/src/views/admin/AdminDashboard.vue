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
        <!-- ============================================
             PAGE HEADER
             ============================================ -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center shadow-[3px_3px_0_0_#0a0a0a]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        Dashboard
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                        Ringkasan statistik toko
                    </p>
                </div>
            </div>

            <button
                @click="fetchDashboard"
                class="inline-flex items-center justify-center gap-2 bg-white text-[#0a0a0a] px-5 py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all self-start md:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>

        <LoadingSpinner v-if="loading" />

        <!-- ============================================
             ERROR STATE
             ============================================ -->
        <div v-else-if="error" class="bg-white border-2 border-red-600 shadow-[4px_4px_0_0_#dc2626] p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 border-2 border-red-600 bg-red-50 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="text-sm font-black uppercase tracking-wider text-red-600 mb-1">Gagal Memuat Data</div>
            <p class="text-xs text-[#0a0a0a]/60 font-medium mb-5">{{ error }}</p>
            <button
                @click="fetchDashboard"
                class="inline-flex items-center gap-2 bg-red-600 text-white px-5 py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] transition-all"
            >
                Coba Lagi
            </button>
        </div>

        <!-- ============================================
             DASHBOARD CONTENT
             ============================================ -->
        <template v-else-if="stats">

            <!-- Row 1: 4 KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                <!-- Total Pendapatan -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-green-600 border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white">Pendapatan</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Total Pendapatan</div>
                        <div class="text-xl font-black text-green-700 leading-none">
                            Rp {{ Number(stats.total_revenue || 0).toLocaleString('id-ID') }}
                        </div>
                    </div>
                </div>

                <!-- Total Pesanan -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-blue-600 border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white">Pesanan</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Total Pesanan</div>
                        <div class="text-xl font-black text-[#0a0a0a] leading-none">
                            {{ stats.total_orders || 0 }}
                        </div>
                    </div>
                </div>

                <!-- Perlu Diproses -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-yellow-500 border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[#0a0a0a]">Antrian</span>
                        <svg class="w-4 h-4 text-[#0a0a0a]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Perlu Diproses</div>
                        <div class="text-xl font-black text-yellow-700 leading-none">
                            {{ stats.pending_orders || 0 }}
                        </div>
                    </div>
                </div>

                <!-- Stok Menipis -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-red-600 border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white">Alert</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Stok Menipis</div>
                        <div class="text-xl font-black text-red-700 leading-none">
                            {{ stats.low_stock || 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: 3 Customer Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-5 flex items-center gap-4">
                    <div class="w-12 h-12 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50">Total Customer</div>
                        <div class="text-2xl font-black text-[#0a0a0a] leading-none mt-1">{{ stats.total_customers || 0 }}</div>
                    </div>
                </div>

                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-5 flex items-center gap-4">
                    <div class="w-12 h-12 flex-shrink-0 border-2 border-[#0a0a0a] bg-primary-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50">Baru (Bulan Ini)</div>
                        <div class="text-2xl font-black text-primary-600 leading-none mt-1">{{ stats.new_customers_this_month || 0 }}</div>
                    </div>
                </div>

                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-5 flex items-center gap-4">
                    <div class="w-12 h-12 flex-shrink-0 border-2 border-[#0a0a0a] bg-green-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50">Pembeli Aktif</div>
                        <div class="text-2xl font-black text-green-700 leading-none mt-1">{{ stats.active_buyers || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Recent Orders + Recent Customers -->
            <div class="grid lg:grid-cols-3 gap-6 mb-6">

                <!-- Recent Orders -->
                <div class="lg:col-span-2 bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-[11px] font-black uppercase tracking-[0.25em]">Pesanan Terbaru</span>
                        </div>
                        <RouterLink
                            to="/admin/orders"
                            class="text-[10px] font-black uppercase tracking-widest text-white/60 hover:text-white transition-colors"
                        >
                            Lihat Semua →
                        </RouterLink>
                    </div>

                    <div v-if="recentOrders.length" class="divide-y divide-[#0a0a0a]/10">
                        <RouterLink
                            v-for="order in recentOrders"
                            :key="order.id"
                            to="/admin/orders"
                            class="flex items-center justify-between py-3 px-5 hover:bg-[#F2ECE4]/50 transition-colors group"
                        >
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                                    <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-black uppercase tracking-wider text-[#8C5830] group-hover:text-[#0a0a0a] transition-colors truncate">
                                        {{ order.order_number }}
                                    </div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 truncate">
                                        {{ order.user?.name }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-right ml-3">
                                <div class="text-sm font-black text-[#0a0a0a]">{{ order.formatted_total }}</div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-[#0a0a0a]/40 mt-0.5">
                                    {{ order.status }}
                                </div>
                            </div>
                        </RouterLink>
                    </div>

                    <div v-else class="p-12 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a]">Belum Ada Pesanan</div>
                    </div>
                </div>

                <!-- Recent Customers -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.25em]">Customer Terbaru</span>
                    </div>

                    <div v-if="recentCustomers.length" class="divide-y divide-[#0a0a0a]/10">
                        <RouterLink
                            v-for="c in recentCustomers"
                            :key="c.id"
                            :to="`/admin/users/${c.id}`"
                            class="flex items-center gap-3 py-3 px-5 hover:bg-[#F2ECE4]/50 transition-colors group"
                        >
                            <div class="w-9 h-9 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] overflow-hidden">
                                <img
                                    v-if="c.avatar_url"
                                    :src="c.avatar_url"
                                    :alt="c.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a] truncate group-hover:text-[#8C5830] transition-colors">
                                    {{ c.name }}
                                </div>
                                <div class="text-[10px] font-bold tracking-wider text-[#0a0a0a]/50 truncate">
                                    {{ c.email }}
                                </div>
                            </div>
                        </RouterLink>
                    </div>

                    <div v-else class="p-12 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a]">Belum Ada Customer</div>
                    </div>
                </div>
            </div>

            <!-- Row 4: Top Products -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    <span class="text-[11px] font-black uppercase tracking-[0.25em]">Produk Terlaris</span>
                </div>

                <div v-if="topProducts.length" class="p-5 grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div
                        v-for="(p, index) in topProducts"
                        :key="p.id"
                        class="group"
                    >
                        <div class="relative border-2 border-[#0a0a0a] overflow-hidden aspect-[3/4] bg-[#F2ECE4]">
                            <img
                                :src="p.image_url"
                                :alt="p.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />

                            <!-- Ranking badge -->
                            <span
                                class="absolute top-2 left-2 w-7 h-7 flex items-center justify-center bg-[#8C5830] text-white text-xs font-black border-2 border-[#0a0a0a] shadow-[2px_2px_0_0_#0a0a0a]"
                            >
                                {{ index + 1 }}
                            </span>
                        </div>

                        <div class="mt-2">
                            <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a] line-clamp-2 leading-tight">
                                {{ p.title }}
                            </div>
                            <div class="mt-1 flex items-center gap-1.5">
                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                <span class="text-[10px] font-black uppercase tracking-widest text-green-700">
                                    {{ p.sold }} Terjual
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a]">Belum Ada Penjualan</div>
                </div>
            </div>
        </template>
    </div>
</template>