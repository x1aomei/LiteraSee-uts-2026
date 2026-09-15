<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const form = ref({ email: '', password: '' });

async function handleSubmit() {
    try {
        await auth.login(form.value);
        const redirect = route.query.redirect || '/';
        router.push(auth.isAdmin ? '/admin/dashboard' : redirect);
    } catch (e) {}
}
</script>

<template>
    <div class="min-h-screen flex">
        <div class="flex-1 flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <RouterLink to="/" class="inline-flex items-center gap-2 text-3xl font-bold text-primary-600">📚 LiteraSee</RouterLink>
                    <h1 class="mt-6 text-2xl font-bold">Selamat Datang Kembali</h1>
                    <p class="mt-2 text-gray-500">Silakan login untuk melanjutkan</p>
                </div>

                <form @submit.prevent="handleSubmit" class="card p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input v-model="form.email" type="email" required autofocus class="input" placeholder="nama@email.com" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Password</label>
                        <input v-model="form.password" type="password" required class="input" placeholder="••••••••" />
                    </div>
                    <button type="submit" :disabled="auth.loading" class="btn-primary w-full py-3">
                        {{ auth.loading ? 'Memproses...' : 'Login' }}
                    </button>
                    <p class="text-center text-sm text-gray-600">
                        Belum punya akun?
                        <RouterLink to="/register" class="text-primary-600 font-medium hover:underline">Daftar Sekarang</RouterLink>
                    </p>
                </form>
            </div>
        </div>
        <div class="hidden lg:flex flex-1 bg-gradient-to-br from-primary-600 to-primary-800 items-center justify-center p-12">
            <div class="text-white max-w-md">
                <div class="text-8xl mb-8">📖</div>
                <h2 class="text-3xl font-bold mb-4">Temukan Buku Favoritmu</h2>
                <p class="text-primary-100 text-lg">Ribuan buku dari berbagai genre dengan harga terbaik.</p>
            </div>
        </div>
    </div>
</template>
