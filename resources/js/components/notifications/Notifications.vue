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

      <NotificationList />

    </div>
  </div>
</template>

