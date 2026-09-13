<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { loginUser } from '../services/authApi'

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
    const response = await loginUser({
      email: email.value,
      password: password.value,
    })

    const user = response.data.user
    const token = response.data.token

    if (user.role === 'admin') {
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
      router.push('/dashboard')
      return
    }

    if (user.role === 'teacher') {
      localStorage.setItem('teacher_token', token)
      localStorage.setItem('teacher_user', JSON.stringify(user))
      localStorage.setItem('user', JSON.stringify(user))
      localStorage.setItem('token', token)
      router.push('/dashboard')
      return
    }

    if (user.role === 'student') {
      localStorage.setItem('student_token', token)
      localStorage.setItem('student_user', JSON.stringify(user))
      localStorage.setItem('user', JSON.stringify(user))
      localStorage.setItem('token', token)
      router.push('/dashboard')
      return
    }

    if (user.role === 'parent') {
      localStorage.setItem('parent_token', token)
      localStorage.setItem('parent_user', JSON.stringify(user))
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
      router.push('/dashboard')
      return
    }

    errorMessage.value = 'Your account does not have a valid role.'
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
      <div class="bg-gradient-to-r from-indigo-600 to-blue-500 px-6 py-8 text-white sm:px-8">
        <p class="text-sm uppercase tracking-[0.2em] text-indigo-100">Student Portal</p>
        <h1 class="mt-3 text-3xl font-bold">Welcome back</h1>
        <p class="mt-2 text-sm text-indigo-100">Sign in to continue to your dashboard.</p>
      </div>

      <div class="p-6 sm:p-8">
        <div v-if="errorMessage" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="login" class="space-y-5">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
            <input
              v-model="email"
              type="email"
              autocomplete="email"
              placeholder="you@example.com"
              class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Password</label>
            <input
              v-model="password"
              type="password"
              autocomplete="current-password"
              placeholder="••••••••"
              class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {{ loading ? 'Logging in...' : 'Login' }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
          Need an account?
          <router-link to="/register" class="font-semibold text-indigo-600 hover:text-indigo-700">Register</router-link>
        </p>
      </div>
    </div>
  </div>
</template>