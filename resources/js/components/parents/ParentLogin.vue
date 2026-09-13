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

    <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">

        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

            <div class="text-center mb-8">

                <h1 class="text-3xl font-bold text-gray-800">
                    Parent Login
                </h1>

                <p class="text-gray-500 mt-2">
                    Login to view your child's academic information
                </p>

            </div>

            <div
                v-if="errorMessage"
                class="mb-5 p-3 rounded-lg bg-red-100 text-red-700 text-sm"
            >
                {{ errorMessage }}
            </div>

            <form @submit.prevent="login" class="space-y-5">

                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>

                    <input
                        v-model="email"
                        type="email"
                        required
                        placeholder="Enter your email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >

                </div>

                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>

                    <input
                        v-model="password"
                        type="password"
                        required
                        placeholder="Enter your password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >

                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:bg-purple-400 text-white font-semibold transition"
                >
                    {{ loading ? 'Logging in...' : 'Login' }}
                </button>

            </form>

        </div>

    </div>

</template>