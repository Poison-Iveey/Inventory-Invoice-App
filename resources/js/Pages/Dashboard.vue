<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { formatCurrency } from '@/currency'
import { onMounted, ref } from 'vue'
import {
  Chart,
  DoughnutController,
  ArcElement,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
} from 'chart.js'

Chart.register(DoughnutController, ArcElement, BarController, BarElement, CategoryScale, LinearScale, Tooltip)

const props = defineProps({ metrics: Object, statusBreakdown: Object, weeklySales: Array, recentInvoices: Object, recentPayments: Object })
const page = usePage()

const paymentStatusClasses = {
  pending: 'bg-stat-blue-tint text-stat-blue-deep',
  approved: 'bg-stat-teal-tint text-stat-teal-deep',
  rejected: 'bg-stat-orange-tint text-stat-orange-deep',
}
const methodLabels = { mpesa: 'M-Pesa', card: 'Visa / Mastercard', paypal: 'PayPal', stripe: 'Stripe' }

// Tailwind's scanner needs full literal class names in the source, so dynamic
// per-card coloring is resolved through this static lookup rather than template
// interpolation (e.g. `bg-stat-${color}-tint`, which Tailwind can't see).
const colorClasses = {
  teal: { tint: 'bg-stat-teal-tint', badge: 'bg-stat-teal', text: 'text-stat-teal-deep' },
  blue: { tint: 'bg-stat-blue-tint', badge: 'bg-stat-blue', text: 'text-stat-blue-deep' },
  orange: { tint: 'bg-stat-orange-tint', badge: 'bg-stat-orange', text: 'text-stat-orange-deep' },
  violet: { tint: 'bg-stat-violet-tint', badge: 'bg-stat-violet', text: 'text-stat-violet-deep' },
}

