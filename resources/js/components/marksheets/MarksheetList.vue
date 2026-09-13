<script setup>

import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { downloadMarksheetPdf } from '../../utils/marksheetPdf'

import {
    getMarksheets,
    deleteMarksheet,
    importMarksheets,
    exportMarksheets
} from '../../services/marksheets/marksheetApi'

const marksheets = ref([])
const loading = ref(false)
const error = ref('')
const successMessage = ref('')
const router= useRouter()

// =========================================================
// CURRENT USER
// =========================================================

const user = ref(
    JSON.parse(localStorage.getItem('user') || 'null')
)

const isAdmin = computed(() => {
    return user.value?.role === 'admin'
})

const canEdit = computed(() => {
    return ['admin', 'teacher'].includes(user.value?.role)
})
const showDeleteModal = ref(false)
const deleteMarksheetId = ref(null)
const importInput = ref(null)
const importing = ref(false)

const downloadExcel = async (id = null) => {
    try {
        const response = await exportMarksheets(id)
        const blobUrl = URL.createObjectURL(response.data)
        const link = document.createElement('a')
        link.href = blobUrl
        link.download = id ? `marksheet-${id}.xlsx` : 'marksheets.xlsx'
        link.click()
        URL.revokeObjectURL(blobUrl)
    } catch (err) {
        error.value = 'Failed to export marksheet data.'
    }
}

const importExcel = async (event) => {
    const file = event.target.files?.[0]
    event.target.value = ''
    if (!file) return

    importing.value = true
    error.value = ''
    try {
        const formData = new FormData()
        formData.append('file', file)
        const response = await importMarksheets(formData)
        successMessage.value = response.data.message
        await fetchMarksheets()
    } catch (err) {
        const data = err.response?.data
        error.value = [data?.message, ...(data?.errors || [])].filter(Boolean).join(' ')
    } finally {
        importing.value = false
    }
}

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

// =========================================================
// DELETE MODAL
// =========================================================

const confirmDeleteMarksheet = (id) => {

    deleteMarksheetId.value = id
    showDeleteModal.value = true

}

// =========================================================
// DELETE MARKSHEET
// =========================================================

const deleteMarksheetItem = async (id) => {

    try {

        await deleteMarksheet(id)

        showDeleteModal.value = false
        deleteMarksheetId.value = null

        await fetchMarksheets()

        successMessage.value =
            'Marksheet deleted successfully.'

        setTimeout(() => {
            successMessage.value = ''
        }, 3000)

    } catch (err) {

        console.error(err)

        error.value = 'Failed to delete marksheet.'

    }

}

// =========================================================
// DOWNLOAD MARKSHEET
// =========================================================

const downloadMarksheet = (marksheet) => {
    downloadMarksheetPdf(marksheet)
}
const logout = () => {
    localStorage.removeItem('token')
    localStorage.removeItem('user')

    router.push('/login')
}

// =========================================================
// MOUNT
// =========================================================

onMounted(() => {
    fetchMarksheets()
})

</script>


<template>

<div
    class="p-6 lg:p-8 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors"
>

    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7"
    >

        <div>

            <h1
                class="text-2xl font-bold text-gray-900 dark:text-white"
            >
                Marksheets
            </h1>

            <p
                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
            >
                Create and manage student marksheets
            </p>

        </div>


        <div
            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3"
        >

            <!-- CREATE MARKSHEET -->

            <button
                v-if="canEdit"
                type="button"
                @click="$router.push('/marksheets/create')"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-purple-600 text-white text-sm font-semibold shadow-sm hover:bg-purple-700 hover:shadow transition-all"
            >

                <span class="text-lg leading-none">
                    +
                </span>

                Create Marksheet

            </button>

            <input ref="importInput" type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="importExcel" />
            <button
                v-if="canEdit"
                type="button"
                :disabled="importing"
                @click="importInput?.click()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60"
            >
                {{ importing ? 'Importing...' : 'Import Excel' }}
            </button>

            <button
                v-if="canEdit"
                type="button"
                @click="downloadExcel()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
            >
                Export All
            </button>


            <!-- DASHBOARD — ADMIN ONLY -->

<button
    v-if="isAdmin"
    type="button"
    @click="$router.push('/dashboard')"
    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-sm font-semibold shadow-sm hover:bg-gray-800 dark:hover:bg-gray-600 transition-all"
>
    ← Dashboard
</button>

