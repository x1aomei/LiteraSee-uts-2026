<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();
const form = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' });

async function handleSubmit() {
    try {
        await auth.register(form.value);
        router.push('/');
    } catch (e) {}
}
</script>

<template>
    <div class="min-h-screen flex">
        <div class="flex-1 flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <RouterLink to="/" class="inline-flex items-center gap-2 text-3xl font-bold text-primary-600">📚 LiteraSee</RouterLink>
                    <h1 class="mt-6 text-2xl font-bold">Buat Akun Baru</h1>
                    <p class="mt-2 text-gray-500">Daftar gratis dan mulai belanja</p>
                </div>

                <form @submit.prevent="handleSubmit" class="card p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                        <input v-model="form.name" type="text" required class="input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input v-model="form.email" type="email" required class="input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">No. HP (opsional)</label>
                        <input v-model="form.phone" type="tel" class="input" placeholder="08xxxxxxxxxx" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Password</label>
                        <input v-model="form.password" type="password" required minlength="8" class="input" />
                        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                        <input v-model="form.password_confirmation" type="password" required class="input" />
                    </div>
                    <button type="submit" :disabled="auth.loading" class="btn-primary w-full py-3">
                        {{ auth.loading ? 'Memproses...' : 'Daftar' }}
                    </button>
                    <p class="text-center text-sm text-gray-600">
                        Sudah punya akun?
                        <RouterLink to="/login" class="text-primary-600 font-medium hover:underline">Login</RouterLink>
                    </p>
                </form>
            </div>
        </div>
        <div class="hidden lg:flex flex-1 bg-gradient-to-br from-primary-600 to-primary-800 items-center justify-center p-12">
            <div class="text-white max-w-md">
                <div class="text-8xl mb-8">✨</div>
                <h2 class="text-3xl font-bold mb-4">Gabung Sekarang</h2>
                <p class="text-primary-100 text-lg">Dapatkan akses ke ribuan buku dan promo eksklusif member.</p>
            </div>
        </div>
    </div>
</template>