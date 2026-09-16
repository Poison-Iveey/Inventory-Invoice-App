<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackLink from '@/Components/BackLink.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const props = defineProps({ customer: Object })

const form = useForm({
  name: props.customer.name,
  email: props.customer.email,
  phone: props.customer.phone,
  address: props.customer.address,
})

function submit() {
  form.put(route('customers.update', props.customer.id))
}
</script>

<template>
  <Head title="Edit Customer" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-text-primary">Edit Customer</h2>
    </template>

    <div class="mx-auto max-w-2xl p-6">
      <div v-if="customer.has_login" class="mb-5 rounded-lg border border-border bg-primary-tint p-4 text-sm text-primary-deep">
        This customer has a login account. Changing their email here will also update the email they log in with.
      </div>

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
          <PrimaryButton :disabled="form.processing">Save changes</PrimaryButton>
          <BackLink :href="route('customers.index')">Cancel</BackLink>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
