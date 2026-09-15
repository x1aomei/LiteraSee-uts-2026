import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/api/axios';
import { useToastStore } from './toast';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));
    const token = ref(localStorage.getItem('token') || null);
    const loading = ref(false);
    const isAuthenticated = computed(() => !!token.value);
    const isAdmin = computed(() => user.value?.role === 'admin');

    async function login(credentials) {
        loading.value = true;
        try {
            const { data } = await api.post('/auth/login', credentials);
            token.value = data.data.token;
            user.value = data.data.user;
            localStorage.setItem('token', data.data.token);
            localStorage.setItem('user', JSON.stringify(data.data.user));
            useToastStore().success('Login berhasil!');
            return data.data;
        } catch (error) {
            useToastStore().error(error.response?.data?.message || 'Login gagal');
            throw error;
        } finally { loading.value = false; }
    }

    async function register(payload) {
        loading.value = true;
        try {
            const { data } = await api.post('/auth/register', payload);
            token.value = data.data.token;
            user.value = data.data.user;
            localStorage.setItem('token', data.data.token);
            localStorage.setItem('user', JSON.stringify(data.data.user));
            useToastStore().success('Registrasi berhasil!');
            return data.data;
        } catch (error) {
            useToastStore().error(error.response?.data?.message || 'Registrasi gagal');
            throw error;
        } finally { loading.value = false; }
    }

    async function logout() {
        try { await api.post('/auth/logout'); } catch (e) {}
        finally {
            token.value = null; user.value = null;
            localStorage.removeItem('token'); localStorage.removeItem('user');
            useToastStore().info('Logout berhasil');
        }
    }

    async function fetchMe() {
        try {
            const { data } = await api.get('/auth/me');
            user.value = data.data;
            localStorage.setItem('user', JSON.stringify(data.data));
            return data.data;
        } catch (e) { return null; }
    }

    function updateUser(newUser) {
        user.value = { ...user.value, ...newUser };
        localStorage.setItem('user', JSON.stringify(user.value));
    }

    return { user, token, loading, isAuthenticated, isAdmin, login, register, logout, fetchMe, updateUser };
});