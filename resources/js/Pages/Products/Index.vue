<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({ products: Object, filters: Object });
const searchTerm = ref(props.filters?.search || '')
let searchTimeout = null

function onSearch(){
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(()=>{
    Inertia.get('/products', { search: searchTerm.value }, { preserveState: true, replace: true })
  }, 300)
}
</script>

<template>
  <Head title="Products" />
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <div>
        <h1 class="text-2xl font-semibold">Products</h1>
        <div class="mt-2">
          <input type="text" v-model="searchTerm" @input="onSearch" placeholder="Search products" class="border p-2" />
        </div>
      </div>
      <Link href="/products/create" class="btn">Create Product</Link>
    </div>

    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="px-4 py-2">SKU</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Price</th>
          <th class="px-4 py-2">Stock</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products.data" :key="product.id" class="border-t">
          <td class="px-4 py-2">{{ product.sku }}</td>
          <td class="px-4 py-2">{{ product.name }}</td>
          <td class="px-4 py-2">{{ product.price }}</td>
          <td class="px-4 py-2">{{ product.stock }}</td>
          <td class="px-4 py-2">
            <Link :href="`/products/${product.id}/edit`" class="text-blue-600">Edit</Link>
            |
            <Link :href="`/products/${product.id}`" class="text-blue-600">View</Link>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4">
      <button v-if="products.prev_page_url" @click="$inertia.get(products.prev_page_url)">Prev</button>
      <button v-if="products.next_page_url" @click="$inertia.get(products.next_page_url)">Next</button>
    </div>
  </div>
</template>

<style scoped>
.btn{background:#1f2937;color:white;padding:8px 12px;border-radius:6px}
</style>
