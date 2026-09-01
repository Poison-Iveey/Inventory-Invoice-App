<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ customers: Array, products: Array })

const form = useForm({
  customer_id: '',
  issue_date: new Date().toISOString().split('T')[0],
  due_date: '',
  items: [{ product_id: '', quantity: 1 }],
})

function addLine() {
  form.items.push({ product_id: '', quantity: 1 })
}

function removeLine(index) {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}

function submit() {
  form.post('/invoices')
}

function getProductStock(productId) {
  const product = props.products.find(p => p.id == productId)
  return product?.stock || 0
}
</script>

<template>
  <Head title="Create Invoice" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-3xl font-bold text-primary">Create New Invoice</h2>
    </template>

    <div class="p-6 max-w-5xl mx-auto">
      <!-- Error Message -->
      <div v-if="form.errors.items" class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
        <p class="text-red-800 font-medium">Unable to create invoice</p>
        <p class="text-red-700 text-sm mt-1">{{ form.errors.items }}</p>
      </div>

      <!-- Form Container -->
      <form @submit.prevent="submit" class="bg-surface-DEFAULT rounded-lg border border-border shadow-sm">
        <!-- Header Section -->
        <div class="px-6 py-6 border-b border-border bg-primary-tint">
          <h3 class="text-lg font-semibold text-primary-deep">Invoice Information</h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Customer Selection -->
          <div>
            <label class="block text-sm font-semibold text-text-primary mb-2">Customer *</label>
            <select 
              v-model="form.customer_id" 
              class="w-full px-4 py-2 border border-border rounded-lg bg-surface-white text-text-primary focus:outline-none focus:ring-2 focus:ring-accent transition"
            >
              <option value="">Choose a customer...</option>
              <option v-for="customer in props.customers" :key="customer.id" :value="customer.id">
                {{ customer.name }}
              </option>
            </select>
            <p v-if="!form.customer_id" class="text-sm text-accent-deep mt-1">Required</p>
          </div>

          <!-- Dates Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-text-primary mb-2">Issue Date *</label>
              <input 
                type="date" 
                v-model="form.issue_date" 
                class="w-full px-4 py-2 border border-border rounded-lg bg-surface-white text-text-primary focus:outline-none focus:ring-2 focus:ring-accent transition"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-text-primary mb-2">Due Date *</label>
              <input 
                type="date" 
                v-model="form.due_date" 
                class="w-full px-4 py-2 border border-border rounded-lg bg-surface-white text-text-primary focus:outline-none focus:ring-2 focus:ring-accent transition"
              />
            </div>
          </div>

          <!-- Line Items Section -->
          <div class="border-t border-border pt-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-primary-deep">Line Items</h3>
              <button 
                @click.prevent="addLine" 
                class="inline-flex items-center gap-2 bg-accent hover:bg-accent-mid text-primary-deep font-semibold py-2 px-4 rounded-lg transition"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
              </button>
            </div>

            <!-- Items Table -->
            <div class="space-y-3">
              <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 md:grid-cols-[2fr_120px_80px] gap-3 items-start p-4 bg-primary-tint rounded-lg">
                <div>
                  <label class="text-xs font-semibold text-text-muted uppercase mb-1 block">Product</label>
                  <select 
                    v-model="item.product_id" 
                    class="w-full px-3 py-2 border border-border rounded-lg bg-surface-white text-text-primary text-sm focus:outline-none focus:ring-2 focus:ring-accent transition"
                  >
                    <option value="">Select product...</option>
                    <option v-for="product in props.products" :key="product.id" :value="product.id">
                      {{ product.name }} (Stock: {{ product.stock }})
                    </option>
                  </select>
                </div>

                <div>
                  <label class="text-xs font-semibold text-text-muted uppercase mb-1 block">Quantity</label>
                  <input 
                    type="number" 
                    min="1" 
                    v-model.number="item.quantity" 
                    class="w-full px-3 py-2 border border-border rounded-lg bg-surface-white text-text-primary text-sm focus:outline-none focus:ring-2 focus:ring-accent transition"
                  />
                  <p v-if="getProductStock(item.product_id) < item.quantity" class="text-xs text-red-600 mt-1">
                    Stock: {{ getProductStock(item.product_id) }}
                  </p>
                </div>

                <div class="flex items-end">
                  <button 
                    @click.prevent="removeLine(index)" 
                    v-if="form.items.length > 1"
                    class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="border-t border-border pt-6 flex gap-3">
            <button 
              @click.prevent="submit" 
              :disabled="form.processing"
              class="inline-flex items-center gap-2 bg-primary hover:bg-primary-deep disabled:opacity-50 text-surface-white font-semibold py-2 px-6 rounded-lg transition"
            >
              <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span v-if="form.processing" class="inline-block animate-spin">⟳</span>
              {{ form.processing ? 'Creating...' : 'Create Invoice' }}
            </button>
            <a href="/invoices" class="inline-flex items-center gap-2 bg-border hover:bg-border-strong text-text-primary font-semibold py-2 px-6 rounded-lg transition">
              Cancel
            </a>
          </div>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
