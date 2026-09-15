<script setup>
import AppNavbar from './AppNavbar.vue';
import AppFooter from './AppFooter.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <AppNavbar />
        <main class="flex-1"><RouterView /></main>
        <AppFooter />
    </div>
</template>