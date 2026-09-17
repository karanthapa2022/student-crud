<script setup>
import NotificationList from './NotificationList.vue'
</script>

<template>
  <div class="min-h-screen bg-[#F7F8F4] px-6 py-8 text-[#1C2B24]">
    <div class="mx-auto max-w-4xl">
      
      <div class="mb-8">
        <h1 class="text-2xl font-semibold">
          Notifications
        </h1>

        <p class="mt-1 text-sm opacity-60">
          View your notifications and updates.
        </p>
      </div>

      <div>
        <button
        type="button"
        @click="$router.push('/dashboard')"
        class="mb-6 inline-flex items-center gap-2 rounded-lg border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-paper">
          ←Back to Dashboard
        </button>
      </div>

      <NotificationList />

    </div>
  </div>
</template>

