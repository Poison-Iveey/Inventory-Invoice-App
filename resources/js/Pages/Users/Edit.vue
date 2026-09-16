<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackLink from '@/Components/BackLink.vue'
import { Head, useForm, router } from '@inertiajs/vue3'

const props = defineProps({ user: Object, customers: Array })
const form = useForm({ name: props.user.name, email: props.user.email, role: props.user.role, customer_id: props.user.customer?.id || '' })

function submit() { form.put(route('users.update', props.user.id)) }

function resendInvite() {
  router.post(route('users.resend-invite', props.user.id), {}, { preserveScroll: true })
}
</script>

<template>
  <Head :title="`Edit ${user.name}`" />
  <AuthenticatedLayout><template #header><h2 class="text-3xl font-bold text-text-primary">Edit User</h2></template>
    <div class="mx-auto max-w-2xl p-6 space-y-6">
      <div class="flex items-center justify-between rounded-lg border border-border bg-surface-DEFAULT p-4 shadow-sm">
        <div>
          <p class="font-medium">Password</p>
          <p class="text-sm text-text-muted">Only {{ user.name }} can set or change their own password. Resend the setup email if they need a new link.</p>
        </div>
        <button type="button" @click="resendInvite" class="rounded-lg border border-primary px-4 py-2 text-sm font-semibold text-primary hover:bg-primary/5">
          Resend setup email
        </button>
      </div>
      <form @submit.prevent="submit" class="space-y-5 rounded-lg border border-border bg-surface-DEFAULT p-6 shadow-sm">
      <div><label class="block font-medium">Name</label><input v-model="form.name" class="mt-1 w-full rounded border-border" /><p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p></div>
      <div><label class="block font-medium">Email</label><input v-model="form.email" type="email" class="mt-1 w-full rounded border-border" /><p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p></div>
      <div><label class="block font-medium">Role</label><select v-model="form.role" class="mt-1 w-full rounded border-border"><option value="admin">Admin</option><option value="staff">Staff / Sales</option><option value="accountant">Accountant</option><option value="customer">Customer</option></select><p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p></div>
      <div v-if="form.role === 'customer'"><label class="block font-medium">Customer profile</label><select v-model="form.customer_id" class="mt-1 w-full rounded border-border"><option value="">Link later</option><option v-for="customer in props.customers" :key="customer.id" :value="customer.id">{{ customer.name }} — {{ customer.email }}</option></select><p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p></div>
      <div class="flex gap-3"><button :disabled="form.processing" class="rounded-lg bg-primary px-5 py-2 font-semibold text-white disabled:opacity-50">Save changes</button><BackLink :href="route('users.index')">Cancel</BackLink></div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
