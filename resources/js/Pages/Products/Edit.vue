<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
const props = defineProps({ product: Object })

const form = useForm({ sku: props.product.sku, name: props.product.name, description: props.product.description, price: props.product.price, stock: props.product.stock, status: props.product.status, image: null })

function onFileChange(e){
  form.image = e.target.files[0]
}

function submit(){
  form.put(`/products/${props.product.id}`)
}
</script>

<template>
  <Head title="Edit Product" />
  <div class="p-6">
    <h1 class="text-2xl mb-4">Edit Product</h1>
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
      <div v-if="props.product.image" class="mt-2">
        <img :src="`/storage/${props.product.image}`" alt="product" class="h-20" />
      </div>
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
