<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const TYPE_LABELS = {
    product: 'Products',
    customer: 'Customers',
    invoice: 'Invoices',
    payment: 'Payments',
    user: 'Users',
}

const query = ref('')
const results = ref([])
const isOpen = ref(false)
const isLoading = ref(false)
const containerRef = ref(null)

let debounceTimer = null
let requestToken = 0

const grouped = computed(() => {
    const groups = {}
    for (const result of results.value) {
        if (!groups[result.type]) groups[result.type] = []
        groups[result.type].push(result)
    }
    return groups
})

function onInput() {
    clearTimeout(debounceTimer)
    const term = query.value.trim()

    if (term.length < 2) {
        results.value = []
        isLoading.value = false
        isOpen.value = term.length > 0
        return
    }

    isLoading.value = true
    isOpen.value = true
    debounceTimer = setTimeout(() => runSearch(term), 250)
}

async function runSearch(term) {
    const token = ++requestToken

    try {
        const { data } = await axios.get(route('search'), { params: { q: term } })
        if (token !== requestToken) return
        results.value = data.results
    } catch (error) {
        if (token === requestToken) results.value = []
    } finally {
        if (token === requestToken) isLoading.value = false
    }
}

function select(result) {
    isOpen.value = false
    query.value = ''
    results.value = []
    router.visit(result.url)
}

function onFocus() {
    if (results.value.length || query.value.trim().length > 0) isOpen.value = true
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        isOpen.value = false
    } else if (event.key === 'Enter' && results.value.length) {
        event.preventDefault()
        select(results.value[0])
    }
}

function onClickOutside(event) {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isOpen.value = false
    }
}

onMounted(() => document.addEventListener('click', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))
</script>

<template>
    <div ref="containerRef" class="relative flex-1 max-w-md">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-muted" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
        <input
            type="search"
            v-model="query"
            @input="onInput"
            @focus="onFocus"
            @keydown="onKeydown"
            autocomplete="off"
            placeholder="Search products, customers, invoices…"
            class="w-full rounded-full border-border bg-background-alt py-2 pl-9 pr-4 text-sm focus:border-primary focus:ring-primary"
        />

        <div
            v-if="isOpen"
            class="absolute z-50 mt-2 max-h-96 w-full overflow-y-auto rounded-xl border border-border bg-surface-white py-2 shadow-lg"
        >
            <template v-if="results.length">
                <div v-for="(items, type) in grouped" :key="type">
                    <p class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-text-muted">
                        {{ TYPE_LABELS[type] || type }}
                    </p>
                    <button
                        v-for="item in items"
                        :key="type + item.url"
                        type="button"
                        @click="select(item)"
                        class="flex w-full flex-col items-start px-4 py-2 text-left transition hover:bg-background-alt"
                    >
                        <span class="text-sm font-medium text-text-primary">{{ item.label }}</span>
                        <span class="text-xs text-text-muted">{{ item.sub }}</span>
                    </button>
                </div>
            </template>
            <p v-else-if="isLoading" class="px-4 py-3 text-sm text-text-muted">Searching…</p>
            <p v-else class="px-4 py-3 text-sm text-text-muted">No results for "{{ query.trim() }}"</p>
        </div>
    </div>
</template>
