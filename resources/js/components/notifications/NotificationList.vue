<script setup>

import { ref, onMounted } from 'vue'

import {
    getNotifications,
    getUnreadNotificationCount,
    markNotificationAsRead
} from '../../services/notifications/notificationApi'


const notifications = ref([])
const unreadCount= ref(0)


const loadNotifications = async () => {

    try {

        const response = await getNotifications()

        notifications.value = response.data

        console.log(
            'NotificationList:',
            notifications.value
        )

    } catch (error) {

        console.error(
            'Failed to load notifications:',
            error
        )

    }

}
const loadUnreadCount= async ()=>{
    try{
        const response=await getUnreadNotificationCount()
        unreadCount.value=response.data.count
    }catch(error){
        console.error('failed to load unread count:',
            error
        )
    }
}
const markAsRead = async (notification) => {

    try {

        await markNotificationAsRead(
            notification.id
        )

        notification.read_at = new Date().toISOString()

if (unreadCount.value > 0) {
    unreadCount.value--
}

    } catch (error) {

        console.error(
            'Failed to mark notification as read:',
            error
        )

    }

}


onMounted(() => {
    loadNotifications()
    loadUnreadCount()
})

</script>

<template>

    <div class="mt-8">

        <div class="mb-4 flex items-center justify-between">

    <h2 class="text-lg font-semibold">
        Notifications
    </h2>

    <span
        v-if="unreadCount > 0"
        class="rounded-full bg-blue-500 px-2.5 py-1 text-xs font-medium text-white"
    >
        {{ unreadCount }} unread
    </span>

</div>

        <div
            v-if="notifications.length === 0"
            class="rounded-xl border border-black/10 bg-white p-5 text-sm opacity-60 dark:border-white/10 dark:bg-white/5"
        >
            No notifications yet.
        </div>

        <div
            v-else
            class="space-y-3"
        >

            <div
                v-for="notification in notifications"
                :key="notification.id"
                @click="markAsRead(notification)"
                class="cursor-pointer rounded-xl border border-black/10 bg-white p-4 transition dark:border-white/10 dark:bg-white/5"
                :class="{
                    'border-blue-500/30 bg-blue-500/5': !notification.read_at,
                    'opacity-60': notification.read_at
                }"
            >

                <div class="font-medium">
                    {{ notification.data.title }}
                </div>

                <p class="mt-1 text-sm opacity-60">
                    {{ notification.data.message }}
                </p>

            </div>

        </div>

    </div>

</template>