const statCards = [
  { key: 'invoice_count', label: 'Total Invoices', color: 'teal', format: (v) => v,
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { key: 'paid_revenue', label: 'Paid Revenue', color: 'blue', format: formatCurrency,
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { key: 'outstanding_total', label: 'Outstanding', color: 'orange', format: formatCurrency,
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
  { key: 'overdue_count', label: 'Overdue Invoices', color: 'violet', format: (v) => v,
    icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
]

const statusLabels = { draft: 'Draft', sent: 'Sent', paid: 'Paid', overdue: 'Overdue' }
const statusColors = { draft: '#4A3AA7', sent: '#2A78D6', paid: '#1BAF7A', overdue: '#EB6834' }

const donutCanvas = ref(null)
const barCanvas = ref(null)

onMounted(() => {
  if (props.statusBreakdown && donutCanvas.value) {
    const entries = Object.entries(props.statusBreakdown)
    new Chart(donutCanvas.value, {
      type: 'doughnut',
      data: {
        labels: entries.map(([key]) => statusLabels[key]),
        datasets: [{
          data: entries.map(([, count]) => count),
          backgroundColor: entries.map(([key]) => statusColors[key]),
          borderColor: '#FFFFFF',
          borderWidth: 2,
        }],
      },
      options: {
        cutout: '70%',
        plugins: { legend: { display: false } },
      },
    })
  }

  if (props.weeklySales && barCanvas.value) {
    new Chart(barCanvas.value, {
      type: 'bar',
      data: {
        labels: props.weeklySales.map((day) => day.label),
        datasets: [{
          data: props.weeklySales.map((day) => day.total),
          backgroundColor: '#1BAF7A',
          borderRadius: 6,
          maxBarThickness: 36,
        }],
      },
      options: {
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => formatCurrency(context.parsed.y),
            },
          },
        },
        scales: {
          y: { beginAtZero: true, ticks: { callback: (value) => formatCurrency(value) } },
        },
      },
    })
  }
})
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-text-primary">Welcome, {{ page.props.auth.user.name }}!</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 p-6">
            <div v-if="metrics" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="card in statCards"
                    :key="card.key"
                    :class="['rounded-2xl p-5', colorClasses[card.color].tint]"
                >
                    <span :class="['flex h-10 w-10 items-center justify-center rounded-full text-white', colorClasses[card.color].badge]">
                        <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                        </svg>
                    </span>
                    <p :class="['mt-4 text-sm font-medium', colorClasses[card.color].text]">{{ card.label }}</p>
                    <p :class="['mt-1 text-2xl font-bold', colorClasses[card.color].text]">{{ card.format(metrics[card.key]) }}</p>
                </div>
            </div>

            <div v-if="metrics?.inventory_units !== null && metrics" class="rounded-2xl border border-border bg-surface-DEFAULT p-5">
                <span class="font-semibold text-text-primary">Inventory on hand:</span>
                <span class="text-text-body"> {{ metrics.inventory_units }} units</span>
            </div>

            <p v-if="!metrics" class="rounded-2xl border border-border bg-surface-DEFAULT p-5 text-text-body">
                Your dashboard shows the invoices available to your role.
            </p>

            <div v-if="statusBreakdown && weeklySales" class="grid gap-6 lg:grid-cols-5">
                <div class="rounded-2xl border border-border bg-surface-DEFAULT p-6 shadow-sm lg:col-span-2">
                    <h3 class="font-semibold text-text-primary">Invoice Status</h3>
                    <div class="relative mx-auto mt-4 h-48 w-48">
                        <canvas ref="donutCanvas"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold text-text-primary">{{ metrics.invoice_count }}</span>
                            <span class="text-xs text-text-muted">Total</span>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-2 text-sm">
                        <div v-for="(count, status) in statusBreakdown" :key="status" class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: statusColors[status] }"></span>
                            <span class="text-text-body">{{ statusLabels[status] }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-surface-DEFAULT p-6 shadow-sm lg:col-span-3">
                    <h3 class="font-semibold text-text-primary">Sales Overview (Last 7 Days)</h3>
                    <div class="mt-4 h-56">
                        <canvas ref="barCanvas"></canvas>
                    </div>
                </div>
            </div>

            <section class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm">
                <div class="flex items-center justify-between border-b border-border p-5">
                    <h3 class="font-semibold text-text-primary">Recent Invoices</h3>
                    <Link :href="route('invoices.index')" class="text-sm font-medium text-primary hover:text-primary-deep">View all</Link>
                </div>
                <div v-if="recentInvoices.data.length" class="divide-y divide-border">
                    <Link
                        v-for="invoice in recentInvoices.data"
                        :key="invoice.id"
                        :href="route('invoices.show', invoice.id)"
                        class="flex items-center justify-between p-4 hover:bg-background-alt"
                    >
                        <span>{{ invoice.invoice_number }} · {{ invoice.customer?.name }}</span>
                        <span class="font-medium">{{ formatCurrency(invoice.total) }} · {{ invoice.status }}</span>
                    </Link>
                </div>
                <p v-else class="p-6 text-text-muted">No invoices to display yet.</p>
            </section>

            <section v-if="recentPayments" class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm">
                <div class="flex items-center justify-between border-b border-border p-5">
                    <h3 class="font-semibold text-text-primary">Recent Payments</h3>
                    <Link :href="route('payments.index')" class="text-sm font-medium text-primary hover:text-primary-deep">View all</Link>
                </div>
                <div v-if="recentPayments.data.length" class="divide-y divide-border">
                    <div
                        v-for="payment in recentPayments.data"
                        :key="payment.id"
                        class="flex items-center justify-between p-4"
                    >
                        <span>{{ payment.invoice.invoice_number }} · {{ methodLabels[payment.method] }}</span>
                        <span class="flex items-center gap-2">
                            <span class="font-medium">{{ formatCurrency(payment.amount) }}</span>
                            <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', paymentStatusClasses[payment.status]]">
                                {{ payment.status }}
                            </span>
                        </span>
                    </div>
                </div>
                <p v-else class="p-6 text-text-muted">No payments submitted yet.</p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
