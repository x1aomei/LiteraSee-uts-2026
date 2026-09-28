<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import { useToastStore } from '@/stores/toast';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const toast = useToastStore();

const categories = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const editingCategory = ref(null);

const form = ref({
    name: '',
    description: '',
    is_active: true,
    image: null,
});

const imagePreview = ref(null);

async function fetchCategories() {
    loading.value = true;
    try {
        const response = await api.get('/admin/categories', { params: { per_page: 100 } });

        const root = response.data;
        let items = [];

        if (Array.isArray(root?.data)) {
            items = root.data;
        } else if (Array.isArray(root?.data?.data)) {
            items = root.data.data;
        } else if (Array.isArray(root?.data?.data?.data)) {
            items = root.data.data.data;
        }

        categories.value = items;
    } catch (e) {
        console.error('FETCH ERROR:', e);
        toast.error('Gagal memuat kategori');
    } finally {
        loading.value = false;
    }
}

function openCreateModal() {
    editingCategory.value = null;
    form.value = { name: '', description: '', is_active: true, image: null };
    imagePreview.value = null;
    showModal.value = true;
}

function openEditModal(category) {
    editingCategory.value = category;
    form.value = {
        name: category.name,
        description: category.description || '',
        is_active: category.is_active,
        image: null,
    };
    imagePreview.value = category.image_url;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingCategory.value = null;
    form.value = { name: '', description: '', is_active: true, image: null };
    imagePreview.value = null;
}

function handleImageChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.value.image = file;

    const reader = new FileReader();
    reader.onload = (ev) => imagePreview.value = ev.target.result;
    reader.readAsDataURL(file);
}

async function submitForm() {
    saving.value = true;
    try {
        const formData = new FormData();
        formData.append('name', form.value.name);
        if (form.value.description) formData.append('description', form.value.description);
        formData.append('is_active', form.value.is_active ? '1' : '0');
        if (form.value.image) formData.append('image', form.value.image);

        let url = '/admin/categories';
        if (editingCategory.value) {
            formData.append('_method', 'PUT');
            url = `/admin/categories/${editingCategory.value.id}`;
        }

        const { data } = await api.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        toast.success(data.message || 'Kategori berhasil disimpan!');
        closeModal();
        fetchCategories();
    } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
            const firstError = Object.values(errors)[0];
            toast.error(Array.isArray(firstError) ? firstError[0] : firstError);
        } else {
            toast.error(e.response?.data?.message || 'Gagal menyimpan');
        }
    } finally {
        saving.value = false;
    }
}

async function deleteCategory(category) {
    if (!confirm(`Yakin hapus kategori "${category.name}"?`)) return;

    try {
        await api.delete(`/admin/categories/${category.id}`);
        toast.success('Kategori dihapus');
        fetchCategories();
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal menghapus');
    }
}

