<script setup>
import { storeToRefs } from 'pinia';
import { useToastStore } from '@/stores/toast';

const { toasts } = storeToRefs(useToastStore());
const colors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
    warning: 'bg-yellow-500',
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-20 right-4 z-[9999] space-y-2 pointer-events-none">
            <TransitionGroup name="toast">
                <div v-for="toast in toasts" :key="toast.id"
                    :class="[colors[toast.type], 'text-white px-4 py-3 rounded-lg shadow-lg max-w-sm pointer-events-auto']">
                    {{ toast.message }}
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateX(50px); }
.toast-leave-to { opacity: 0; transform: translateX(50px); }
</style>