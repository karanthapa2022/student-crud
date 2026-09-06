<script setup>

import { ref, computed, onMounted, watch } from 'vue'
import { useParentStore } from '../../stores/parents/parent'
import {
    getParent,
    updateParentChildren
} from '../../services/parents/parentApi'
import { getStudents } from '../../services/students/studentApi'

const parentStore = useParentStore()

// =========================================================
// SEARCH & FILTER
// =========================================================

const searchQuery = ref('')
const relationshipFilter = ref('all')

watch(
    [searchQuery, relationshipFilter],
    () => {
        parentStore.fetchParents(
            1,
            searchQuery.value,
            relationshipFilter.value
        )
    }
)

// =========================================================
// MODALS
// =========================================================

const showModal = ref(false)
const editingParent = ref(null)

const showViewModal = ref(false)
const viewingParent = ref(null)

// =========================================================
// STUDENT ASSIGNMENT
// =========================================================

const students = ref([])
const selectedStudentIds = ref([])
const studentSearchQuery = ref('')

const filteredStudents = computed(() => {

    const search = studentSearchQuery.value
        .toLowerCase()
        .trim()

    if (!search) {
        return students.value
    }

    return students.value.filter(student =>
        student.name?.toLowerCase().includes(search) ||
        String(student.class || '')
            .toLowerCase()
            .includes(search) ||
        String(student.email || '')
            .toLowerCase()
            .includes(search)
    )
})

// =========================================================
// FORM
// =========================================================

const form = ref({
    name: '',
    email: '',
    phone: '',
    relationship: ''
})

const errorMessage = ref('')

// =========================================================
// OPEN ADD MODAL
// =========================================================

const openAddModal = () => {

    editingParent.value = null

    form.value = {
        name: '',
        email: '',
        phone: '',
        relationship: ''
    }

    errorMessage.value = ''

    showModal.value = true
}

// =========================================================
// OPEN EDIT MODAL
// =========================================================

const openEditModal = async (parent) => {

    try {

        const response = await getParent(parent.id)

        editingParent.value = response.data

        // Load students
        const studentsResponse = await getStudents(
            1,
            '',
            'all'
        )

        students.value =
            studentsResponse.data.data || []

        // Select students currently assigned
        selectedStudentIds.value =
            (response.data.students || []).map(
                student => student.id
            )

        studentSearchQuery.value = ''

        form.value = {
            name: response.data.name || '',
            email: response.data.email || '',
            phone: response.data.phone || '',
            relationship:
                response.data.relationship || ''
        }

        errorMessage.value = ''

        showModal.value = true

    } catch (error) {

        console.error(
            'Error fetching parent/students:',
            error
        )

        alert('Unable to load parent details.')
    }
}

// =========================================================
// REASSIGNMENT MODAL
// =========================================================

const showReassignModal = ref(false)
const reassigningStudent = ref(null)

const toggleStudentSelection = (student) => {

    const isSelected =
        selectedStudentIds.value.includes(student.id)

    // Unchecking
    if (isSelected) {

        selectedStudentIds.value =
            selectedStudentIds.value.filter(
                id => id !== student.id
            )

        return
    }

    // Student belongs to another parent
    if (
        student.parent &&
        student.parent.id !== editingParent.value?.id
    ) {

        reassigningStudent.value = student
        showReassignModal.value = true

        return
    }

    // Student has no parent
    selectedStudentIds.value.push(student.id)
}

const confirmReassignment = () => {

    if (!reassigningStudent.value) {
        return
    }

    selectedStudentIds.value.push(
        reassigningStudent.value.id
    )

    showReassignModal.value = false
    reassigningStudent.value = null
}

const cancelReassignment = () => {

    showReassignModal.value = false
    reassigningStudent.value = null
}

// =========================================================
// OPEN VIEW MODAL
// =========================================================

const openViewModal = async (parent) => {

    try {

        const response = await getParent(parent.id)

        viewingParent.value = response.data

        showViewModal.value = true

    } catch (error) {

        console.error(
            'Error fetching parent:',
            error
        )

        alert('Unable to load parent details.')
    }
}

const closeViewModal = () => {

    showViewModal.value = false
    viewingParent.value = null
}

// =========================================================
// CLOSE EDIT MODAL
// =========================================================

const closeModal = () => {

    showModal.value = false

    editingParent.value = null

    errorMessage.value = ''

    selectedStudentIds.value = []

    students.value = []

    studentSearchQuery.value = ''
}

