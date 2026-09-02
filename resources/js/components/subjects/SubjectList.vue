<script setup>

import { ref, onMounted, watch } from 'vue'
import { useSubjectStore } from '../../stores/subjects/subject'



const subjectStore = useSubjectStore()


//Search and Filter
const searchQuery= ref('')
const teacherFilter=ref('all')

//watch search & filter
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

//open view modal
const openViewModal=(subject)=>{
viewingSubject.value=subject
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

    await subjectStore.fetchSubjects(page,
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
    subjectStore.fetchSubjects(1,
        searchQuery.value,
        teacherFilter.value
    )

})

</script>


<template>

<div class="p-6 text-gray-800 bg-gray-100 dark:bg-gray-900 dark:text-gray-100 min-h-screen">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="flex items-center justify-between mb-6">

        <div>

            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Subjects
            </h1>

            <p class="text-gray-500 dark:text-gray-400">
                Manage student subjects
            </p>

        </div>


        <button
            @click="openAddModal"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
        >
            + Add Subject
        </button>

    </div>

    <!--Search and Filter-->
        <div class="flex flex-col md:flex-row gap-3 mb-6">
            <!--search-->
            <input v-model="searchQuery"
            type="text"
            placeholder="Search subjects by name or code"
            class="flex-1 px-4 py-2 border rounded-lg bg-white dark:bg-gray-800 dark:text-white text-gray-800 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500">

            <!--teacher filter-->
            <select v-model="teacherFilter"
            class="px-4 py-2 border rounded-lg bg-white dark:bg-gray-800 dark:text-white text-gray-800 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500"
            >
            <option value="all">All Subjects</option>
            <option value="with_teacher">With Teacher</option>
            <option value="without_teacher">Without Teacher</option>
        
        </select>

        </div>

    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
        v-if="subjectStore.error"
        class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"
    >
        {{ subjectStore.error }}
    </div>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">

        <table class="w-full">

            <thead class="bg-gray-100 dark:bg-gray-800">

                <tr>

                    <th class="px-4 py-3 text-left">
                        S.N
                    </th>

                    <th class="px-4 py-3 text-left">
                        Subject Name
                    </th>

                    <th class="px-4 py-3 text-left">
                        Code
                    </th>

                    <th class="px-4 py-3 text-left">
                        Description
                    </th>

                    <th class="px-4 py-3 text-left">
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                <!-- LOADING -->

                <tr v-if="subjectStore.loading">

                    <td
                        colspan="5"
                        class="text-center py-8 text-gray-500 dark:text-gray-400"
                    >
                        Loading subjects...
                    </td>

                </tr>


                <!-- EMPTY -->

                <tr
                    v-else-if="subjectStore.subjects.length === 0"
                >

                    <td
                        colspan="5"
                        class="text-center py-8 text-gray-500 dark:text-gray-400 "
                    >
                        No subjects found.
                    </td>

                </tr>


                <!-- SUBJECTS -->

                <tr
                    v-else
                    v-for="(subject, index) in subjectStore.subjects"
                    :key="subject.id"
                    class="border-t hover:bg-gray-50 dark:hover:bg-gray-700"
                >

                    <td class="px-4 py-3">

                        {{
                            (subjectStore.pagination.currentPage - 1)
                            * subjectStore.pagination.perPage
                            + index + 1
                        }}

                    </td>


                    <td class="px-4 py-3 font-medium">

                        {{ subject.name }}

                    </td>


                    <td class="px-4 py-3">

                        <span
                            class="px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-300 rounded-md text-sm font-medium"
                        >
                            {{ subject.code }}
                        </span>

                    </td>


                    <td class="px-4 py-3">

                        {{ subject.description || '-' }}

                    </td>


                    <td class="px-4 py-3">

                        <div class="flex gap-2">

                            <button
                            @click="openViewModal(subject)"
                                class="px-3 py-1 bg-gray-500 text-white dark:bg-gray-600 dark:text-white rounded hover:bg-gray-600"
                            >
                                View
                            </button>
                            <button
                                @click="openEditModal(subject)"
                                class="px-3 py-1 bg-blue-500 text-white dark:bg-blue-600 dark:text-white rounded hover:bg-blue-600"
                            >
                                Edit
                            </button>


                            <button
                                @click="removeSubject(subject.id)"
                                class="px-3 py-1 bg-red-500 text-white dark:bg-red-600 dark:text-white rounded hover:bg-red-600"
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
         PAGINATION
    ====================================================== -->

    <div
        v-if="subjectStore.pagination.lastPage > 1"
        class="flex items-center justify-between mt-4"
    >

        <p class="text-sm text-gray-500 dark:text-gray-400">

            Showing
            {{ subjectStore.pagination.from }}
            -
            {{ subjectStore.pagination.to }}
            of
            {{ subjectStore.pagination.totalSubjects }}

        </p>


        <div class="flex gap-2">

            <button
                @click="
                    changePage(
                        subjectStore.pagination.currentPage - 1
                    )
                "
                :disabled="
                    subjectStore.pagination.currentPage === 1
                "
                class="px-3 py-1 border rounded disabled:opacity-50"
            >
                Previous
            </button>


            <span class="px-3 py-1">

                Page
                {{ subjectStore.pagination.currentPage }}
                of
                {{ subjectStore.pagination.lastPage }}

            </span>


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
                class="px-3 py-1 border rounded disabled:opacity-50"
            >
                Next
            </button>

        </div>

    </div>


    <!-- =====================================================
         ADD / EDIT MODAL
    ====================================================== -->

    <div
        v-if="showModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >

        <div
            class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6"
        >

            <div class="flex justify-between items-center mb-6">

                <h2 class="text-xl font-bold">

                    {{
                        editingSubject
                            ? 'Edit Subject'
                            : 'Add Subject'
                    }}

                </h2>


                <button
                    @click="closeModal"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 text-xl"
                >
                    ×
                </button>

            </div>


            <!-- ERROR -->

            <div
                v-if="errorMessage"
                class="mb-4 p-3 bg-red-100 text-red-700 rounded"
            >
                {{ errorMessage }}
            </div>


            <!-- NAME -->

            <div class="mb-4">

                <label class="block mb-1 font-medium">
                    Subject Name
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border rounded-lg px-3 py-2"
                    placeholder="Enter subject name"
                >

            </div>


            <!-- CODE -->

            <div class="mb-4">

                <label class="block mb-1 font-medium">
                    Subject Code
                </label>

                <input
                    v-model="form.code"
                    type="text"
                    class="w-full border rounded-lg px-3 py-2 uppercase"
                    placeholder="Enter subject code"
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="mb-6">

                <label class="block mb-1 font-medium">
                    Description
                </label>

                <textarea
                    v-model="form.description"
                    rows="4"
                    class="w-full border rounded-lg px-3 py-2"
                    placeholder="Enter subject description"
                ></textarea>

            </div>


            <!-- ACTIONS -->

            <div class="flex justify-end gap-3">

                <button
                    @click="closeModal"
                    class="px-4 py-2 border rounded-lg"
                >
                    Cancel
                </button>


                <button
                    @click="saveSubject"
                    :disabled="subjectStore.loading"
                    class="px-4 py-2 bg-green-600 text-white dark:bg-green-700 rounded-lg hover:bg-green-700 dark:hover:bg-green-800 disabled:opacity-50"
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

  <!-- =====================================================
     VIEW SUBJECT MODAL
