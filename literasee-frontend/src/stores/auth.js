import { defineStore } from 'pinia';
import api from '@/api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        token: localStorage.getItem('token') || null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        isAdmin: (state) => state.user?.role === 'admin',
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            try {
                const { data } = await api.post('/auth/login', credentials);
                this.user = data.data.user;
                this.token = data.data.token;
                localStorage.setItem('user', JSON.stringify(this.user));
                localStorage.setItem('token', this.token);
                return data;
            } finally {
                this.loading = false;
            }
        },

        async register(payload) {
            this.loading = true;
            try {
                const { data } = await api.post('/auth/register', payload);
                this.user = data.data.user;
                this.token = data.data.token;
                localStorage.setItem('user', JSON.stringify(this.user));
                localStorage.setItem('token', this.token);
                return data;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await api.post('/auth/logout');
            } catch (e) {
                // ignore
            }
            this.user = null;
            this.token = null;
            localStorage.removeItem('user');
            localStorage.removeItem('token');
        },

        async fetchMe() {
            const { data } = await api.get('/auth/me');
            this.user = data.data;
            localStorage.setItem('user', JSON.stringify(this.user));
            return data;
        },
    },
});
