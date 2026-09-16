<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackLink from '@/Components/BackLink.vue'

const props = defineProps({ customer: Object })
const page = usePage()
const canManageCustomers = page.props.auth.user?.role === 'staff'

function destroyCustomer() {
  if (confirm(`Delete ${props.customer.name}? This also deletes their login and all of their invoices. This cannot be undone.`)) {
    router.delete(route('customers.destroy', props.customer.id))
  }
}
</script>

<template>
  <Head :title="customer.name" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-3xl font-bold text-text-primary">{{ customer.name }}</h2>
        <div class="flex gap-3">
          <Link
            v-if="canManageCustomers"
            :href="route('customers.edit', customer.id)"
            class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-primary-deep"
          >
            Edit Customer
          </Link>
          <button
            v-if="canManageCustomers"
            @click="destroyCustomer"
            class="inline-flex items-center rounded-md border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition duration-150 ease-in-out hover:bg-red-50"
          >
            Delete
          </button>
          <BackLink :href="route('customers.index')">Back to list</BackLink>
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-3xl space-y-6 p-6">
      <div class="rounded-lg border border-border bg-surface-DEFAULT p-6 shadow-sm">
        <dl class="grid gap-4 sm:grid-cols-2">
          <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-text-muted">Email</dt>
            <dd class="mt-1 text-text-primary">{{ customer.email }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-text-muted">Phone</dt>
            <dd class="mt-1 text-text-primary">{{ customer.phone || '—' }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-text-muted">Address</dt>
            <dd class="mt-1 whitespace-pre-line text-text-primary">{{ customer.address || '—' }}</dd>
          </div>
        </dl>
      </div>

      <div class="rounded-lg border border-border bg-surface-DEFAULT p-6 shadow-sm">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-text-muted">Portal login</h3>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
          <span
            :class="[
              'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold',
              customer.has_login ? 'bg-primary-tint text-primary-deep' : 'bg-border text-text-body',
            ]"
          >
            {{ customer.has_login ? 'Login active' : 'No login yet' }}
          </span>

          <p class="text-sm text-text-muted">
            Logins are created by an admin under Users management, not here.
          </p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