====================================================== -->

<div
    v-if="viewingSubject"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
>

    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6"
    >

        <div class="flex justify-between items-center mb-6">

            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                Subject Details
            </h2>

            <button
                @click="viewingSubject = null"
                class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 text-xl"
            >
                ×
            </button>

        </div>


        <!-- SUBJECT NAME -->

        <div class="mb-4">

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Subject Name
            </p>

            <p class="font-semibold text-gray-800 dark:text-white">
                {{ viewingSubject.name }}
            </p>

        </div>


        <!-- CODE -->

        <div class="mb-4">

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Subject Code
            </p>

            <p class="font-semibold text-gray-800 dark:text-white">
                {{ viewingSubject.code }}
            </p>

        </div>


        <!-- DESCRIPTION -->

        <div class="mb-4">

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Description
            </p>

            <p class="text-gray-800 dark:text-gray-200">
                {{ viewingSubject.description || '-' }}
            </p>

        </div>


        <!-- TEACHER -->

        <div class="mb-6">

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                Teacher
            </p>

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
                    class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg"
                >

                    <p class="font-semibold text-gray-800 dark:text-white">
                        {{ teacher.name }}
                    </p>

                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ teacher.email }}
                    </p>

                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ teacher.phone }}
                    </p>

                </div>

            </div>

            <p
                v-else
                class="text-gray-500 dark:text-gray-400"
            >
                No teacher assigned.
            </p>

        </div>


        <!-- CLOSE -->

        <div class="flex justify-end">

            <button
                @click="viewingSubject = null"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
            >
                Close
            </button>

        </div>

    </div>

</div>

</div>

</template>
