<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackLink from '@/Components/BackLink.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const props = defineProps({ product: Object })

const form = useForm({
  name: props.product.name,
  description: props.product.description,
  price: props.product.price,
  stock: props.product.stock,
  status: props.product.status,
  image: null,
})

function onFileChange(e) {
  form.image = e.target.files[0]
}

function submit() {
  form.put(route('products.update', props.product.id))
}
</script>

<template>
  <Head title="Edit Product" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-text-primary">Edit Product</h2>
    </template>

    <div class="mx-auto max-w-2xl p-6">
      <form @submit.prevent="submit" class="space-y-5 rounded-lg border border-border bg-surface-DEFAULT p-6 shadow-sm">
        <div>
          <InputLabel value="SKU" />
          <p class="mt-1 rounded-md border border-border bg-background-alt px-3 py-2 font-mono text-sm text-text-body">{{ product.sku }}</p>
        </div>

        <div>
          <InputLabel for="name" value="Name" />
          <TextInput id="name" v-model="form.name" class="mt-1 block w-full" />
          <InputError :message="form.errors.name" class="mt-1" />
        </div>

        <div>
          <InputLabel for="description" value="Description" />
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"
          ></textarea>
          <InputError :message="form.errors.description" class="mt-1" />
        </div>

        <div class="grid gap-5 md:grid-cols-2">
          <div>
            <InputLabel for="price" value="Price" />
            <TextInput id="price" type="number" step="0.01" min="0.01" max="99999999.99" v-model.number="form.price" class="mt-1 block w-full" />
            <InputError :message="form.errors.price" class="mt-1" />
          </div>
          <div>
            <InputLabel for="stock" value="Stock" />
            <TextInput id="stock" type="number" min="0" v-model.number="form.stock" class="mt-1 block w-full" />
            <InputError :message="form.errors.stock" class="mt-1" />
          </div>
        </div>

        <div>
          <InputLabel for="status" value="Status" />
          <select
            id="status"
            v-model="form.status"
            class="mt-1 block w-full rounded-md border-border shadow-sm focus:border-accent focus:ring-accent"
          >
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <InputError :message="form.errors.status" class="mt-1" />
        </div>

        <div>
          <InputLabel for="image" value="Image" />
          <div v-if="product.image_url" class="mt-2 mb-3">
            <img :src="product.image_url" alt="Current product image" class="h-20 rounded-md border border-border" />
          </div>
          <input
            id="image"
            type="file"
            accept="image/*"
            @change="onFileChange"
            class="mt-1 block w-full text-sm text-text-body file:mr-4 file:rounded-md file:border-0 file:bg-primary-tint file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-deep hover:file:bg-accent-tint"
          />
          <InputError :message="form.errors.image" class="mt-1" />
        </div>

        <div class="flex items-center gap-3 border-t border-border pt-5">
          <PrimaryButton :disabled="form.processing">Save changes</PrimaryButton>
          <BackLink :href="route('products.index')">Cancel</BackLink>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
