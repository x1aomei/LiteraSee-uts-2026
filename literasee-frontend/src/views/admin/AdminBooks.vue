<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import { useToastStore } from '@/stores/toast';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const toast = useToastStore();
const books = ref([]);
const loading = ref(true);
const filters = ref({ q: '', status: '' });

async function fetchBooks() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/books', { params: filters.value });
        books.value = data.data.data || [];
    } finally { loading.value = false; }
}

async function deleteBook(id) {
    if (!confirm('Yakin hapus buku ini?')) return;
    try {
        await api.delete(`/admin/books/${id}`);
        toast.success('Buku dihapus');
        fetchBooks();
    } catch (e) { toast.error(e.response?.data?.message || 'Gagal hapus'); }
}

onMounted(fetchBooks);
</script>

<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">📚 Manajemen Buku</h1>
            <RouterLink to="/admin/books/create" class="btn-primary">+ Tambah Buku</RouterLink>
        </div>

        <div class="card p-4 mb-6">
            <div class="grid md:grid-cols-2 gap-3">
                <input v-model="filters.q" @input="fetchBooks" placeholder="Cari buku..." class="input" />
                <select v-model="filters.status" @change="fetchBooks" class="input">
                    <option value="">Semua Status</option>
                    <option value="low_stock">Stok Menipis</option>
                    <option value="out_of_stock">Habis</option>
                </select>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />

        <div v-else class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-left text-sm">
                        <tr>
                            <th class="px-4 py-3">Cover</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="book in books" :key="book.id" class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3"><img :src="book.image_url" class="w-12 h-16 object-cover rounded" /></td>
                            <td class="px-4 py-3">
                                <div class="font-medium line-clamp-1">{{ book.title }}</div>
                                <div class="text-xs text-gray-500">{{ book.author }}</div>
                            </td>
                            <td class="px-4 py-3"><span class="badge bg-gray-100">{{ book.category?.name }}</span></td>
                            <td class="px-4 py-3 font-semibold text-primary-600">{{ book.formatted_price }}</td>
                            <td class="px-4 py-3">
                                <span :class="['badge', book.stock > 5 ? 'bg-green-100 text-green-700' : book.stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700']">
                                    {{ book.stock }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <RouterLink :to="`/admin/books/${book.id}/edit`" class="text-primary-600 hover:underline text-sm">Edit</RouterLink>
                                    <button @click="deleteBook(book.id)" class="text-red-600 hover:underline text-sm">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>