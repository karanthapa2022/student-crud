<script setup>

import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { downloadMarksheetPdf } from '../../utils/marksheetPdf'
import { clearAuthSessions } from '../../services/apiConfig'

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
const route = useRoute()
const studentFilter = computed(() => route.query.student_id || null)

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

        const response = await getMarksheets(studentFilter.value)

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
    clearAuthSessions()

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
    <div class="min-h-screen bg-[#EFF1EA] text-[#1C2B24] dark:bg-[#17221D] dark:text-[#F5F7F3]">
        <div class="mx-auto w-full max-w-7xl px-5 py-7 lg:px-8">

            <!-- HEADER -->
            <header class="mb-8 border-b border-[#D8DDD3] pb-6 dark:border-[#39483F]">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">

                    <div>
                        <p class="mb-2 text-sm font-medium text-[#2F6F4E] dark:text-[#75B28F]">
                            Academic records
                        </p>

                        <h1 class="text-3xl font-semibold">
                            Marksheets
                        </h1>

                        <p class="mt-2 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                            Create, review and manage student results.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="canEdit"
                            type="button"
                            @click="$router.push('/marksheets/create')"
                            class="bg-[#2F6F4E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#275E42]"
                        >
                            + Create marksheet
                        </button>

                        <input
                            ref="importInput"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="hidden"
                            @change="importExcel"
                        />

                        <button
                            v-if="canEdit"
                            type="button"
                            :disabled="importing"
                            @click="importInput?.click()"
                            class="border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-semibold hover:border-[#2F6F4E] hover:text-[#2F6F4E] disabled:opacity-50 dark:border-[#39483F] dark:bg-[#202D26] dark:hover:border-[#75B28F] dark:hover:text-[#75B28F]"
                        >
                            {{ importing ? 'Importing...' : 'Import Excel' }}
                        </button>

                        <button
                            v-if="canEdit"
                            type="button"
                            @click="downloadExcel()"
                            class="border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-semibold hover:border-[#2F6F4E] hover:text-[#2F6F4E] dark:border-[#39483F] dark:bg-[#202D26]"
                        >
                            Export
                        </button>

                        <button
                            
                            type="button"
                            @click="$router.push('/dashboard')"
                            class="border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-semibold hover:border-[#2F6F4E] hover:text-[#2F6F4E] dark:border-[#39483F] dark:bg-[#202D26]"
                        >
                            Dashboard
                        </button>

                        <button
                            type="button"
                            @click="logout"
                            class="border border-[#B5563C]/40 px-4 py-2.5 text-sm font-semibold text-[#B5563C] hover:bg-[#B5563C]/5"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </header>

            <!-- MESSAGES -->
            <div
                v-if="error"
                class="mb-5 border border-[#B5563C]/30 bg-[#B5563C]/5 px-4 py-3 text-sm text-[#8F3F2B] dark:bg-[#B5563C]/10 dark:text-[#E6A18E]"
            >
                {{ error }}
            </div>

            <div
                v-if="successMessage"
                class="mb-5 border border-[#2F6F4E]/30 bg-[#2F6F4E]/5 px-4 py-3 text-sm text-[#2F6F4E] dark:text-[#A8D1B8]"
            >
                ✓ {{ successMessage }}
            </div>

            <!-- LOADING -->
            <div
                v-if="loading"
                class="border border-[#D8DDD3] bg-white py-16 text-center dark:border-[#39483F] dark:bg-[#202D26]"
            >
                <div class="mx-auto mb-4 h-7 w-7 animate-spin border-2 border-[#D8DDD3] border-t-[#2F6F4E]"></div>
                <p class="text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                    Loading marksheets...
                </p>
            </div>

            <!-- EMPTY -->
            <div
                v-else-if="marksheets.length === 0"
                class="border border-[#D8DDD3] bg-white px-6 py-16 text-center dark:border-[#39483F] dark:bg-[#202D26]"
            >
                <p class="text-2xl font-semibold">
                    No marksheets yet
                </p>

                <p class="mt-2 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                    Create a marksheet to begin building the academic register.
                </p>

                <button
                    v-if="canEdit"
                    type="button"
                    @click="$router.push('/marksheets/create')"
                    class="mt-6 bg-[#2F6F4E] px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Create marksheet
                </button>
            </div>

            <!-- TABLE -->
            <div
                v-else
                class="overflow-x-auto border border-[#D8DDD3] bg-white dark:border-[#39483F] dark:bg-[#202D26]"
            >
                <table class="w-full min-w-[1100px]">
                    <thead class="border-b border-[#D8DDD3] bg-[#EFF1EA] dark:border-[#39483F] dark:bg-[#17221D]">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">S.N.</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">Student</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">Class</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">Total</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">Percentage</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">Grade</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">Result</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-[#5B6B62]">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(marksheet, index) in marksheets"
                            :key="marksheet.id"
                            class="border-b border-[#D8DDD3] last:border-b-0 hover:bg-[#EFF1EA]/50 dark:border-[#39483F] dark:hover:bg-[#17221D]"
                        >
                            <td class="px-4 py-4 text-sm text-[#5B6B62]">
                                {{ index + 1 }}
                            </td>

                            <td class="px-4 py-4">
                                <p class=" text-lg font-semibold">
                                    {{ marksheet.student?.name || '-' }}
                                </p>
                            </td>

                            <td class="px-4 py-4 text-sm">
                                {{ marksheet.student?.class || '-' }}
                            </td>

                            <td class="px-4 py-4 text-sm font-semibold">
                                {{ marksheet.total }}
                            </td>

                            <td class="px-4 py-4 text-sm">
                                {{ marksheet.percentage }}%
                            </td>

                            <td class="px-4 py-4">
                                <span class="font-semibold text-[#2F6F4E] dark:text-[#75B28F]">
                                    {{ marksheet.grade || '-' }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <span
                                    class="font-semibold"
                                    :class="
                                        marksheet.result === 'Pass'
                                            ? 'text-[#2F6F4E] dark:text-[#75B28F]'
                                            : marksheet.result === 'Fail'
                                                ? 'text-[#B5563C]'
                                                : 'text-[#5B6B62]'
                                    "
                                >
                                    {{ marksheet.result || '-' }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm">
                                    <button
                                        type="button"
                                        @click="$router.push(`/marksheets/${marksheet.id}`)"
                                        class="font-semibold text-[#2F6F4E] hover:underline dark:text-[#75B28F]"
                                    >
                                        View
                                    </button>

                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        @click="downloadExcel(marksheet.id)"
                                        class="font-semibold text-[#5B6B62] hover:text-[#1C2B24] hover:underline dark:text-[#AEBBB3] dark:hover:text-white"
                                    >
                                        Excel
                                    </button>

                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        @click="$router.push(`/marksheets/${marksheet.id}/edit`)"
                                        class="font-semibold text-[#5B6B62] hover:text-[#1C2B24] hover:underline dark:text-[#AEBBB3] dark:hover:text-white"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        @click="downloadMarksheet(marksheet)"
                                        class="font-semibold text-[#2F6F4E] hover:underline dark:text-[#75B28F]"
                                    >
                                        Download
                                    </button>

                                    <button
                                        v-if="isAdmin"
                                        type="button"
                                        @click="confirmDeleteMarksheet(marksheet.id)"
                                        class="font-semibold text-[#B5563C] hover:underline"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- DELETE MODAL -->
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C2B24]/60 px-4"
            >
                <div class="w-full max-w-md border border-[#D8DDD3] bg-white p-6 dark:border-[#39483F] dark:bg-[#202D26]">
                    <p class="text-sm font-medium text-[#B5563C]">
                        Confirmation
                    </p>

                    <h2 class="mt-1 font-serif text-2xl font-semibold">
                        Delete marksheet?
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-[#5B6B62] dark:text-[#AEBBB3]">
                        This action cannot be undone. The selected marksheet will be permanently removed.
                    </p>

                    <div class="mt-6 flex justify-end gap-3 border-t border-[#D8DDD3] pt-5 dark:border-[#39483F]">
                        <button
                            type="button"
                            @click="showDeleteModal = false"
                            class="border border-[#D8DDD3] px-4 py-2.5 text-sm font-semibold dark:border-[#39483F]"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            @click="deleteMarksheetItem(deleteMarksheetId)"
                            class="bg-[#B5563C] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#9F4932]"
                        >
                            Delete marksheet
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>