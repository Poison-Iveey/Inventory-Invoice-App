<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackLink from '@/Components/BackLink.vue'
import { formatCurrency } from '@/currency'

const props = defineProps({ product: Object })
const page = usePage()
const canEditProducts = page.props.auth.user?.role === 'staff'
</script>

<template>
  <Head :title="product.name" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-3xl font-bold text-text-primary">{{ product.name }}</h2>
        <div class="flex gap-3">
          <Link
            v-if="canEditProducts"
            :href="route('products.edit', product.id)"
            class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-primary-deep"
          >
            Edit Product
          </Link>
          <BackLink :href="route('products.index')">Back to list</BackLink>
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-3xl p-6">
      <div class="overflow-hidden rounded-lg border border-border bg-surface-DEFAULT shadow-sm">
        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-56 w-full object-cover" />

        <div class="p-6">
          <span
            :class="[
              'inline-block rounded-full px-3 py-1 text-xs font-semibold',
              product.status === 'active' ? 'bg-primary-tint text-primary-deep' : 'bg-border text-text-body',
            ]"
          >
            {{ product.status === 'active' ? 'Active' : 'Inactive' }}
          </span>

          <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-xs font-semibold uppercase tracking-wide text-text-muted">SKU</dt>
              <dd class="mt-1 text-text-primary">{{ product.sku }}</dd>
            </div>
            <div>
              <dt class="text-xs font-semibold uppercase tracking-wide text-text-muted">Price</dt>
              <dd class="mt-1 text-text-primary">{{ formatCurrency(product.price) }}</dd>
            </div>
            <div>
              <dt class="text-xs font-semibold uppercase tracking-wide text-text-muted">Stock</dt>
              <dd class="mt-1 text-text-primary">{{ product.stock }} units</dd>
            </div>
          </dl>

          <div v-if="product.description" class="mt-6 border-t border-border pt-4">
            <dt class="text-xs font-semibold uppercase tracking-wide text-text-muted">Description</dt>
            <dd class="mt-1 whitespace-pre-line text-text-body">{{ product.description }}</dd>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
