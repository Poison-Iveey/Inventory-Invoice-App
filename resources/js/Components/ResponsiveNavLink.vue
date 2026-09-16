<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'flex w-full items-center gap-3 rounded-full bg-ink px-4 py-2.5 text-sm font-semibold text-white transition duration-150 ease-in-out'
        : 'flex w-full items-center gap-3 rounded-full px-4 py-2.5 text-sm font-medium text-text-body transition duration-150 ease-in-out hover:bg-background-alt hover:text-text-primary',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
