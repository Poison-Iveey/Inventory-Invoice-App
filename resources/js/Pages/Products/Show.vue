<script setup>
import { Head } from '@inertiajs/vue3'
const props = defineProps({ product: Object })
</script>

<template>
  <Head title="Product" />
  <div class="p-6">
    <h1 class="text-2xl mb-4">{{ product.name }}</h1>
    <div><strong>SKU:</strong> {{ product.sku }}</div>
    <div><strong>Price:</strong> {{ product.price }}</div>
    <div><strong>Stock:</strong> {{ product.stock }}</div>
    <div class="mt-4">
      <a href="/products" class="text-blue-600">Back to list</a>
    </div>
  </div>
</template>
