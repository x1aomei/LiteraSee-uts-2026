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

        // DEBUG LOG
        console.log('=== API Response ===');
        console.log('Full:', response);
        console.log('Data:', response.data);

        const root = response.data;
        let items = [];

        // Robust parsing — handle semua format
        if (Array.isArray(root?.data)) {
            items = root.data;
            console.log('Format 1: data is array');
        } else if (Array.isArray(root?.data?.data)) {
            items = root.data.data;
            console.log('Format 2: data.data is array');
        } else if (Array.isArray(root?.data?.data?.data)) {
            items = root.data.data.data;
            console.log('Format 3: data.data.data is array');
        }

        console.log('Parsed items:', items);
        console.log('Count:', items.length);

        categories.value = items;
    } catch (e) {
        console.error('=== FETCH ERROR ===');
        console.error('Status:', e.response?.status);
        console.error('Data:', e.response?.data);
        console.error('Message:', e.message);
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
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">📂 Manajemen Kategori</h1>
                <p class="text-sm text-gray-500">Total: {{ categories.length }} kategori</p>
            </div>
            <button @click="openCreateModal" class="btn-primary">
                + Tambah Kategori
            </button>
        </div>

        <LoadingSpinner v-if="loading" />

        <div v-else-if="categories.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
                v-for="cat in categories"
                :key="cat.id"
                class="card p-4 group hover:shadow-md transition"
            >
                <div class="flex items-center gap-3 mb-3">
                    <img
                        :src="cat.image_url"
                        class="w-16 h-16 rounded-lg object-cover bg-gray-100"
                    />
                    <div class="flex-1 min-w-0">
                        <div class="font-bold truncate">{{ cat.name }}</div>
                        <div class="text-xs text-gray-500 truncate">
                            /{{ cat.slug }}
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 line-clamp-2 mb-3 min-h-[2rem]">
                    {{ cat.description || 'Tidak ada deskripsi' }}
                </p>

                <div class="flex items-center justify-between mb-3">
                    <span class="badge bg-gray-100 text-gray-700">
                        {{ cat.books_count || 0 }} buku
                    </span>
                    <span
                        :class="[
                            'badge',
                            cat.is_active
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-500'
                        ]"
                    >
                        {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="flex gap-2 pt-2 border-t">
                    <button
                        @click="openEditModal(cat)"
                        class="flex-1 text-sm text-primary-600 hover:bg-primary-50 py-1.5 rounded transition"
                    >
                        Edit
                    </button>
                    <button
                        @click="deleteCategory(cat)"
                        class="flex-1 text-sm text-red-600 hover:bg-red-50 py-1.5 rounded transition"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="card p-12 text-center">
            <div class="text-6xl mb-4">📂</div>
            <h3 class="text-lg font-semibold mb-2">Belum ada kategori</h3>
            <p class="text-gray-500 mb-4">Tambahkan kategori pertama</p>
            <button @click="openCreateModal" class="btn-primary">+ Tambah Kategori</button>
        </div>

        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showModal"
                    class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                    @click.self="closeModal"
                >
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                        <div class="p-6 border-b flex items-center justify-between">
                            <h2 class="font-bold text-lg">
                                {{ editingCategory ? '✏️ Edit Kategori' : '➕ Tambah Kategori' }}
                            </h2>
                            <button
                                @click="closeModal"
                                class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                            >
                                ×
                            </button>
                        </div>

                        <form @submit.prevent="submitForm" class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Nama Kategori *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="input"
                                    placeholder="Contoh: Fiksi"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="input"
                                    placeholder="Deskripsi singkat kategori..."
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Gambar (opsional)</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageChange"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                                />

                                <div v-if="imagePreview" class="mt-3">
                                    <img :src="imagePreview" class="w-24 h-24 object-cover rounded-lg" />
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="w-5 h-5 rounded text-primary-600"
                                />
                                <span class="text-sm font-medium">Aktif</span>
                            </label>

                            <div class="flex gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="btn-outline flex-1"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="saving"
                                    class="btn-primary flex-1"
                                >
                                    {{ saving ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
