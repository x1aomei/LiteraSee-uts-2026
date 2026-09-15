<script setup>
defineProps({ currentPage: Number, lastPage: Number });
const emit = defineEmits(['change']);
</script>

<template>
    <div v-if="lastPage > 1" class="flex justify-center gap-1 mt-6">
        <button :disabled="currentPage === 1" @click="emit('change', currentPage - 1)"
            class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 hover:bg-gray-50">←</button>
        <button v-for="page in lastPage" :key="page" @click="emit('change', page)"
            :class="['px-4 py-2 rounded-lg border', page === currentPage ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 hover:bg-gray-50']">
            {{ page }}
        </button>
        <button :disabled="currentPage === lastPage" @click="emit('change', currentPage + 1)"
            class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 hover:bg-gray-50">→</button>
    </div>
</template>