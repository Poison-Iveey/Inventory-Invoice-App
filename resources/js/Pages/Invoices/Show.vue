<script setup>
import { ref } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackLink from '@/Components/BackLink.vue'
import InputError from '@/Components/InputError.vue'
import { formatCurrency } from '@/currency'

const props = defineProps({ invoice: Object })
const invoice = props.invoice
const page = usePage()
const role = page.props.auth.user?.role
const canSend = invoice.status === 'draft' && role === 'staff'
const isCustomer = role === 'customer'
const isAccountant = role === 'accountant'
const canReviewPayments = role === 'accountant'
const canSeePaymentHistory = ['admin', 'staff', 'accountant'].includes(role)

const payments = invoice.payments || []
const latestPayment = payments[0] ?? null
const hasPendingPayment = latestPayment?.status === 'pending'
const awaitingPayment = ['sent', 'overdue'].includes(invoice.status) && !hasPendingPayment
const showPaymentForm = (isCustomer || isAccountant) && awaitingPayment

const paymentMethods = [
  { value: 'mpesa', label: 'M-Pesa' },
  { value: 'card', label: 'Visa / Mastercard' },
  { value: 'paypal', label: 'PayPal' },
  { value: 'stripe', label: 'Stripe' },
]

const paymentForm = useForm({
  method: 'mpesa',
  reference: '',
  notes: '',
})

function submitPayment() {
  paymentForm.post(route('payments.store', invoice.id), {
    preserveScroll: true,
    onSuccess: () => paymentForm.reset(),
  })
}

const rejectingId = ref(null)
const rejectReason = ref('')

function approvePayment(payment) {
  if (confirm('Approve this payment and mark the invoice as paid?')) {
    router.patch(route('payments.approve', payment.id), {}, { preserveScroll: true })
  }
}

function startReject(payment) {
  rejectingId.value = payment.id
  rejectReason.value = ''
}

function confirmReject(payment) {
  router.patch(route('payments.reject', payment.id), { rejection_reason: rejectReason.value }, {
    preserveScroll: true,
    onSuccess: () => { rejectingId.value = null },
  })
}

function sendInvoice() {
  router.post(route('invoices.send', invoice.id))
}

