
<script setup>

import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getParentStudentMarksheet } from '../../services/marksheets/marksheetApi'
import { downloadMarksheetPdf } from '../../utils/marksheetPdf'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const marksheet = ref(null)

const fetchMarksheet = async () => {

    loading.value = true
    error.value = ''
    marksheet.value = null

    try {

        const studentId = route.params.studentId

        if (!studentId) {
            error.value = 'Student information is missing.'
            return
        }

        const response = await getParentStudentMarksheet(studentId)

        marksheet.value = response.data

    } catch (err) {

        console.error('Parent marksheet error:', err)

        if (err.response?.data?.message) {
            error.value = err.response.data.message
        } else {
            error.value = 'Failed to load marksheet.'
        }

    } finally {

        loading.value = false

    }
}

const goBack = () => {
    router.push('/parents/dashboard')
}

onMounted(() => {
    fetchMarksheet()
})

</script>

<template>

    <div
        class="min-h-screen bg-gray-100 dark:bg-gray-950 px-4 py-10"
    >

        <div class="max-w-5xl mx-auto">

            <!-- ================================================= -->
            <!-- HEADER -->
            <!-- ================================================= -->

            <div class="flex items-center justify-between mb-8">

                <div>

                    <h1
                        class="text-3xl font-bold text-gray-800 dark:text-white"
                    >
                        Student Marksheet
                    </h1>

                    <p
                        class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        View your child's academic results.
                    </p>

                </div>

                <button
                    @click="goBack"
                    class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white transition"
                >
                    ← Back
                </button>

            </div>


            <!-- ================================================= -->
            <!-- LOADING -->
            <!-- ================================================= -->

            <div
                v-if="loading"
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-12 text-center"
            >

                <div class="text-lg text-gray-600 dark:text-gray-300">
                    Loading marksheet...
                </div>

            </div>


            <!-- ================================================= -->
            <!-- ERROR -->
            <!-- ================================================= -->

            <div
                v-else-if="error"
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8"
            >

                <div
                    class="p-4 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300"
                >
                    {{ error }}
                </div>

                <button
                    @click="goBack"
                    class="mt-5 px-5 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white transition"
                >
                    Back to Dashboard
                </button>

            </div>


            <!-- ================================================= -->
            <!-- MARKSHEET -->
            <!-- ================================================= -->

            <div
                v-else-if="marksheet"
                class="space-y-6"
            >

                <!-- ================================================= -->
                <!-- STUDENT INFORMATION -->
                <!-- ================================================= -->

                <section
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8"
                >

                    <div class="flex items-center justify-between">

                        <div>

                            <h2
                                class="text-2xl font-bold text-gray-800 dark:text-white"
                            >
                                {{ marksheet.student?.name || '-' }}
                            </h2>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Academic Marksheet
                            </p>

                        </div>

                        <div
                            class="w-14 h-14 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-2xl"
                        >
                            🎓
                        </div>

                    </div>


                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8"
                    >

                        <div>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Symbol No.
                            </p>

                            <p
                                class="font-semibold text-gray-800 dark:text-white mt-1"
                            >
                                {{ marksheet.student?.symbol_no || '-' }}
                            </p>

                        </div>


                        <div>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Date of Birth
                            </p>

                            <p
                                class="font-semibold text-gray-800 dark:text-white mt-1"
                            >
                                {{ marksheet.student?.date_of_birth || '-' }}
                            </p>

                        </div>


                        <div>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Class
                            </p>

                            <p
                                class="font-semibold text-gray-800 dark:text-white mt-1"
                            >
                                {{ marksheet.student?.class || '-' }}
                            </p>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- SUBJECT MARKS -->
                <!-- ================================================= -->

                <section
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl overflow-hidden"
                >

                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">

                        <h2
                            class="text-xl font-bold text-gray-800 dark:text-white"
                        >
                            Subject Results
                        </h2>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full text-sm">

                            <thead
                                class="bg-gray-100 dark:bg-gray-800"
                            >

                                <tr>

                                    <th
                                        class="px-6 py-4 text-left text-gray-700 dark:text-gray-300"
                                    >
                                        S.N.
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-gray-700 dark:text-gray-300"
                                    >
                                        Subject
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-gray-700 dark:text-gray-300"
                                    >
                                        Full Marks
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-gray-700 dark:text-gray-300"
                                    >
                                        Pass Marks
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-gray-700 dark:text-gray-300"
                                    >
                                        Obtained
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <tr
                                    v-for="(item, index) in marksheet.items"
                                    :key="item.id"
                                    class="border-t border-gray-200 dark:border-gray-700"
                                >

                                    <td
                                        class="px-6 py-4 text-gray-700 dark:text-gray-300"
                                    >
                                        {{ index + 1 }}
                                    </td>

                                    <td
                                        class="px-6 py-4 font-medium text-gray-800 dark:text-white"
                                    >
                                        {{ item.subject_name || item.subject?.name || '-' }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-gray-700 dark:text-gray-300"
                                    >
                                        {{ item.full_marks }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-gray-700 dark:text-gray-300"
                                    >
                                        {{ item.pass_marks }}
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-gray-800 dark:text-white"
                                    >
                                        {{ item.marks }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- RESULT SUMMARY -->
                <!-- ================================================= -->

                <section
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8"
                >

                    <h2
                        class="text-xl font-bold text-gray-800 dark:text-white mb-6"
                    >
                        Result Summary
                    </h2>


                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5"
                    >

                        <div
                            class="rounded-xl bg-gray-100 dark:bg-gray-800 p-5"
                        >

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Total
                            </p>

                            <p
                                class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                            >
                                {{ marksheet.total }}
                            </p>

                        </div>


                        <div
                            class="rounded-xl bg-gray-100 dark:bg-gray-800 p-5"
                        >

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Percentage
                            </p>

                            <p
                                class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                            >
                                {{ marksheet.percentage }}%
                            </p>

                        </div>


                        <div
                            class="rounded-xl bg-gray-100 dark:bg-gray-800 p-5"
                        >

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Grade
                            </p>

                            <p
                                class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                            >
                                {{ marksheet.grade || '-' }}
                            </p>

                        </div>


                        <div
                            class="rounded-xl bg-gray-100 dark:bg-gray-800 p-5"
                        >

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Result
                            </p>

                            <p
                                class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                            >
                                {{ marksheet.result || '-' }}
                            </p>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- DOWNLOAD -->
                    <!-- ================================================= -->

                    <button
                        type="button"
                        @click="downloadMarksheetPdf(marksheet)"
                        class="w-full mt-8 px-5 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-medium transition"
                    >
                        Download Marksheet
                    </button>

                </section>

            </div>

        </div>

    </div>

</template>

