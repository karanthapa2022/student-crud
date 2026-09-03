<script setup>

import { ref } from 'vue'
import { searchParentMarksheet } from '../../services/marksheets/marksheetApi'
import { downloadMarksheetPdf } from '../../utils/marksheetPdf'

const name = ref('')
const symbolNo = ref('')
const dateOfBirth = ref('')

const loading = ref(false)
const error = ref('')
const marksheet= ref(null)

const searchMarksheet = async () => {

    error.value = ''

    if (!name.value || !symbolNo.value || !dateOfBirth.value) {
        error.value = 'Please enter all details.'
        return
    }

    loading.value = true

    try {

        const response = await searchParentMarksheet({
            name: name.value,
            symbol_no: symbolNo.value,
            date_of_birth: dateOfBirth.value
        })

        marksheet.value=response.data

    } catch (err) {

        console.error(err)

        if (err.response?.data?.message) {
            error.value = err.response.data.message
        } else {
            error.value = 'Failed to search marksheet.'
        }

    } finally {

        loading.value = false

    }

}

</script>

<template>

    <div
        class="min-h-screen bg-gray-100 dark:bg-gray-950 flex items-center justify-center px-4 py-10"
    >

        <div class="w-full max-w-lg">

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8"
            >

                <!-- HEADER -->

                <div class="text-center mb-8">

                    <h1
                        class="text-2xl font-bold text-gray-800 dark:text-white"
                    >
                        Parent Marksheet Portal
                    </h1>

                    <p
                        class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        Enter your child's details to view the marksheet.
                    </p>

                </div>


                <!-- FORM -->

                <form
                    @submit.prevent="searchMarksheet"
                    class="space-y-5"
                >

                    <!-- CHILD NAME -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Child Name
                        </label>

                        <input
                            v-model="name"
                            type="text"
                            placeholder="Enter child's full name"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        >

                    </div>


                    <!-- SYMBOL NUMBER -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Symbol No.
                        </label>

                        <input
                            v-model="symbolNo"
                            type="text"
                            placeholder="Enter symbol number"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        >

                    </div>


                    <!-- DATE OF BIRTH -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Date of Birth
                        </label>

                        <input
                            v-model="dateOfBirth"
                            type="date"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        >

                    </div>


                    <!-- ERROR -->

                    <div
                        v-if="error"
                        class="p-3 rounded-xl bg-red-100 text-red-700 text-sm"
                    >
                        {{ error }}
                    </div>


                    <!-- SEARCH BUTTON -->

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full px-5 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-medium transition disabled:opacity-50"
                    >
                        {{ loading ? 'Searching...' : 'Search Marksheet' }}
                    </button>

                </form>
                <!-- MARKSHEET RESULT -->

<div
    v-if="marksheet"
    class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6"
>

    <h2
        class="text-xl font-bold text-gray-800 dark:text-white"
    >
        Marksheet Found
    </h2>

    <div class="mt-4 space-y-2">

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Student:</strong>
            {{ marksheet.student?.name || '-' }}
        </p>

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Symbol No.:</strong>
            {{ marksheet.student?.symbol_no || '-' }}
        </p>

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Date of Birth:</strong>
            {{ marksheet.student?.date_of_birth || '-' }}
        </p>

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Class:</strong>
            {{ marksheet.student?.class || '-' }}
        </p>

    </div>


    <!-- SUBJECTS -->

    <div class="mt-6 overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-gray-100 dark:bg-gray-800">

                <tr>

                    <th class="px-3 py-3 text-left">
                        S.N.
                    </th>

                    <th class="px-3 py-3 text-left">
                        Subject
                    </th>

                    <th class="px-3 py-3 text-left">
                        Marks
                    </th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="(item, index) in marksheet.items"
                    :key="item.id"
                    class="border-t border-gray-200 dark:border-gray-700"
                >

                    <td class="px-3 py-3">
                        {{ index + 1 }}
                    </td>

                    <td class="px-3 py-3">
                        {{ item.subject?.name || '-' }}
                    </td>

                    <td class="px-3 py-3 font-medium">
                        {{ item.marks }}
                    </td>

                </tr>

            </tbody>

        </table>

    </div>


    <!-- SUMMARY -->

    <div class="mt-6 space-y-2">

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Total:</strong>
            {{ marksheet.total }}
        </p>

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Percentage:</strong>
            {{ marksheet.percentage }}%
        </p>

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Grade:</strong>
            {{ marksheet.grade || '-' }}
        </p>

        <p class="text-gray-700 dark:text-gray-300">
            <strong>Result:</strong>
            {{ marksheet.result || '-' }}
        </p>

    </div>
    <button
    type="button"
    @click="downloadMarksheetPdf(marksheet)"
    class="w-full mt-6 px-5 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-medium transition"
>
    Download Marksheet
</button>

</div>

            </div>

        </div>

    </div>

</template>