router.push('/dashboard')
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    router.push('/dashboard')
<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const login = async () => {
    errorMessage.value = ''
    loading.value = true

    try {
        const response = await axios.post('http://127.0.0.1:8000/api/parent/login', {
            email: email.value,
            password: password.value,
        })

        localStorage.setItem('parent_token', response.data.token)
        localStorage.setItem('parent_user', JSON.stringify(response.data.user))
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.user))
        router.push('/dashboard')
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Unable to login. Please check your email and password.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-paper px-6 py-10">
        <div
            class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-5xl items-center justify-center"
        >
            <div
                class="grid w-full overflow-hidden border border-hairline bg-surface md:grid-cols-[0.9fr_1.1fr]"
            >
                <!-- LEFT / INTRO -->
                <div
                    class="hidden border-r border-hairline bg-paper p-10 md:flex md:flex-col md:justify-between"
                >
                    <div>
                        <div
                            class="mb-8 flex h-12 w-12 items-center justify-center border border-hairline bg-surface text-xl"
                        >
                            👨‍👩‍👧
                        </div>

                        <p
                            class="mb-3 text-sm font-medium text-forest"
                        >
                            Parent Portal
                        </p>

                        <h1
                            class="font-serif text-4xl font-medium leading-tight text-ink"
                        >
                            Stay connected to your child's academic journey.
                        </h1>

                        <p
                            class="mt-5 max-w-sm text-sm leading-6 text-ink-soft"
                        >
                            Sign in to view your child's academic information,
                            subjects, marks and progress.
                        </p>
                    </div>

                    <div
                        class="border-t border-hairline pt-5 text-xs text-ink-soft"
                    >
                        Academic Management System
                    </div>
                </div>

                <!-- RIGHT / LOGIN -->
                <div class="p-7 sm:p-10">
                    <div class="mb-8">
                        <div
                            class="mb-6 flex h-11 w-11 items-center justify-center border border-hairline bg-paper text-lg md:hidden"
                        >
                            👨‍👩‍👧
                        </div>

                        <p
                            class="mb-2 text-sm font-medium text-forest"
                        >
                            Parent access
                        </p>

                        <h2
                            class="font-serif text-3xl font-medium text-ink"
                        >
                            Parent Login
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-ink-soft"
                        >
                            Login to view your child's academic information.
                        </p>
                    </div>

                    <!-- ERROR -->
                    <div
                        v-if="errorMessage"
                        class="mb-5 border border-sienna bg-paper px-4 py-3 text-sm text-sienna"
                    >
                        {{ errorMessage }}
                    </div>

                    <!-- FORM -->
                    <form
                        @submit.prevent="login"
                        class="space-y-5"
                    >
                        <!-- EMAIL -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-ink"
                            >
                                Email
                            </label>

                            <input
                                v-model="email"
                                type="email"
                                required
                                placeholder="Enter your email"
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                            >
                        </div>

                        <!-- PASSWORD -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-ink"
                            >
                                Password
                            </label>

                            <input
                                v-model="password"
                                type="password"
                                required
                                placeholder="Enter your password"
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                            >
                        </div>

                        <!-- LOGIN -->
                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full border border-forest bg-forest px-4 py-3 text-sm font-medium text-white hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ loading ? 'Logging in...' : 'Login' }}
                        </button>
                    </form>

                    <div
                        class="mt-8 border-t border-hairline pt-5 text-xs leading-5 text-ink-soft"
                    >
                        Your parent account provides access to your child's
                        academic records and information.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>