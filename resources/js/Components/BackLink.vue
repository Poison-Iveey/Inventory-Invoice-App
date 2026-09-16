<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
});

// Prefer returning to the exact page the user came from (filters, search,
// pagination and scroll position intact) instead of always landing on a
// fresh, unfiltered index. Falls back to `href` when there's nowhere to go
// back to, e.g. the page was opened directly from a bookmark or new tab.
function goBack(event) {
    if (window.history.length > 1) {
        event.preventDefault();
        window.history.back();
    } else {
        event.preventDefault();
        router.visit(props.href);
    }
}
</script>

<template>
    <Link
        :href="href"
        @click="goBack"
        class="inline-flex items-center gap-1.5 rounded-md border border-border bg-surface-white px-4 py-2 text-sm font-medium text-text-primary shadow-sm transition duration-150 ease-in-out hover:bg-primary-tint focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <slot>Back</slot>
    </Link>
</template>
