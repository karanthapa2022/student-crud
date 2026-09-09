<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// =========================================================
// STATE
// =========================================================

const loading = ref(true)
const errorMessage = ref('')

const parent = ref(null)
const children = ref([])

const darkMode = ref(false)


// =========================================================
// FETCH PARENT DASHBOARD
// =========================================================

const fetchParentDashboard = async () => {

    loading.value = true
    errorMessage.value = ''

    try {

        const token = localStorage.getItem('parent_token')

        const response = await axios.get(
            'http://127.0.0.1:8000/api/parent/dashboard',
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        parent.value = response.data.parent
        children.value = response.data.children || []

    } catch (error) {

        console.error('Parent dashboard error:', error)

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to load parent dashboard.'

    } finally {

        loading.value = false

    }
}


// =========================================================
// VIEW MARKSHEETS
// =========================================================

const viewMarksheets = (studentId) => {

    if (!studentId) {
        return
    }

    router.push(`/parents/marksheet/${studentId}`)
}


// =========================================================
// LOGOUT
// =========================================================

const logout = () => {

    localStorage.removeItem('parent_token')
    localStorage.removeItem('parent_user')

    router.push('/parents/login')
}


// =========================================================
// DARK MODE
// =========================================================

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


// =========================================================
// ON MOUNT
// =========================================================

onMounted(() => {

    fetchParentDashboard()

    const savedTheme = localStorage.getItem('theme')

    if (savedTheme === 'dark') {

        darkMode.value = true

        document.documentElement.classList.add('dark')

    } else {

        darkMode.value = false

        document.documentElement.classList.remove('dark')

    }

})

</script>


<template>

    <div
        class="min-h-screen bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 transition-colors duration-300"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <header
            class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm transition-colors duration-300"
        >

            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
            >

                <!-- TITLE -->

                <div>

                    <h1
                        class="text-2xl font-bold text-gray-800 dark:text-white"
                    >
                        Parent Dashboard
                    </h1>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Welcome back

                        <span
                            v-if="parent"
                            class="font-medium text-gray-700 dark:text-gray-300"
                        >
                            {{ parent.name }}
                        </span>
                    </p>

                </div>


                <!-- ACTIONS -->

                <div class="flex items-center gap-3">

                    <!-- DARK MODE -->

                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-700 transition"
                    >

                        <span v-if="darkMode">
                             Light
                        </span>

                        <span v-else>
                             Dark
                        </span>

                    </button>


                    <!-- LOGOUT -->

                    <button
                        @click="logout"
                        type="button"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition"
                    >
                        Logout
                    </button>

                </div>

            </div>

        </header>


        <!-- ================================================= -->
        <!-- MAIN -->
        <!-- ================================================= -->

        <main
            class="max-w-7xl mx-auto px-4 sm:px-6 py-8"
        >


            <!-- ================================================= -->
            <!-- LOADING -->
            <!-- ================================================= -->

            <div
                v-if="loading"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-12 text-center transition-colors duration-300"
            >

                <div
                    class="text-gray-500 dark:text-gray-400"
                >
                    Loading dashboard...
                </div>

            </div>


            <!-- ================================================= -->
            <!-- ERROR -->
            <!-- ================================================= -->

            <div
                v-else-if="errorMessage"
                class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 rounded-xl p-5"
            >

                {{ errorMessage }}

            </div>


            <!-- ================================================= -->
            <!-- DASHBOARD -->
            <!-- ================================================= -->

            <div v-else>


                <!-- ================================================= -->
                <!-- PARENT INFORMATION -->
                <!-- ================================================= -->

                <section
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-6 mb-8 transition-colors duration-300"
                >

                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white mb-5"
                    >
                        My Information
                    </h2>


                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-6"
                    >

                        <!-- NAME -->

                        <div>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Name
                            </p>

                            <p
                                class="font-medium text-gray-800 dark:text-white mt-1"
                            >
                                {{ parent?.name || '—' }}
                            </p>

                        </div>


                        <!-- EMAIL -->

                        <div>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Email
                            </p>

                            <p
                                class="font-medium text-gray-800 dark:text-white mt-1"
                            >
                                {{ parent?.email || '—' }}
                            </p>

                        </div>


                        <!-- PHONE -->

                        <div>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Phone
                            </p>

                            <p
                                class="font-medium text-gray-800 dark:text-white mt-1"
                            >
                                {{ parent?.phone || '—' }}
                            </p>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- CHILDREN -->
                <!-- ================================================= -->

                <section class="mb-8">


                    <!-- SECTION HEADER -->

                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5"
                    >

                        <div>

                            <h2
                                class="text-xl font-bold text-gray-800 dark:text-white"
                            >
                                My Children
                            </h2>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Students associated with your account
                            </p>

                        </div>


                        <!-- CHILD COUNT -->

                        <div
                            class="bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900 px-4 py-2 rounded-lg font-semibold"
                        >
                            {{ children.length }}
                            {{ children.length === 1 ? 'Child' : 'Children' }}
                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- NO CHILDREN -->
                    <!-- ================================================= -->

                    <div
                        v-if="children.length === 0"
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-10 text-center"
                    >

                        <div class="text-4xl mb-4">
                            👨‍👩‍👧
                        </div>

                        <p
                            class="text-gray-500 dark:text-gray-400"
                        >
                            No children are currently associated with your account.
                        </p>

                    </div>


                    <!-- ================================================= -->
                    <!-- CHILD CARDS -->
                    <!-- ================================================= -->

                    <div
                        v-else
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    >

                        <div
                            v-for="child in children"
                            :key="child.id"
                            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-6 hover:shadow-md dark:hover:shadow-gray-950/50 transition-all duration-300"
                        >


                            <!-- CHILD HEADER -->

                            <div
                                class="flex items-center justify-between mb-5"
                            >

                                <div>

                                    <h3
                                        class="text-lg font-bold text-gray-800 dark:text-white"
                                    >
                                        {{ child.name }}
                                    </h3>

                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Student
                                    </p>

                                </div>


                                <div
                                    class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900 flex items-center justify-center text-xl"
                                >
                                    🎓
                                </div>

                            </div>


                            <!-- CHILD DETAILS -->

                            <div
                                class="space-y-3 text-sm"
                            >

                                <!-- STUDENT ID -->

                                <div
                                    class="flex justify-between gap-4"
                                >

                                    <span
                                        class="text-gray-500 dark:text-gray-400"
                                    >
                                        Student ID
                                    </span>

                                    <span
                                        class="font-medium text-gray-800 dark:text-gray-200"
                                    >
                                        {{ child.id }}
                                    </span>

                                </div>


                                <!-- EMAIL -->

                                <div
                                    v-if="child.email"
                                    class="flex justify-between gap-4"
                                >

                                    <span
                                        class="text-gray-500 dark:text-gray-400"
                                    >
                                        Email
                                    </span>

                                    <span
                                        class="font-medium text-gray-800 dark:text-gray-200 text-right break-all"
                                    >
                                        {{ child.email }}
                                    </span>

                                </div>

                            </div>


                            <!-- VIEW MARKSHEET -->

                            <button
                                @click="viewMarksheets(child.id)"
                                type="button"
                                class="w-full mt-6 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition"
                            >
                                View Marksheets
                            </button>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- QUICK ACTIONS -->
                <!-- ================================================= -->

                <section>

                    <h2
                        class="text-xl font-bold text-gray-800 dark:text-white mb-5"
                    >
                        Quick Actions
                    </h2>


                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >


                        <!-- MARKSHEETS -->

                        <div
                            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-6 transition-colors duration-300"
                        >

                            <div class="text-3xl mb-3">
                                📊
                            </div>

                            <h3
                                class="text-lg font-semibold text-gray-800 dark:text-white"
                            >
                                View Marksheets
                            </h3>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Select a child above to view their academic results and marksheet.
                            </p>

                        </div>


                        <!-- PROFILE -->

                        <button
                            @click="router.push('/parents/profile')"
                            type="button"
                            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-6 text-left hover:shadow-md dark:hover:shadow-gray-950/50 transition-all duration-300"
                        >

                            <div class="text-3xl mb-3">
                                👤
                            </div>

                            <h3
                                class="text-lg font-semibold text-gray-800 dark:text-white"
                            >
                                My Profile
                            </h3>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                View and manage your profile information.
                            </p>

                        </button>


                    </div>

                </section>


            </div>

        </main>

    </div>

</template>

