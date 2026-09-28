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
        <!-- ============================================
             PAGE HEADER
             ============================================ -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center shadow-[3px_3px_0_0_#0a0a0a]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        Manajemen Buku
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                        Kelola koleksi buku toko
                    </p>
                </div>
            </div>

            <RouterLink
                to="/admin/books/create"
                class="inline-flex items-center justify-center gap-2 bg-[#8C5830] text-white px-5 py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all self-start md:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Buku
            </RouterLink>
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

            <div class="p-5 grid md:grid-cols-2 gap-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#0a0a0a]/40 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        v-model="filters.q"
                        @input="fetchBooks"
                        placeholder="Cari judul, penulis, atau ISBN..."
                        class="input pl-10"
                    />
                </div>

                <select v-model="filters.status" @change="fetchBooks" class="input">
                    <option value="">Semua Status</option>
                    <option value="low_stock">Stok Menipis</option>
                    <option value="out_of_stock">Stok Habis</option>
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
                    <span class="text-[11px] font-black uppercase tracking-[0.25em]">Daftar Buku</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-white/60">
                    {{ books.length }} Buku
                </span>
            </div>

            <!-- Empty state -->
            <div v-if="!books.length" class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                    <svg class="w-8 h-8 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                </div>
                <div class="text-sm font-black uppercase tracking-wider text-[#0a0a0a]">Belum Ada Buku</div>
                <p class="text-xs text-[#0a0a0a]/50 font-medium mt-1">Mulai tambahkan buku pertama Anda</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#F2ECE4] border-b-2 border-[#0a0a0a]">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Cover</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Judul</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Kategori</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Harga</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Stok</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/70">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="book in books"
                            :key="book.id"
                            class="border-b border-[#0a0a0a]/10 hover:bg-[#F2ECE4]/50 transition-colors"
                        >
                            <!-- Cover -->
                            <td class="px-4 py-3">
                                <div class="w-12 h-16 border-2 border-[#0a0a0a] bg-[#F2ECE4] overflow-hidden">
                                    <img
                                        :src="book.image_url"
                                        :alt="book.title"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                            </td>

                            <!-- Judul -->
                            <td class="px-4 py-3">
                                <div class="font-black text-sm text-[#0a0a0a] line-clamp-1">{{ book.title }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-0.5">
                                    {{ book.author }}
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center bg-[#F2ECE4] border-2 border-[#0a0a0a] px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]">
                                    {{ book.category?.name || 'Tanpa Kategori' }}
                                </span>
                            </td>

                            <!-- Harga -->
                            <td class="px-4 py-3">
                                <div class="font-black text-sm text-[#8C5830]">{{ book.formatted_price }}</div>
                            </td>

                            <!-- Stok -->
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center justify-center min-w-[44px] px-2.5 py-1 border-2 border-[#0a0a0a] text-[11px] font-black',
                                        book.stock > 5
                                            ? 'bg-green-100 text-green-800'
                                            : book.stock > 0
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ book.stock }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <RouterLink
                                        :to="`/admin/books/${book.id}/edit`"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-white border-2 border-[#0a0a0a] text-[#0a0a0a] hover:bg-[#8C5830] hover:text-white hover:border-[#8C5830] shadow-[2px_2px_0_0_#0a0a0a] hover:shadow-[2px_2px_0_0_#8C5830] transition-all"
                                        title="Edit"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </RouterLink>

                                    <button
                                        @click="deleteBook(book.id)"
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
            </div>
        </div>
    </div>
</template>