<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import BookCard from '@/components/book/BookCard.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const categories = ref([]);
const featuredBooks = ref([]);
const latestBooks = ref([]);
const onSaleBooks = ref([]);
const loading = ref(true);

const categoryIcons = [
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.121 2.121 0 0114.1 24.15L8.27 18.32M11.42 15.17l-4.65-4.65a4.242 4.242 0 116-6L17.25 9.75M11.42 15.17l4.65 4.65"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.875 2.122L7 21h10l-1.125-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12H3V5.25"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h16.5V18H3.75V3zM6 16.5h12.75A2.25 2.25 0 0021 14.25V3"/></svg>',
    '<svg class="w-6 h-6 text-[#5C4A3F]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 10.100V18.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 18.75v3.35"/></svg>'
];

const dummyBooks = [
    { id: 1, title: 'Filosofi Teras', author: 'Henry Manampiring', price: 98000, cover_image: 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=600&auto=format&fit=crop' },
    { id: 2, title: 'Atomic Habits', author: 'James Clear', price: 125000, cover_image: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=600&auto=format&fit=crop' },
    { id: 3, title: 'Bumi Manusia', author: 'Pramoedya Ananta Toer', price: 85000, cover_image: 'https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=600&auto=format&fit=crop' },
    { id: 4, title: 'Pulang', author: 'Leila S. Chudori', price: 95000, cover_image: 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=600&auto=format&fit=crop' }
];

async function fetchHome() {
    loading.value = true;
    try {
        const { data } = await api.get('/home');
        
        categories.value = (data.data.categories?.length ? data.data.categories : [
            { id: 1, name: 'Fiksi', slug: 'fiksi', active_books_count: 12 },
            { id: 2, name: 'Novel', slug: 'novel', active_books_count: 8 },
            { id: 3, name: 'Self Improvement', slug: 'self-improvement', active_books_count: 15 },
            { id: 4, name: 'Teknologi', slug: 'teknologi', active_books_count: 6 },
            { id: 5, name: 'Bisnis', slug: 'bisnis', active_books_count: 10 },
            { id: 6, name: 'Sejarah', slug: 'sejarah', active_books_count: 5 },
        ]).map((cat, index) => ({
            ...cat,
            svgIcon: categoryIcons[index % categoryIcons.length]
        }));
        
        featuredBooks.value = data.data.featured_books?.length ? data.data.featured_books : dummyBooks;
        latestBooks.value = data.data.latest_books?.length ? data.data.latest_books : dummyBooks;
        onSaleBooks.value = data.data.on_sale_books || [];
    } catch (e) { 
        console.error(e); 
        categories.value = [
            { id: 1, name: 'Fiksi', slug: 'fiksi', active_books_count: 12, svgIcon: categoryIcons[0] },
            { id: 2, name: 'Novel', slug: 'novel', active_books_count: 8, svgIcon: categoryIcons[1] },
            { id: 3, name: 'Self Improvement', slug: 'self-improvement', active_books_count: 15, svgIcon: categoryIcons[2] },
            { id: 4, name: 'Teknologi', slug: 'teknologi', active_books_count: 6, svgIcon: categoryIcons[3] },
            { id: 5, name: 'Bisnis', slug: 'bisnis', active_books_count: 10, svgIcon: categoryIcons[4] },
            { id: 6, name: 'Sejarah', slug: 'sejarah', active_books_count: 5, svgIcon: categoryIcons[5] },
        ];
        featuredBooks.value = dummyBooks;
        latestBooks.value = dummyBooks;
    } finally { 
        loading.value = false; 
    }
}
onMounted(fetchHome);
</script>

<template>
    <!-- Section Hero dengan Efek Parallax/Zoom Halus -->
    <section class="relative bg-cover bg-center text-white overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=1920&auto=format&fit=crop');">
        <div class="absolute inset-0 bg-gradient-to-r from-[#3B2E26]/95 via-[#5C4A3F]/85 to-[#3B2E26]/70 backdrop-blur-[2px]"></div>

        <div class="relative max-w-7xl mx-auto px-4 py-28 md:py-40">
            <div class="max-w-2xl animate-fade-in">
                <span class="inline-block bg-[#7A6355]/60 text-[#F2ECE4] text-xs font-bold px-3 py-1 mb-4 rounded tracking-widest uppercase border border-[#9A7D6B]">
                    📚 Toko Buku Digital Terpercaya
                </span>
                <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight drop-shadow-md tracking-tight">
                    Temukan Buku Favoritmu di <span class="text-[#E4DBD0] underline decoration-[#9A7D6B] decoration-wavy underline-offset-8">LiteraSee</span>
                </h1>
                <p class="text-lg md:text-xl text-neutral-200 mb-8 drop-shadow font-light leading-relaxed">
                    Ribuan koleksi buku pilihan dari berbagai genre menantimu. Belanja mudah, cepat, dan harga bersahabat!
                </p>
                <div class="flex flex-wrap gap-4">
                    <RouterLink 
                        to="/catalog" 
                        class="bg-[#8C5830] text-white px-8 py-4 font-bold shadow-xl hover:bg-[#A36A3D] transition-all duration-300 transform hover:-translate-y-0.5 inline-flex items-center gap-2 border border-[#B87A4A] tracking-wider uppercase text-sm rounded-sm"
                    >
                        <span>Mulai Belanja</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </RouterLink>
                </div>
            </div>
        </div>
    </section>

    <LoadingSpinner v-if="loading" />

    <!-- KONTEN UTAMA -->
    <div v-else class="bg-[#F2ECE4] text-[#3B2E26] min-h-screen">
        
        <!-- Section 1: Kategori Populer -->
        <section class="max-w-7xl mx-auto px-4 py-24">
            <div class="text-center mb-14">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <span class="w-12 h-[2px] bg-[#5C4A3F]"></span>
                    <span class="text-xs font-black tracking-[0.25em] text-[#5C4A3F] uppercase">Jelajahi Genre</span>
                    <span class="w-12 h-[2px] bg-[#5C4A3F]"></span>
                </div>
                <h2 class="text-3xl md:text-4xl font-black tracking-wider uppercase text-[#3B2E26]">
                    Kategori Populer
                </h2>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-6">
                <RouterLink 
                    v-for="cat in categories" 
                    :key="cat.id" 
                    :to="{ path: '/catalog', query: { category: cat.slug } }"
                    class="bg-white rounded-xl border border-[#D4C5B9] group hover:border-[#5C4A3F] hover:shadow-lg hover:-translate-y-1.5 transition-all duration-300 flex flex-col overflow-hidden"
                >
                    <div class="bg-[#5C4A3F] text-white py-1 px-3 text-[10px] font-bold tracking-widest uppercase text-center group-hover:bg-[#3B2E26] transition-colors">
                        GENRE
                    </div>
                    
                    <div class="p-5 text-center flex-1 flex flex-col items-center justify-center bg-[#FAF7F2] group-hover:bg-white transition-colors">
                        <div class="w-14 h-14 rounded-2xl bg-[#F2ECE4] flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-[#E4DBD0] group-hover:shadow-inner transition-all duration-300" v-html="cat.svgIcon"></div>
                        <div class="text-xs md:text-sm font-bold text-[#3B2E26] mb-1 line-clamp-1">{{ cat.name }}</div>
                        <div class="text-[10px] text-[#8C7A6B] font-bold tracking-wider">{{ cat.active_books_count || 0 }} BUKU</div>
                    </div>
                </RouterLink>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4"><hr class="border-t border-[#D4C5B9]" /></div>

        <!-- Section 2: Buku Unggulan -->
        <section class="max-w-7xl mx-auto px-4 py-24">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 pb-6 border-b border-[#D4C5B9] gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-8 h-[2px] bg-[#8C5830]"></span>
                        <span class="text-xs font-black tracking-[0.2em] text-[#8C5830] uppercase">Pilihan Kurator</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black tracking-wide text-[#3B2E26]">
                        ⭐ Buku Unggulan
                    </h2>
                </div>
                <RouterLink 
                    to="/catalog" 
                    class="inline-flex items-center gap-2 bg-[#5C4A3F] text-white px-6 py-3 text-xs font-bold tracking-widest uppercase hover:bg-[#3B2E26] transition shadow-sm self-start md:self-auto rounded-sm"
                >
                    <span>LIHAT SEMUA</span>
                    <span>→</span>
                </RouterLink>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div 
                    v-for="book in featuredBooks" 
                    :key="book.id" 
                    class="bg-white rounded-lg p-4 border border-[#D4C5B9] hover:border-[#5C4A3F] hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
                >
                    <BookCard :book="book" />
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4"><hr class="border-t border-[#D4C5B9]" /></div>
        
        <!-- Section 3: Buku Terbaru -->
        <section class="max-w-7xl mx-auto px-4 py-24">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 pb-6 border-b border-[#D4C5B9] gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-8 h-[2px] bg-[#4A6B82]"></span>
                        <span class="text-xs font-black tracking-[0.2em] text-[#4A6B82] uppercase">Koleksi Segar</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black tracking-wide text-[#3B2E26]">
                        🆕 Buku Terbaru
                    </h2>
                </div>
                <RouterLink 
                    to="/catalog" 
                    class="inline-flex items-center gap-2 bg-[#5C4A3F] text-white px-6 py-3 text-xs font-bold tracking-widest uppercase hover:bg-[#3B2E26] transition shadow-sm self-start md:self-auto rounded-sm"
                >
                    <span>LIHAT SEMUA</span>
                    <span>→</span>
                </RouterLink>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div 
                    v-for="book in latestBooks" 
                    :key="book.id" 
                    class="bg-white rounded-lg p-4 border border-[#D4C5B9] hover:border-[#5C4A3F] hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
                >
                    <BookCard :book="book" />
                </div>
            </div>
        </section>

        <!-- Section Sedang Diskon -->
        <section v-if="onSaleBooks.length" class="py-20 border-t border-[#D4C5B9] bg-[#E4DBD0]">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between mb-10">
                    <h2 class="text-2xl md:text-3xl font-black tracking-wider uppercase flex items-center gap-2 text-[#3B2E26]">
                        🔥 Sedang Diskon
                    </h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="book in onSaleBooks" :key="book.id" class="bg-white rounded-lg p-4 border border-[#D4C5B9] shadow-sm">
                        <BookCard :book="book" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>