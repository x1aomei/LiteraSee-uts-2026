<template>
    <footer class="bg-[#3B2E26] text-[#E4DBD0] pt-10 pb-8 px-6 overflow-hidden border-t border-[#4A3B32]">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-[#4A3B32]">
            <!-- Kolom 1: Brand & Newsletter -->
            <div class="space-y-3 md:col-span-1">
                <h3 class="text-white font-black tracking-widest text-sm uppercase">📚 LiteraSee</h3>
                <p class="text-xs text-[#B5A89B]">Toko buku online terpercaya dengan ribuan koleksi.</p>
                <div class="flex items-center">
                    <input 
                        type="email" 
                        placeholder="literasee2026@gmail.com" 
                        class="bg-[#2D221D] border border-[#5C4A3F] text-xs px-3 py-1.5 text-white focus:outline-none focus:border-white w-full placeholder-[#8C7A6B]"
                    >
                    <button class="bg-white text-[#3B2E26] px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-[#E4DBD0] transition">
                        JOIN
                    </button>
                </div>
                <div class="flex gap-2 pt-1">
                    <span class="w-6 h-6 bg-[#2D221D] border border-[#5C4A3F] flex items-center justify-center text-[10px] text-white">f</span>
                    <span class="w-6 h-6 bg-[#2D221D] border border-[#5C4A3F] flex items-center justify-center text-[10px] text-white">ig</span>
                    <span class="w-6 h-6 bg-[#2D221D] border border-[#5C4A3F] flex items-center justify-center text-[10px] text-white">t</span>
                    <span class="w-6 h-6 bg-[#2D221D] border border-[#5C4A3F] flex items-center justify-center text-[10px] text-white">in</span>
                </div>
            </div>

            <!-- Kolom 2: Menu -->
            <div class="space-y-2">
                <h4 class="text-white font-bold tracking-wider text-xs uppercase">Menu</h4>
                <ul class="space-y-1.5 text-xs text-[#B5A89B]">
                    <li><RouterLink to="/catalog" class="hover:text-white transition">Katalog</RouterLink></li>
                    <li><RouterLink to="/" class="hover:text-white transition">Beranda</RouterLink></li>
                </ul>
            </div>

            <!-- Kolom 3: Bantuan -->
            <div class="space-y-2">
                <h4 class="text-white font-bold tracking-wider text-xs uppercase">Bantuan</h4>
                <ul class="space-y-1.5 text-xs text-[#B5A89B]">
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    <li><a href="#" class="hover:text-white transition">Cara Belanja</a></li>
                </ul>
            </div>

            <!-- Kolom 4: Kontak -->
            <div class="space-y-2">
                <h4 class="text-white font-bold tracking-wider text-xs uppercase">Kontak</h4>
                <ul class="space-y-1.5 text-xs text-[#B5A89B]">
                    <li>📧 info@literasee.com</li>
                    <li>📞 (022) 123-4567</li>
                    <li>📍 Bandung, Indonesia</li>
                </ul>
            </div>
        </div>

        <!-- Teks Raksasa LITERASEE Dibuat Lebih Proporsional -->
        <div class="max-w-7xl mx-auto pt-8 pb-2 flex flex-col items-center justify-center text-center">
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-black tracking-tighter text-[#8C7A6B]/25 uppercase select-none w-full">
                LITERASEE
            </h1>
        </div>
        <div class="max-w-7xl mx-auto text-center text-[10px] text-[#8C7A6B] tracking-widest uppercase">
            Copyright By © LITERASEE - {{ new Date().getFullYear() }}. All Rights Reserved.
        </div>
    </footer>
</template>