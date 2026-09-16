<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import HeaderSearch from '@/Components/HeaderSearch.vue';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarOpen = ref(false);
const page = usePage();

const userRole = computed(() => page.props.auth.user?.role);
const isAdmin = computed(() => userRole.value === 'admin');
const isStaff = computed(() => userRole.value === 'staff');
const isAccountant = computed(() => userRole.value === 'accountant');
const isCustomer = computed(() => userRole.value === 'customer');
const canViewProducts = computed(() => isAdmin.value || isStaff.value || isAccountant.value || isCustomer.value);
const canViewCustomers = computed(() => isAdmin.value || isStaff.value || isAccountant.value);
const canViewInvoices = computed(() => isAdmin.value || isStaff.value || isAccountant.value || isCustomer.value);
const canViewReports = computed(() => isAdmin.value || isAccountant.value);

const userInitials = computed(() => {
    const name = page.props.auth.user?.name || '';
    return name.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase();
});
</script>

<template>
    <div>
        <FlashMessages />

        <div class="min-h-screen bg-background-DEFAULT md:flex">
            <!-- Mobile top bar -->
            <div class="flex items-center justify-between border-b border-border bg-surface-white px-4 py-3 shadow-sm md:hidden">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-auto fill-current text-primary" />
                    <span class="font-semibold text-text-primary">GreenLeaf Traders</span>
                </Link>
                <div class="flex items-center gap-1">
                    <DarkModeToggle />
                    <button
                        @click="sidebarOpen = true"
                        class="rounded-md p-2 text-text-muted transition duration-150 ease-in-out hover:bg-background-alt hover:text-text-primary focus:outline-none"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile overlay -->
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-black/40 md:hidden"
                @click="sidebarOpen = false"
            ></div>

            <!-- Sidebar -->
            <aside
                :class="[
                    'fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-surface-white transition-transform duration-200 ease-in-out md:relative md:z-auto md:flex md:translate-x-0 md:flex-shrink-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                ]"
            >
                <div class="flex items-center justify-between px-6 py-6">
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <ApplicationLogo class="h-8 w-auto fill-current text-primary" />
                        <span class="font-semibold text-text-primary">GreenLeaf Traders</span>
                    </Link>
                    <button
                        @click="sidebarOpen = false"
                        class="rounded-md p-1 text-text-muted hover:bg-background-alt hover:text-text-primary md:hidden"
                    >
                        <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-6 overflow-y-auto px-4 pb-4">
                    <div>
                        <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wider text-text-muted">Main menu</p>
                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-14 0v8a2 2 0 002 2h3m8-10l2 2m-2-2v8a2 2 0 01-2 2h-3m-4 0h4m-4 0v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
                                </svg>
                                Dashboard
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <div v-if="canViewProducts || canViewCustomers || canViewInvoices || isAdmin">
                        <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wider text-text-muted">Management</p>
                        <div class="space-y-1">
                            <ResponsiveNavLink v-if="canViewProducts" :href="route('products.index')" :active="route().current('products.*')">
                                <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Products
                            </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="canViewCustomers" :href="route('customers.index')" :active="route().current('customers.*')">
                                <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-1.13-7.84" />
                                </svg>
                                Customers
                            </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="canViewInvoices" :href="route('invoices.index')" :active="route().current('invoices.*')">
                                <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Invoices
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('payments.index')" :active="route().current('payments.*')">
                                <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Payments
                            </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="isAdmin" :href="route('users.index')" :active="route().current('users.*')">
                                <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Users
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <div v-if="canViewReports">
                        <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wider text-text-muted">Insights</p>
                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('reports.index')" :active="route().current('reports.*')">
                                <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2" />
                                </svg>
                                Reports
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </nav>

                <div class="p-4">
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')" :active="route().current('profile.edit')">
                            <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </ResponsiveNavLink>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-3 rounded-full px-4 py-2.5 text-start text-sm font-medium text-text-body transition duration-150 ease-in-out hover:bg-background-alt hover:text-text-primary"
                        >
                            <svg class="h-5 w-5 flex-shrink-0" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Log Out
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Main content -->
            <div class="flex min-h-screen min-w-0 flex-1 flex-col">
                <!-- Top bar -->
                <div class="hidden items-center gap-4 border-b border-border bg-surface-white px-6 py-4 md:flex">
                    <HeaderSearch />

                    <DarkModeToggle />

                    <button class="rounded-full p-2 text-text-muted transition hover:bg-background-alt hover:text-text-primary" title="Notifications">
                        <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-3 border-l border-border pl-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-tint text-sm font-semibold text-primary-deep">
                            {{ userInitials }}
                        </span>
                        <div class="leading-tight">
                            <p class="text-sm font-semibold text-text-primary">{{ $page.props.auth.user.name }}</p>
                            <p class="text-xs text-text-muted">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>
                </div>

                <header
                    class="border-b border-border bg-surface-white shadow-sm"
                    v-if="$slots.header"
                >
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main class="flex-1 bg-background-DEFAULT">
                    <slot />
                </main>

                <SiteFooter />
            </div>
        </div>
    </div>
</template>