<!-- Admin, Teacher & Parent -->
    <button
        @click="logout"
        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
    >
        Logout
    </button>


        </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
        v-if="error"
        class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg"
    >

        {{ error }}

    </div>


    <!-- =====================================================
         SUCCESS
    ====================================================== -->

    <div
        v-if="successMessage"
        class="mb-4"
    >

        <div
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800 text-sm font-medium"
        >

            <span>
                ✓
            </span>

            {{ successMessage }}

        </div>

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
                Loading marksheets...
            </p>

        </div>

    </div>


    <!-- =====================================================
         EMPTY STATE
    ====================================================== -->

    <div
        v-else-if="marksheets.length === 0"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-colors"
    >

        <div
            class="flex flex-col items-center justify-center py-12"
        >

            <div
                class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3 text-xl"
            >
                📄
            </div>

            <p
                class="font-medium text-gray-700 dark:text-gray-200"
            >
                No marksheets found
            </p>

            <p
                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
            >
                Create a marksheet to get started.
            </p>

        </div>

    </div>


    <!-- =====================================================
         MARKSHEETS TABLE
    ====================================================== -->

    <div
        v-else
        class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-colors"
    >

        <table class="w-full min-w-[1000px]">

            <!-- TABLE HEADER -->

            <thead
                class="bg-gray-50 dark:bg-gray-700"
            >

                <tr>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        S.N.
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        Student
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        Class
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        Total
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        Percentage
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        Grade
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        Result
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-200"
                    >
                        Actions
                    </th>

                </tr>

            </thead>


            <!-- TABLE BODY -->

            <tbody>

                <tr
                    v-for="(marksheet, index) in marksheets"
                    :key="marksheet.id"
                    class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                >

                    <!-- S.N. -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{ index + 1 }}
                    </td>


                    <!-- STUDENT -->

                    <td
                        class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100"
                    >
                        {{ marksheet.student?.name || '-' }}
                    </td>


                    <!-- CLASS -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{ marksheet.student?.class || '-' }}
                    </td>


                    <!-- TOTAL -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{ marksheet.total }}
                    </td>


                    <!-- PERCENTAGE -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >

                        <span
                            class="font-medium"
                        >
                            {{ marksheet.percentage }}%
                        </span>

                    </td>


                    <!-- GRADE -->

                    <td class="px-4 py-3">

                        <span
                            class="inline-flex items-center justify-center min-w-10 px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-sm font-semibold"
                        >
                            {{ marksheet.grade || '-' }}
                        </span>

                    </td>


                    <!-- RESULT -->

                    <td class="px-4 py-3">

                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium"
                            :class="
                                marksheet.result === 'Pass'
                                    ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                    : marksheet.result === 'Fail'
                                        ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                            "
                        >
                            {{ marksheet.result || '-' }}
                        </span>

                    </td>


                    <!-- ACTIONS -->

                    <td class="px-4 py-3">

                        <div
                            class="flex items-center gap-2"
                        >

                            <!-- VIEW -->

                            <button
                                type="button"
                                @click="
                                    $router.push(
                                        `/marksheets/${marksheet.id}`
                                    )
                                "
                                class="px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 text-sm font-medium hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                            >
                                View
                            </button>

                            <button
                                v-if="canEdit"
                                type="button"
                                @click="downloadExcel(marksheet.id)"
                                class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-medium hover:bg-emerald-100 transition"
                            >
                                Excel
                            </button>


                            <!-- EDIT -->

                            <button
                                v-if="canEdit"
                                type="button"
                                @click="
                                    $router.push(
                                        `/marksheets/${marksheet.id}/edit`
                                    )
                                "
                                class="px-3 py-1.5 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-sm font-medium hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition"
                            >
                                Edit
                            </button>


                            <!-- DOWNLOAD -->

                            <button
                                type="button"
                                @click="
                                    downloadMarksheet(
                                        marksheet
                                    )
                                "
                                class="px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm font-medium hover:bg-green-100 dark:hover:bg-green-900/50 transition"
                            >
                                Download
                            </button>


                            <!-- DELETE -->

<button
    v-if="isAdmin"
    type="button"
    @click="
        confirmDeleteMarksheet(
            marksheet.id
        )
    "
    class="px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 text-sm font-medium hover:bg-red-100 dark:hover:bg-red-900/50 transition"
>
    Delete
</button>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>


    <!-- =====================================================
         DELETE CONFIRMATION MODAL
    ====================================================== -->

    <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 px-4"
    >

        <div
            class="w-full max-w-md rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl transition-colors"
        >

            <!-- HEADER -->

            <div
                class="flex items-center gap-3 mb-4"
            >

                <div
                    class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0"
                >
                    ⚠️
                </div>

                <h2
                    class="text-xl font-semibold text-gray-900 dark:text-white"
                >
                    Delete Marksheet
                </h2>

            </div>


            <!-- MESSAGE -->

            <p
                class="text-gray-600 dark:text-gray-300"
            >
                Are you sure you want to delete this marksheet?
            </p>

            <p
                class="text-sm text-gray-500 dark:text-gray-400 mt-2"
            >
                This action cannot be undone.
            </p>


            <!-- ACTIONS -->

            <div
                class="mt-6 flex justify-end gap-3"
            >

                <!-- CANCEL -->

                <button
                    type="button"
                    @click="
                        showDeleteModal = false
                    "
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    Cancel
                </button>


                <!-- DELETE -->

                <button
                    type="button"
                    @click="
                        deleteMarksheetItem(
                            deleteMarksheetId
                        )
                    "
                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
                >
                    Delete
                </button>

            </div>

        </div>

    </div>

</div>

</template>