function formatStatus(status) {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

function formatMethod(method) {
  return paymentMethods.find((m) => m.value === method)?.label ?? method
}

const paymentStatusClasses = {
  pending: 'bg-stat-blue-tint text-stat-blue-deep',
  approved: 'bg-stat-teal-tint text-stat-teal-deep',
  rejected: 'bg-stat-orange-tint text-stat-orange-deep',
}
</script>

<template>
  <Head :title="`Invoice ${invoice.invoice_number}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-text-primary">Invoice {{ invoice.invoice_number }}</h2>
        <div class="flex gap-3">
          <a :href="`/invoices/${invoice.id}/pdf`" target="_blank" class="inline-flex items-center gap-2 bg-accent hover:bg-accent-mid text-primary-deep font-semibold py-2 px-4 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8l-6-4m6 4l6-4" />
            </svg>
            Download PDF
          </a>
          <button v-if="canSend" @click="sendInvoice" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 font-semibold text-surface-white transition hover:bg-primary-deep">
            Send Invoice
          </button>
          <BackLink :href="route('invoices.index')">Back</BackLink>
        </div>
      </div>
    </template>

    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <!-- Status Badge -->
      <div>
        <span :class="{
          'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold': true,
          'bg-stat-violet-tint text-stat-violet-deep': invoice.status === 'draft',
          'bg-stat-blue-tint text-stat-blue-deep': invoice.status === 'sent',
          'bg-stat-teal-tint text-stat-teal-deep': invoice.status === 'paid',
          'bg-stat-orange-tint text-stat-orange-deep': invoice.status === 'overdue',
        }">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ formatStatus(invoice.status) }}
        </span>
      </div>

      <!-- Invoice Grid -->
      <div class="grid md:grid-cols-3 gap-6">
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
              <span class="text-sm font-medium text-text-primary">{{ formatCurrency(invoice.subtotal) }}</span>
            </div>
            <div class="flex justify-between pb-2 border-b border-border">
              <span class="text-sm text-text-body">Tax</span>
              <span class="text-sm font-medium text-text-primary">{{ formatCurrency(invoice.tax) }}</span>
            </div>
            <div class="flex justify-between pt-2">
              <span class="text-base font-semibold text-primary-deep">Total</span>
              <span class="text-lg font-bold text-accent-deep">{{ formatCurrency(invoice.total) }}</span>
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
                <td class="px-6 py-4 text-sm text-text-body text-right">{{ formatCurrency(item.unit_price) }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-accent-deep text-right">{{ formatCurrency(item.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Make/Record a Payment (customer or accountant) -->
      <div v-if="isCustomer && hasPendingPayment" class="rounded-lg border border-stat-blue-tint bg-stat-blue-tint p-6">
        <h3 class="font-semibold text-stat-blue-deep">Payment submitted</h3>
        <p class="mt-1 text-sm text-stat-blue-deep">
          Your {{ formatMethod(latestPayment.method) }} payment (ref: {{ latestPayment.reference }}) is awaiting approval from our accountant.
        </p>
      </div>

      <div v-if="showPaymentForm" class="rounded-lg border border-border bg-surface-DEFAULT p-6 shadow-sm">
        <h3 class="font-semibold text-text-primary">{{ isAccountant ? 'Record a Payment' : 'Make a Payment' }}</h3>
        <p v-if="isAccountant" class="mt-1 text-sm text-text-body">
          Use this when the customer has paid outside the system (cash, bank transfer, etc). This immediately marks
          the invoice as paid.
        </p>

        <div v-if="latestPayment?.status === 'rejected'" class="mt-3 rounded-lg border border-stat-orange-tint bg-stat-orange-tint p-4 text-sm text-stat-orange-deep">
          {{ isCustomer ? 'Your previous payment was not approved' : 'The previous payment for this invoice was rejected' }}: {{ latestPayment.rejection_reason }}.
        </div>

        <form @submit.prevent="submitPayment" class="mt-4 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-text-primary mb-1">Payment method</label>
            <select v-model="paymentForm.method" class="w-full rounded-lg border-border bg-surface-white text-text-primary focus:border-accent focus:ring-accent">
              <option v-for="m in paymentMethods" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
            <InputError :message="paymentForm.errors.method" class="mt-1" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-text-primary mb-1">
              {{ isAccountant ? 'Transaction reference / receipt number' : 'Transaction reference / confirmation code' }}
            </label>
            <input
              v-model="paymentForm.reference"
              type="text"
              placeholder="e.g. M-Pesa code, bank reference, receipt number"
              class="w-full rounded-lg border-border bg-surface-white text-text-primary focus:border-accent focus:ring-accent"
            />
            <InputError :message="paymentForm.errors.reference" class="mt-1" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-text-primary mb-1">Notes (optional)</label>
            <textarea
              v-model="paymentForm.notes"
              rows="2"
              class="w-full rounded-lg border-border bg-surface-white text-text-primary focus:border-accent focus:ring-accent"
            ></textarea>
          </div>
          <button
            type="submit"
            :disabled="paymentForm.processing"
            class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 font-semibold text-white transition hover:bg-primary-deep disabled:opacity-50"
          >
            {{ isAccountant ? 'Record Payment of' : 'Submit Payment of' }} {{ formatCurrency(invoice.total) }}
          </button>
        </form>
      </div>

      <!-- Payment history (staff / accountant / admin) -->
      <div v-if="canSeePaymentHistory" class="bg-surface-DEFAULT rounded-lg border border-border shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border bg-primary-tint">
          <h3 class="text-sm font-semibold text-primary-deep">Payments</h3>
        </div>
        <div v-if="!payments.length" class="p-6 text-sm text-text-muted">No payments submitted for this invoice yet.</div>
        <div v-else class="divide-y divide-border">
          <div v-for="payment in payments" :key="payment.id" class="p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div>
                <p class="text-sm font-medium text-text-primary">{{ formatMethod(payment.method) }} · ref {{ payment.reference }}</p>
                <p class="text-xs text-text-muted">{{ new Date(payment.created_at).toLocaleString() }} · {{ formatCurrency(payment.amount) }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span :class="['rounded-full px-3 py-1 text-xs font-semibold capitalize', paymentStatusClasses[payment.status]]">
                  {{ payment.status }}
                </span>
                <template v-if="canReviewPayments && payment.status === 'pending'">
                  <button @click="approvePayment(payment)" class="rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-deep">
                    Approve
                  </button>
                  <button @click="startReject(payment)" class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                    Reject
                  </button>
                </template>
              </div>
            </div>
            <p v-if="payment.status === 'rejected' && payment.rejection_reason" class="mt-2 text-sm text-stat-orange-deep">
              Rejected: {{ payment.rejection_reason }}
            </p>
            <div v-if="rejectingId === payment.id" class="mt-3 flex flex-wrap items-center gap-2">
              <input
                v-model="rejectReason"
                type="text"
                placeholder="Reason for rejecting this payment"
                class="flex-1 min-w-[200px] rounded-md border-border text-sm focus:border-accent focus:ring-accent"
              />
              <button @click="confirmReject(payment)" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500">
                Confirm reject
              </button>
              <button @click="rejectingId = null" class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-body">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
