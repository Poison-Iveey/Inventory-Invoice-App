<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <nav v-if="links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-2">
        <Link
            v-for="link in links"
            :key="link.label"
            :href="link.url || '#'"
            preserve-scroll
            :class="[
                'rounded-md border px-3 py-1.5 text-sm transition duration-150 ease-in-out',
                link.active
                    ? 'border-primary bg-primary text-white'
                    : 'border-border bg-surface-white text-text-body hover:bg-primary-tint',
                !link.url && 'pointer-events-none opacity-50',
            ]"
            v-html="link.label"
        />
    </nav>
</template>
