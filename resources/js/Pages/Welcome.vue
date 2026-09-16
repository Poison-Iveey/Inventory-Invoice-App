<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SiteFooter from '@/Components/SiteFooter.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen bg-background flex flex-col">
        <header class="max-w-5xl mx-auto w-full flex items-center justify-between px-6 py-6">
            <span class="text-lg font-bold text-primary">GreenLeaf Traders</span>
            <nav class="flex items-center gap-4">
                <DarkModeToggle />
                <template v-if="canLogin">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="text-sm font-semibold text-primary hover:text-primary-deep"
                    >
                        Dashboard
                    </Link>
                    <Link v-else :href="route('login')" class="text-sm font-semibold text-primary hover:text-primary-deep">
                        Log in
                    </Link>
                </template>
            </nav>
        </header>

        <main class="flex-1 max-w-3xl mx-auto w-full px-6 py-16 text-center">
            <h1 class="text-4xl font-bold text-primary mb-4">Inventory &amp; Invoice Management</h1>
            <p class="text-text-body text-lg mb-10">
                Manage products, customers and invoices in one place. Stock updates automatically when an invoice
                is created, and each invoice can be downloaded as a PDF or emailed straight to the customer.
            </p>
            <Link
                v-if="canLogin"
                :href="route('login')"
                class="inline-block bg-accent hover:bg-accent-mid text-primary-deep font-semibold py-3 px-8 rounded-lg transition"
            >
                Log in to get started
            </Link>
        </main>

        <SiteFooter />
    </div>
</template>
