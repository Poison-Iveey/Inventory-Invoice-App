<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({ sku: '', name: '', description: '', price: 0, stock: 0, status: 'active', image: null })

function onFileChange(e){
  form.image = e.target.files[0]
}

function submit(){
  form.post('/products')
}
</script>

<template>
  <Head title="Create Product" />
  <div class="p-6">
    <h1 class="text-2xl mb-4">Create Product</h1>
    <div>
      <label>SKU</label>
      <input v-model="form.sku" class="border p-2 w-full" />
    </div>
    <div>
      <label>Name</label>
      <input v-model="form.name" class="border p-2 w-full" />
    </div>
    <div>
      <label>Price</label>
      <input type="number" v-model.number="form.price" class="border p-2 w-full" />
    </div>
    <div>
      <label>Stock</label>
      <input type="number" v-model.number="form.stock" class="border p-2 w-full" />
    </div>
    <div>
      <label>Image</label>
      <input type="file" @change="onFileChange" class="border p-2 w-full" />
    </div>
    <div class="mt-4">
      <button @click.prevent="submit" class="btn">Save</button>
      <Link href="/products" class="ml-2">Cancel</Link>
    </div>
  </div>
</template>

<style scoped>
.btn{background:#1f2937;color:white;padding:8px 12px;border-radius:6px}
</style>
