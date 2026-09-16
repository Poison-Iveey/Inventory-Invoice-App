<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { formatCurrency } from '@/currency'

const props = defineProps({ products: Object, filters: Object })
const page = usePage()
const searchTerm = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
const isStaff = page.props.auth.user?.role === 'staff'
const canCreateProducts = isStaff
const canEditProducts = isStaff
let searchTimeout = null

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('products.index'), { search: searchTerm.value, status: status.value }, { preserveState: true, replace: true })
  }, 300)
}

function filterProducts() {
  router.get(route('products.index'), { search: searchTerm.value, status: status.value }, { preserveState: true, replace: true })
}
</script>

<template>
  <Head title="Products" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-text-primary">Products</h2>
    </template>

    <div class="mx-auto max-w-7xl space-y-6 p-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2">
          <input
            type="text"
            v-model="searchTerm"
            @input="onSearch"
            placeholder="Search by name or SKU"
            class="rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"
          />
          <select
            v-model="status"
            @change="filterProducts"
            class="rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"
          >
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <Link
          v-if="canCreateProducts"
          :href="route('products.create')"
          class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-primary-deep"
        >
          Create Product
        </Link>
      </div>

      <div class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="border-b border-border bg-background-alt">
              <tr>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">SKU</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Name</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Price</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Stock</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Status</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="product in products.data" :key="product.id" class="transition hover:bg-background-alt">
                <td class="px-6 py-4 text-sm text-text-body">{{ product.sku }}</td>
                <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ product.name }}</td>
                <td class="px-6 py-4 text-sm text-text-body">{{ formatCurrency(product.price) }}</td>
                <td class="px-6 py-4 text-sm">
                  <span
                    :class="[
                      'inline-flex items-center gap-1.5 font-medium',
                      product.stock === 0 ? 'text-stat-orange-deep' : product.stock < 10 ? 'text-stat-blue-deep' : 'text-stat-teal-deep',
                    ]"
                  >
                    <span
                      :class="[
                        'h-1.5 w-1.5 rounded-full',
                        product.stock === 0 ? 'bg-stat-orange' : product.stock < 10 ? 'bg-stat-blue' : 'bg-stat-teal',
                      ]"
                    ></span>
                    {{ product.stock }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <span
                    :class="[
                      'inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold',
                      product.status === 'active' ? 'bg-stat-teal-tint text-stat-teal-deep' : 'bg-border text-text-body',
                    ]"
                  >
                    {{ product.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <Link
                    v-if="canEditProducts"
                    :href="route('products.edit', product.id)"
                    class="font-medium text-primary hover:text-primary-deep"
                  >
                    Edit
                  </Link>
                  <span v-if="canEditProducts" class="mx-1 text-border">|</span>
                  <Link :href="route('products.show', product.id)" class="font-medium text-primary hover:text-primary-deep">
                    View
                  </Link>
                </td>
              </tr>
              <tr v-if="!products.data.length">
                <td colspan="6" class="px-6 py-10 text-center text-text-muted">No products found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Pagination :links="products.meta.links" />
    </div>
  </AuthenticatedLayout>
</template>
