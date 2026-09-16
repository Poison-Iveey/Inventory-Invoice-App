<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { ref } from 'vue'
import { formatCurrency } from '@/currency'

const props = defineProps({ invoices: Object, filters: Object })
const page = usePage()
const canCreateInvoices = page.props.auth.user?.role === 'staff'
const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
let searchTimeout

function applyFilters() {
  router.get(route('invoices.index'), { search: search.value, status: status.value }, { preserveState: true, replace: true })
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 300)
}

const statusBadgeClasses = {
  draft: 'bg-stat-violet-tint text-stat-violet-deep',
  sent: 'bg-stat-blue-tint text-stat-blue-deep',
  paid: 'bg-stat-teal-tint text-stat-teal-deep',
  overdue: 'bg-stat-orange-tint text-stat-orange-deep',
}
</script>

<template>
  <Head title="Invoices" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-text-primary">Invoices</h2>
        <Link v-if="canCreateInvoices" href="/invoices/create" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-deep text-surface-white font-semibold py-2 px-4 rounded-lg transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Invoice
        </Link>
      </div>
    </template>

    <div class="p-6 max-w-7xl mx-auto">
      <div class="mb-5 flex flex-wrap gap-3">
        <input v-model="search" @input="debouncedSearch" type="search" placeholder="Search invoice or customer" class="rounded-lg border-border" />
        <select v-model="status" @change="applyFilters" class="rounded-lg border-border"><option value="">All statuses</option><option value="draft">Draft</option><option value="sent">Sent</option><option value="paid">Paid</option><option value="overdue">Overdue</option></select>
      </div>
      <!-- Invoice Cards / Table -->
      <div class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm">
        <div v-if="invoices.data.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="border-b border-border bg-background-alt">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">Invoice #</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">Customer</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">Issue Date</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">Total</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="invoice in invoices.data" :key="invoice.id" class="transition hover:bg-background-alt">
                <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ invoice.invoice_number }}</td>
                <td class="px-6 py-4 text-sm text-text-body">{{ invoice.customer?.name }}</td>
                <td class="px-6 py-4 text-sm text-text-body">{{ new Date(invoice.issue_date).toLocaleDateString() }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-primary">{{ formatCurrency(invoice.total) }}</td>
                <td class="px-6 py-4 text-sm">
                  <span :class="['inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold capitalize', statusBadgeClasses[invoice.status]]">
                    {{ invoice.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm space-x-4">
                  <Link :href="`/invoices/${invoice.id}`" class="font-medium text-primary transition hover:text-primary-deep">View</Link>
                  <a :href="`/invoices/${invoice.id}/pdf`" target="_blank" class="font-medium text-primary transition hover:text-primary-deep">PDF</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="p-12 text-center">
          <p class="text-text-muted mb-4">No invoices yet. Get started by creating one!</p>
          <Link v-if="canCreateInvoices" href="/invoices/create" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-deep text-surface-white font-semibold py-2 px-4 rounded-lg transition">
            Create Your First Invoice
          </Link>
        </div>
      </div>

      <Pagination :links="invoices.meta.links" />
    </div>
  </AuthenticatedLayout>
</template>
