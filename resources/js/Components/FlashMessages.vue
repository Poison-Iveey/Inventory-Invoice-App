<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const page = usePage();
const message = ref(null);
const type = ref('success');
let timer = null;

watch(
    () => page.props.flash,
    (flash) => {
        const text = flash?.success || flash?.error;
        if (!text) {
            return;
        }

        message.value = text;
        type.value = flash.success ? 'success' : 'error';

        clearTimeout(timer);
        timer = setTimeout(() => {
            message.value = null;
        }, 5000);
    },
    { immediate: true },
);

function dismiss() {
    clearTimeout(timer);
    message.value = null;
}
</script>

<template>
    <div
        v-if="message"
        class="fixed right-4 top-4 z-[100] max-w-sm rounded-lg border px-4 py-3 shadow-lg"
        :class="type === 'success'
            ? 'border-primary-tint bg-primary-tint text-primary-deep'
            : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-300'"
    >
        <div class="flex items-start gap-3">
            <p class="flex-1 text-sm font-medium">{{ message }}</p>
            <button @click="dismiss" class="text-current opacity-60 transition hover:opacity-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>
