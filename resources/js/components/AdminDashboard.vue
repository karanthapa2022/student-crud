<script setup>

import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const loading = ref(true)
const error = ref('')

const statistics = ref({
    totalStudents: 0,
    totalParents: 0,
    totalTeachers: 0,
    totalSubjects: 0,
})

const fetchDashboardData = async () => {

    loading.value = true
    error.value = ''

    try {

        const token = localStorage.getItem('token')

        const response = await axios.get(
            'http://127.0.0.1:8000/api/dashboard/statistics',
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        console.log('Dashboard statistics:', response.data)

        statistics.value = {
            totalStudents: response.data.totalStudents ?? 0,
            totalParents: response.data.totalParents ?? 0,
            totalTeachers: response.data.totalTeachers ?? 0,
            totalSubjects: response.data.totalSubjects ?? 0,
        }

    } catch (err) {

        console.error('Dashboard error:', err)

        error.value =
            err.response?.data?.message ||
            'Unable to load dashboard statistics.'

    } finally {

        loading.value = false

    }
}
const goToStudents = () => {
    router.push('/students')
}

const goToParents = () => {
    router.push('/parents')
}

const goToSubjects = () => {
    router.push('/subjects')
}

const goToMarksheets = () => {
    router.push('/marksheets')
}

const goToCreateMarksheet = () => {
    router.push('/marksheets/create')
}

onMounted(() => {
    fetchDashboardData()
})

</script>

<template>

    <div class="min-h-screen bg-gray-100 dark:bg-gray-950 transition-colors">

        <!-- HEADER -->
        <div class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">

            <div class="max-w-7xl mx-auto px-6 py-5">

                <div class="flex items-center justify-between">

                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Admin Dashboard
                        </h1>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage your student management system
                        </p>
                    </div>

                    <button
                        @click="goToStudents"
                        class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition"
                    >
                        Students
                    </button>

                </div>

            </div>

        </div>


        <!-- MAIN -->
        <main class="max-w-7xl mx-auto px-6 py-8">

            <!-- LOADING -->
            <div
                v-if="loading"
                class="text-center py-10 text-gray-500 dark:text-gray-400"
            >
                Loading dashboard...
            </div>


            <!-- ERROR -->
            <div
                v-else-if="error"
                class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400"
            >
                {{ error }}
            </div>


            <template v-else>

                <!-- STATISTICS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    <!-- STUDENTS -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
                    >

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Students
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ statistics.totalStudents }}
                        </p>

                    </div>


                    <!-- PARENTS -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
                    >

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Parents
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ statistics.totalParents }}
                        </p>

                    </div>


                    <!-- TEACHERS -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
                    >

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Teachers
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ statistics.totalTeachers }}
                        </p>

                    </div>


                    <!-- SUBJECTS -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
                    >

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Subjects
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ statistics.totalSubjects }}
                        </p>

                    </div>

                </div>


                <!-- QUICK ACTIONS -->
                <div class="mt-8">

                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Quick Actions
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-4">

                        <button
                            @click="goToStudents"
                            class="text-left bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:shadow-md transition"
                        >
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Manage Students
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Add, edit and manage students
                            </p>
                        </button>


                        <button
                            @click="goToParents"
                            class="text-left bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:shadow-md transition"
                        >
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Manage Parents
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Manage parents and children
                            </p>
                        </button>

                        


                        <button
                            @click="goToSubjects"
                            class="text-left bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:shadow-md transition"
                        >
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Manage Subjects
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Create and manage subjects
                            </p>
                        </button>

                        <button
                            @click="$router.push('/addresses')"
                            class="text-left bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:shadow-md transition"
                        >
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Manage Addresses
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                View and manage student addresses
                            </p>
                        </button>


                        <button
                            @click="goToCreateMarksheet"
                            class="text-left bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:shadow-md transition"
                        >
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Create Marksheet
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Create a new student marksheet
                            </p>
                        </button>

                    </div>

                </div>


                <!-- MARKSHEET SECTION -->
                <div class="mt-8">

                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6"
                    >

                        <div class="flex items-center justify-between">

                            <div>

                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                    Marksheets
                                </h2>

                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    View and manage student results.
                                </p>

                            </div>

                            <button
                                @click="goToMarksheets"
                                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                            >
                                View Marksheets
                            </button>

                        </div>

                    </div>

                </div>

            </template>

        </main>

    </div>

</template>