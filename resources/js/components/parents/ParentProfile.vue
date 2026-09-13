
<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

const darkMode = ref(false)

const form = ref({
    name: '',
    email: '',
    phone: '',
    relationship: ''
})

const fetchProfile = async () => {

    loading.value = true
    errorMessage.value = ''

    try {

        const token = localStorage.getItem('parent_token')

        if (!token) {
            router.push('/parents/login')
            return
        }

        const response = await axios.get(
            'http://127.0.0.1:8000/api/parent/profile',
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        const parent = response.data.parent

        form.value = {
            name: parent.name || '',
            email: parent.email || '',
            phone: parent.phone || '',
            relationship: parent.relationship || ''
        }

    } catch (error) {

        console.error('Parent profile error:', error)

        if (error.response?.status === 401) {

            localStorage.removeItem('parent_token')
            localStorage.removeItem('parent_user')

            router.push('/parents/login')

            return
        }

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to load profile.'

    } finally {

        loading.value = false

    }
}

const updateProfile = async () => {

    saving.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {

        const token = localStorage.getItem('parent_token')

        const response = await axios.put(
            'http://127.0.0.1:8000/api/parent/profile',
            form.value,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        successMessage.value =
            response.data.message || 'Profile updated successfully.'

        // Update stored parent user
        const storedUser = localStorage.getItem('parent_user')

        if (storedUser) {

            const user = JSON.parse(storedUser)

            user.parent = response.data.parent

            localStorage.setItem(
                'parent_user',
                JSON.stringify(user)
            )
        }

    } catch (error) {

        console.error('Update parent profile error:', error)

        if (error.response?.status === 401) {

            localStorage.removeItem('parent_token')
            localStorage.removeItem('parent_user')

            router.push('/parents/login')

            return
        }

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to update profile.'

    } finally {

        saving.value = false

    }
}

const goBack = () => {
            router.push('/dashboard')
}

const toggleDarkMode = () => {

    darkMode.value = !darkMode.value

    document.documentElement.classList.toggle(
        'dark',
        darkMode.value
    )

    localStorage.setItem(
        'theme',
        darkMode.value ? 'dark' : 'light'
    )
}

onMounted(() => {

    const savedTheme = localStorage.getItem('theme')

    if (savedTheme === 'dark') {

        darkMode.value = true
        document.documentElement.classList.add('dark')

    } else {

        darkMode.value = false
        document.documentElement.classList.remove('dark')

    }

    fetchProfile()

})

</script>

<template>

    <div
        class="min-h-screen bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 transition-colors duration-300"
    >

        <!-- HEADER -->

        <header
            class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm"
        >

            <div
                class="max-w-5xl mx-auto px-4 sm:px-6 py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
            >

                <div>

                    <h1
                        class="text-2xl font-bold text-gray-800 dark:text-white"
                    >
                        My Profile
                    </h1>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Manage your parent account information.
                    </p>

                </div>

                <div class="flex items-center gap-3">

                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-700 transition"
                    >

                        <span v-if="darkMode">
                            ☀️ Light
                        </span>

                        <span v-else>
                            🌙 Dark
                        </span>

                    </button>

                    <button
                        @click="goBack"
                        type="button"
                        class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white transition"
                    >
                        ← Dashboard
                    </button>

                </div>

            </div>

        </header>


        <!-- MAIN -->

        <main
            class="max-w-5xl mx-auto px-4 sm:px-6 py-8"
        >

            <!-- LOADING -->

            <div
                v-if="loading"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-12 text-center"
            >

                <p
                    class="text-gray-500 dark:text-gray-400"
                >
                    Loading profile...
                </p>

            </div>


            <!-- ERROR -->

            <div
                v-else-if="errorMessage"
                class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 rounded-xl p-5"
            >

                {{ errorMessage }}

            </div>


            <!-- PROFILE -->

            <div
                v-else
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden"
            >

                <!-- PROFILE HEADER -->

                <div
                    class="px-6 py-8 border-b border-gray-200 dark:border-gray-800"
                >

                    <div
                        class="flex flex-col sm:flex-row items-center sm:items-start gap-5"
                    >

                        <div
                            class="w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900 flex items-center justify-center text-4xl"
                        >
                            👤
                        </div>

                        <div
                            class="text-center sm:text-left"
                        >

                            <h2
                                class="text-2xl font-bold text-gray-800 dark:text-white"
                            >
                                {{ form.name || 'Parent' }}
                            </h2>

                            <p
                                class="text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Parent Account
                            </p>

                        </div>

                    </div>

                </div>


                <!-- SUCCESS -->

                <div
                    v-if="successMessage"
                    class="mx-6 mt-6 p-4 rounded-xl bg-green-100 dark:bg-green-950/40 border border-green-200 dark:border-green-900 text-green-700 dark:text-green-300"
                >

                    {{ successMessage }}

                </div>


                <!-- FORM -->

                <form
                    @submit.prevent="updateProfile"
                    class="p-6 space-y-6"
                >

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >

                        <!-- NAME -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Full Name
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                placeholder="Enter your full name"
                            >

                        </div>


                        <!-- EMAIL -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Email
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                placeholder="Enter your email"
                            >

                        </div>


                        <!-- PHONE -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Phone
                            </label>

                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                placeholder="Enter your phone number"
                            >

                        </div>


                        <!-- RELATIONSHIP -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Relationship
                            </label>

                            <input
                                v-model="form.relationship"
                                type="text"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                placeholder="e.g. Father, Mother, Guardian"
                            >

                        </div>

                    </div>


                    <!-- ERROR -->

                    <div
                        v-if="errorMessage"
                        class="p-4 rounded-xl bg-red-100 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300"
                    >

                        {{ errorMessage }}

                    </div>


                    <!-- BUTTON -->

                    <div
                        class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-800"
                    >

                        <button
                            type="submit"
                            :disabled="saving"
                            class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium transition"
                        >

                            <span v-if="saving">
                                Saving...
                            </span>

                            <span v-else>
                                Save Changes
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</template>

