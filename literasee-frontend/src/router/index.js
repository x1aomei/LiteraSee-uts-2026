import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    scrollBehavior: () => ({ top: 0 }),
    routes: [
        // PUBLIC (dengan layout)
        {
            path: '/',
            component: () => import('@/components/common/AppLayout.vue'),
            children: [
                { path: '', name: 'home', component: () => import('@/views/HomeView.vue') },
                { path: 'catalog', name: 'catalog', component: () => import('@/views/CatalogView.vue') },
                { path: 'books/:slug', name: 'book-detail', component: () => import('@/views/BookDetailView.vue') },
                { path: 'cart', name: 'cart', component: () => import('@/views/CartView.vue'), meta: { requiresAuth: true } },
                { path: 'wishlist', name: 'wishlist', component: () => import('@/views/WishlistView.vue'), meta: { requiresAuth: true } },
                { path: 'checkout', name: 'checkout', component: () => import('@/views/CheckoutView.vue'), meta: { requiresAuth: true } },
                { path: 'orders', name: 'orders', component: () => import('@/views/OrdersView.vue'), meta: { requiresAuth: true } },
                { path: 'orders/:id', name: 'order-detail', component: () => import('@/views/OrderDetailView.vue'), meta: { requiresAuth: true } },
                { path: 'profile', name: 'profile', component: () => import('@/views/ProfileView.vue'), meta: { requiresAuth: true } },
            ],
        },

        // AUTH
        { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue'), meta: { guestOnly: true } },
        { path: '/register', name: 'register', component: () => import('@/views/RegisterView.vue'), meta: { guestOnly: true } },

        // ADMIN
        {
            path: '/admin',
            component: () => import('@/components/common/AdminLayout.vue'),
            meta: { requiresAuth: true, requiresAdmin: true },
            children: [
                { path: '', redirect: '/admin/dashboard' },
                { path: 'dashboard', name: 'admin-dashboard', component: () => import('@/views/admin/AdminDashboard.vue') },
                { path: 'books', name: 'admin-books', component: () => import('@/views/admin/AdminBooks.vue') },
                { path: 'books/create', name: 'admin-books-create', component: () => import('@/views/admin/AdminBookForm.vue') },
                { path: 'books/:id/edit', name: 'admin-books-edit', component: () => import('@/views/admin/AdminBookForm.vue') },
                { path: 'categories', name: 'admin-categories', component: () => import('@/views/admin/AdminCategories.vue') },
                { path: 'orders', name: 'admin-orders', component: () => import('@/views/admin/AdminOrders.vue') },
                { path: 'users', name: 'admin-users', component: () => import('@/views/admin/AdminUsers.vue') },
                { path: 'users/:id', name: 'admin-user-detail', component: () => import('@/views/admin/AdminUserDetail.vue') },
            ],
        },

        { path: '/:pathMatch(.*)*', component: () => import('@/views/NotFoundView.vue') },
    ],
});

router.beforeEach((to) => {
    const auth = useAuthStore();
    if (to.meta.guestOnly && auth.isAuthenticated) {
        return auth.isAdmin ? '/admin/dashboard' : '/';
    }
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }
    if (to.meta.requiresAdmin && !auth.isAdmin) return '/';
});

export default router;