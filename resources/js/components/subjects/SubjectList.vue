<script setup>

import { ref, onMounted, watch } from 'vue'
import { useSubjectStore } from '../../stores/subjects/subject'

const currentUser = JSON.parse(localStorage.getItem('user') || 'null')
const canManageSubjects = currentUser?.role === 'admin'

const subjectStore = useSubjectStore()

// =========================================================
// SEARCH & FILTER
// =========================================================

const searchQuery = ref('')
const teacherFilter = ref('all')

watch(
    [searchQuery, teacherFilter],
    () => {

        subjectStore.fetchSubjects(
            1,
            searchQuery.value,
            teacherFilter.value
        )

    }
)

// =========================================================
// FORM
// =========================================================

const showModal = ref(false)

const editingSubject = ref(null)
const viewingSubject = ref(null)

const form = ref({
    name: '',
    code: '',
    class: '',
    description: ''
})

const errorMessage = ref('')

// =========================================================
// OPEN VIEW MODAL
// =========================================================

const openViewModal = (subject) => {

    viewingSubject.value = subject

}

// =========================================================
// OPEN ADD MODAL
// =========================================================

const openAddModal = () => {

    editingSubject.value = null

    form.value = {
        name: '',
        code: '',
        class: '',
        description: ''
    }

    errorMessage.value = ''

    showModal.value = true

}

// =========================================================
// OPEN EDIT MODAL
// =========================================================

const openEditModal = (subject) => {

    editingSubject.value = subject

    form.value = {
        name: subject.name || '',
        code: subject.code || '',
        class: subject.class || '',
        description: subject.description || ''
    }

    errorMessage.value = ''

    showModal.value = true

}

// =========================================================
// CLOSE MODAL
// =========================================================

const closeModal = () => {

    showModal.value = false

    editingSubject.value = null

    errorMessage.value = ''

}

// =========================================================
// SAVE SUBJECT
// =========================================================

const saveSubject = async () => {

    errorMessage.value = ''

    try {

        if (editingSubject.value) {

            await subjectStore.editSubject(
                editingSubject.value.id,
                form.value
            )

        } else {

            await subjectStore.addSubject(
                form.value
            )

        }

        closeModal()

    } catch (error) {

        console.error(
            'Save subject error:',
            error
        )

        errorMessage.value =
            error.response?.data?.message ||
            'Something went wrong.'

    }

}

// =========================================================
// DELETE SUBJECT
// =========================================================

const removeSubject = async (id) => {

    if (
        !confirm(
            'Are you sure you want to delete this subject?'
        )
    ) {
        return
    }

    try {

        await subjectStore.removeSubject(id)

    } catch (error) {

        console.error(
            'Delete subject error:',
            error
        )

    }

}

// =========================================================
// PAGINATION
// =========================================================

const changePage = async (page) => {

    if (
        page < 1 ||
        page > subjectStore.pagination.lastPage
    ) {
        return
    }

    await subjectStore.fetchSubjects(
        page,
        searchQuery.value,
        teacherFilter.value
    )

}

// =========================================================
// INITIAL LOAD
// =========================================================

onMounted(() => {

    const savedTheme = localStorage.getItem('theme')

    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }

    subjectStore.fetchSubjects(
        1,
        searchQuery.value,
        teacherFilter.value
    )

})

</script>


