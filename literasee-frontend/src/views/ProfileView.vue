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
        toast.success('Profil diperbarui!');
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal update');
    } finally { saving.value = false; }
}

async function updatePassword() {
    savingPassword.value = true;
    try {
        await api.put('/profile/password', passwordForm.value);
        toast.success('Password diubah!');
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal update password');
    } finally { savingPassword.value = false; }
}

function handleAvatarChange(e) { avatarFile.value = e.target.files[0]; }
</script>

<template>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">👤 Profil Saya</h1>

        <div class="card p-6 mb-6">
            <div class="flex items-center gap-4">
                <img :src="auth.user?.avatar_url" class="w-20 h-20 rounded-full object-cover" />
                <div>
                    <div class="font-semibold">{{ auth.user?.name }}</div>
                    <div class="text-sm text-gray-500">{{ auth.user?.email }}</div>
                    <label class="mt-2 inline-block text-sm text-primary-600 cursor-pointer hover:underline">
                        Ganti Foto
                        <input type="file" accept="image/*" @change="handleAvatarChange" class="hidden" />
                    </label>
                </div>
            </div>
        </div>

        <form @submit.prevent="updateProfile" class="card p-6 mb-6 space-y-4">
            <h2 class="font-bold">Informasi Profil</h2>
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input v-model="form.name" type="text" required class="input" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input v-model="form.email" type="email" required class="input" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">No. HP</label>
                <input v-model="form.phone" type="tel" class="input" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Alamat</label>
                <textarea v-model="form.address" rows="3" class="input"></textarea>
            </div>
            <button type="submit" :disabled="saving" class="btn-primary">
                {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
        </form>

        <form @submit.prevent="updatePassword" class="card p-6 space-y-4">
            <h2 class="font-bold">Ubah Password</h2>
            <div>
                <label class="block text-sm font-medium mb-1">Password Saat Ini</label>
                <input v-model="passwordForm.current_password" type="password" required class="input" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password Baru</label>
                <input v-model="passwordForm.password" type="password" required minlength="8" class="input" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input v-model="passwordForm.password_confirmation" type="password" required class="input" />
            </div>
            <button type="submit" :disabled="savingPassword" class="btn-primary">
                {{ savingPassword ? 'Menyimpan...' : 'Ubah Password' }}
            </button>
        </form>
    </div>
</template>
