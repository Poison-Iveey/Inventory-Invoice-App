<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ invoices: Object })
</script>

<template>
  <Head title="Invoices" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-primary">Invoices</h2>
        <Link href="/invoices/create" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-deep text-surface-white font-semibold py-2 px-4 rounded-lg transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Invoice
        </Link>
      </div>
    </template>

    <div class="p-6 max-w-7xl mx-auto">
      <!-- Invoice Cards / Table -->
      <div class="bg-surface-DEFAULT rounded-lg border border-border shadow-sm overflow-hidden">
        <div v-if="invoices.data.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-primary-tint border-b border-border">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-primary-deep">Invoice #</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-primary-deep">Customer</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-primary-deep">Issue Date</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-primary-deep">Total</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-primary-deep">Status</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-primary-deep">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-primary-tint transition">
                <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ invoice.invoice_number }}</td>
                <td class="px-6 py-4 text-sm text-text-body">{{ invoice.customer?.name }}</td>
                <td class="px-6 py-4 text-sm text-text-body">{{ new Date(invoice.issue_date).toLocaleDateString() }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-accent-deep">${{ Number(invoice.total).toFixed(2) }}</td>
                <td class="px-6 py-4 text-sm">
                  <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-primary-tint text-primary-deep">
                    {{ invoice.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm space-x-4">
                  <Link :href="`/invoices/${invoice.id}`" class="text-accent hover:text-accent-deep font-medium transition">View</Link>
                  <a :href="`/invoices/${invoice.id}/pdf`" target="_blank" class="text-accent hover:text-accent-deep font-medium transition">PDF</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="p-12 text-center">
          <p class="text-text-muted mb-4">No invoices yet. Get started by creating one!</p>
          <Link href="/invoices/create" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-deep text-surface-white font-semibold py-2 px-4 rounded-lg transition">
            Create Your First Invoice
          </Link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="invoices.links.length > 3" class="mt-6 flex gap-2 justify-center">
        <Link 
          v-for="link in invoices.links" 
          :key="link.label"
          :href="link.url || '#'"
          :class="{
            'px-4 py-2 rounded-lg font-medium transition': true,
            'bg-primary text-surface-white': link.active,
            'bg-surface-DEFAULT border border-border text-text-body hover:bg-primary-tint': !link.active && link.url,
            'bg-surface-DEFAULT border border-border text-text-muted cursor-default': !link.url,
          }"
          v-html="link.label"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