<template>
    <div class="mt-8">
        <div class="mx-auto w-full max-w-7xl">

            <!-- =========================================
                 HEADER
            ========================================== -->

            <div
                class="flex flex-col gap-5 border-b border-[#D8DDD3] pb-6 dark:border-[#2E3B33] lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-medium text-[#2F6F4E] dark:text-[#2F6F4E]"
                    >
                        Academic management
                    </p>

                    <h1
                        class="mt-1 text-2xl text-[#1C2B24] dark:text-[#E8EBE4] sm:text-3xl"
                    >
                        Manage subjects
                    </h1>

                    <p
                        class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                    >
                        Manage subjects, course information, and teacher
                        assignments.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openAddModal"
                        v-if="canManageSubjects"
                        class="border border-[#2F6F4E] bg-[#2F6F4E] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#2F6F4E]/90"
                    >
                        + Add subject
                    </button>
                </div>
            </div>

            <!-- =========================================
                 ERROR
            ========================================== -->

            <div
                v-if="subjectStore.error"
                class="mt-6 border border-[#B5563C]/30 bg-white px-4 py-3 dark:bg-[#1E2B24]"
            >
                <div class="flex items-start gap-3">
                    <span class="text-sm font-semibold text-[#B5563C]">
                        !
                    </span>

                    <p class="text-sm text-[#B5563C]">
                        {{ subjectStore.error }}
                    </p>
                </div>
            </div>

            <!-- =========================================
                 STATS
            ========================================== -->

            <section
                class="mt-8 overflow-hidden rounded-md border border-[#D8DDD3] bg-white dark:border-[#2E3B33] dark:bg-[#1E2B24]"
            >
                <div class="grid sm:grid-cols-3">

                    <!-- TOTAL SUBJECTS -->

                    <div
                        class="border-b border-[#D8DDD3] px-5 py-5 dark:border-[#2E3B33] sm:border-b-0 sm:border-r"
                    >
                        <p
                            class="text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Total subjects
                        </p>

                        <p
                            class="mt-1 text-2xl text-[#1C2B24] dark:text-[#E8EBE4]"
                        >
                            {{
                                subjectStore.pagination.totalSubjects
                            }}
                        </p>
                    </div>

                    <!-- CURRENT PAGE -->

                    <div
                        class="border-b border-[#D8DDD3] px-5 py-5 dark:border-[#2E3B33] sm:border-b-0 sm:border-r"
                    >
                        <p
                            class="text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Current page
                        </p>

                        <p
                            class="mt-1 text-2xl text-[#1C2B24] dark:text-[#E8EBE4]"
                        >
                            {{
                                subjectStore.pagination.currentPage
                            }}
                        </p>
                    </div>

                    <!-- TOTAL PAGES -->

                    <div class="px-5 py-5">
                        <p
                            class="text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Total pages
                        </p>

                        <p
                            class="mt-1 text-2xl text-[#1C2B24] dark:text-[#E8EBE4]"
                        >
                            {{
                                subjectStore.pagination.lastPage
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- =========================================
                 SEARCH / FILTER
            ========================================== -->

            <section
                class="mt-6 rounded-md border border-[#D8DDD3] bg-white p-5 dark:border-[#2E3B33] dark:bg-[#1E2B24]"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                >
                    <!-- SEARCH -->

                    <div class="w-full lg:w-96">
                        <label
                            class="mb-2 block text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Search subjects
                        </label>

                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by subject name or code"
                            class="w-full border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm text-[#1C2B24] outline-none placeholder:text-[#5B6B62]/50 transition focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4] dark:placeholder:text-[#9AA79E]/50"
                        />
                    </div>

                    <!-- TEACHER FILTER -->

                    <div class="w-full lg:w-72">
                        <label
                            class="mb-2 block text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Teacher assignment
                        </label>

                        <select
                            v-model="teacherFilter"
                            class="w-full border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm text-[#1C2B24] outline-none transition focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]"
                        >
                            <option value="all">
                                All subjects
                            </option>

                            <option value="with_teacher">
                                With teacher
                            </option>

                            <option value="without_teacher">
                                Without teacher
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- =========================================
                 SUBJECT TABLE
            ========================================== -->

            <section
                class="mt-6 overflow-hidden rounded-md border border-[#D8DDD3] bg-white dark:border-[#2E3B33] dark:bg-[#1E2B24]"
            >
                <!-- TABLE HEADER -->

                <div
                    class="flex flex-col gap-2 border-b border-[#D8DDD3] px-5 py-5 dark:border-[#2E3B33] sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-lg font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                        >
                            Subject register
                        </h2>

                        <p
                            class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Subjects currently registered in the system.
                        </p>
                    </div>

                    <p
                        v-if="!subjectStore.loading"
                        class="text-xs text-[#5B6B62] dark:text-[#9AA79E]"
                    >
                        {{ subjectStore.pagination.totalSubjects }}
                        subject{{
                            subjectStore.pagination.totalSubjects === 1
                                ? ''
                                : 's'
                        }}
                    </p>
                </div>

                <!-- TABLE -->

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">

                        <thead
                            class="border-b border-[#D8DDD3] bg-[#F4F6F1] dark:border-[#2E3B33] dark:bg-[#16211B]"
                        >
                            <tr>
                                <th
                                    class="w-20 px-5 py-4 text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    S.N.
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    Subject
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    Code
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    Description
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            <!-- LOADING -->

                            <tr v-if="subjectStore.loading">
                                <td
                                    colspan="5"
                                    class="px-5 py-14 text-center"
                                >
                                    <div
                                        class="mx-auto mb-4 h-7 w-7 animate-spin rounded-full border-2 border-[#D8DDD3] border-t-[#2F6F4E]"
                                    ></div>

                                    <p
                                        class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                                    >
                                        Loading subjects...
                                    </p>
                                </td>
                            </tr>

                            <!-- EMPTY -->

                            <tr
                                v-else-if="
                                    subjectStore.subjects.length === 0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="px-5 py-14 text-center"
                                >
                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center border border-[#D8DDD3] bg-[#F4F6F1] text-lg text-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#16211B]"
                                    >
                                        —
                                    </div>

                                    <p
                                        class="mt-4 text-sm font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                                    >
                                        No subjects found
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                                    >
                                        Try changing your search or filter.
                                    </p>
                                </td>
                            </tr>

                            <!-- SUBJECT ROWS -->

                            <tr
                                v-else
                                v-for="(subject, index) in subjectStore.subjects"
                                :key="subject.id"
                                class="border-b border-[#D8DDD3] last:border-b-0 hover:bg-[#F4F6F1] dark:border-[#2E3B33] dark:hover:bg-[#243329]"
                            >

                                <!-- S.N. -->

                                <td
                                    class="px-5 py-4 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    {{
                                        (subjectStore.pagination.currentPage - 1)
                                        * subjectStore.pagination.perPage
                                        + index + 1
                                    }}
                                </td>

                                <!-- SUBJECT -->

                                <td class="px-5 py-4">
                                    <p
                                        class="text-base font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                                    >
                                        {{ subject.name }}
                                    </p>
                                </td>

                                <!-- CODE -->

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex border border-[#2F6F4E]/30 bg-[#F4F6F1] px-2.5 py-1 text-xs font-medium text-[#2F6F4E] dark:bg-[#16211B]"
                                    >
                                        {{ subject.code }}
                                    </span>
                                </td>

                                <!-- DESCRIPTION -->

                                <td class="max-w-sm px-5 py-4">
                                    <p
                                        class="truncate text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                                        :title="
                                            subject.description || '-'
                                        "
                                    >
                                        {{ subject.description || '-' }}
                                    </p>
                                </td>

                                <!-- ACTIONS -->

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <!-- VIEW -->

                                        <button
                                            type="button"
                                            @click="openViewModal(subject)"
                                            class="border border-[#D8DDD3] bg-white px-3 py-1.5 text-sm font-medium text-[#5B6B62] transition hover:border-[#5B6B62] hover:text-[#1C2B24] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#9AA79E] dark:hover:border-[#9AA79E] dark:hover:text-[#E8EBE4]"
                                        >
                                            View
                                        </button>

                                        <!-- EDIT -->

                                        <button
                                            type="button"
                                            @click="openEditModal(subject)"
                                            v-if="canManageSubjects"
                                            class="border border-[#2F6F4E]/40 bg-white px-3 py-1.5 text-sm font-medium text-[#2F6F4E] transition hover:border-[#2F6F4E] hover:bg-[#F4F6F1] dark:bg-[#1E2B24] dark:hover:bg-[#243329]"
                                        >
                                            Edit
                                        </button>

                                        <!-- DELETE -->

                                        <button
                                            type="button"
                                            @click="removeSubject(subject.id)"
                                            v-if="canManageSubjects"
                                            class="border border-[#B5563C]/40 bg-white px-3 py-1.5 text-sm font-medium text-[#B5563C] transition hover:border-[#B5563C] hover:bg-[#F4F6F1] dark:bg-[#1E2B24] dark:hover:bg-[#243329]"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- =========================================
                 PAGINATION
            ========================================== -->

            <div
                v-if="subjectStore.pagination.lastPage > 1"
                class="flex flex-col gap-4 border-b border-[#D8DDD3] py-5 dark:border-[#2E3B33] sm:flex-row sm:items-center sm:justify-between"
            >
                <p
                    class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    Showing

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ subjectStore.pagination.from }}
                    </span>

                    -

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ subjectStore.pagination.to }}
                    </span>

                    of

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ subjectStore.pagination.totalSubjects }}
                    </span>
                </p>

                <div class="flex items-center">
                    <button
                        type="button"
                        @click="
                            changePage(
                                subjectStore.pagination.currentPage - 1
                            )
                        "
                        :disabled="
                            subjectStore.pagination.currentPage === 1
                        "
                        class="border border-[#D8DDD3] bg-white px-3 py-2 text-sm font-medium text-[#5B6B62] transition hover:border-[#2F6F4E] hover:text-[#2F6F4E] disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#9AA79E]"
                    >
                        ← Previous
                    </button>

                    <span
                        class="border-y border-[#D8DDD3] bg-[#F4F6F1] px-4 py-2 text-sm text-[#5B6B62] dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#9AA79E]"
                    >
                        Page

                        <span
                            class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                        >
                            {{ subjectStore.pagination.currentPage }}
                        </span>

                        of

                        <span
                            class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                        >
                            {{ subjectStore.pagination.lastPage }}
                        </span>
                    </span>

                    <button
                        type="button"
                        @click="
                            changePage(
                                subjectStore.pagination.currentPage + 1
                            )
                        "
                        :disabled="
                            subjectStore.pagination.currentPage ===
                            subjectStore.pagination.lastPage
                        "
                        class="border border-[#D8DDD3] bg-white px-3 py-2 text-sm font-medium text-[#5B6B62] transition hover:border-[#2F6F4E] hover:text-[#2F6F4E] disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#9AA79E]"
                    >
                        Next →
                    </button>
                </div>
            </div>

            <!-- =========================================
                 FOOTER NOTE
            ========================================== -->

            <div class="border-t border-[#D8DDD3] py-5 dark:border-[#2E3B33]">
                <p
                    class="text-xs leading-5 text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    Subjects define the academic structure of the student
                    management system. Keep subject codes consistent and
                    review teacher assignments regularly.
                </p>
            </div>
        </div>

        <!-- =========================================================
             ADD / EDIT MODAL
        ========================================================== -->

        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C2B24]/50 px-4 py-6"
        >
            <div
                class="w-full max-w-lg border border-[#D8DDD3] bg-white dark:border-[#2E3B33] dark:bg-[#1E2B24]"
            >
                <!-- MODAL HEADER -->

                <div
                    class="border-b border-[#D8DDD3] px-6 py-5 dark:border-[#2E3B33]"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <h2
                                class="text-2xl font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                            >
                                {{
                                    editingSubject
                                        ? 'Edit subject'
                                        : 'Add subject'
                                }}
                            </h2>

                            <p
                                class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                            >
                                {{
                                    editingSubject
                                        ? 'Update subject information.'
                                        : 'Add a new subject to the system.'
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeModal"
                            class="text-2xl leading-none text-[#5B6B62] transition hover:text-[#1C2B24] dark:text-[#9AA79E] dark:hover:text-[#E8EBE4]"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- MODAL BODY -->

                <div class="p-6">

                    <!-- ERROR -->

                    <div
                        v-if="errorMessage"
                        class="mb-5 border border-[#B5563C]/30 bg-[#F4F6F1] px-4 py-3 dark:bg-[#16211B]"
                    >
                        <div class="flex items-start gap-3">
                            <span class="font-semibold text-[#B5563C]">
                                !
                            </span>

                            <p class="text-sm text-[#B5563C]">
                                {{ errorMessage }}
                            </p>
                        </div>
                    </div>

                    <!-- NAME -->

                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Subject name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Enter subject name"
                            class="w-full border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm text-[#1C2B24] outline-none placeholder:text-[#5B6B62]/60 focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]"
                        />
                    </div>

                    <!-- CLASS -->

                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Class
                        </label>

                        <select
                            v-model="form.class"
                            required
                            class="w-full border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm text-[#1C2B24] outline-none focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]"
                        >
                            <option value="" disabled>
                                Select class
                            </option>

                            <option
                                v-for="classNumber in 10"
                                :key="classNumber"
                                :value="classNumber"
                            >
                                Class {{ classNumber }}
                            </option>
                        </select>
                    </div>

                    <!-- CODE -->

                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Subject code
                        </label>

                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="Enter subject code"
                            class="w-full border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm uppercase text-[#1C2B24] outline-none placeholder:text-[#5B6B62]/60 focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]"
                        />
                    </div>

                    <!-- DESCRIPTION -->

                    <div class="mb-6">
                        <label
                            class="mb-1.5 block text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Description
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Enter subject description"
                            class="w-full resize-none border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm text-[#1C2B24] outline-none placeholder:text-[#5B6B62]/60 focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]"
                        ></textarea>
                    </div>

                    <!-- ACTIONS -->

                    <div
                        class="flex flex-col-reverse gap-2 border-t border-[#D8DDD3] pt-5 dark:border-[#2E3B33] sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="border border-[#D8DDD3] bg-white px-5 py-2.5 text-sm font-medium text-[#5B6B62] transition hover:border-[#5B6B62] hover:text-[#1C2B24] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#9AA79E] dark:hover:text-[#E8EBE4]"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            @click="saveSubject"
                            :disabled="subjectStore.loading"
                            class="border border-[#2F6F4E] bg-[#2F6F4E] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#2F6F4E]/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                editingSubject
                                    ? 'Update subject'
                                    : 'Add subject'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             VIEW SUBJECT MODAL
        ========================================================== -->

        <div
            v-if="viewingSubject"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C2B24]/50 px-4 py-6"
        >
            <div
                class="w-full max-w-lg border border-[#D8DDD3] bg-white dark:border-[#2E3B33] dark:bg-[#1E2B24]"
            >
                <!-- MODAL HEADER -->

                <div
                    class="border-b border-[#D8DDD3] px-6 py-5 dark:border-[#2E3B33]"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <h2
                                class="text-2xl font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                            >
                                Subject details
                            </h2>

                            <p
                                class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                            >
                                Subject and teacher assignment information.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="viewingSubject = null"
                            class="text-2xl leading-none text-[#5B6B62] transition hover:text-[#1C2B24] dark:text-[#9AA79E] dark:hover:text-[#E8EBE4]"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- MODAL BODY -->

                <div class="p-6">

                    <!-- SUBJECT -->

                    <div
                        class="border-b border-[#D8DDD3] pb-5 dark:border-[#2E3B33]"
                    >
                        <p
                            class="text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Subject name
                        </p>

                        <p
                            class="mt-1 text-xl font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                        >
                            {{ viewingSubject.name }}
                        </p>
                    </div>

                    <!-- CODE -->

                    <div
                        class="border-b border-[#D8DDD3] py-5 dark:border-[#2E3B33]"
                    >
                        <p
                            class="text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Subject code
                        </p>

                        <span
                            class="mt-2 inline-flex border border-[#2F6F4E]/30 bg-[#F4F6F1] px-2.5 py-1 text-xs font-medium text-[#2F6F4E] dark:bg-[#16211B]"
                        >
                            {{ viewingSubject.code }}
                        </span>
                    </div>

                    <!-- DESCRIPTION -->

                    <div
                        class="border-b border-[#D8DDD3] py-5 dark:border-[#2E3B33]"
                    >
                        <p
                            class="text-xs font-medium text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            Description
                        </p>

                        <p
                            class="mt-2 text-sm leading-6 text-[#5B6B62] dark:text-[#9AA79E]"
                        >
                            {{ viewingSubject.description || '-' }}
                        </p>
                    </div>

                    <!-- TEACHERS -->

                    <div class="pt-5">
                        <div
                            class="flex items-center justify-between"
                        >
                            <p
                                class="text-sm font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                            >
                                Teacher assignment
                            </p>

                            <span
                                class="text-xs text-[#5B6B62] dark:text-[#9AA79E]"
                            >
                                {{
                                    viewingSubject.teachers?.length || 0
                                }}
                                assigned
                            </span>
                        </div>

                        <!-- TEACHERS -->

                        <div
                            v-if="
                                viewingSubject.teachers &&
                                viewingSubject.teachers.length > 0
                            "
                            class="mt-4 divide-y divide-[#D8DDD3] border-y border-[#D8DDD3] dark:divide-[#2E3B33] dark:border-[#2E3B33]"
                        >
                            <div
                                v-for="teacher in viewingSubject.teachers"
                                :key="teacher.id"
                                class="py-4"
                            >
                                <p
                                    class="text-sm font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                                >
                                    {{ teacher.name }}
                                </p>

                                <p
                                    class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    {{ teacher.email }}
                                </p>

                                <p
                                    v-if="teacher.phone"
                                    class="mt-0.5 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                                >
                                    {{ teacher.phone }}
                                </p>
                            </div>
                        </div>

                        <!-- NO TEACHER -->

                        <div
                            v-else
                            class="mt-4 border border-[#D8DDD3] bg-[#F4F6F1] px-4 py-4 dark:border-[#2E3B33] dark:bg-[#16211B]"
                        >
                            <p
                                class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                            >
                                No teacher assigned.
                            </p>
                        </div>
                    </div>

                    <!-- CLOSE -->

                    <div
                        class="mt-6 flex justify-end border-t border-[#D8DDD3] pt-5 dark:border-[#2E3B33]"
                    >
                        <button
                            type="button"
                            @click="viewingSubject = null"
                            class="border border-[#D8DDD3] bg-white px-5 py-2.5 text-sm font-medium text-[#5B6B62] transition hover:border-[#2F6F4E] hover:text-[#2F6F4E] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#9AA79E] dark:hover:text-[#2F6F4E]"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
