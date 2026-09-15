<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api/axios';
import { useToastStore } from '@/stores/toast';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const saving = ref(false);
const categories = ref([]);
const existingImages = ref([]);
const newImageFiles = ref([]);
const newImagePreviews = ref([]);

const form = ref({
    category_id: '',
    title: '',
    author: '',
    publisher: '',
    isbn: '',
    year: '',
    pages: '',
    language: 'Indonesia',
    description: '',
    price: '',
    discount_price: '',
    stock: 0,
    weight: 300,
    is_active: true,
    is_featured: false,
});

async function fetchCategories() {
    try {
        const { data } = await api.get('/admin/categories', { params: { per_page: 100 } });
        categories.value = data.data.data || data.data || [];
    } catch (e) {
        console.error(e);
    }
}

async function fetchBook() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/admin/books/${route.params.id}`);
        const book = data.data;

        Object.keys(form.value).forEach(key => {
            if (book[key] !== undefined && book[key] !== null) {
                form.value[key] = book[key];
            }
        });

        form.value.is_active = !!book.is_active;
        form.value.is_featured = !!book.is_featured;
        existingImages.value = book.images || [];
    } catch (e) {
        toast.error('Gagal memuat buku');
        router.push('/admin/books');
    } finally {
        loading.value = false;
    }
}

function handleImagesChange(e) {
    const files = Array.from(e.target.files);
    newImageFiles.value = files;

    newImagePreviews.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (ev) => newImagePreviews.value.push(ev.target.result);
        reader.readAsDataURL(file);
    });
}

function removeNewImage(index) {
    newImageFiles.value.splice(index, 1);
    newImagePreviews.value.splice(index, 1);
}

async function deleteExistingImage(imageId) {
    if (!confirm('Hapus gambar ini?')) return;
    try {
        await api.delete(`/admin/books/${route.params.id}/images/${imageId}`);
        existingImages.value = existingImages.value.filter(i => i.id !== imageId);
        toast.success('Gambar dihapus');
    } catch (e) {
        toast.error('Gagal hapus gambar');
    }
}

async function setPrimaryImage(imageId) {
    try {
        await api.patch(`/admin/books/${route.params.id}/images/${imageId}/primary`);
        existingImages.value = existingImages.value.map(img => ({
            ...img,
            is_primary: img.id === imageId,
        }));
        toast.success('Gambar utama diubah');
    } catch (e) {
        toast.error('Gagal ubah gambar utama');
    }
}

async function submitForm() {
    saving.value = true;
    try {
        const formData = new FormData();

        Object.entries(form.value).forEach(([key, value]) => {
            if (value === null || value === undefined) return;
            if (typeof value === 'boolean') {
                formData.append(key, value ? '1' : '0');
            } else if (value !== '') {
                formData.append(key, value);
            }
        });

        newImageFiles.value.forEach((file) => {
            formData.append('images[]', file);
        });

        if (isEdit.value) {
            formData.append('_method', 'PUT');
        }

        const url = isEdit.value
            ? `/admin/books/${route.params.id}`
            : '/admin/books';

        const { data } = await api.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        toast.success(data.message || 'Buku berhasil disimpan!');
        router.push('/admin/books');
    } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
            const firstError = Object.values(errors)[0];
            toast.error(Array.isArray(firstError) ? firstError[0] : firstError);
        } else {
            toast.error(e.response?.data?.message || 'Gagal menyimpan buku');
        }
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    await Promise.all([fetchCategories(), fetchBook()]);
});
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <RouterLink to="/admin/books" class="text-sm text-gray-500 hover:text-primary-600">
                    ← Kembali ke Daftar Buku
                </RouterLink>
                <h1 class="text-2xl font-bold mt-2">
                    {{ isEdit ? '✏️ Edit Buku' : '➕ Tambah Buku Baru' }}
                </h1>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />

        <form v-else @submit.prevent="submitForm" class="grid lg:grid-cols-3 gap-6">
            <!-- Left: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Buku -->
                <div class="card p-6">
                    <h2 class="font-bold mb-4">📖 Informasi Buku</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Judul Buku *</label>
                            <input v-model="form.title" type="text" required class="input" placeholder="Contoh: Bumi Manusia" />
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Penulis *</label>
                                <input v-model="form.author" type="text" required class="input" placeholder="Pramoedya Ananta Toer" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Penerbit *</label>
                                <input v-model="form.publisher" type="text" required class="input" placeholder="Gramedia" />
                            </div>
                        </div>

                        <div class="grid md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">ISBN</label>
                                <input v-model="form.isbn" type="text" class="input" placeholder="978-xxx" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Tahun Terbit</label>
                                <input v-model.number="form.year" type="number" min="1900" :max="new Date().getFullYear() + 1" class="input" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Halaman</label>
                                <input v-model.number="form.pages" type="number" min="1" class="input" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Bahasa</label>
                                <select v-model="form.language" class="input">
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Inggris">Inggris</option>
                                    <option value="Arab">Arab</option>
                                    <option value="Jepang">Jepang</option>
                                    <option value="Korea">Korea</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Deskripsi</label>
                            <textarea v-model="form.description" rows="5" class="input" placeholder="Sinopsis atau deskripsi buku..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Harga & Stok -->
                <div class="card p-6">
                    <h2 class="font-bold mb-4">💰 Harga & Stok</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Harga Normal *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input v-model.number="form.price" type="number" min="0" required class="input pl-10" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Harga Diskon (opsional)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input v-model.number="form.discount_price" type="number" min="0" class="input pl-10" />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Harus lebih kecil dari harga normal</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Stok *</label>
                            <input v-model.number="form.stock" type="number" min="0" required class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Berat (gram) *</label>
                            <input v-model.number="form.weight" type="number" min="0" required class="input" />
                        </div>
                    </div>
                </div>

                <!-- Gambar -->
                <div class="card p-6">
                    <h2 class="font-bold mb-4">🖼️ Gambar Buku</h2>

                    <div v-if="existingImages.length" class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Gambar tersimpan:</p>
                        <div class="grid grid-cols-4 gap-3">
                            <div
                                v-for="img in existingImages"
                                :key="img.id"
                                class="relative group border-2 rounded-lg overflow-hidden"
                                :class="img.is_primary ? 'border-primary-500' : 'border-gray-200'"
                            >
                                <img :src="img.image_url" class="w-full aspect-[3/4] object-cover" />

                                <span
                                    v-if="img.is_primary"
                                    class="absolute top-1 left-1 bg-primary-600 text-white text-xs px-1.5 py-0.5 rounded"
                                >
                                    Utama
                                </span>

                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-1">
                                    <button
                                        type="button"
                                        v-if="!img.is_primary"
                                        @click="setPrimaryImage(img.id)"
                                        class="bg-white text-primary-600 text-xs px-2 py-1 rounded hover:bg-primary-50"
                                        title="Jadikan utama"
                                    >
                                        ★
                                    </button>
                                    <button
                                        type="button"
                                        @click="deleteExistingImage(img.id)"
                                        class="bg-white text-red-600 text-xs px-2 py-1 rounded hover:bg-red-50"
                                        title="Hapus"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Upload Gambar Baru</label>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleImagesChange"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Format: JPG, PNG, WebP. Max 2MB per gambar. Gambar pertama jadi cover utama.
                        </p>
                    </div>

                    <div v-if="newImagePreviews.length" class="mt-4 grid grid-cols-4 gap-3">
                        <div
                            v-for="(preview, index) in newImagePreviews"
                            :key="index"
                            class="relative group border-2 border-dashed border-primary-300 rounded-lg overflow-hidden"
                        >
                            <img :src="preview" class="w-full aspect-[3/4] object-cover" />
                            <button
                                type="button"
                                @click="removeNewImage(index)"
                                class="absolute top-1 right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center hover:bg-red-600"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <div class="card p-6">
                    <h2 class="font-bold mb-4">📂 Kategori *</h2>
                    <select v-model="form.category_id" required class="input">
                        <option value="">-- Pilih Kategori --</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>
                </div>

                <div class="card p-6">
                    <h2 class="font-bold mb-4">⚙️ Status</h2>

                    <label class="flex items-center gap-3 cursor-pointer mb-3">
                        <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded text-primary-600" />
                        <div>
                            <div class="font-medium text-sm">Aktif</div>
                            <div class="text-xs text-gray-500">Buku tampil di katalog</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" v-model="form.is_featured" class="w-5 h-5 rounded text-primary-600" />
                        <div>
                            <div class="font-medium text-sm">Unggulan</div>
                            <div class="text-xs text-gray-500">Tampil di beranda</div>
                        </div>
                    </label>
                </div>

                <div class="card p-6 sticky top-20">
                    <button
                        type="submit"
                        :disabled="saving"
                        class="btn-primary w-full py-3 mb-2"
                    >
                        {{ saving ? 'Menyimpan...' : (isEdit ? '💾 Simpan Perubahan' : '➕ Tambah Buku') }}
                    </button>

                    <RouterLink to="/admin/books" class="btn-outline w-full py-3 block text-center">
                        Batal
                    </RouterLink>
                </div>
            </div>
        </form>
    </div>
</template>