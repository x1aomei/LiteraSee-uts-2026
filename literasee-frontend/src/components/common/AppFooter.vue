<template>
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="text-2xl font-bold text-white mb-4">📚 LiteraSee</div>
                    <p class="text-sm text-gray-400">Toko buku online terpercaya dengan ribuan koleksi.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Menu</h4>
                    <ul class="space-y-2 text-sm">
                        <li><RouterLink to="/catalog" class="hover:text-white">Katalog</RouterLink></li>
                        <li><RouterLink to="/" class="hover:text-white">Beranda</RouterLink></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Bantuan</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white">FAQ</a></li>
                        <li><a href="#" class="hover:text-white">Cara Belanja</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-sm">
                        <li>📧 info@literasee.com</li>
                        <li>📞 (022) 123-4567</li>
                        <li>📍 Bandung, Indonesia</li>
                    </ul>
                </div>
            </div>
            <hr class="my-8 border-gray-800" />
            <div class="text-center text-sm text-gray-500">© {{ new Date().getFullYear() }} LiteraSee. All rights reserved.</div>
        </div>
    </footer>
</template>