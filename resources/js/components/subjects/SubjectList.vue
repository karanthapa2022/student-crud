<script setup>

import { ref, onMounted, watch } from 'vue'
import { useSubjectStore } from '../../stores/subjects/subject'

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

<div
    class="p-6 lg:p-8 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors"
>

    <div class="w-full max-w-7xl mx-auto">


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
                    Subjects
                </h1>

                <p
                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                >
                    Manage student subjects and teacher assignments
                </p>

            </div>


            <!-- HEADER ACTIONS -->

<div class="flex flex-col sm:flex-row gap-3">

    <!-- ADMIN DASHBOARD -->
    <button
        type="button"
        @click="$router.push('/admin/dashboard')"
        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-600 dark:bg-purple-700 text-white font-medium hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-sm"
    >
        Dashboard
    </button>

    <!-- ADD SUBJECT -->
    <button
        type="button"
        @click="openAddModal"
        class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold shadow-sm hover:bg-green-700 transition"
    >

        <span class="mr-1">
            +
        </span>

        Add Subject

    </button>

</div>

        </div>


        <!-- =====================================================
             SEARCH & FILTER CARD
        ====================================================== -->

        <div
            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 mb-6 transition-colors"
        >

            <div
                class="flex flex-col md:flex-row gap-4"
            >

                <!-- SEARCH -->

                <div class="flex-1">

                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Search Subjects
                    </label>

                    <div class="relative">

                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                        >
                            🔍
                        </span>

                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by subject name or code"
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                        >

                    </div>

                </div>


                <!-- TEACHER FILTER -->

                <div class="w-full md:w-64">

                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Teacher Assignment
                    </label>

                    <select
                        v-model="teacherFilter"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                    >

                        <option value="all">
                            All Subjects
                        </option>

                        <option value="with_teacher">
                            With Teacher
                        </option>

                        <option value="without_teacher">
                            Without Teacher
                        </option>

                    </select>

                </div>

            </div>

        </div>


        <!-- =====================================================
             ERROR
        ====================================================== -->

        <div
            v-if="subjectStore.error"
            class="mb-5 p-4 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800"
        >

            <div class="flex items-center gap-2">

                <span>
                    ⚠️
                </span>

                <span class="text-sm font-medium">
                    {{ subjectStore.error }}
                </span>

            </div>

        </div>


        <!-- =====================================================
             TABLE CARD
        ====================================================== -->

        <div
            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden transition-colors"
        >

            <div class="overflow-x-auto">

                <table class="w-full min-w-[850px]">


                    <!-- =================================================
                         TABLE HEADER
                    ================================================== -->

                    <thead
                        class="bg-gray-50 dark:bg-gray-700"
                    >

                        <tr>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-200"
                            >
                                S.N.
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-200"
                            >
                                Subject Name
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-200"
                            >
                                Code
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-200"
                            >
                                Description
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-200"
                            >
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <!-- =================================================
                         TABLE BODY
                    ================================================== -->

                    <tbody>


                        <!-- LOADING -->

                        <tr v-if="subjectStore.loading">

                            <td
                                colspan="5"
                                class="px-5 py-12 text-center"
                            >

                                <div
                                    class="flex flex-col items-center"
                                >

                                    <div
                                        class="w-8 h-8 border-4 border-gray-200 dark:border-gray-600 border-t-green-600 rounded-full animate-spin mb-3"
                                    ></div>

                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Loading subjects...
                                    </p>

                                </div>

                            </td>

                        </tr>


                        <!-- EMPTY -->

                        <tr
                            v-else-if="subjectStore.subjects.length === 0"
                        >

                            <td
                                colspan="5"
                                class="px-5 py-12 text-center"
                            >

                                <div
                                    class="flex flex-col items-center"
                                >

                                    <div
                                        class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-2xl mb-3"
                                    >
                                        📚
                                    </div>

                                    <p
                                        class="font-semibold text-gray-700 dark:text-gray-300"
                                    >
                                        No subjects found
                                    </p>

                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                                    >
                                        Try changing your search or filter.
                                    </p>

                                </div>

                            </td>

                        </tr>


                        <!-- SUBJECTS -->

                        <tr
                            v-else
                            v-for="(subject, index) in subjectStore.subjects"
                            :key="subject.id"
                            class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                        >

                            <!-- S.N. -->

                            <td
                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300"
                            >

                                {{
                                    (subjectStore.pagination.currentPage - 1)
                                    * subjectStore.pagination.perPage
                                    + index + 1
                                }}

                            </td>


                            <!-- SUBJECT NAME -->

                            <td
                                class="px-5 py-4"
                            >

                                <span
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ subject.name }}
                                </span>

                            </td>


                            <!-- CODE -->

                            <td
                                class="px-5 py-4"
                            >

                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-semibold"
                                >
                                    {{ subject.code }}
                                </span>

                            </td>


                            <!-- DESCRIPTION -->

                            <td
                                class="px-5 py-4 max-w-xs"
                            >

                                <p
                                    class="text-sm text-gray-600 dark:text-gray-300 truncate"
                                    :title="subject.description || '-'"
                                >
                                    {{ subject.description || '-' }}
                                </p>

                            </td>


                            <!-- ACTIONS -->

                            <td
                                class="px-5 py-4"
                            >

                                <div
                                    class="flex items-center gap-2"
                                >

                                    <!-- VIEW -->

                                    <button
                                        @click="openViewModal(subject)"
                                        class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                                    >
                                        View
                                    </button>


                                    <!-- EDIT -->

                                    <button
                                        @click="openEditModal(subject)"
                                        class="px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                                    >
                                        Edit
                                    </button>


                                    <!-- DELETE -->

                                    <button
                                        @click="removeSubject(subject.id)"
                                        class="px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 text-xs font-semibold hover:bg-red-100 dark:hover:bg-red-900/50 transition"
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


        <!-- =====================================================
             PAGINATION
        ====================================================== -->

        <div
            v-if="subjectStore.pagination.lastPage > 1"
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-5"
        >

            <!-- RESULTS -->

            <p
                class="text-sm text-gray-500 dark:text-gray-400"
            >

                Showing
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ subjectStore.pagination.from }}
                </span>
                -
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ subjectStore.pagination.to }}
                </span>
                of
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ subjectStore.pagination.totalSubjects }}
                </span>

            </p>


            <!-- PAGE CONTROLS -->

            <div
                class="flex items-center gap-2"
            >

                <!-- PREVIOUS -->

                <button
                    @click="
                        changePage(
                            subjectStore.pagination.currentPage - 1
                        )
                    "
                    :disabled="
                        subjectStore.pagination.currentPage === 1
                    "
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    Previous
                </button>


                <!-- PAGE -->

                <span
                    class="px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300"
                >

                    Page
                    {{ subjectStore.pagination.currentPage }}
                    of
                    {{ subjectStore.pagination.lastPage }}

                </span>


                <!-- NEXT -->

                <button
                    @click="
                        changePage(
                            subjectStore.pagination.currentPage + 1
                        )
                    "
                    :disabled="
                        subjectStore.pagination.currentPage ===
                        subjectStore.pagination.lastPage
                    "
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    Next
                </button>

            </div>

        </div>


    </div>


    <!-- =====================================================
         ADD / EDIT MODAL
    ====================================================== -->

    <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    >

        <div
            class="w-full max-w-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl overflow-hidden"
        >


            <!-- MODAL HEADER -->

            <div
                class="px-6 py-5 border-b border-gray-200 dark:border-gray-700"
            >

                <div
                    class="flex items-center justify-between"
                >

                    <div>

                        <h2
                            class="text-xl font-bold text-gray-900 dark:text-white"
                        >

                            {{
                                editingSubject
                                    ? 'Edit Subject'
                                    : 'Add Subject'
                            }}

                        </h2>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                        >

                            {{
                                editingSubject
                                    ? 'Update subject information'
                                    : 'Add a new subject to the system'
                            }}

                        </p>

                    </div>


                    <!-- CLOSE -->

                    <button
                        @click="closeModal"
                        class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-200 transition text-xl"
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
                    class="mb-5 p-4 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800"
                >

                    <div class="flex items-center gap-2">

                        <span>
                            ⚠️
                        </span>

                        <span class="text-sm">
                            {{ errorMessage }}
                        </span>

                    </div>

                </div>


                <!-- NAME -->

                <div class="mb-5">

                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Subject Name
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Enter subject name"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                    >

                </div>


                <!-- CODE -->

                <div class="mb-5">

                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Subject Code
                    </label>

                    <input
                        v-model="form.code"
                        type="text"
                        placeholder="Enter subject code"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 uppercase focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="mb-6">

                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="4"
                        placeholder="Enter subject description"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition resize-none"
                    ></textarea>

                </div>


                <!-- ACTIONS -->

                <div
                    class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700"
                >

                    <!-- CANCEL -->

                    <button
                        @click="closeModal"
                        class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        Cancel
                    </button>


                    <!-- SAVE -->

                    <button
                        @click="saveSubject"
                        :disabled="subjectStore.loading"
                        class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >

                        {{
                            editingSubject
                                ? 'Update Subject'
                                : 'Add Subject'
                        }}

                    </button>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         VIEW SUBJECT MODAL
    ====================================================== -->

    <div
        v-if="viewingSubject"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    >

        <div
            class="w-full max-w-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl overflow-hidden"
        >


            <!-- MODAL HEADER -->

            <div
                class="px-6 py-5 border-b border-gray-200 dark:border-gray-700"
            >

                <div
                    class="flex items-center justify-between"
                >

                    <div>

                        <h2
                            class="text-xl font-bold text-gray-900 dark:text-white"
                        >
                            Subject Details
                        </h2>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                        >
                            View subject and teacher information
                        </p>

                    </div>


                    <!-- CLOSE -->

                    <button
                        @click="viewingSubject = null"
                        class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-200 transition text-xl"
                    >
                        ×
                    </button>

                </div>

            </div>


            <!-- MODAL BODY -->

            <div class="p-6">


                <!-- SUBJECT NAME -->

                <div
                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 mb-4"
                >

                    <p
                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Subject Name
                    </p>

                    <p
                        class="mt-1 font-semibold text-gray-900 dark:text-white"
                    >
                        {{ viewingSubject.name }}
                    </p>

                </div>


                <!-- CODE -->

                <div
                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 mb-4"
                >

                    <p
                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Subject Code
                    </p>

                    <span
                        class="inline-flex mt-2 px-2.5 py-1 rounded-md bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-semibold"
                    >
                        {{ viewingSubject.code }}
                    </span>

                </div>


                <!-- DESCRIPTION -->

                <div
                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 mb-5"
                >

                    <p
                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Description
                    </p>

                    <p
                        class="mt-1 text-sm text-gray-700 dark:text-gray-200 leading-relaxed"
                    >
                        {{ viewingSubject.description || '-' }}
                    </p>

                </div>


                <!-- TEACHER -->

                <div>

                    <div class="flex items-center justify-between mb-3">

                        <p
                            class="text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Teacher Assignment
                        </p>

                    </div>


                    <!-- TEACHERS -->

                    <div
                        v-if="
                            viewingSubject.teachers &&
                            viewingSubject.teachers.length > 0
                        "
                        class="space-y-3"
                    >

                        <div
                            v-for="teacher in viewingSubject.teachers"
                            :key="teacher.id"
                            class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                        >

                            <div class="flex items-start gap-3">

                                <div
                                    class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center flex-shrink-0"
                                >
                                    👨‍🏫
                                </div>

                                <div>

                                    <p
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ teacher.name }}
                                    </p>

                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-300 mt-1"
                                    >
                                        {{ teacher.email }}
                                    </p>

                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-300 mt-0.5"
                                    >
                                        {{ teacher.phone }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- NO TEACHER -->

                    <div
                        v-else
                        class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                    >

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            No teacher assigned.
                        </p>

                    </div>

                </div>


                <!-- CLOSE BUTTON -->

                <div
                    class="flex justify-end mt-6 pt-5 border-t border-gray-200 dark:border-gray-700"
                >

                    <button
                        @click="viewingSubject = null"
                        class="px-5 py-2.5 rounded-lg bg-gray-800 dark:bg-gray-700 text-white font-medium hover:bg-gray-900 dark:hover:bg-gray-600 transition"
                    >
                        Close
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

</template>