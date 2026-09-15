import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/api/axios';
import { useToastStore } from './toast';

export const useWishlistStore = defineStore('wishlist', () => {
    const bookIds = ref(new Set());
    const count = ref(0);

    async function fetchWishlist() {
        try {
            const { data } = await api.get('/wishlist');
            const books = data.data.books?.data || [];
            bookIds.value = new Set(books.map(b => b.id));
            count.value = data.data.count || 0;
        } catch (e) {}
    }

    async function toggle(bookId) {
        try {
            const { data } = await api.post(`/wishlist/toggle/${bookId}`);
            if (data.data.added) bookIds.value.add(bookId);
            else bookIds.value.delete(bookId);
            count.value = data.data.count;
            useToastStore().success(data.message);
            return data.data.added;
        } catch (error) {
            useToastStore().error('Gagal update wishlist');
            return null;
        }
    }

    function has(bookId) { return bookIds.value.has(bookId); }

    return { bookIds, count, fetchWishlist, toggle, has };
});