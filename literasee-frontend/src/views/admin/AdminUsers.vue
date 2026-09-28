<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '@/api/axios';
import { useToastStore } from '@/stores/toast';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import Pagination from '@/components/common/Pagination.vue';

const toast = useToastStore();
const users = ref([]);
const stats = ref(null);
const loading = ref(true);
const pagination = ref({ current: 1, last: 1, total: 0 });
const filters = ref({ q: '', role: '', sort: 'newest' });
let searchTimeout = null;

async function fetchUsers(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/users', { params: { ...filters.value, page, per_page: 15 } });
        users.value = data.data.users || [];
        stats.value = data.data.stats;
        pagination.value = {
            current: data.data.meta.current_page,
            last: data.data.meta.last_page,
            total: data.data.meta.total,
        };
    } finally { loading.value = false; }
}

watch(() => filters.value.q, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchUsers(1), 400);
});
watch([() => filters.value.role, () => filters.value.sort], () => fetchUsers(1));

function changePage(page) {
    fetchUsers(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function updateRole(user, newRole) {
    if (!confirm(`Ubah role ${user.name} menjadi ${newRole}?`)) return;
    try {
        await api.patch(`/admin/users/${user.id}/role`, { role: newRole });
        toast.success('Role diubah');
        fetchUsers(pagination.value.current);
    } catch (e) { toast.error(e.response?.data?.message || 'Gagal ubah role'); }
}

async function deleteUser(user) {
    if (!confirm(`Yakin hapus user ${user.name}?`)) return;
    try {
        await api.delete(`/admin/users/${user.id}`);
        toast.success('User dihapus');
        fetchUsers(pagination.value.current);
    } catch (e) { toast.error(e.response?.data?.message || 'Gagal hapus'); }
}

onMounted(() => fetchUsers(1));
</script>

<template>
    <div>
        <!-- ============================================
             PAGE HEADER
             ============================================ -->
        <div class="flex justify-between items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center shadow-[3px_3px_0_0_#0a0a0a]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        Manajemen Pengguna
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                        Total {{ pagination.total }} pengguna
                    </p>
                </div>
            </div>
        </div>

        <!-- ============================================
             STATS CARDS
             ============================================ -->
        <div v-if="stats" class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">

            <!-- Total Users -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                <div class="bg-[#0a0a0a] border-b-2 border-[#0a0a0a] px-3 py-2 flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-white">Total</span>
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="p-3">
                    <div class="text-[9px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Total</div>
                    <div class="text-xl font-black text-[#0a0a0a] leading-none">{{ stats.total_users }}</div>
                </div>
            </div>

            <!-- Customers -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                <div class="bg-blue-600 border-b-2 border-[#0a0a0a] px-3 py-2 flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-white">Customer</span>
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="p-3">
                    <div class="text-[9px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Customer</div>
                    <div class="text-xl font-black text-blue-700 leading-none">{{ stats.total_customers }}</div>
                </div>
            </div>

            <!-- Admins -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                <div class="bg-purple-600 border-b-2 border-[#0a0a0a] px-3 py-2 flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-white">Admin</span>
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="p-3">
                    <div class="text-[9px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Admin</div>
                    <div class="text-xl font-black text-purple-700 leading-none">{{ stats.total_admins }}</div>
                </div>
            </div>

            <!-- New This Month -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden">
                <div class="bg-primary-600 border-b-2 border-[#0a0a0a] px-3 py-2 flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-white">Baru</span>
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="p-3">
                    <div class="text-[9px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Bulan Ini</div>
                    <div class="text-xl font-black text-primary-700 leading-none">{{ stats.new_this_month }}</div>
                </div>
            </div>

            <!-- Active Buyers -->
            <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden col-span-2 lg:col-span-1">
                <div class="bg-green-600 border-b-2 border-[#0a0a0a] px-3 py-2 flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-white">Aktif</span>
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="p-3">
                    <div class="text-[9px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mb-1">Pembeli Aktif</div>
                    <div class="text-xl font-black text-green-700 leading-none">{{ stats.active_buyers }}</div>
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

            <div class="p-5 grid md:grid-cols-3 gap-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#0a0a0a]/40 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        v-model="filters.q"
                        type="text"
                        placeholder="Cari nama, email, no. HP..."
                        class="input pl-10"
                    />
                </div>

                <select v-model="filters.role" class="input">
                    <option value="">Semua Role</option>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>

                <select v-model="filters.sort" class="input">
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="most_orders">Banyak Order</option>
                    <option value="most_spent">Banyak Belanja</option>
                    <option value="name_asc">Nama A-Z</option>
                    <option value="name_desc">Nama Z-A</option>
                </select>
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
                    <span class="text-[11px] font-black uppercase tracking-[0.25em]">Daftar Pengguna</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-white/60">
                    {{ users.length }} dari {{ pagination.total }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#F2ECE4] border-b-2 border-[#0a0a0a]">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Pengguna</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Kontak</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Role</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Order</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Belanja</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Terdaftar</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-b border-[#0a0a0a]/10 hover:bg-[#F2ECE4]/50 transition-colors"
                        >
                            <!-- User -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#F2ECE4] overflow-hidden">
                                        <img
                                            v-if="user.avatar_url"
                                            :src="user.avatar_url"
                                            :alt="user.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <RouterLink
                                            :to="`/admin/users/${user.id}`"
                                            class="text-xs font-black uppercase tracking-wider text-[#8C5830] hover:text-[#0a0a0a] transition-colors line-clamp-1"
                                        >
                                            {{ user.name }}
                                        </RouterLink>
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/40">
                                            ID: {{ user.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kontak -->
                            <td class="px-4 py-3">
                                <div class="text-xs font-bold text-[#0a0a0a] truncate">{{ user.email }}</div>
                                <div class="text-[10px] font-bold text-[#0a0a0a]/50 truncate">{{ user.phone || '-' }}</div>
                            </td>

                            <!-- Role (dropdown) -->
                            <td class="px-4 py-3">
                                <select
                                    :value="user.role"
                                    @change="e => updateRole(user, e.target.value)"
                                    :class="[
                                        'text-[10px] font-black uppercase tracking-widest border-2 border-[#0a0a0a] px-2.5 py-1 cursor-pointer focus:outline-none transition-all',
                                        user.role === 'admin'
                                            ? 'bg-purple-300 text-purple-900'
                                            : 'bg-blue-300 text-blue-900'
                                    ]"
                                >
                                    <option value="customer">Customer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </td>

                            <!-- Order count -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 bg-[#F2ECE4] border-2 border-[#0a0a0a] px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    {{ user.orders_count }}
                                </span>
                            </td>

                            <!-- Total Spent -->
                            <td class="px-4 py-3">
                                <span class="font-black text-sm text-[#8C5830]">{{ user.total_spent_formatted }}</span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#0a0a0a]/60">
                                    {{ new Date(user.created_at).toLocaleDateString('id-ID') }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <RouterLink
                                        :to="`/admin/users/${user.id}`"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-white border-2 border-[#0a0a0a] text-[#0a0a0a] hover:bg-[#8C5830] hover:text-white hover:border-[#8C5830] shadow-[2px_2px_0_0_#0a0a0a] hover:shadow-[2px_2px_0_0_#8C5830] transition-all"
                                        title="Lihat Detail"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </RouterLink>

                                    <button
                                        @click="deleteUser(user)"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-white border-2 border-[#0a0a0a] text-[#0a0a0a] hover:bg-red-600 hover:text-white hover:border-red-600 shadow-[2px_2px_0_0_#0a0a0a] hover:shadow-[2px_2px_0_0_#dc2626] transition-all"
                                        title="Hapus"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty state -->
                <div v-if="!users.length" class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-black uppercase tracking-wider text-[#0a0a0a]">Tidak Ada Pengguna</div>
                    <p class="text-xs text-[#0a0a0a]/50 font-medium mt-1">Belum ada pengguna yang sesuai filter</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.length" class="p-4 border-t-2 border-[#0a0a0a]/10">
                <Pagination
                    :current-page="pagination.current"
                    :last-page="pagination.last"
                    @change="changePage"
                />
            </div>
        </div>
    </div>
</template>