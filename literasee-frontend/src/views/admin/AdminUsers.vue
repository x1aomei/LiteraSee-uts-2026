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
        pagination.value = { current: data.data.meta.current_page, last: data.data.meta.last_page, total: data.data.meta.total };
    } finally { loading.value = false; }
}

watch(() => filters.value.q, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchUsers(1), 400);
});
watch([() => filters.value.role, () => filters.value.sort], () => fetchUsers(1));

function changePage(page) { fetchUsers(page); window.scrollTo({ top: 0, behavior: 'smooth' }); }

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
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">👥 Manajemen Pengguna</h1>
                <p class="text-sm text-gray-500">Total: {{ pagination.total }}</p>
            </div>
        </div>

        <div v-if="stats" class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="card p-4"><div class="text-sm text-gray-500">Total</div><div class="text-2xl font-bold">{{ stats.total_users }}</div></div>
            <div class="card p-4"><div class="text-sm text-gray-500">Customer</div><div class="text-2xl font-bold text-blue-600">{{ stats.total_customers }}</div></div>
            <div class="card p-4"><div class="text-sm text-gray-500">Admin</div><div class="text-2xl font-bold text-purple-600">{{ stats.total_admins }}</div></div>
            <div class="card p-4"><div class="text-sm text-gray-500">Baru Bulan Ini</div><div class="text-2xl font-bold text-primary-600">{{ stats.new_this_month }}</div></div>
            <div class="card p-4"><div class="text-sm text-gray-500">Pembeli Aktif</div><div class="text-2xl font-bold text-green-600">{{ stats.active_buyers }}</div></div>
        </div>

        <div class="card p-4 mb-6">
            <div class="grid md:grid-cols-3 gap-3">
                <input v-model="filters.q" type="text" placeholder="🔍 Cari nama, email, no. HP..." class="input" />
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

        <div v-else class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-left text-sm">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Total Belanja</th>
                            <th class="px-4 py-3">Terdaftar</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img :src="user.avatar_url" class="w-10 h-10 rounded-full" />
                                    <div>
                                        <RouterLink :to="`/admin/users/${user.id}`" class="font-medium text-primary-600 hover:underline">{{ user.name }}</RouterLink>
                                        <div class="text-xs text-gray-500">ID: {{ user.id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div>{{ user.email }}</div>
                                <div class="text-gray-500">{{ user.phone || '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <select :value="user.role" @change="e => updateRole(user, e.target.value)"
                                    :class="['text-xs rounded-full px-2 py-1 border-0 font-medium', user.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700']">
                                    <option value="customer">Customer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </td>
                            <td class="px-4 py-3"><span class="badge bg-gray-100">{{ user.orders_count }} order</span></td>
                            <td class="px-4 py-3 font-semibold text-primary-600">{{ user.total_spent_formatted }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ new Date(user.created_at).toLocaleDateString('id-ID') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <RouterLink :to="`/admin/users/${user.id}`" class="text-primary-600 hover:underline text-sm">Detail</RouterLink>
                                    <button @click="deleteUser(user)" class="text-red-600 hover:underline text-sm">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!users.length" class="p-12 text-center text-gray-500">Tidak ada user ditemukan</div>
            </div>
            <div v-if="users.length" class="p-4">
                <Pagination :current-page="pagination.current" :last-page="pagination.last" @change="changePage" />
            </div>
        </div>
    </div>
</template>