<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { formatCurrency } from '@/currency'

const props = defineProps({ products: Object, filters: Object })
const searchTerm = ref(props.filters?.search || '')
let searchTimeout = null

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('products.index'), { search: searchTerm.value }, { preserveState: true, replace: true })
  }, 300)
}
</script>

<template>
  <Head title="Products" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-text-primary">Products</h2>
    </template>

    <div class="mx-auto max-w-7xl space-y-6 p-6">
      <input
        type="text"
        v-model="searchTerm"
        @input="onSearch"
        placeholder="Search products"
        class="w-full max-w-sm rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"
      />

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div
          v-for="product in products.data"
          :key="product.id"
          class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm"
        >
          <img
            v-if="product.image_url"
            :src="product.image_url"
            :alt="product.name"
            class="h-40 w-full object-cover"
          />
          <div v-else class="flex h-40 w-full items-center justify-center bg-background-alt text-text-muted">
            No image
          </div>

          <div class="p-4">
            <h3 class="font-semibold text-text-primary">{{ product.name }}</h3>
            <p v-if="product.description" class="mt-1 line-clamp-2 text-sm text-text-body">{{ product.description }}</p>
            <div class="mt-3 flex items-center justify-between">
              <span class="font-semibold text-primary">{{ formatCurrency(product.price) }}</span>
              <span
                :class="[
                  'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                  product.in_stock ? 'bg-stat-teal-tint text-stat-teal-deep' : 'bg-stat-orange-tint text-stat-orange-deep',
                ]"
              >
                {{ product.in_stock ? 'In stock' : 'Out of stock' }}
              </span>
            </div>
          </div>
        </div>

        <p v-if="!products.data.length" class="col-span-full py-10 text-center text-text-muted">
          No products found.
        </p>
      </div>

      <Pagination :links="products.links" />
    </div>
  </AuthenticatedLayout>
</template>
