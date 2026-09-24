<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from './components/AppLayout.vue'

const route = useRoute()

const showLayout = computed(() => {
    return route.meta.requiresAuth === true
})
</script>

<template>
    <!-- Authenticated pages use the universal layout -->
    <AppLayout v-if="showLayout">
        <router-view />
    </AppLayout>

    <!-- Login / Register / Parent Login stay outside the layout -->
    <router-view v-else />
</template>
