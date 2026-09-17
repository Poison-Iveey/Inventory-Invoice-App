<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackLink from '@/Components/BackLink.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const form = useForm({ name: '', email: '', phone: '', address: '' })

function submit() {
  form.post(route('customers.store'))
}
</script>

<template>
  <Head title="Create Customer" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-text-primary">Create Customer</h2>
    </template>

    <div class="mx-auto max-w-2xl p-6">
      <form @submit.prevent="submit" class="space-y-5 rounded-lg border border-border bg-surface-DEFAULT p-6 shadow-sm">
        <div>
          <InputLabel for="name" value="Name" />
          <TextInput id="name" v-model="form.name" class="mt-1 block w-full" />
          <InputError :message="form.errors.name" class="mt-1" />
        </div>

        <div>
          <InputLabel for="email" value="Email" />
          <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" />
          <InputError :message="form.errors.email" class="mt-1" />
        </div>

        <div>
          <InputLabel for="phone" value="Phone" />
          <TextInput id="phone" v-model="form.phone" class="mt-1 block w-full" />
          <InputError :message="form.errors.phone" class="mt-1" />
        </div>

        <div>
          <InputLabel for="address" value="Address" />
          <textarea
            id="address"
            v-model="form.address"
            rows="3"
            class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"
          ></textarea>
          <InputError :message="form.errors.address" class="mt-1" />
        </div>

        <div class="flex items-center gap-3 border-t border-border pt-5">
          <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
          <BackLink :href="route('customers.index')">Cancel</BackLink>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
