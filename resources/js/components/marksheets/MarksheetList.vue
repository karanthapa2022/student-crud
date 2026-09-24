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
    <main class="min-h-screen bg-paper">
        <!-- HEADER -->
        <header class="border-b border-hairline bg-paper">
            <div class="mx-auto max-w-7xl px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center border border-hairline bg-surface text-sm font-semibold text-forest"
                            >
                                MR
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.16em] text-ink-soft">
                                    Student Portal
                                </p>

                                <p class="text-xs text-ink-soft">
                                    Academic Administration
                                </p>
                            </div>
                        </div>

                        <p class="mb-2 text-sm font-medium text-forest">
                            Academic management
                        </p>

                        <h1 class="text-3xl font-semibold text-ink">
                            Marksheets
                        </h1>

                        <p class="mt-2 text-sm text-ink-soft">
                            Create, review and manage student results.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="canEdit"
                            type="button"
                            @click="$router.push('/marksheets/create')"
                            class="bg-forest px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
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
                            class="border border-hairline bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-forest hover:text-forest disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ importing ? 'Importing...' : 'Import Excel' }}
                        </button>

                        <button
                            v-if="canEdit"
                            type="button"
                            @click="downloadExcel()"
                            class="border border-hairline bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-forest hover:text-forest"
                        >
                            Export
                        </button>

                        <button
                            type="button"
                            @click="$router.push('/dashboard')"
                            class="border border-hairline bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-forest hover:text-forest"
                        >
                            Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="mx-auto max-w-7xl px-6 py-8 lg:px-8">

            <!-- MESSAGES -->
            <div
                v-if="error"
                class="mb-5 border border-sienna/30 bg-sienna/5 px-4 py-3 text-sm text-sienna"
            >
                {{ error }}
            </div>

            <div
                v-if="successMessage"
                class="mb-5 border border-forest/30 bg-forest/5 px-4 py-3 text-sm text-forest"
            >
                ✓ {{ successMessage }}
            </div>

            <!-- LOADING -->
            <div
                v-if="loading"
                class="border border-hairline bg-surface py-16 text-center"
            >
                <div
                    class="mx-auto mb-4 h-7 w-7 animate-spin border-2 border-hairline border-t-forest"
                ></div>

                <p class="text-sm text-ink-soft">
                    Loading marksheets...
                </p>
            </div>

            <!-- EMPTY -->
            <div
                v-else-if="marksheets.length === 0"
                class="border border-hairline bg-surface px-6 py-16 text-center"
            >
                <p class="text-2xl font-semibold text-ink">
                    No marksheets yet
                </p>

                <p class="mt-2 text-sm text-ink-soft">
                    Create a marksheet to begin building the academic register.
                </p>

                <button
                    v-if="canEdit"
                    type="button"
                    @click="$router.push('/marksheets/create')"
                    class="mt-6 bg-forest px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                >
                    Create marksheet
                </button>
            </div>

            <!-- MARKSHEET REGISTER -->
            <section
                v-else
                class="overflow-hidden border border-hairline bg-surface"
            >
                <!-- SECTION HEADER -->
                <div
                    class="flex flex-col gap-2 border-b border-hairline px-5 py-5 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.14em] text-ink-soft">
                            Academic register
                        </p>

                        <h2 class="mt-1 text-xl font-semibold text-ink">
                            Student results
                        </h2>
                    </div>

                    <p class="text-sm text-ink-soft">
                        {{ marksheets.length }} marksheet{{ marksheets.length === 1 ? '' : 's' }}
                    </p>
                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px]">
                        <thead class="border-b border-hairline bg-paper">
                            <tr>
                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    S.N.
                                </th>

                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    Student
                                </th>

                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    Class
                                </th>

                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    Total
                                </th>

                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    Percentage
                                </th>

                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    Grade
                                </th>

                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    Result
                                </th>

                                <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-ink-soft">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(marksheet, index) in marksheets"
                                :key="marksheet.id"
                                class="border-b border-hairline last:border-b-0 transition hover:bg-paper/60"
                            >
                                <td class="px-4 py-4 text-sm text-ink-soft">
                                    {{ index + 1 }}
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-base font-semibold text-ink">
                                        {{ marksheet.student?.name || '-' }}
                                    </p>
                                </td>

                                <td class="px-4 py-4 text-sm text-ink">
                                    {{ marksheet.student?.class || '-' }}
                                </td>

                                <td class="px-4 py-4 text-sm font-semibold text-ink">
                                    {{ marksheet.total }}
                                </td>

                                <td class="px-4 py-4 text-sm text-ink">
                                    {{ marksheet.percentage }}%
                                </td>

                                <td class="px-4 py-4">
                                    <span class="font-semibold text-forest">
                                        {{ marksheet.grade || '-' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <span
                                        class="font-semibold"
                                        :class="
                                            marksheet.result === 'Pass'
                                                ? 'text-forest'
                                                : marksheet.result === 'Fail'
                                                    ? 'text-sienna'
                                                    : 'text-ink-soft'
                                        "
                                    >
                                        {{ marksheet.result || '-' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            @click="$router.push(`/marksheets/${marksheet.id}`)"
                                            class="border border-hairline px-3 py-1.5 text-xs font-semibold text-forest transition hover:border-forest hover:bg-forest/5"
                                        >
                                            View
                                        </button>

                                        <button
                                            v-if="canEdit"
                                            type="button"
                                            @click="downloadExcel(marksheet.id)"
                                            class="border border-hairline px-3 py-1.5 text-xs font-semibold text-ink-soft transition hover:border-forest hover:text-forest"
                                        >
                                            Excel
                                        </button>

                                        <button
                                            v-if="canEdit"
                                            type="button"
                                            @click="$router.push(`/marksheets/${marksheet.id}/edit`)"
                                            class="border border-hairline px-3 py-1.5 text-xs font-semibold text-ink-soft transition hover:border-forest hover:text-forest"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            @click="downloadMarksheet(marksheet)"
                                            class="border border-hairline px-3 py-1.5 text-xs font-semibold text-forest transition hover:border-forest hover:bg-forest/5"
                                        >
                                            Download
                                        </button>

                                        <button
                                            v-if="isAdmin"
                                            type="button"
                                            @click="confirmDeleteMarksheet(marksheet.id)"
                                            class="border border-sienna/30 px-3 py-1.5 text-xs font-semibold text-sienna transition hover:bg-sienna/5"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER -->
                <div class="border-t border-hairline px-5 py-4">
                    <p class="text-xs text-ink-soft">
                        Marksheets are part of the academic student register.
                    </p>
                </div>
            </section>

            <!-- DELETE MODAL -->
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            >
                <div class="w-full max-w-md border border-hairline bg-surface">
                    <!-- MODAL HEADER -->
                    <div class="border-b border-hairline px-6 py-5">
                        <p class="text-xs font-medium uppercase tracking-[0.14em] text-sienna">
                            Confirmation
                        </p>

                        <h2 class="mt-1 text-2xl font-semibold text-ink">
                            Delete marksheet?
                        </h2>
                    </div>

                    <!-- MODAL BODY -->
                    <div class="px-6 py-5">
                        <p class="text-sm leading-6 text-ink-soft">
                            This action cannot be undone. The selected marksheet
                            will be permanently removed.
                        </p>
                    </div>

                    <!-- MODAL FOOTER -->
                    <div class="flex justify-end gap-3 border-t border-hairline px-6 py-5">
                        <button
                            type="button"
                            @click="showDeleteModal = false"
                            class="border border-hairline bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-forest hover:text-forest"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            @click="deleteMarksheetItem(deleteMarksheetId)"
                            class="bg-sienna px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                        >
                            Delete marksheet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
