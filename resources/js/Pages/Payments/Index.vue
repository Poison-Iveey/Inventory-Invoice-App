<script setup>
import { ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { formatCurrency } from '@/currency'

const props = defineProps({ payments: Object, filters: Object })
const page = usePage()
const role = page.props.auth.user?.role
const canReviewPayments = role === 'accountant'
const status = ref(props.filters?.status || '')

const methodLabels = { mpesa: 'M-Pesa', card: 'Visa / Mastercard', paypal: 'PayPal', stripe: 'Stripe' }
const statusClasses = {
  pending: 'bg-stat-blue-tint text-stat-blue-deep',
  approved: 'bg-stat-teal-tint text-stat-teal-deep',
  rejected: 'bg-stat-orange-tint text-stat-orange-deep',
}

function applyFilter() {
  router.get(route('payments.index'), { status: status.value }, { preserveState: true, replace: true })
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
</script>

<template>
  <Head title="Payments" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-text-primary">Payments</h2>
    </template>

    <div class="mx-auto max-w-7xl space-y-6 p-6">
      <select v-model="status" @change="applyFilter" class="rounded-md border-border shadow-sm focus:border-accent focus:ring-accent">
        <option value="">All statuses</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>

      <div class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="border-b border-border bg-background-alt">
              <tr>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Invoice</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Customer</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Amount</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Method</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Reference</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Status</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Date</th>
                <th v-if="canReviewPayments" class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <template v-for="payment in payments.data" :key="payment.id">
                <tr class="transition hover:bg-background-alt">
                  <td class="px-6 py-4 text-sm">
                    <Link :href="route('invoices.show', payment.invoice.id)" class="font-medium text-primary hover:text-primary-deep">
                      {{ payment.invoice.invoice_number }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 text-sm text-text-body">{{ payment.invoice.customer?.name }}</td>
                  <td class="px-6 py-4 text-sm font-semibold text-text-primary">{{ formatCurrency(payment.amount) }}</td>
                  <td class="px-6 py-4 text-sm text-text-body">{{ methodLabels[payment.method] }}</td>
                  <td class="px-6 py-4 text-sm text-text-body">{{ payment.reference }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', statusClasses[payment.status]]">
                      {{ payment.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-text-body">{{ new Date(payment.created_at).toLocaleDateString() }}</td>
                  <td v-if="canReviewPayments" class="px-6 py-4 text-sm">
                    <div v-if="payment.status === 'pending'" class="flex gap-2">
                      <button @click="approvePayment(payment)" class="rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-deep">
                        Approve
                      </button>
                      <button @click="startReject(payment)" class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                        Reject
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="rejectingId === payment.id">
                  <td :colspan="canReviewPayments ? 8 : 7" class="bg-background-alt px-6 py-3">
                    <div class="flex flex-wrap items-center gap-2">
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
                  </td>
                </tr>
              </template>
              <tr v-if="!payments.data.length">
                <td :colspan="canReviewPayments ? 8 : 7" class="px-6 py-10 text-center text-text-muted">No payments found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Pagination :links="payments.meta.links" />
    </div>
  </AuthenticatedLayout>
</template>
