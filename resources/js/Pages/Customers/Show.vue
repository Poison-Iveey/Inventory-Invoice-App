<script setup>
import { Head } from '@inertiajs/vue3'
const props = defineProps({ customer: Object })
</script>

<template>
  <Head title="Customer" />
  <div class="p-6">
    <h1 class="text-2xl mb-4">{{ customer.name }}</h1>
    <div><strong>Email:</strong> {{ customer.email }}</div>
    <div><strong>Phone:</strong> {{ customer.phone }}</div>
    <div><strong>Address:</strong> <div v-html="customer.address"></div></div>
    <div class="mt-4"><a href="/customers" class="text-blue-600">Back</a></div>
  </div>
</template>
