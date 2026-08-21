<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')

const errorMessage = ref('')
const loading = ref(false)

const register = async () => {

  errorMessage.value = ''

  if (
    !name.value ||
    !email.value ||
    !password.value ||
    !passwordConfirmation.value
  ) {
    errorMessage.value = 'Please fill in all fields.'
    return
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  loading.value = true

  try {

    const response = await axios.post(
      'http://127.0.0.1:8000/api/register',
      {
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value
      }
    )

    localStorage.setItem('token', response.data.token)

    localStorage.setItem(
      'user',
      JSON.stringify(response.data.user)
    )

    router.push('/students')

  } catch (error) {

    console.error('Registration error:', error)

    if (error.response?.data?.errors) {

      const errors = error.response.data.errors

      errorMessage.value =
        Object.values(errors)
          .flat()
          .join(' ')

    } else {

      errorMessage.value =
        error.response?.data?.message ||
        'Registration failed.'

    }

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
          Create Account
        </h1>

        <p class="text-gray-500 mt-2">
          Register a new account
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
        @submit.prevent="register"
        class="mt-6"
      >

        <!-- Name -->

        <label class="block text-sm font-medium text-gray-700 mb-2">
          Name
        </label>

        <input
          v-model="name"
          type="text"
          placeholder="Enter your name"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500"
        />

        <!-- Email -->

        <label class="block text-sm font-medium text-gray-700 mb-2">
          Email
        </label>

        <input
          v-model="email"
          type="email"
          placeholder="Enter your email"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500"
        />

        <!-- Password -->

        <label class="block text-sm font-medium text-gray-700 mb-2">
          Password
        </label>

        <input
          v-model="password"
          type="password"
          placeholder="Enter password"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500"
        />

        <!-- Confirm Password -->

        <label class="block text-sm font-medium text-gray-700 mb-2">
          Confirm Password
        </label>

        <input
          v-model="passwordConfirmation"
          type="password"
          placeholder="Confirm password"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-6 text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500"
        />

        <!-- Register -->

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition disabled:opacity-50"
        >
          {{ loading ? 'Creating Account...' : 'Register' }}
        </button>

      </form>

      <!-- Login -->

      <p class="text-center text-gray-600 mt-6">

        Already have an account?

        <router-link
          to="/login"
          class="text-blue-600 font-semibold hover:underline ml-1"
        >
          Login
        </router-link>

      </p>

    </div>

  </div>

</template>