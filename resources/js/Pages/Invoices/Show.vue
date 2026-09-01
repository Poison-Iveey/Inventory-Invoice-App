<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ invoice: Object })
const invoice = props.invoice
</script>

<template>
  <Head :title="`Invoice ${invoice.invoice_number}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-primary">Invoice {{ invoice.invoice_number }}</h2>
        <div class="flex gap-3">
          <a :href="`/invoices/${invoice.id}/pdf`" target="_blank" class="inline-flex items-center gap-2 bg-accent hover:bg-accent-mid text-primary-deep font-semibold py-2 px-4 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8l-6-4m6 4l6-4" />
            </svg>
            Download PDF
          </a>
          <Link href="/invoices" class="inline-flex items-center gap-2 bg-primary-light hover:bg-primary text-surface-white font-semibold py-2 px-4 rounded-lg transition">
            Back
          </Link>
        </div>
      </div>
    </template>

    <div class="p-6 max-w-7xl mx-auto">
      <!-- Status Badge -->
      <div class="mb-6">
        <span :class="{
          'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold': true,
          'bg-primary-tint text-primary-deep': invoice.status === 'sent',
          'bg-accent-tint text-accent-deep': invoice.status === 'paid',
          'bg-red-100 text-red-800': invoice.status === 'overdue',
        }">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ invoice.status | capitalize }}
        </span>
      </div>

      <!-- Invoice Grid -->
      <div class="grid md:grid-cols-3 gap-6 mb-8">
        <!-- Customer Info -->
        <div class="bg-surface-DEFAULT rounded-lg border border-border p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-text-muted uppercase tracking-wide mb-4">Customer</h3>
          <p class="text-lg font-semibold text-text-primary mb-1">{{ invoice.customer?.name }}</p>
          <p class="text-sm text-text-body mb-1">{{ invoice.customer?.email }}</p>
          <p class="text-sm text-text-body">{{ invoice.customer?.phone }}</p>
        </div>

        <!-- Invoice Dates -->
        <div class="bg-surface-DEFAULT rounded-lg border border-border p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-text-muted uppercase tracking-wide mb-4">Timeline</h3>
          <div class="space-y-2">
            <div>
              <p class="text-xs text-text-muted">Issue Date</p>
              <p class="text-sm font-medium text-text-primary">{{ new Date(invoice.issue_date).toLocaleDateString() }}</p>
            </div>
            <div>
              <p class="text-xs text-text-muted">Due Date</p>
              <p class="text-sm font-medium text-text-primary">{{ new Date(invoice.due_date).toLocaleDateString() }}</p>
            </div>
          </div>
        </div>

        <!-- Totals Summary -->
        <div class="bg-primary-tint rounded-lg border border-border p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-text-muted uppercase tracking-wide mb-4">Summary</h3>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-text-body">Subtotal</span>
              <span class="text-sm font-medium text-text-primary">${{ Number(invoice.subtotal).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between pb-2 border-b border-border">
              <span class="text-sm text-text-body">Tax</span>
              <span class="text-sm font-medium text-text-primary">${{ Number(invoice.tax).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between pt-2">
              <span class="text-base font-semibold text-primary-deep">Total</span>
              <span class="text-lg font-bold text-accent-deep">${{ Number(invoice.total).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Line Items Table -->
      <div class="bg-surface-DEFAULT rounded-lg border border-border shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border bg-primary-tint">
          <h3 class="text-sm font-semibold text-primary-deep">Line Items</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="border-b border-border">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase">Product</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-text-muted uppercase">Qty</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-text-muted uppercase">Unit Price</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-text-muted uppercase">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="item in invoice.items" :key="item.id" class="hover:bg-primary-tint transition">
                <td class="px-6 py-4 text-sm text-text-primary font-medium">{{ item.product?.name }}</td>
                <td class="px-6 py-4 text-sm text-text-body text-right">{{ item.quantity }}</td>
                <td class="px-6 py-4 text-sm text-text-body text-right">${{ Number(item.unit_price).toFixed(2) }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-accent-deep text-right">${{ Number(item.total).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
