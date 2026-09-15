<script setup>
import { ref } from 'vue';
import api from '@/api/axios';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';

const auth = useAuthStore();
const toast = useToastStore();

const form = ref({
    name: auth.user?.name || '',
    email: auth.user?.email || '',
    phone: auth.user?.phone || '',
    address: auth.user?.address || '',
});

const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' });
const saving = ref(false);
const savingPassword = ref(false);
const avatarFile = ref(null);
const avatarPreview = ref(null);

async function updateProfile() {
    saving.value = true;
    try {
        const formData = new FormData();
        Object.entries(form.value).forEach(([k, v]) => formData.append(k, v || ''));
        if (avatarFile.value) formData.append('avatar', avatarFile.value);
        formData.append('_method', 'PUT');

        const { data } = await api.post('/profile', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        auth.updateUser(data.data);
        toast.success('Profil berhasil diperbarui!');
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal memperbarui profil');
    } finally { saving.value = false; }
}

async function updatePassword() {
    savingPassword.value = true;
    try {
        await api.put('/profile/password', passwordForm.value);
        toast.success('Password berhasil diubah!');
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal mengubah password');
    } finally { savingPassword.value = false; }
}

function handleAvatarChange(e) {
    const file = e.target.files[0];
    if (file) {
        avatarFile.value = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
}
</script>

<template>
    <div class="max-w-3xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-8 flex items-center gap-2">
            👤 Profil Saya
        </h1>

        <!-- Header Card Profil -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 flex flex-col sm:flex-row items-center gap-6">
            <div class="relative group">
                <img 
                    :src="avatarPreview || auth.user?.avatar_url || '/profile-default.jpg'" 
                    class="w-24 h-24 rounded-full object-cover ring-4 ring-primary-50 shadow-md"
                />
                <label class="absolute bottom-0 right-0 bg-primary-600 text-white p-2 rounded-full cursor-pointer hover:bg-primary-700 shadow transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <circle cx="12" cy="13" r="3"/>
                    </svg>
                    <input type="file" accept="image/*" @change="handleAvatarChange" class="hidden" />
                </label>
            </div>

            <div class="text-center sm:text-left space-y-1">
                <h2 class="text-xl font-bold text-gray-900">{{ auth.user?.name || 'User LiteraSee' }}</h2>
                <p class="text-sm text-gray-500 font-medium">{{ auth.user?.email }}</p>
                <span class="inline-block mt-2 px-3 py-1 bg-primary-50 text-primary-600 text-xs font-semibold rounded-full">
                    Member LiteraSee
                </span>
            </div>
        </div>

        <!-- Form Informasi Profil -->
        <form @submit.prevent="updateProfile" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 space-y-5">
            <div class="border-b pb-3">
                <h2 class="text-lg font-bold text-gray-800">Informasi Pribadi</h2>
                <p class="text-xs text-gray-500">Perbarui data diri dan kontak akunmu.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                    <input v-model="form.name" type="text" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP / WhatsApp</label>
                <input v-model="form.phone" type="tel" placeholder="081234567890" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Pengiriman</label>
                <textarea v-model="form.address" rows="3" placeholder="Masukkan alamat lengkap..." class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none"></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="saving" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow transition-all disabled:opacity-50">
                    {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
            </div>
        </form>

        <!-- Form Ubah Password -->
        <form @submit.prevent="updatePassword" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div class="border-b pb-3">
                <h2 class="text-lg font-bold text-gray-800">Keamanan Akun</h2>
                <p class="text-xs text-gray-500">Ubah kata sandi untuk mengamankan akunmu.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password Saat Ini</label>
                    <input v-model="passwordForm.current_password" type="password" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                        <input v-model="passwordForm.password" type="password" required minlength="8" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input v-model="passwordForm.password_confirmation" type="password" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="savingPassword" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow transition-all disabled:opacity-50">
                    {{ savingPassword ? 'Menyimpan...' : 'Ubah Password' }}
                </button>
            </div>
        </form>
    </div>
</template>