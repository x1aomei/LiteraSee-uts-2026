<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import ToastContainer from '@/components/common/ToastContainer.vue';

const auth = useAuthStore();
onMounted(async () => { if (auth.isAuthenticated) await auth.fetchMe(); });
</script>

<template>
    <RouterView />
    <ToastContainer />
</template>