<script setup>

import { ref, onMounted } from 'vue'
import { useSubjectStore } from '../../stores/subjects/subject'

const subjectStore = useSubjectStore()

// =========================================================
// FORM
// =========================================================

const showModal = ref(false)

const editingSubject = ref(null)

const form = ref({
    name: '',
    code: '',
    description: ''
})

const errorMessage = ref('')


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

    await subjectStore.fetchSubjects(page)

}


// =========================================================
// INITIAL LOAD
// =========================================================

onMounted(() => {

    subjectStore.fetchSubjects()

})

</script>


<template>

<div class="p-6">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="flex items-center justify-between mb-6">

        <div>

            <h1 class="text-2xl font-bold text-gray-800">
                Subjects
            </h1>

            <p class="text-gray-500">
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

    <div class="overflow-x-auto bg-white rounded-lg shadow">

        <table class="w-full">

            <thead class="bg-gray-100">

                <tr>

                    <th class="px-4 py-3 text-left">
                        #
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
                        class="text-center py-8 text-gray-500"
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
                        class="text-center py-8 text-gray-500"
                    >
                        No subjects found.
                    </td>

                </tr>


                <!-- SUBJECTS -->

                <tr
                    v-else
                    v-for="(subject, index) in subjectStore.subjects"
                    :key="subject.id"
                    class="border-t hover:bg-gray-50"
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
                            class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-sm font-medium"
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
                                @click="openEditModal(subject)"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                            >
                                Edit
                            </button>


                            <button
                                @click="removeSubject(subject.id)"
                                class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
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

        <p class="text-sm text-gray-500">

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
                    class="text-gray-500 hover:text-gray-800 text-xl"
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
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
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

</template>
