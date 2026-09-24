<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getUnreadNotificationCount } from '../services/notifications/notificationApi'

const router = useRouter()

const unreadNotificationCount = ref(0)
const isDarkMode = ref(false)

const roleLabel = {
    admin: 'Admin',
    teacher: 'Teacher',
    student: 'Student',
    parent: 'Parent',
}

const currentUser = ref(null)

const loadCurrentUser = () => {
    const sessions = [
        { token: 'token', user: 'user' },
        { token: 'teacher_token', user: 'teacher_user' },
        { token: 'student_token', user: 'student_user' },
        { token: 'parent_token', user: 'parent_user' },
    ]

    for (const session of sessions) {
        const token = localStorage.getItem(session.token)
        const userData = localStorage.getItem(session.user)

        if (!token || !userData) {
            continue
        }

        try {
            const user = JSON.parse(userData)

            if (user?.role) {
                currentUser.value = user
                return
            }
        } catch (error) {
            console.error('Invalid user data:', error)
        }
    }

    currentUser.value = null
}

const currentRole = computed(() => {
    return currentUser.value?.role || 'admin'
})

const currentRoleLabel = computed(() => {
    return roleLabel[currentRole.value] || 'Dashboard'
})

const roleInitial = computed(() => {
    return currentRoleLabel.value.charAt(0)
})

const loadUnreadNotificationCount = async () => {
    try {
        const response = await getUnreadNotificationCount()
        unreadNotificationCount.value = response.data.count
    } catch (error) {
        console.error(
            'Failed to load unread notification count:',
            error
        )
    }
}

const loadTheme = () => {
    isDarkMode.value =
        localStorage.getItem('theme') === 'dark'
}

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value

    if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
}

const goToDashboard = () => {
    router.push('/dashboard')
}

const goToProfile = () => {
    router.push('/profile')
}

const goToNotifications = () => {
    router.push('/notifications')
}

const logout = () => {
    localStorage.clear()
    router.push('/login')
}

onMounted(() => {
    loadCurrentUser()
    loadUnreadNotificationCount()
    loadTheme()
})
</script>

<template>
    <div
        class="min-h-screen bg-[#F4F6F1] text-[#1C2B24] transition-colors duration-300 dark:bg-[#111815] dark:text-[#E8EEE9]"
    >
        <!-- Universal Header -->
        <header
            class="sticky top-0 z-50 border-b border-[#D8DDD3] bg-white transition-colors duration-300 dark:border-[#2A352F] dark:bg-[#18211C]"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-6"
            >
                <!-- Logo / Dashboard -->
                <button
                    @click="goToDashboard"
                    class="flex items-center gap-3 text-left"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center bg-[#2F6F4E] text-sm font-semibold text-white"
                    >
                        {{ roleInitial }}
                    </div>

                    <div>
                        <p
                            class="text-sm font-semibold text-[#1C2B24] dark:text-[#E8EEE9]"
                        >
                            {{ currentRoleLabel }}
                        </p>

                        <p
                            class="text-xs text-[#6B776F] dark:text-[#9BA89F]"
                        >
                            School Management System
                        </p>
                    </div>
                </button>

                <!-- Universal Actions -->
                <div class="flex items-center gap-2">
                    <!-- Profile -->
                    <button
                        @click="goToProfile"
                        class="hidden border border-[#D8DDD3] bg-white px-4 py-2 text-sm font-medium text-[#1C2B24] transition hover:bg-[#F4F6F1] dark:border-[#36433B] dark:bg-[#202B25] dark:text-[#E8EEE9] dark:hover:bg-[#29362F] sm:block"
                    >
                        Profile
                    </button>

                    <!-- Notifications -->
                    <button
                        @click="goToNotifications"
                        class="relative border border-[#D8DDD3] bg-white px-4 py-2 text-sm font-medium text-[#1C2B24] transition hover:bg-[#F4F6F1] dark:border-[#36433B] dark:bg-[#202B25] dark:text-[#E8EEE9] dark:hover:bg-[#29362F]"
                    >
                        Notifications

                        <span
                            v-if="unreadNotificationCount > 0"
                            class="ml-2 inline-flex min-w-5 items-center justify-center rounded-full bg-[#B5563C] px-1.5 py-0.5 text-xs font-semibold text-white"
                        >
                            {{ unreadNotificationCount }}
                        </span>
                    </button>

                    <!-- Dark Mode -->
                    <button
                        @click="toggleDarkMode"
                        class="border border-[#D8DDD3] bg-white px-4 py-2 text-sm font-medium text-[#1C2B24] transition hover:bg-[#F4F6F1] dark:border-[#36433B] dark:bg-[#202B25] dark:text-[#E8EEE9] dark:hover:bg-[#29362F]"
                    >
                        {{ isDarkMode ? '☀️ Light' : '🌙 Dark' }}
                    </button>

                    <!-- Sign Out -->
                    <button
                        @click="logout"
                        class="border border-[#B5563C]/30 px-4 py-2 text-sm font-medium text-[#B5563C] transition hover:bg-[#B5563C]/5 dark:border-[#B5563C]/40"
                    >
                        Sign out
                    </button>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
