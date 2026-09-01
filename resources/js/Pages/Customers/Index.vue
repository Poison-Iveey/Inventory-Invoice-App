<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({ customers: Object, filters: Object })
const searchTerm = ref(props.filters?.search || '')
let timeout = null
function onSearch(){
  clearTimeout(timeout)
  timeout = setTimeout(()=>{
    Inertia.get('/customers',{search:searchTerm.value},{preserveState:true,replace:true})
  },300)
}
</script>

<template>
  <Head title="Customers" />
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <div>
        <h1 class="text-2xl font-semibold">Customers</h1>
        <div class="mt-2">
          <input type="text" v-model="searchTerm" @input="onSearch" class="border p-2" placeholder="Search customers" />
        </div>
      </div>
      <Link href="/customers/create" class="btn">Create Customer</Link>
    </div>

    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Phone</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="customer in customers.data" :key="customer.id" class="border-t">
          <td class="px-4 py-2">{{ customer.name }}</td>
          <td class="px-4 py-2">{{ customer.email }}</td>
          <td class="px-4 py-2">{{ customer.phone }}</td>
          <td class="px-4 py-2">
            <Link :href="`/customers/${customer.id}/edit`" class="text-blue-600">Edit</Link> |
            <Link :href="`/customers/${customer.id}`" class="text-blue-600">View</Link>
          </td>
        </tr>
      </tbody>
    </table>

  </div>
</template>

<style scoped>
.btn{background:#1f2937;color:white;padding:8px 12px;border-radius:6px}
</style>
