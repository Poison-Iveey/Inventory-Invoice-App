<template>
    <div class="min-h-screen bg-background-DEFAULT flex flex-col items-center justify-center px-4 text-center">
        <div class="max-w-md mx-auto">
            <div class="mb-8">
                <h1 class="text-6xl font-bold text-primary-deep mb-4">403</h1>
                <h2 class="text-3xl font-semibold text-primary mb-2">Access Denied</h2>
                <p class="text-text-body text-lg">You don't have permission to access this resource.</p>
            </div>

            <div class="bg-surface-DEFAULT rounded-lg border border-border px-6 py-8 shadow-sm mb-8">
                <p class="text-text-body mb-6">
                    Your current role may not have the required permissions. If you believe this is a mistake, please contact your administrator.
                </p>
                
                <div class="space-y-3">
                    <Link href="/" class="block w-full bg-primary hover:bg-primary-deep text-surface-white font-semibold py-2 px-4 rounded-lg transition">
                        Go to Dashboard
                    </Link>
                    <button @click="$inertia.back()" class="w-full bg-accent hover:bg-accent-mid text-primary-deep font-semibold py-2 px-4 rounded-lg transition">
                        Go Back
                    </button>
                </div>
            </div>

            <p class="text-text-muted text-sm">
                Error Code: {{ code }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    code: {
        type: Number,
        default: 403,
    },
});
</script>