// =========================================================
// SAVE PARENT
// =========================================================

const saveParent = async () => {

    errorMessage.value = ''

    try {

        if (editingParent.value) {

            // Update parent information
            await parentStore.editParent(
                editingParent.value.id,
                form.value
            )

            // Update children
            await updateParentChildren(
                editingParent.value.id,
                selectedStudentIds.value
            )

        } else {

            await parentStore.addParent(
                form.value
            )
        }

        // Refresh parent list
        await parentStore.fetchParents(
            1,
            searchQuery.value,
            relationshipFilter.value
        )

        closeModal()

    } catch (error) {

        console.error(
            'Save parent error:',
            error
        )

        errorMessage.value =
            error.response?.data?.message ||
            'Something went wrong.'
    }
}

// =========================================================
// DELETE PARENT
// =========================================================

const removeParent = async (id) => {

    if (
        !confirm(
            'Are you sure you want to delete this parent?'
        )
    ) {
        return
    }

    try {

        await parentStore.removeParent(id)

        // Refresh immediately
        await parentStore.fetchParents(
            1,
            searchQuery.value,
            relationshipFilter.value
        )

    } catch (error) {

        console.error(
            'Delete parent error:',
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
        page > parentStore.pagination.lastPage
    ) {
        return
    }

    await parentStore.fetchParents(
        page,
        searchQuery.value,
        relationshipFilter.value
    )
}

// =========================================================
// INITIAL LOAD
// =========================================================

onMounted(() => {

    parentStore.fetchParents()

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
                Parents
            </h1>

            <p
                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
            >
                Manage student parents and their children
            </p>

        </div>


        <button
            @click="openAddModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-green-700 hover:shadow transition-all"
        >

            <span class="text-lg leading-none">
                +
            </span>

            Add Parent

        </button>

    </div>


    <!-- =====================================================
         SEARCH & FILTER
    ====================================================== -->

    <div
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-6 shadow-sm transition-colors"
    >

        <div
            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3"
        >

            <!-- SEARCH -->

            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search parents by name, email, or phone...."
                class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
            />


            <!-- FILTER -->

            <select
                v-model="relationshipFilter"
                class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
            >

                <option value="all">
                    All Relationships
                </option>

                <option value="Father">
                    Father
                </option>

                <option value="Mother">
                    Mother
                </option>

                <option value="Guardian">
                    Guardian
                </option>

                <option value="Other">
                    Other
                </option>

            </select>

        </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
        v-if="parentStore.error"
        class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg"
    >
        {{ parentStore.error }}
    </div>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div
        class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-colors"
    >

        <table class="w-full min-w-[700px]">

            <!-- TABLE HEADER -->

            <thead class="bg-gray-50 dark:bg-gray-700">

                <tr>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-gray-600 dark:text-gray-200 text-sm font-semibold"
                    >
                        S.N.
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-gray-600 dark:text-gray-200 text-sm font-semibold"
                    >
                        Name
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-gray-600 dark:text-gray-200 text-sm font-semibold"
                    >
                        Email
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-gray-600 dark:text-gray-200 text-sm font-semibold"
                    >
                        Phone
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-gray-600 dark:text-gray-200 text-sm font-semibold"
                    >
                        Relationship
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-gray-600 dark:text-gray-200 text-sm font-semibold"
                    >
                        Students
                    </th>

                    <th
                        class="px-4 py-3 text-left whitespace-nowrap text-gray-600 dark:text-gray-200 text-sm font-semibold"
                    >
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                <!-- =================================================
                     LOADING
                ================================================== -->

                <tr v-if="parentStore.loading">

                    <td
                        colspan="7"
                        class="text-center py-10"
                    >

                        <div
                            class="flex flex-col items-center justify-center"
                        >

                            <div
                                class="w-8 h-8 border-4 border-gray-200 dark:border-gray-600 border-t-green-600 rounded-full animate-spin mb-3"
                            ></div>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Loading parents...
                            </p>

                        </div>

                    </td>

                </tr>


                <!-- =================================================
                     EMPTY
                ================================================== -->

                <tr
                    v-else-if="
                        parentStore.parents.length === 0
                    "
                >

                    <td
                        colspan="7"
                        class="text-center py-12"
                    >

                        <div
                            class="flex flex-col items-center"
                        >

                            <div
                                class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3 text-xl"
                            >
                                👨‍👩‍👧
                            </div>

                            <p
                                class="font-medium text-gray-700 dark:text-gray-200"
                            >
                                No parents found
                            </p>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Try changing your search or filter.
                            </p>

                        </div>

                    </td>

                </tr>


                <!-- =================================================
                     PARENTS
                ================================================== -->

                <tr
                    v-else
                    v-for="(parent, index) in parentStore.parents"
                    :key="parent.id"
                    class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                >

                    <!-- S.N. -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >

                        {{
                            (parentStore.pagination.currentPage - 1)
                            * parentStore.pagination.perPage
                            + index + 1
                        }}

                    </td>


                    <!-- NAME -->

                    <td
                        class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100"
                    >

                        {{ parent.name }}

                    </td>


                    <!-- EMAIL -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >

                        {{ parent.email || '-' }}

                    </td>


                    <!-- PHONE -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >

                        {{ parent.phone || '-' }}

                    </td>


                    <!-- RELATIONSHIP -->

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >

                        {{ parent.relationship || '-' }}

                    </td>


                    <!-- STUDENTS -->

                    <td class="px-4 py-3">

                        <span
                            class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-full bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm font-semibold"
                        >
                            {{ parent.students?.length || 0 }}
                        </span>

                    </td>


                    <!-- ACTIONS -->

                    <td class="px-4 py-3">

                        <div
                            class="flex items-center gap-2"
                        >

                            <!-- VIEW -->

                            <button
                                @click="openViewModal(parent)"
                                class="px-3 py-1.5 text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                            >
                                View
                            </button>


                            <!-- EDIT -->

                            <button
                                @click="openEditModal(parent)"
                                class="px-3 py-1.5 text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                            >
                                Edit
                            </button>


                            <!-- DELETE -->

                            <button
                                @click="removeParent(parent.id)"
                                class="px-3 py-1.5 text-sm font-medium bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition"
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
        v-if="parentStore.pagination.lastPage > 1"
        class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-5"
    >

        <p
            class="text-sm text-gray-500 dark:text-gray-400"
        >

            Showing
            {{ parentStore.pagination.from }}
            -
            {{ parentStore.pagination.to }}
            of
            {{ parentStore.pagination.totalParents }}

        </p>


        <div class="flex gap-2">

            <!-- PREVIOUS -->

            <button
                @click="
                    changePage(
                        parentStore.pagination.currentPage - 1
                    )
                "
                :disabled="
                    parentStore.pagination.currentPage === 1
                "
                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
            >
                Previous
            </button>


            <!-- PAGE -->

            <span
                class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300"
            >

                Page
                {{ parentStore.pagination.currentPage }}
                of
                {{ parentStore.pagination.lastPage }}

            </span>


            <!-- NEXT -->

            <button
                @click="
                    changePage(
                        parentStore.pagination.currentPage + 1
                    )
                "
                :disabled="
                    parentStore.pagination.currentPage ===
                    parentStore.pagination.lastPage
                "
                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
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
        class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4"
    >

        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto transition-colors"
        >

            <!-- HEADER -->

            <div
                class="flex justify-between items-start mb-6"
            >

                <div>

                    <h2
                        class="text-xl font-bold text-gray-900 dark:text-white"
                    >

                        {{
                            editingParent
                                ? 'Edit Parent'
                                : 'Add Parent'
                        }}

                    </h2>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >

                        {{
                            editingParent
                                ? 'Update parent information and manage children'
                                : 'Create a new parent account'
                        }}

                    </p>

                </div>


                <button
                    @click="closeModal"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-xl"
                >
                    ×
                </button>

            </div>


            <!-- ERROR -->

            <div
                v-if="errorMessage"
                class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg"
            >
                {{ errorMessage }}
            </div>


            <!-- NAME -->

            <div class="mb-4">

                <label
                    class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300"
                >
                    Name
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Enter parent name"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                >

            </div>


            <!-- EMAIL -->

            <div class="mb-4">

                <label
                    class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300"
                >
                    Email
                </label>

                <input
                    v-model="form.email"
                    type="email"
                    placeholder="Enter email"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                >

            </div>


            <!-- PHONE -->

            <div class="mb-4">

                <label
                    class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300"
                >
                    Phone
                </label>

                <input
                    v-model="form.phone"
                    type="text"
                    placeholder="Enter phone number"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                >

            </div>


            <!-- RELATIONSHIP -->

            <div class="mb-6">

                <label
                    class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300"
                >
                    Relationship
                </label>

                <select
                    v-model="form.relationship"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                >

                    <option value="">
                        Select relationship
                    </option>

                    <option value="Father">
                        Father
                    </option>

                    <option value="Mother">
                        Mother
                    </option>

                    <option value="Guardian">
                        Guardian
                    </option>

                    <option value="Other">
                        Other
                    </option>

                </select>

            </div>


            <!-- =================================================
                 MANAGE CHILDREN
            ================================================== -->

            <div
                v-if="editingParent"
                class="mb-6"
            >

                <div
                    class="flex items-center justify-between mb-2"
                >

                    <label
                        class="text-sm font-semibold text-gray-700 dark:text-gray-300"
                    >
                        Manage Children
                    </label>

                    <span
                        class="text-xs text-gray-500 dark:text-gray-400"
                    >
                        {{ selectedStudentIds.length }}
                        selected
                    </span>

                </div>


                <!-- STUDENT SEARCH -->

                <input
                    v-model="studentSearchQuery"
                    type="text"
                    placeholder="Search children by name, class, or email..."
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 mb-3 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                />


                <!-- NO STUDENTS -->

                <div
                    v-if="students.length === 0"
                    class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 text-sm"
                >
                    No children assigned.
                </div>


                <!-- STUDENT LIST -->

                <div
                    v-else
                    class="border border-gray-200 dark:border-gray-600 rounded-xl divide-y divide-gray-200 dark:divide-gray-600 max-h-72 overflow-y-auto"
                >

                    <label
                        v-for="student in filteredStudents"
                        :key="student.id"
                        class="flex items-center gap-3 px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer"
                    >

                        <input
                            type="checkbox"
                            :checked="
                                selectedStudentIds.includes(
                                    student.id
                                )
                            "
                            @change="
                                toggleStudentSelection(student)
                            "
                            class="w-4 h-4 accent-green-600"
                        />


                        <div>

                            <p
                                class="font-medium text-gray-800 dark:text-gray-100"
                            >
                                {{ student.name }}
                            </p>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Class:
                                {{ student.class || 'Not assigned' }}
                            </p>


                            <!-- CURRENT PARENT -->

                            <p
                                v-if="
                                    student.parent &&
                                    student.parent.id ===
                                    editingParent?.id
                                "
                                class="text-xs text-green-600 dark:text-green-400 mt-1"
                            >
                                Currently assigned to this parent
                            </p>


                            <!-- OTHER PARENT -->

                            <p
                                v-else-if="student.parent"
                                class="text-xs text-orange-600 dark:text-orange-400 mt-1"
                            >

                                ⚠️ Current parent:

                                <span class="font-medium">
                                    {{ student.parent.name }}
                                </span>

                            </p>


                            <!-- NO PARENT -->

                            <p
                                v-else
                                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                            >
                                No parent assigned
                            </p>

                        </div>

                    </label>

                </div>

            </div>


            <!-- =================================================
                 STICKY ACTIONS
            ================================================== -->

            <div
                class="sticky bottom-0 -mx-6 -mb-6 mt-6 px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3"
            >

                <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    @click="saveParent"
                    :disabled="parentStore.loading"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >

                    {{
                        editingParent
                            ? 'Update Parent'
                            : 'Add Parent'
                    }}

                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         VIEW PARENT MODAL
    ====================================================== -->

    <div
        v-if="showViewModal"
        class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 p-4"
    >

        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto transition-colors"
        >

            <!-- HEADER -->

            <div
                class="flex justify-between items-center mb-6"
            >

                <div>

                    <h2
                        class="text-xl font-bold text-gray-900 dark:text-white"
                    >
                        Parent Details
                    </h2>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Parent information and assigned children
                    </p>

                </div>


                <button
                    @click="closeViewModal"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-xl"
                >
                    ×
                </button>

            </div>


            <!-- PARENT INFORMATION -->

            <div
                v-if="viewingParent"
                class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8"
            >

                <!-- NAME -->

                <div
                    class="p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Name
                    </p>

                    <p
                        class="mt-1 font-semibold text-gray-900 dark:text-white"
                    >
                        {{ viewingParent.name }}
                    </p>

                </div>


                <!-- EMAIL -->

                <div
                    class="p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Email
                    </p>

                    <p
                        class="mt-1 font-semibold text-gray-900 dark:text-white break-words"
                    >
                        {{ viewingParent.email || '-' }}
                    </p>

                </div>


                <!-- PHONE -->

                <div
                    class="p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Phone
                    </p>

                    <p
                        class="mt-1 font-semibold text-gray-900 dark:text-white"
                    >
                        {{ viewingParent.phone || '-' }}
                    </p>

                </div>


                <!-- RELATIONSHIP -->

                <div
                    class="p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Relationship
                    </p>

                    <p
                        class="mt-1 font-semibold text-gray-900 dark:text-white"
                    >
                        {{ viewingParent.relationship || '-' }}
                    </p>

                </div>

            </div>


            <!-- =================================================
                 CHILDREN
            ================================================== -->

            <div>

                <!-- CHILDREN HEADER -->

                <div
                    class="flex items-center justify-between mb-4 gap-3"
                >

                    <div>

                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Children
                        </h3>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                        >
                            Students currently assigned to this parent
                        </p>

                    </div>


                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm font-semibold whitespace-nowrap"
                    >

                        {{ viewingParent?.students?.length || 0 }}

                        {{
                            viewingParent?.students?.length === 1
                                ? 'Child'
                                : 'Children'
                        }}

                    </span>

                </div>


                <!-- NO CHILDREN -->

                <div
                    v-if="!viewingParent?.students?.length"
                    class="text-center py-8 bg-gray-50 dark:bg-gray-700 rounded-xl text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-600"
                >
                    This parent has no children assigned.
                </div>


                <!-- CHILDREN TABLE -->

                <div
                    v-else
                    class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-xl"
                >

                    <table class="w-full">

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
                                    Name
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                                >
                                    Class
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                                >
                                    Email
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                                >
                                    Phone
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="(
                                    student,
                                    index
                                ) in viewingParent.students"
                                :key="student.id"
                                class="border-t border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                            >

                                <td
                                    class="px-4 py-3 text-gray-700 dark:text-gray-300"
                                >
                                    {{ index + 1 }}
                                </td>


                                <td
                                    class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100"
                                >
                                    {{ student.name }}
                                </td>


                                <td
                                    class="px-4 py-3 text-gray-700 dark:text-gray-300"
                                >
                                    {{
                                        student.class ||
                                        'Not assigned'
                                    }}
                                </td>


                                <td
                                    class="px-4 py-3 text-gray-700 dark:text-gray-300"
                                >
                                    {{ student.email || '-' }}
                                </td>


                                <td
                                    class="px-4 py-3 text-gray-700 dark:text-gray-300"
                                >
                                    {{ student.phone || '-' }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- FOOTER -->

            <div class="flex justify-end mt-6">

                <button
                    @click="closeViewModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    Close
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         REASSIGN CHILD CONFIRMATION MODAL
    ====================================================== -->

    <div
        v-if="showReassignModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 dark:bg-black/70 p-4"
    >

        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 transition-colors"
        >

            <!-- HEADER -->

            <div
                class="flex items-center gap-3 mb-4"
            >

                <div
                    class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0"
                >
                    ⚠️
                </div>

                <h2
                    class="text-lg font-semibold text-gray-900 dark:text-white"
                >
                    Reassign Child?
                </h2>

            </div>


            <!-- CURRENT PARENT -->

            <p
                class="text-gray-600 dark:text-gray-300 mb-2"
            >
                This child is currently assigned to:
            </p>


            <p
                v-if="reassigningStudent?.parent"
                class="font-semibold text-gray-900 dark:text-white mb-4"
            >
                {{ reassigningStudent.parent.name }}
            </p>


            <!-- CONFIRMATION -->

            <p
                class="text-gray-600 dark:text-gray-300"
            >

                Do you want to reassign

                <span
                    class="font-semibold text-gray-900 dark:text-white"
                >
                    {{ reassigningStudent?.name }}
                </span>

                to

                <span
                    class="font-semibold text-gray-900 dark:text-white"
                >
                    {{ editingParent?.name }}
                </span>?

            </p>


            <!-- ACTIONS -->

            <div
                class="flex justify-end gap-3 mt-6"
            >

                <button
                    type="button"
                    @click="cancelReassignment"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    @click="confirmReassignment"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition"
                >
                    Reassign
                </button>

            </div>

        </div>

    </div>

</div>

</template>