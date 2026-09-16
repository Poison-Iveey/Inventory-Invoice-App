<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center bg-background-DEFAULT pt-6 sm:justify-center sm:pt-0"
    >
        <DarkModeToggle class="absolute right-4 top-4" />

        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-primary" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-surface-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>

        <SiteFooter class="mt-6 w-full sm:max-w-md" />
    </div>
</template>
