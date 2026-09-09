<script setup>

import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getMarksheet } from '../../services/marksheets/marksheetApi'

const route = useRoute()
const router = useRouter()

const goBackToMarksheets = () => {
    if (localStorage.getItem('teacher_token')) {
        router.push('/teacher/marksheets')
    } else {
        router.push('/marksheets')
    }
}

const marksheet = ref(null)
const loading = ref(false)
const error = ref('')

const fetchMarksheet = async () => {

    loading.value = true
    error.value = ''

    try {

        const response = await getMarksheet(route.params.id)

        marksheet.value = response.data

    } catch (err) {

        console.error(err)

        error.value = 'Failed to load marksheet.'

    } finally {

        loading.value = false

    }

}

onMounted(() => {
    fetchMarksheet()
})

</script>


<template>

<div
    class="p-6 lg:p-8 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors"
>

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7"
    >

        <div>

            <h1
                class="text-2xl font-bold text-gray-900 dark:text-white"
            >
                Marksheet
            </h1>

            <p
                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
            >
                View student marksheet details and results
            </p>

        </div>


        <!-- BACK BUTTON -->

        <button
            type="button"
            @click="goBackToMarksheets"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-gray-800 dark:bg-gray-700 text-white text-sm font-semibold shadow-sm hover:bg-gray-900 dark:hover:bg-gray-600 transition"
        >
            ← Back to Marksheets
        </button>

    </div>


    <!-- =====================================================
         LOADING
    ====================================================== -->

    <div
        v-if="loading"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm py-12 transition-colors"
    >

        <div
            class="flex flex-col items-center justify-center"
        >

            <div
                class="w-8 h-8 border-4 border-gray-200 dark:border-gray-600 border-t-purple-600 rounded-full animate-spin mb-3"
            ></div>

            <p
                class="text-sm text-gray-500 dark:text-gray-400"
            >
                Loading marksheet...
            </p>

        </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
        v-else-if="error"
        class="p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-xl"
    >
        {{ error }}
    </div>


    <!-- =====================================================
         MARKSHEET
    ====================================================== -->

    <div
        v-else-if="marksheet"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-colors"
    >

        <!-- =================================================
             STUDENT INFORMATION
        ================================================== -->

        <div
            class="p-6 border-b border-gray-200 dark:border-gray-700"
        >

            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >

                <div>

                    <h2
                        class="text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        Student Information
                    </h2>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Details of the student associated with this marksheet
                    </p>

                </div>

            </div>


            <!-- STUDENT INFO CARDS -->

            <div
                class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6"
            >

                <!-- STUDENT -->

                <div
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Student
                    </p>

                    <p
                        class="mt-1 text-base font-semibold text-gray-900 dark:text-white"
                    >
                        {{ marksheet.student?.name || '-' }}
                    </p>

                </div>


                <!-- CLASS -->

                <div
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Class
                    </p>

                    <p
                        class="mt-1 text-base font-semibold text-gray-900 dark:text-white"
                    >
                        {{ marksheet.student?.class || '-' }}
                    </p>

                </div>

            </div>

        </div>


        <!-- =================================================
             SUBJECT MARKS
        ================================================== -->

        <div class="p-6">

            <div class="mb-4">

                <h2
                    class="text-xl font-semibold text-gray-900 dark:text-white"
                >
                    Subject Marks
                </h2>

                <p
                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                >
                    Marks obtained in each subject
                </p>

            </div>


            <!-- TABLE -->

            <div
                class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl"
            >

                <table class="w-full min-w-[600px]">

                    <!-- HEADER -->

                    <thead
                        class="bg-gray-50 dark:bg-gray-700"
                    >

                        <tr>

                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                S.N.
                            </th>

                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                Subject
                            </th>

                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                Marks
                            </th>

                        </tr>

                    </thead>


                    <!-- BODY -->

                    <tbody>

                        <tr
                            v-for="(item, index) in marksheet.items"
                            :key="item.id"
                            class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                        >

                            <!-- S.N. -->

                            <td
                                class="px-4 py-3 text-gray-700 dark:text-gray-300"
                            >
                                {{ index + 1 }}
                            </td>


                            <!-- SUBJECT -->

                            <td
                                class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100"
                            >
                                {{ item.subject?.name || '-' }}
                            </td>


                            <!-- MARKS -->

                            <td
                                class="px-4 py-3 text-gray-700 dark:text-gray-300"
                            >
                                {{ item.marks }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- =================================================
                 RESULT SUMMARY
            ================================================== -->

            <div class="mt-6">

                <h2
                    class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
                >
                    Result Summary
                </h2>


                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                >

                    <!-- TOTAL -->

                    <div
                        class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                    >

                        <p
                            class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            Total
                        </p>

                        <p
                            class="mt-1 text-xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ marksheet.total }}
                        </p>

                    </div>


                    <!-- PERCENTAGE -->

                    <div
                        class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                    >

                        <p
                            class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            Percentage
                        </p>

                        <p
                            class="mt-1 text-xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ marksheet.percentage }}%
                        </p>

                    </div>


                    <!-- GRADE -->

                    <div
                        class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/30 border border-purple-100 dark:border-purple-800"
                    >

                        <p
                            class="text-xs font-medium uppercase tracking-wide text-purple-600 dark:text-purple-300"
                        >
                            Grade
                        </p>

                        <p
                            class="mt-1 text-xl font-bold text-purple-700 dark:text-purple-200"
                        >
                            {{ marksheet.grade || '-' }}
                        </p>

                    </div>


                    <!-- RESULT -->

                    <div
                        class="p-4 rounded-xl border"
                        :class="
                            marksheet.result === 'Pass'
                                ? 'bg-green-50 dark:bg-green-900/30 border-green-100 dark:border-green-800'
                                : marksheet.result === 'Fail'
                                    ? 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-800'
                                    : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600'
                        "
                    >

                        <p
                            class="text-xs font-medium uppercase tracking-wide"
                            :class="
                                marksheet.result === 'Pass'
                                    ? 'text-green-600 dark:text-green-300'
                                    : marksheet.result === 'Fail'
                                        ? 'text-red-600 dark:text-red-300'
                                        : 'text-gray-500 dark:text-gray-400'
                            "
                        >
                            Result
                        </p>

                        <p
                            class="mt-1 text-xl font-bold"
                            :class="
                                marksheet.result === 'Pass'
                                    ? 'text-green-700 dark:text-green-200'
                                    : marksheet.result === 'Fail'
                                        ? 'text-red-700 dark:text-red-200'
                                        : 'text-gray-900 dark:text-white'
                            "
                        >
                            {{ marksheet.result || '-' }}
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</template>