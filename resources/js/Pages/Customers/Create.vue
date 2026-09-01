<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({ name: '', email: '', phone: '', address: '' })

function submit(){
  form.post('/customers')
}
</script>

<template>
  <Head title="Create Customer" />
  <div class="p-6">
    <h1 class="text-2xl mb-4">Create Customer</h1>
    <div>
      <label>Name</label>
      <input v-model="form.name" class="border p-2 w-full" />
    </div>
    <div>
      <label>Email</label>
      <input v-model="form.email" class="border p-2 w-full" />
    </div>
    <div>
      <label>Phone</label>
      <input v-model="form.phone" class="border p-2 w-full" />
    </div>
    <div>
      <label>Address</label>
      <textarea v-model="form.address" class="border p-2 w-full"></textarea>
    </div>
    <div class="mt-4">
      <button @click.prevent="submit" class="btn">Save</button>
      <Link href="/customers" class="ml-2">Cancel</Link>
    </div>
  </div>
</template>

<style scoped>
.btn{background:#1f2937;color:white;padding:8px 12px;border-radius:6px}
</style>
