import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/api/axios';
import { useToastStore } from './toast';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);
    const itemCount = ref(0);
    const subtotal = ref(0);
    const loading = ref(false);
    const isEmpty = computed(() => itemCount.value === 0);

    function parseItems(raw) {
        console.log('🔍 parseItems input:', raw);
        if (!raw) return [];
        if (Array.isArray(raw)) return raw;
        if (Array.isArray(raw?.data)) return raw.data;
        return [];
    }

    async function fetchCart() {
        try {
            const response = await api.get('/cart');
            console.log('📦 fetchCart full response:', response.data);

            const cart = response.data?.data || {};
            console.log('📦 cart object:', cart);

            items.value = parseItems(cart.items);
            itemCount.value = cart.item_count ?? 0;
            subtotal.value = cart.subtotal ?? 0;

            console.log('✅ Cart parsed:', {
                itemsCount: items.value.length,
                items: items.value,
                itemCount: itemCount.value,
            });
        } catch (e) {
            console.error('❌ fetchCart error:', e);
            console.error('Status:', e.response?.status);
            console.error('Data:', e.response?.data);
            items.value = [];
        }
    }

    async function fetchSummary() {
        try {
            const { data } = await api.get('/cart/summary');
            itemCount.value = data.data?.item_count ?? 0;
            subtotal.value = data.data?.subtotal ?? 0;
            return data.data;
        } catch (e) {
            console.error('fetchSummary error:', e);
        }
    }

    async function addToCart(bookId, quantity = 1) {
        loading.value = true;
        try {
            const { data } = await api.post('/cart/add', { book_id: bookId, quantity });
            itemCount.value = data.data?.item_count ?? 0;
            subtotal.value = data.data?.subtotal ?? 0;
            useToastStore().success(data.message);
            await fetchCart();
            return true;
        } catch (error) {
            useToastStore().error(error.response?.data?.message || 'Gagal menambahkan');
            return false;
        } finally { loading.value = false; }
    }

    async function updateQuantity(itemId, quantity) {
        try {
            const { data } = await api.patch(`/cart/${itemId}`, { quantity });
            itemCount.value = data.data?.item_count ?? 0;
            subtotal.value = data.data?.subtotal ?? 0;
            await fetchCart();
            return true;
        } catch (error) {
            useToastStore().error(error.response?.data?.message || 'Gagal update');
            return false;
        }
    }

    async function removeItem(itemId) {
        try {
            const { data } = await api.delete(`/cart/${itemId}`);
            itemCount.value = data.data?.item_count ?? 0;
            subtotal.value = data.data?.subtotal ?? 0;
            useToastStore().success(data.message);
            await fetchCart();
            return true;
        } catch (error) {
            useToastStore().error('Gagal hapus');
            return false;
        }
    }

    return {
        items, itemCount, subtotal, loading, isEmpty,
        fetchCart, fetchSummary, addToCart, updateQuantity, removeItem,
    };
});
