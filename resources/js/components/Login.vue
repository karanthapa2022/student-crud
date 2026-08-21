<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/StudentApi'



const router = useRouter()

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const login = async () => {
  errorMessage.value = ''

  if (!email.value || !password.value) {
    errorMessage.value = 'Please enter email and password.'
    return
  }

  loading.value = true

  try {
    const response = await api.post('/login', {
  email: email.value,
  password: password.value
})

    localStorage.setItem('token', response.data.token)
    localStorage.setItem(
      'user',
      JSON.stringify(response.data.user)
    )

    router.push('/students')

  } catch (error) {
    console.error('Login error:', error)

    errorMessage.value =
      error.response?.data?.message ||
      'Invalid email or password.'

  } finally {
    loading.value = false
  }
}
</script>

<template>

  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">

      <!-- Heading -->

      <div class="text-center">

        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
          Login
        </h1>

        <p class="text-gray-500 mt-2">
          Login to your account
        </p>

      </div>

      <!-- Error -->

      <div
        v-if="errorMessage"
        class="mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg"
      >
        {{ errorMessage }}
      </div>

      <!-- Form -->

      <form
        @submit.prevent="login"
        class="mt-6"
      >

        <!-- Email -->

        <label class="block text-sm font-medium text-gray-700 mb-2">
          Email
        </label>

        <input
          v-model="email"
          type="email"
          placeholder="Enter your email"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <!-- Password -->

        <label class="block text-sm font-medium text-gray-700 mb-2">
          Password
        </label>

        <input
          v-model="password"
          type="password"
          placeholder="Enter your password"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-6 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <!-- Login -->

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>

      </form>

      <!-- Register -->

      <p class="text-center text-gray-600 mt-6">

        Don't have an account?

        <router-link
          to="/register"
          class="text-blue-600 font-semibold hover:underline ml-1"
        >
          Register
        </router-link>

      </p>

    </div>

  </div>

</template>