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
        <!-- ============================================
             PAGE HEADER
             ============================================ -->
        <div class="mb-6">
            <RouterLink
                to="/admin/books"
                class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/60 hover:text-[#8C5830] transition-colors mb-3"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="square" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Daftar Buku
            </RouterLink>

            <div class="flex items-center gap-3">
                <div class="w-11 h-11 flex-shrink-0 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center shadow-[3px_3px_0_0_#0a0a0a]">
                    <svg v-if="isEdit" class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        {{ isEdit ? 'Edit Buku' : 'Tambah Buku Baru' }}
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-1">
                        {{ isEdit ? 'Perbarui informasi buku' : 'Isi data buku yang akan ditambahkan' }}
                    </p>
                </div>
            </div>
        </div>

        <LoadingSpinner v-if="loading" />

        <form v-else @submit.prevent="submitForm" class="grid lg:grid-cols-3 gap-6">

            <!-- ============================================
                 LEFT: MAIN INFO
                 ============================================ -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Informasi Buku -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.25em]">Informasi Buku</span>
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                Judul Buku <span class="text-red-600">*</span>
                            </label>
                            <input v-model="form.title" type="text" required class="input" placeholder="Contoh: Bumi Manusia" />
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                    Penulis <span class="text-red-600">*</span>
                                </label>
                                <input v-model="form.author" type="text" required class="input" placeholder="Pramoedya Ananta Toer" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                    Penerbit <span class="text-red-600">*</span>
                                </label>
                                <input v-model="form.publisher" type="text" required class="input" placeholder="Gramedia" />
                            </div>
                        </div>

                        <div class="grid md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">ISBN</label>
                                <input v-model="form.isbn" type="text" class="input" placeholder="978-xxx" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">Tahun</label>
                                <input v-model.number="form.year" type="number" min="1900" :max="new Date().getFullYear() + 1" class="input" placeholder="2024" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">Halaman</label>
                                <input v-model.number="form.pages" type="number" min="1" class="input" placeholder="300" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">Bahasa</label>
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
                            <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">Deskripsi</label>
                            <textarea v-model="form.description" rows="5" class="input" placeholder="Sinopsis atau deskripsi buku..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Harga & Stok -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.25em]">Harga & Stok</span>
                    </div>

                    <div class="p-5 grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                Harga Normal <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#0a0a0a]/60 text-xs font-black">Rp</span>
                                <input v-model.number="form.price" type="number" min="0" required class="input pl-10" placeholder="0" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">Harga Diskon</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#0a0a0a]/60 text-xs font-black">Rp</span>
                                <input v-model.number="form.discount_price" type="number" min="0" class="input pl-10" placeholder="0" />
                            </div>
                            <p class="text-[10px] text-[#0a0a0a]/50 font-bold uppercase tracking-wider mt-1.5">
                                Harus lebih kecil dari harga normal
                            </p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                Stok <span class="text-red-600">*</span>
                            </label>
                            <input v-model.number="form.stock" type="number" min="0" required class="input" placeholder="0" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                Berat (gram) <span class="text-red-600">*</span>
                            </label>
                            <input v-model.number="form.weight" type="number" min="0" required class="input" placeholder="300" />
                        </div>
                    </div>
                </div>

                <!-- Gambar Buku -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.25em]">Gambar Buku</span>
                    </div>

                    <div class="p-5">
                        <div v-if="existingImages.length" class="mb-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#0a0a0a]/60 mb-3">
                                Gambar Tersimpan
                            </p>
                            <div class="grid grid-cols-4 gap-3">
                                <div
                                    v-for="img in existingImages"
                                    :key="img.id"
                                    class="relative group border-2 overflow-hidden"
                                    :class="img.is_primary ? 'border-[#8C5830] shadow-[3px_3px_0_0_#8C5830]' : 'border-[#0a0a0a]'"
                                >
                                    <img :src="img.image_url" class="w-full aspect-[3/4] object-cover" />

                                    <span
                                        v-if="img.is_primary"
                                        class="absolute top-1 left-1 bg-[#8C5830] text-white text-[9px] font-black uppercase tracking-widest px-1.5 py-0.5 border border-white"
                                    >
                                        Utama
                                    </span>

                                    <div class="absolute inset-0 bg-[#0a0a0a]/70 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                        <button
                                            v-if="!img.is_primary"
                                            type="button"
                                            @click="setPrimaryImage(img.id)"
                                            class="w-8 h-8 bg-white border-2 border-[#0a0a0a] flex items-center justify-center hover:bg-[#8C5830] hover:text-white transition-all"
                                            title="Jadikan utama"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteExistingImage(img.id)"
                                            class="w-8 h-8 bg-white border-2 border-[#0a0a0a] flex items-center justify-center hover:bg-red-600 hover:text-white transition-all"
                                            title="Hapus"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-[#0a0a0a] mb-2">
                                Upload Gambar Baru
                            </label>
                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleImagesChange"
                                class="block w-full text-xs text-[#0a0a0a]/70 border-2 border-dashed border-[#0a0a0a]/30 p-3 cursor-pointer hover:border-[#0a0a0a] hover:bg-[#F2ECE4]/30 transition-all file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-[#0a0a0a] file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-[#8C5830] file:text-white file:cursor-pointer hover:file:bg-[#0a0a0a]"
                            />
                            <p class="text-[10px] text-[#0a0a0a]/50 font-bold uppercase tracking-wider mt-2">
                                Format: JPG, PNG, WebP · Max 2MB per gambar · Gambar pertama jadi cover utama
                            </p>
                        </div>

                        <div v-if="newImagePreviews.length" class="mt-4 grid grid-cols-4 gap-3">
                            <div
                                v-for="(preview, index) in newImagePreviews"
                                :key="index"
                                class="relative group border-2 border-dashed border-[#8C5830] overflow-hidden"
                            >
                                <img :src="preview" class="w-full aspect-[3/4] object-cover" />
                                <button
                                    type="button"
                                    @click="removeNewImage(index)"
                                    class="absolute top-1 right-1 w-6 h-6 bg-red-600 text-white border-2 border-white flex items-center justify-center hover:bg-red-700 transition-all"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 RIGHT: SETTINGS
                 ============================================ -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Kategori -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.25em]">
                            Kategori <span class="text-red-400">*</span>
                        </span>
                    </div>

                    <div class="p-5">
                        <select v-model="form.category_id" required class="input">
                            <option value="">-- Pilih Kategori --</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] overflow-hidden">
                    <div class="bg-[#0a0a0a] text-white px-5 py-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-[0.25em]">Status</span>
                    </div>

                    <div class="p-5 space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="w-5 h-5 mt-0.5 border-2 border-[#0a0a0a] text-[#8C5830] focus:ring-0 cursor-pointer accent-[#8C5830]"
                            />
                            <div>
                                <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a]">Aktif</div>
                                <div class="text-[10px] text-[#0a0a0a]/50 font-bold uppercase tracking-wider mt-0.5">
                                    Buku tampil di katalog
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.is_featured"
                                class="w-5 h-5 mt-0.5 border-2 border-[#0a0a0a] text-[#8C5830] focus:ring-0 cursor-pointer accent-[#8C5830]"
                            />
                            <div>
                                <div class="text-xs font-black uppercase tracking-wider text-[#0a0a0a]">Unggulan</div>
                                <div class="text-[10px] text-[#0a0a0a]/50 font-bold uppercase tracking-wider mt-0.5">
                                    Tampil di beranda
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#0a0a0a] p-5 sticky top-20 space-y-3">
                    <button
                        type="submit"
                        :disabled="saving"
                        class="w-full flex items-center justify-center gap-2 bg-[#8C5830] text-white py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <template v-if="!saving">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            {{ isEdit ? 'Simpan Perubahan' : 'Tambah Buku' }}
                        </template>
                        <template v-else>
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Menyimpan...
                        </template>
                    </button>

                    <RouterLink
                        to="/admin/books"
                        class="w-full flex items-center justify-center gap-2 bg-white text-[#0a0a0a] py-3 text-xs font-black uppercase tracking-widest border-2 border-[#0a0a0a] shadow-[3px_3px_0_0_#0a0a0a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0_0_#0a0a0a] active:translate-x-[3px] active:translate-y-[3px] active:shadow-none transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </RouterLink>
                </div>
            </div>
        </form>
    </div>
</template>