<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { loginUser } from '../services/authApi'
import { clearAuthSessions } from '../services/apiConfig'

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

    clearAuthSessions()

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
      if (user.must_change_password){
        router.push('/change-password')
      } else {
        router.push('/dashboard')
      }
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
    <main class="min-h-screen bg-paper px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center justify-center">

            <div class="grid w-full max-w-5xl overflow-hidden border border-hairline bg-surface lg:grid-cols-[1fr_1.05fr]">

                <!-- LEFT: LETTERHEAD / BRAND -->
                <section
                    class="border-b border-hairline bg-paper p-7 sm:p-10 lg:border-b-0 lg:border-r lg:p-12"
                >
                    <div class="flex h-full flex-col">

                        <!-- BRAND -->
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center border border-forest bg-surface text-sm font-semibold text-forest"
                            >
                                SP
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-ink">
                                    Student Portal
                                </p>

                                <p class="text-xs text-ink-soft">
                                    Academic records
                                </p>
                            </div>
                        </div>


                        <!-- INTRO -->
                        <div class="mt-16 lg:mt-auto">

                            <p class="text-sm font-medium text-forest">
                                Welcome back
                            </p>

                            <h1
                                class="mt-3 max-w-md text-4xl font-medium leading-tight tracking-tight text-ink sm:text-5xl"
                            >
                                Your academic records, in one place.
                            </h1>

                        </div>


                        <!-- FOOTER -->
                        <div class="mt-12 border-t border-hairline pt-5 lg:mt-auto">
                            <p class="text-xs leading-5 text-ink-soft">
                                Secure access for students, teachers,
                                administrators, and parents.
                            </p>
                        </div>

                    </div>
                </section>


                <!-- RIGHT: LOGIN -->
                <section class="p-7 sm:p-10 lg:p-12">

                    <div class="mx-auto max-w-md">

                        <!-- FORM HEADER -->
                        <div>
                            <p class="text-xs font-medium text-ink-soft">
                                Account access
                            </p>

                            <h2
                                class="mt-2 text-3xl font-medium text-ink"
                            >
                                Sign in
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-ink-soft">
                                Enter your credentials to continue to your
                                dashboard.
                            </p>
                        </div>


                        <!-- ERROR -->
                        <div
                            v-if="errorMessage"
                            class="mt-6 border border-sienna/30 bg-surface px-4 py-3 text-sm leading-5 text-sienna"
                        >
                            {{ errorMessage }}
                        </div>


                        <!-- FORM -->
                        <form
                            @submit.prevent="login"
                            class="mt-8 space-y-5"
                        >

                            <!-- EMAIL -->
                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-xs font-medium text-ink-soft"
                                >
                                    Email
                                </label>

                                <input
                                    id="email"
                                    v-model="email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="you@example.com"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                                />
                            </div>


                            <!-- PASSWORD -->
                            <div>
                                <label
                                    for="password"
                                    class="mb-2 block text-xs font-medium text-ink-soft"
                                >
                                    Password
                                </label>

                                <input
                                    id="password"
                                    v-model="password"
                                    type="password"
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                                />
                            </div>


                            <!-- LOGIN BUTTON -->
                            <button
                                type="submit"
                                :disabled="loading"
                                class="w-full border border-forest bg-forest px-4 py-3 text-sm font-medium text-white hover:bg-forest/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ loading ? 'Signing in...' : 'Sign in' }}
                            </button>

                        </form>


                        <!-- REGISTER -->
                        <div
                            class="mt-8 border-t border-hairline pt-6"
                        >
                            <p class="text-center text-sm text-ink-soft">
                                Need an account?

                                <router-link
                                    to="/register"
                                    class="ml-1 font-medium text-forest hover:underline"
                                >
                                    Register
                                </router-link>
                            </p>
                        </div>

                    </div>

                </section>

            </div>

        </div>
    </main>
</template>