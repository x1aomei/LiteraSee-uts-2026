<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();
const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: ''
});

const wallpaperUrl = '/wallpaperlogin.jpeg';
const logoUrl = '/logo3.jpg';

async function handleSubmit() {
    try {
        await auth.register(form.value);
        router.push('/');
    } catch (e) {}
}
</script>

<template>
    <div class="min-h-screen flex bg-[#f4f1ea]">

        <!-- ============================================
             KIRI — FORM REGISTER (background putih)
             ============================================ -->
        <div class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">

                <!-- Logo & Brand -->
                <RouterLink to="/" class="inline-flex items-center gap-3 group">
                    <img
                        :src="logoUrl"
                        alt="LiteraSee"
                        class="h-12 w-auto object-contain border-2 border-[#0a0a0a] p-1 bg-white transition-transform group-hover:-translate-y-0.5"
                    />
                    <span class="text-2xl font-black uppercase tracking-tight">
                        <span class="text-[#0a0a0a]">Litera</span><span class="text-primary-600">Store</span>
                    </span>
                </RouterLink>

                <!-- Heading -->
                <div class="mt-10 mb-8">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-8 h-[2px] bg-[#0a0a0a]"></span>
                        <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#0a0a0a]/60">
                            Daftar Akun
                        </span>
                    </div>
                    <h1 class="text-3xl font-black uppercase tracking-tight text-[#0a0a0a] leading-none">
                        Buat Akun<br/>Baru
                    </h1>
                    <p class="mt-3 text-sm text-[#0a0a0a]/60 font-medium">
                        Daftar gratis dan mulai belanja buku favoritmu
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="card p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-[#0a0a0a] mb-2">
                            Nama Lengkap
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            class="input"
                            placeholder="Nama lengkap Anda"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-[#0a0a0a] mb-2">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="input"
                            placeholder="nama@email.com"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-[#0a0a0a] mb-2">
                            No. HP <span class="text-[#0a0a0a]/40 normal-case font-medium">(opsional)</span>
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="input"
                            placeholder="08xxxxxxxxxx"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-[#0a0a0a] mb-2">
                            Password
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            minlength="8"
                            class="input"
                            placeholder="••••••••"
                        />
                        <p class="text-[10px] text-[#0a0a0a]/50 mt-1.5 font-medium uppercase tracking-wider">
                            Minimal 8 karakter
                        </p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-[#0a0a0a] mb-2">
                            Konfirmasi Password
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="input"
                            placeholder="••••••••"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="auth.loading"
                        class="btn-primary w-full py-3.5"
                    >
                        <span v-if="!auth.loading" class="flex items-center justify-center gap-2">
                            Daftar
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </form>

                <!-- Login CTA -->
                <div class="mt-6 pt-6 border-t-2 border-[#0a0a0a]/10 text-center">
                    <p class="text-sm text-[#0a0a0a]/60 font-medium">
                        Sudah punya akun?
                        <RouterLink
                            to="/login"
                            class="text-primary-600 font-bold uppercase text-xs tracking-widest hover:text-primary-800 ml-1 underline underline-offset-4 decoration-2"
                        >
                            Login
                        </RouterLink>
                    </p>
                </div>

                <!-- Footer -->
                <p class="mt-8 text-center text-[10px] uppercase tracking-widest text-[#0a0a0a]/40 font-bold">
                    &copy; 2026 LiteraStore — Toko Buku Digital
                </p>
            </div>
        </div>

        <!-- ============================================
             KANAN — VISUAL
             ============================================ -->
        <div class="hidden lg:flex flex-1 relative items-center justify-center p-12 overflow-hidden bg-[#0a0a0a]">

            <video
                autoplay
                loop
                muted
                playsinline
                class="absolute inset-0 w-full h-full object-cover z-0 opacity-40 grayscale"
            >
                <source
                    src="/From Klickpin.com- Modern digital product ideas that help you create a beautiful result without overspending for ideas worth saving right now-pin-.mp4"
                    type="video/mp4"
                />
            </video>

            <div class="absolute inset-0 bg-gradient-to-br from-[#0a0a0a]/95 via-[#0a0a0a]/70 to-primary-900/80 z-0"></div>

            <div class="relative z-10 text-white max-w-md">

                <img
                    :src="logoUrl"
                    alt="LiteraSee"
                    class="h-12 w-auto object-contain border-2 border-white p-1 bg-white mb-8"
                />

                <div class="flex items-center gap-2 mb-4">
                    <span class="w-8 h-[2px] bg-white"></span>
                    <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/70">
                        Gabung Sekarang
                    </span>
                </div>

                <h2 class="text-4xl font-black uppercase tracking-tight leading-none mb-6">
                    Mulai<br/>
                    Baca<br/>
                    <span class="text-primary-300">Hari Ini</span>
                </h2>

                <p class="text-white/70 text-base leading-relaxed font-medium mb-8">
                    Dapatkan akses ke ribuan buku dan promo eksklusif untuk member LiteraStore.
                </p>

                <div class="space-y-3 pt-6 border-t-2 border-white/20">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 flex-shrink-0 border-2 border-white flex items-center justify-center">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="square" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold uppercase tracking-wider text-white">Gratis Selamanya</div>
                            <div class="text-xs text-white/50 font-medium">Tanpa biaya pendaftaran</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 flex-shrink-0 border-2 border-white flex items-center justify-center">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="square" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold uppercase tracking-wider text-white">Promo Eksklusif</div>
                            <div class="text-xs text-white/50 font-medium">Diskon khusus member</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 flex-shrink-0 border-2 border-white flex items-center justify-center">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="square" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold uppercase tracking-wider text-white">Wishlist & Riwayat</div>
                            <div class="text-xs text-white/50 font-medium">Simpan buku favoritmu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>