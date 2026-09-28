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
        <!-- ============================================
             BACK LINK
             ============================================ -->
        <RouterLink
            to="/admin/users"
            class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/60 hover:text-[#8C5830] transition-colors mb-5"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="square" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar User
        </RouterLink>

        <LoadingSpinner v-if="loading" />

        <div v-else-if="data">

            <!-- ============================================
                 USER PROFILE CARD
                 ============================================ -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden mb-6">

                <!-- Header -->
                <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-[11px] font-black uppercase tracking-[0.25em]">Profil Pengguna</span>
                </div>

                <div class="p-5">
                    <!-- Top: Avatar + Info -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pb-5 border-b-2 border-dashed border-[#0a0a0a]/15">

                        <!-- Avatar -->
                        <div class="w-20 h-20 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] overflow-hidden shadow-[3px_3px_0_0_#0a0a0a]">
                            <img
                                v-if="data.user.avatar_url"
                                :src="data.user.avatar_url"
                                :alt="data.user.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                                {{ data.user.name }}
                            </h1>
                            <p class="text-xs font-bold text-[#0a0a0a]/60 mt-1.5">{{ data.user.email }}</p>

                            <!-- Role badge -->
                            <span
                                :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a] mt-2.5 shadow-[2px_2px_0_0_#0a0a0a]',
                                    data.user.role === 'admin'
                                        ? 'bg-purple-300 text-purple-900'
                                        : 'bg-blue-300 text-blue-900'
                                ]"
                            >
                                <svg v-if="data.user.role === 'admin'" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ data.user.role }}
                            </span>
                        </div>
                    </div>

                    <!-- Detail Info Grid -->
                    <div class="pt-5 grid md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50">No. HP</div>
                                <div class="text-xs font-black text-[#0a0a0a] mt-0.5">
                                    {{ data.user.phone || '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50">Terdaftar</div>
                                <div class="text-xs font-black text-[#0a0a0a] mt-0.5">
                                    {{ new Date(data.user.created_at).toLocaleString('id-ID') }}
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 flex items-start gap-3">
                            <div class="w-8 h-8 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50">Alamat</div>
                                <div class="text-xs font-bold text-[#0a0a0a] mt-0.5 leading-relaxed">
                                    {{ data.user.address || '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 STATS CARDS
                 ============================================ -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                <!-- Total Order -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-blue-600 border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white">Total</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Total Order</div>
                        <div class="text-xl font-black text-[#0a0a0a] leading-none">{{ data.stats.total_orders }}</div>
                    </div>
                </div>

                <!-- Paid -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-green-600 border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white">Paid</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Paid</div>
                        <div class="text-xl font-black text-green-700 leading-none">{{ data.stats.paid_orders }}</div>
                    </div>
                </div>

                <!-- Cancelled -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-red-600 border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white">Cancel</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Cancelled</div>
                        <div class="text-xl font-black text-red-700 leading-none">{{ data.stats.cancelled_orders }}</div>
                    </div>
                </div>

                <!-- Total Belanja -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                    <div class="bg-[#8C5830] border-b-2 border-[#0a0a0a] px-4 py-2 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white">Spent</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Total Belanja</div>
                        <div class="text-base font-black text-[#8C5830] leading-none">{{ data.stats.total_spent_formatted }}</div>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 RECENT ORDERS
                 ============================================ -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">

                <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.25em]">Pesanan Terakhir</span>
                    </div>
                    <RouterLink
                        to="/admin/orders"
                        class="text-[10px] font-black uppercase tracking-widest text-white/60 hover:text-white transition-colors"
                    >
                        Lihat Semua →
                    </RouterLink>
                </div>

                <div v-if="data.recent_orders?.length" class="divide-y divide-[#0a0a0a]/10">
                    <RouterLink
                        v-for="order in data.recent_orders"
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
                                    {{ new Date(order.created_at).toLocaleString('id-ID') }}
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
                    <p class="text-[10px] text-[#0a0a0a]/50 font-bold uppercase tracking-widest mt-1">
                        Customer ini belum melakukan pemesanan
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>