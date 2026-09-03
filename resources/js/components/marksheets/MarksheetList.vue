<script setup>

import { ref, onMounted } from 'vue'
import { downloadMarksheetPdf } from '../../utils/marksheetPdf'


import {
    getMarksheets, deleteMarksheet
} from '../../services/marksheets/marksheetApi'

const marksheets = ref([])
const loading = ref(false)
const error = ref('')
const successMessage= ref('')

const showDeleteModal= ref(false)
const deleteMarksheetId= ref(null)


// =========================================================
// FETCH MARKSHEETS
// =========================================================

const fetchMarksheets = async () => {

    loading.value = true
    error.value = ''

    try {

        const response = await getMarksheets()

        marksheets.value = response.data

    } catch (err) {

        console.error(err)

        error.value = 'Failed to load marksheets.'

    } finally {

        loading.value = false

    }
}
const confirmDeleteMarksheet = (id) => {

    deleteMarksheetId.value = id
    showDeleteModal.value = true

}


const deleteMarksheetItem = async (id) => {

    try {

        await deleteMarksheet(id)
        showDeleteModal.value=false
        deleteMarksheetId.value=null

        await fetchMarksheets()

        successMessage.value = 'Marksheet deleted successfully.'

        setTimeout(() => {
            successMessage.value = ''
        }, 3000)

    } catch (err) {

        console.error(err)

        error.value = 'Failed to delete marksheet.'

    }

}
const downloadMarksheet = (marksheet) => {
    downloadMarksheetPdf(marksheet)
}

// =========================================================
// MOUNT
// =========================================================

onMounted(() => {
    fetchMarksheets()
})

</script>


<template>

    <div class="p-6">

        <!-- PAGE HEADER -->

        <div class="flex justify-between items-center mb-6">

            <div>

                <h1 class="text-2xl font-bold">
                    Marksheets
                </h1>

                <p class="text-gray-500">
                    Create and manage student marksheets
                </p>

            </div>

            <div class="flex flex-col items-end gap-3">

    <button
        type="button"
        @click="$router.push('/marksheets/create')"
        class="px-4 py-2 rounded-lg bg-purple-600 text-white font-medium hover:bg-purple-700 transition"
    >
        Create Marksheet
    </button>

    <button
        type="button"
        @click="$router.push('/students')"
        class="px-4 py-2 rounded-lg bg-gray-800 text-white font-medium hover:bg-gray-900 transition"
    >
        ← Students
    </button>

</div>

        </div>


        <!-- ERROR -->

        <div
            v-if="error"
            class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"
        >
            {{ error }}
        </div>
        <div
    v-if="successMessage"
    class="mb-4 flex justify-start"
>
    <button
        type="button"
        class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium shadow-sm cursor-default"
    >
        ✓ {{ successMessage }}
    </button>
</div>


        <!-- LOADING -->

        <div
            v-if="loading"
            class="text-gray-500"
        >
            Loading marksheets...
        </div>


        <!-- EMPTY STATE -->

        <div
            v-else-if="marksheets.length === 0"
            class="p-8 text-center bg-gray-50 rounded-lg"
        >

            <p class="text-gray-500">
                No marksheets found.
            </p>

        </div>


        <!-- MARKSHEETS -->

        <div
            v-else
            class="bg-white rounded-lg shadow overflow-hidden"
        >

            <table class="w-full">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-4 py-3 text-left">
                            S.N.
                        </th>

                        <th class="px-4 py-3 text-left">
                            Student
                        </th>

                        <th class="px-4 py-3 text-left">
                            Class
                        </th>

                        <th class="px-4 py-3 text-left">
                            Total
                        </th>

                        <th class="px-4 py-3 text-left">
                            Percentage
                        </th>

                        <th class="px-4 py-3 text-left">
                            Grade
                        </th>

                        <th class="px-4 py-3 text-left">
                            Result
                        </th>

                        <th class="px-4 py-3 text-left">
    Actions
</th>

                    </tr>

                </thead>

                <tbody>

                    <tr
                        v-for="(marksheet, index) in marksheets"
                        :key="marksheet.id"
                        class="border-t hover:bg-gray-50"
                    >

                        <td class="px-4 py-3">
                            {{ index + 1 }}
                        </td>

                        <td class="px-4 py-3 font-medium">
                            {{ marksheet.student?.name || '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ marksheet.student?.class || '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ marksheet.total }}
                        </td>

                        <td class="px-4 py-3">
                            {{ marksheet.percentage }}%
                        </td>

                        <td class="px-4 py-3">
                            {{ marksheet.grade || '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ marksheet.result || '-' }}
                        </td>
                        <td class="px-4 py-3">
    <div class="flex gap-2">

        <button
            type="button"
            @click="$router.push(`/marksheets/${marksheet.id}`)"
            class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 transition"
        >
            View
        </button>

        <button
            type="button"
            @click="$router.push(`/marksheets/${marksheet.id}/edit`)"
            class="px-3 py-1.5 rounded-lg bg-yellow-500 text-white text-sm hover:bg-yellow-600 transition"
        >
            Edit
        </button>

        <button
    type="button"
    @click="downloadMarksheet(marksheet)"
    class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-sm hover:bg-green-700 transition"
>
    Download
</button>

        <button
    type="button"
    @click="confirmDeleteMarksheet(marksheet.id)"
    class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700 transition"
>
    Delete
</button>


    </div>
</td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>
    <!-- Delete Confirmation Modal -->
<div
    v-if="showDeleteModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
>
    <div
        class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-xl"
    >

        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Delete Marksheet
        </h2>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Are you sure you want to delete this marksheet?
        </p>

        <div class="mt-6 flex justify-end gap-3">

            <button
                type="button"
                @click="showDeleteModal = false"
                class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition"
            >
                Cancel
            </button>

            <button
                type="button"
                @click="deleteMarksheetItem(deleteMarksheetId)"
                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
            >
                Delete
            </button>

        </div>

    </div>
</div>

</template>