onMounted(fetchCategories);
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        Manajemen Kategori
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                        Total {{ categories.length }} kategori
                    </p>
                </div>
            </div>

            <button
                @click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 bg-[#8C5830] text-white px-5 py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all self-start md:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kategori
            </button>
        </div>

        <LoadingSpinner v-if="loading" />

        <!-- ============================================
             GRID KATEGORI
             ============================================ -->
        <div
            v-else-if="categories.length"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
        >
            <div
                v-for="cat in categories"
                :key="cat.id"
                class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] hover:shadow-[6px_6px_0_0_#0a0a0a] hover:-translate-x-[2px] hover:-translate-y-[2px] transition-all duration-150 overflow-hidden flex flex-col"
            >
                <!-- Header dengan gambar -->
                <div class="relative border-b-2 border-[#0a0a0a] bg-[#F2ECE4]">
                    <div class="aspect-[16/9] overflow-hidden">
                        <img
                            :src="cat.image_url"
                            :alt="cat.name"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                    </div>

                    <!-- Status badge overlay -->
                    <span
                        class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-1 text-[9px] font-black uppercase tracking-widest border-2 border-[#0a0a0a]"
                        :class="cat.is_active
                            ? 'bg-green-400 text-[#0a0a0a]'
                            : 'bg-gray-300 text-[#0a0a0a]/70'"
                    >
                        <span
                            class="w-1.5 h-1.5 border border-[#0a0a0a]"
                            :class="cat.is_active ? 'bg-green-700' : 'bg-gray-600'"
                        ></span>
                        {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <!-- Content -->
                <div class="p-4 flex flex-col flex-1">

                    <!-- Nama + slug -->
                    <div class="mb-2">
                        <div class="font-black uppercase tracking-tight text-base text-[#0a0a0a] line-clamp-1 leading-tight">
                            {{ cat.name }}
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/40 mt-0.5">
                            /{{ cat.slug }}
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <p class="text-xs text-[#0a0a0a]/60 font-medium line-clamp-2 mb-3 min-h-[2.4rem] leading-relaxed">
                        {{ cat.description || 'Tidak ada deskripsi' }}
                    </p>

                    <!-- Statistik -->
                    <div class="flex items-center gap-2 mb-4 mt-auto">
                        <span class="inline-flex items-center gap-1.5 bg-[#F2ECE4] border-2 border-[#0a0a0a] px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                            </svg>
                            {{ cat.books_count || 0 }} Buku
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-2 pt-3 border-t-2 border-dashed border-[#0a0a0a]/15">
                        <button
                            @click="openEditModal(cat)"
                            class="flex items-center justify-center gap-1.5 py-2 text-[10px] font-black uppercase tracking-widest bg-white border-2 border-[#0a0a0a] text-[#0a0a0a] hover:bg-[#8C5830] hover:text-white transition-all shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>

                        <button
                            @click="deleteCategory(cat)"
                            class="flex items-center justify-center gap-1.5 py-2 text-[10px] font-black uppercase tracking-widest bg-white border-2 border-[#0a0a0a] text-[#0a0a0a] hover:bg-red-600 hover:text-white hover:border-red-600 transition-all shadow-[2px_2px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================
             EMPTY STATE
             ============================================ -->
        <div v-else class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 border-2 border-[#0a0a0a] bg-[#F2ECE4] flex items-center justify-center">
                <svg class="w-8 h-8 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <div class="text-sm font-black uppercase tracking-wider text-[#0a0a0a] mb-1">Belum Ada Kategori</div>
            <p class="text-xs text-[#0a0a0a]/50 font-medium mb-5">Tambahkan kategori pertama Anda</p>
            <button
                @click="openCreateModal"
                class="inline-flex items-center gap-2 bg-[#8C5830] text-white px-5 py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kategori
            </button>
        </div>

        <!-- ============================================
             MODAL
             ============================================ -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showModal"
                    class="fixed inset-0 bg-[#0a0a0a]/70 z-50 flex items-center justify-center p-4"
                    @click.self="closeModal"
                >
                    <div class="bg-white border-2 border-[#0a0a0a] shadow-[8px_8px_0_0_#0a0a0a] max-w-md w-full max-h-[90vh] overflow-y-auto">

                        <!-- Header -->
                        <div class="bg-[#0a0a0a] text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
                            <div class="flex items-center gap-2.5">
                                <svg v-if="editingCategory" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-[11px] font-black uppercase tracking-[0.25em]">
                                    {{ editingCategory ? 'Edit Kategori' : 'Tambah Kategori' }}
                                </span>
                            </div>
                            <button
                                @click="closeModal"
                                class="w-7 h-7 flex items-center justify-center border-2 border-white/30 text-white/70 hover:text-white hover:border-white transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitForm" class="p-5 space-y-5">

                            <!-- Nama -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                    Nama Kategori <span class="text-red-600">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="input"
                                    placeholder="Contoh: Fiksi"
                                />
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                    Deskripsi
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="input"
                                    placeholder="Deskripsi singkat kategori..."
                                ></textarea>
                            </div>

                            <!-- Gambar -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                    Gambar <span class="text-[#0a0a0a]/40 normal-case font-medium">(opsional)</span>
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageChange"
                                    class="block w-full text-xs text-[#0a0a0a]/70 border-2 border-dashed border-[#0a0a0a]/30 p-3 cursor-pointer hover:border-[#0a0a0a] hover:bg-[#F2ECE4]/30 transition-all file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-[#0a0a0a] file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-[#8C5830] file:text-white file:cursor-pointer hover:file:bg-[#0a0a0a]"
                                />

                                <div v-if="imagePreview" class="mt-3">
                                    <div class="w-24 h-24 border-2 border-[#0a0a0a] overflow-hidden">
                                        <img :src="imagePreview" class="w-full h-full object-cover" />
                                    </div>
                                </div>
                            </div>

                            <!-- Aktif checkbox -->
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="w-5 h-5 mt-0.5 border-2 border-[#0a0a0a] text-[#8C5830] focus:ring-0 cursor-pointer accent-[#8C5830]"
                                />
                                <div>
                                    <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a]">Aktif</div>
                                    <div class="text-[10px] text-[#0a0a0a]/50 font-bold uppercase tracking-wider mt-0.5">
                                        Kategori tampil di katalog
                                    </div>
                                </div>
                            </label>

                            <!-- Buttons -->
                            <div class="flex gap-3 pt-2 border-t-2 border-dashed border-[#0a0a0a]/15">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="flex-1 flex items-center justify-center gap-2 bg-white text-[#0a0a0a] py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="saving"
                                    class="flex-1 flex items-center justify-center gap-2 bg-[#8C5830] text-white py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <template v-if="!saving">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                        </svg>
                                        Simpan
                                    </template>
                                    <template v-else>
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        Menyimpan...
                                    </template>
                                </button>
                            </div>
                        </form>
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