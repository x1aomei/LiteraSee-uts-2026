<script setup>
import { onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

onMounted(() => {
    const token = route.query.token;
    const userJson = route.query.user;

    if (!token) {
        router.push('/login?error=google_auth_failed');
        return;
    }

    try {
        const user = JSON.parse(decodeURIComponent(userJson));

        // Simpan token & user
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));

        // Update auth store (silent — tanpa API call)
        auth.token = token;
        auth.user = user;

        // Redirect ke homepage
        router.push('/');
    } catch (e) {
        console.error('Auth callback error:', e);
        router.push('/login?error=google_auth_failed');
    }
});
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-[#F2ECE4]">
        <div class="bg-white border-2 border-[#0a0a0a] shadow-[4px_4px_0_0_#8C5830] p-8 text-center max-w-sm">
            <div class="w-14 h-14 mx-auto mb-4 border-2 border-[#0a0a0a] bg-[#8C5830] flex items-center justify-center">
                <svg class="w-7 h-7 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
            <div class="text-sm font-black uppercase tracking-wider text-[#0a0a0a]">
                Memproses login...
            </div>
            <div class="text-[10px] font-bold uppercase tracking-widest text-[#0a0a0a]/50 mt-2">
                Mohon tunggu sebentar
            </div>
        </div>
    </div>
</template>