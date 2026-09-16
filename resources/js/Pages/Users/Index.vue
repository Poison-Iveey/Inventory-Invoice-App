<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({ users: Object, filters: Object })
const search = ref(props.filters?.search || '')
const role = ref(props.filters?.role || '')
let searchTimeout

function searchUsers() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    Inertia.get('/users', { search: search.value, role: role.value }, { preserveState: true, replace: true })
  }, 300)
}

function filterUsers() { Inertia.get('/users', { search: search.value, role: role.value }, { preserveState: true, replace: true }) }

const roleBadgeClasses = {
  admin: 'bg-stat-violet-tint text-stat-violet-deep',
  staff: 'bg-stat-blue-tint text-stat-blue-deep',
  accountant: 'bg-stat-teal-tint text-stat-teal-deep',
  customer: 'bg-stat-orange-tint text-stat-orange-deep',
}
</script>

<template>
  <Head title="Users" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-text-primary">Users</h2>
          <p class="mt-1 text-sm text-text-muted">Manage login accounts and roles.</p>
        </div>
        <Link :href="route('users.create')" class="rounded-lg bg-primary px-4 py-2 font-semibold text-surface-white hover:bg-primary-deep">Add user</Link>
      </div>
    </template>

    <div class="mx-auto max-w-7xl p-6">
      <div class="mb-5 flex gap-3"><input v-model="search" @input="searchUsers" type="search" placeholder="Search users" class="w-full max-w-sm rounded-lg border-border" /><select v-model="role" @change="filterUsers" class="rounded-lg border-border"><option value="">All roles</option><option value="admin">Admin</option><option value="staff">Staff</option><option value="accountant">Accountant</option><option value="customer">Customer</option></select></div>
      <div class="overflow-hidden rounded-2xl border border-border bg-surface-DEFAULT shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="border-b border-border bg-background-alt text-left">
              <tr>
                <th class="p-4 text-xs font-semibold uppercase tracking-wide text-text-muted">Name</th>
                <th class="p-4 text-xs font-semibold uppercase tracking-wide text-text-muted">Email</th>
                <th class="p-4 text-xs font-semibold uppercase tracking-wide text-text-muted">Role</th>
                <th class="p-4 text-xs font-semibold uppercase tracking-wide text-text-muted">Customer profile</th>
                <th class="p-4 text-xs font-semibold uppercase tracking-wide text-text-muted">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="user in users.data" :key="user.id" class="transition hover:bg-background-alt">
                <td class="p-4 font-medium text-text-primary">{{ user.name }}</td>
                <td class="p-4 text-text-body">{{ user.email }}</td>
                <td class="p-4">
                  <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', roleBadgeClasses[user.role]]">
                    {{ user.role }}
                  </span>
                </td>
                <td class="p-4 text-text-body">{{ user.customer?.name || '—' }}</td>
                <td class="p-4"><Link :href="route('users.edit', user.id)" class="font-medium text-primary hover:text-primary-deep">Edit</Link></td>
              </tr>
              <tr v-if="users.data.length === 0"><td colspan="5" class="p-8 text-center text-text-muted">No users found.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <Pagination :links="users.meta.links" />
    </div>
  </AuthenticatedLayout>
</template>
