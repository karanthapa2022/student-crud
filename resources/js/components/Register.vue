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
    <main class="min-h-screen bg-paper px-4 py-8 sm:px-6 lg:px-8">
        <div
            class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center justify-center"
        >
            <div
                class="grid w-full max-w-5xl overflow-hidden border border-hairline bg-surface lg:grid-cols-[1fr_1.05fr]"
            >
                <!-- LEFT: LETTERHEAD / BRAND -->
                <section
                    class="border-b border-hairline bg-paper p-7 sm:p-10 lg:border-b-0 lg:border-r lg:p-12"
                >
                    <div class="flex h-full flex-col">
                        <!-- Brand -->
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

                        <!-- Main message -->
                        <div class="mt-16 lg:mt-auto">
                            <p class="text-sm font-medium text-forest">
                                Get started
                            </p>

                            <h1
                                class="mt-3 max-w-md text-4xl font-medium leading-tight tracking-tight text-ink sm:text-5xl"
                            >
                                Create your academic account.
                            </h1>
                        </div>

                        <!-- Footer note -->
                        <div class="mt-12 border-t border-hairline pt-5 lg:mt-auto">
                            <p class="text-xs leading-5 text-ink-soft">
                                Secure access for students, teachers,
                                administrators, and parents.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- RIGHT: REGISTER FORM -->
                <section class="p-7 sm:p-10 lg:p-12">
                    <div class="mx-auto max-w-md">
                        <!-- Heading -->
                        <div>
                            <p class="text-xs font-medium text-ink-soft">
                                Account registration
                            </p>

                            <h2
                                class="mt-2 text-3xl font-medium text-ink"
                            >
                                Create account
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-ink-soft">
                                Enter your details to create your account.
                            </p>
                        </div>

                        <!-- Error -->
                        <div
                            v-if="errorMessage"
                            class="mt-6 border border-sienna/30 bg-surface px-4 py-3 text-sm leading-5 text-sienna"
                        >
                            {{ errorMessage }}
                        </div>

                        <!-- Form -->
                        <form
                            @submit.prevent="register"
                            class="mt-8 space-y-5"
                        >
                            <!-- Name -->
                            <div>
                                <label
                                    for="name"
                                    class="mb-2 block text-xs font-medium text-ink-soft"
                                >
                                    Name
                                </label>

                                <input
                                    id="name"
                                    v-model="name"
                                    type="text"
                                    autocomplete="name"
                                    placeholder="Enter your name"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                                />
                            </div>

                            <!-- Email -->
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

                            <!-- Password -->
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
                                    autocomplete="new-password"
                                    placeholder="Enter your password"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                                />
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label
                                    for="password-confirmation"
                                    class="mb-2 block text-xs font-medium text-ink-soft"
                                >
                                    Confirm password
                                </label>

                                <input
                                    id="password-confirmation"
                                    v-model="passwordConfirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    placeholder="Confirm your password"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                                />
                            </div>

                            <!-- Register -->
                            <button
                                type="submit"
                                :disabled="loading"
                                class="w-full border border-forest bg-forest px-4 py-3 text-sm font-medium text-white hover:bg-forest/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    loading
                                        ? 'Creating account...'
                                        : 'Create account'
                                }}
                            </button>
                        </form>

                        <!-- Login -->
                        <div class="mt-8 border-t border-hairline pt-6">
                            <p class="text-center text-sm text-ink-soft">
                                Already have an account?

                                <router-link
                                    to="/login"
                                    class="ml-1 font-medium text-forest hover:underline"
                                >
                                    Sign in
                                </router-link>
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</template>