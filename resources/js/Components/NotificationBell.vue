<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()

const isOpen = ref(false)
const isLoading = ref(false)
const notifications = ref([])
const unreadCount = ref(page.props.notificationsUnreadCount ?? 0)
const containerRef = ref(null)

const POLL_INTERVAL_MS = 20000
let pollTimer = null

async function toggle() {
    isOpen.value = !isOpen.value
    if (!isOpen.value) return

    isLoading.value = true
    try {
        const { data } = await axios.get(route('notifications.index'))
        notifications.value = data.notifications

        if (data.unread_count > 0) {
            await axios.post(route('notifications.read-all'))
        }
        // opening the dropdown is what "reads" everything in it
        unreadCount.value = 0
    } finally {
        isLoading.value = false
    }
}

function select(notification) {
    isOpen.value = false
    if (notification.url) router.visit(notification.url)
}

async function pollUnreadCount() {
    if (isOpen.value) return
    try {
        const { data } = await axios.get(route('notifications.unread-count'))
        unreadCount.value = data.unread_count
    } catch (error) {
        // a transient network hiccup shouldn't spam the console every 20s
    }
}

function onClickOutside(event) {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', onClickOutside)
    pollTimer = setInterval(pollUnreadCount, POLL_INTERVAL_MS)
})
onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside)
    clearInterval(pollTimer)
})
</script>

<template>
    <div ref="containerRef" class="relative">
        <button
            type="button"
            @click="toggle"
            :class="[
                'relative rounded-full p-2 text-text-muted transition hover:bg-background-alt hover:text-text-primary',
                unreadCount > 0 ? 'animate-bell-shake' : '',
            ]"
            title="Notifications"
        >
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute right-1 top-1 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-surface-white"
            ></span>
        </button>

        <div
            v-if="isOpen"
            class="absolute right-0 z-50 mt-2 w-80 max-h-96 overflow-y-auto rounded-xl border border-border bg-surface-white py-2 shadow-lg"
        >
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wider text-text-muted">Notifications</p>

            <template v-if="notifications.length">
                <button
                    v-for="notification in notifications"
                    :key="notification.id"
                    type="button"
                    @click="select(notification)"
                    class="flex w-full flex-col items-start gap-0.5 px-4 py-2 text-left transition hover:bg-background-alt"
                >
                    <span class="text-sm font-medium text-text-primary">{{ notification.title }}</span>
                    <span class="text-xs text-text-muted">{{ notification.body }}</span>
                    <span class="text-[11px] text-text-muted">{{ notification.created_at }}</span>
                </button>
            </template>
            <p v-else-if="isLoading" class="px-4 py-3 text-sm text-text-muted">Loading…</p>
            <p v-else class="px-4 py-3 text-sm text-text-muted">No notifications yet.</p>
        </div>
    </div>
</template>
