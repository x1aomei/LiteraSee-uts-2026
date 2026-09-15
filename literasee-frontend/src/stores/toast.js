import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([]);
    let id = 0;

    function show(message, type = 'info', duration = 3000) {
        const toastId = ++id;
        toasts.value.push({ id: toastId, message, type });
        setTimeout(() => remove(toastId), duration);
    }
    function remove(toastId) { toasts.value = toasts.value.filter(t => t.id !== toastId); }

    return {
        toasts,
        success: (m, d) => show(m, 'success', d),
        error: (m, d) => show(m, 'error', d),
        info: (m, d) => show(m, 'info', d),
        warning: (m, d) => show(m, 'warning', d),
        remove,
    };
});