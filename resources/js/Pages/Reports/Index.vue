<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'
import { formatCurrency } from '@/currency'

const props = defineProps({ filters: Object, summary: Object, topProducts: Array })
const page = usePage()
const canExport = page.props.auth.user?.role === 'accountant'
const form = reactive({ from: props.filters.from, to: props.filters.to })

const exportUrl = computed(() => route('reports.export', { from: form.from, to: form.to }))

function applyFilters() {
  router.get(route('reports.index'), form, { preserveState: true, replace: true })
}
</script>

<template>
  <Head title="Reports" />
  <AuthenticatedLayout>
    <template #header><div><h2 class="text-3xl font-bold text-text-primary">Reports</h2><p class="mt-1 text-sm text-text-muted">Paid revenue and sales performance.</p></div></template>
    <div class="mx-auto max-w-7xl space-y-6 p-6">
      <form @submit.prevent="applyFilters" class="flex flex-wrap items-end gap-4 rounded-lg border border-border bg-surface-DEFAULT p-4"><label>From<input v-model="form.from" type="date" class="ml-2 rounded border-border" /></label><label>To<input v-model="form.to" type="date" class="ml-2 rounded border-border" /></label><button class="rounded-lg bg-primary px-4 py-2 font-semibold text-white">Apply</button><a v-if="canExport" :href="exportUrl" class="rounded-lg border border-border px-4 py-2 font-semibold text-text-primary hover:bg-background-alt">Export CSV</a></form>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><div class="rounded-lg border border-border bg-surface-DEFAULT p-5"><p class="text-sm text-text-muted">Invoices</p><p class="mt-2 text-3xl font-bold">{{ summary.invoice_count }}</p></div><div class="rounded-lg border border-border bg-surface-DEFAULT p-5"><p class="text-sm text-text-muted">Paid revenue</p><p class="mt-2 text-3xl font-bold text-accent-deep">{{ formatCurrency(summary.paid_revenue) }}</p></div><div class="rounded-lg border border-border bg-surface-DEFAULT p-5"><p class="text-sm text-text-muted">Outstanding</p><p class="mt-2 text-3xl font-bold">{{ formatCurrency(summary.outstanding_total) }}</p></div><div class="rounded-lg border border-border bg-surface-DEFAULT p-5"><p class="text-sm text-text-muted">Overdue</p><p class="mt-2 text-3xl font-bold text-red-700">{{ summary.overdue_count }}</p></div></div>
      <div class="grid gap-6 lg:grid-cols-2"><section class="rounded-lg border border-border bg-surface-DEFAULT p-5"><h3 class="font-semibold text-text-primary">Invoice statuses</h3><div v-for="(count, status) in summary.status_counts" :key="status" class="mt-3 flex justify-between border-b border-border pb-2 capitalize"><span>{{ status }}</span><strong>{{ count }}</strong></div></section><section class="rounded-lg border border-border bg-surface-DEFAULT p-5"><h3 class="font-semibold text-text-primary">Top paid products</h3><div v-for="product in topProducts" :key="product.name" class="mt-3 flex justify-between border-b border-border pb-2"><span>{{ product.name }} <small class="text-text-muted">({{ product.quantity_sold }} sold)</small></span><strong>{{ formatCurrency(product.revenue) }}</strong></div><p v-if="!topProducts.length" class="mt-4 text-text-muted">No paid sales in this period.</p></section></div>
    </div>
  </AuthenticatedLayout>
</template>
