<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({ customers: Object, filters: Object })
const page = usePage()
const searchTerm = ref(props.filters?.search || '')
const canManageCustomers = page.props.auth.user?.role === 'staff'
let timeout = null

function onSearch() {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    router.get(route('customers.index'), { search: searchTerm.value }, { preserveState: true, replace: true })
  }, 300)
}

function destroyCustomer(customer) {
  if (confirm(`Delete ${customer.name}? This also deletes their login and all of their invoices. This cannot be undone.`)) {
    router.delete(route('customers.destroy', customer.id), { preserveScroll: true })
  }
}
</script>

<template>
  <Head title="Customers" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-text-primary">Customers</h2>
    </template>

    <div class="mx-auto max-w-7xl space-y-6 p-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <input
          type="text"
          v-model="searchTerm"
          @input="onSearch"
          placeholder="Search customers"
          class="rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"
        />
        <Link
          v-if="canManageCustomers"
          :href="route('customers.create')"
          class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-primary-deep"
        >
          Create Customer
        </Link>
      </div>

      <div class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="border-b border-border bg-background-alt">
              <tr>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Name</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Email</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Phone</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Login</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="customer in customers.data" :key="customer.id" class="transition hover:bg-background-alt">
                <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ customer.name }}</td>
                <td class="px-6 py-4 text-sm text-text-body">{{ customer.email }}</td>
                <td class="px-6 py-4 text-sm text-text-body">{{ customer.phone || '—' }}</td>
                <td class="px-6 py-4 text-sm">
                  <span
                    :class="[
                      'inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold',
                      customer.has_login ? 'bg-stat-teal-tint text-stat-teal-deep' : 'bg-border text-text-body',
                    ]"
                  >
                    {{ customer.has_login ? 'Active' : 'None' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex flex-wrap items-center gap-x-1 gap-y-1">
                    <template v-if="canManageCustomers">
                      <Link :href="route('customers.edit', customer.id)" class="font-medium text-primary hover:text-primary-deep">
                        Edit
                      </Link>
                      <span class="text-border">|</span>
                    </template>
                    <Link :href="route('customers.show', customer.id)" class="font-medium text-primary hover:text-primary-deep">
                      View
                    </Link>
                    <template v-if="canManageCustomers">
                      <span class="text-border">|</span>
                      <button @click="destroyCustomer(customer)" class="font-medium text-red-600 hover:text-red-800">
                        Delete
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
              <tr v-if="!customers.data.length">
                <td colspan="5" class="px-6 py-10 text-center text-text-muted">No customers found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Pagination :links="customers.meta.links" />
    </div>
  </AuthenticatedLayout>
</template>
