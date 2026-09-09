
<script setup>

import { ref, onMounted, watch } from 'vue'
import { useParentStore } from '../../stores/parents/parent'
import {
    getParent,
    updateParentChildren,
    changeParentPassword
} from '../../services/parents/parentApi'

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
// DARK MODE
// =========================================================

const darkMode = ref(false)

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value

    document.documentElement.classList.toggle(
        'dark',
        darkMode.value
    )

    localStorage.setItem(
        'theme',
        darkMode.value ? 'dark' : 'light'
    )
}


// =========================================================
// ADD / EDIT FORM
// =========================================================

const showModal = ref(false)
const editingParent = ref(null)

const form = ref({
    name: '',
    email: '',
    phone: '',
    relationship: '',
    password: ''
})

const errorMessage = ref('')


// =========================================================
// VIEW PARENT
// =========================================================

const showViewModal = ref(false)
const viewingParent = ref(null)


// =========================================================
// REASSIGN CHILD
// =========================================================

const showReassignModal = ref(false)
const reassigningChild = ref(null)
const reassignParentId = ref('')


// =========================================================
// PASSWORD CHANGE
// =========================================================

const showPasswordModal = ref(false)
const passwordParent = ref(null)

const passwordForm = ref({
    password: '',
    password_confirmation: ''
})

const passwordError = ref('')
const passwordSuccess = ref('')
const changingPassword = ref(false)


// =========================================================
// OPEN ADD MODAL
// =========================================================

const openAddModal = () => {

    editingParent.value = null

    form.value = {
        name: '',
        email: '',
        phone: '',
        relationship: '',
        password: ''
    }

    errorMessage.value = ''

    showModal.value = true
}


// =========================================================
// OPEN EDIT MODAL
// =========================================================

const openEditModal = (parent) => {

    editingParent.value = parent

    form.value = {
        name: parent.name || '',
        email: parent.email || '',
        phone: parent.phone || '',
        relationship: parent.relationship || '',
        password: ''
    }

    errorMessage.value = ''

    showModal.value = true
}


// =========================================================
// CLOSE ADD / EDIT MODAL
// =========================================================

const closeModal = () => {

    showModal.value = false

    editingParent.value = null

    errorMessage.value = ''

    form.value = {
        name: '',
        email: '',
        phone: '',
        relationship: '',
        password: ''
    }
}


// =========================================================
// SAVE PARENT
// =========================================================

const saveParent = async () => {

    errorMessage.value = ''

    try {

        if (editingParent.value) {

            const updateData = {
                name: form.value.name,
                email: form.value.email,
                phone: form.value.phone,
                relationship: form.value.relationship
            }

            await parentStore.editParent(
                editingParent.value.id,
                updateData
            )

        } else {

            await parentStore.addParent(
                form.value
            )
        }

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
// VIEW PARENT
// =========================================================

const openViewModal = async (parent) => {

    try {

        const response = await getParent(parent.id)

        viewingParent.value =
            response.data.parent ||
            response.data

        showViewModal.value = true

    } catch (error) {

        console.error(
            'View parent error:',
            error
        )

        viewingParent.value = parent
        showViewModal.value = true
    }
}


const closeViewModal = () => {

    showViewModal.value = false
    viewingParent.value = null
}


// =========================================================
// OPEN REASSIGN CHILD MODAL
// =========================================================

const openReassignModal = (child) => {

    reassigningChild.value = child

    reassignParentId.value =
        child.parent_id || ''

    showReassignModal.value = true
}


// =========================================================
// CLOSE REASSIGN MODAL
// =========================================================

const closeReassignModal = () => {

    showReassignModal.value = false

    reassigningChild.value = null

    reassignParentId.value = ''
}


// =========================================================
// REASSIGN CHILD
// =========================================================

const saveReassignment = async () => {

    if (
        !reassigningChild.value ||
        !reassignParentId.value
    ) {
        return
    }

    try {

        await updateParentChildren(
            reassignParentId.value,
            [reassigningChild.value.id]
        )

        closeReassignModal()

        await parentStore.fetchParents(
            parentStore.pagination.currentPage,
            searchQuery.value,
            relationshipFilter.value
        )

        if (viewingParent.value) {

            const response =
                await getParent(
                    viewingParent.value.id
                )

            viewingParent.value =
                response.data.parent ||
                response.data
        }

    } catch (error) {

        console.error(
            'Reassign child error:',
            error
        )

        alert(
            error.response?.data?.message ||
            'Unable to reassign child.'
        )
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

    } catch (error) {

        console.error(
            'Delete parent error:',
            error
        )
    }
}


// =========================================================
// OPEN PASSWORD MODAL
// =========================================================

const openPasswordModal = (parent) => {

    passwordParent.value = parent

    passwordForm.value = {
        password: '',
        password_confirmation: ''
    }

    passwordError.value = ''
    passwordSuccess.value = ''

    showPasswordModal.value = true
}


// =========================================================
// CLOSE PASSWORD MODAL
// =========================================================

const closePasswordModal = () => {

    showPasswordModal.value = false

    passwordParent.value = null

    passwordForm.value = {
        password: '',
        password_confirmation: ''
    }

    passwordError.value = ''
    passwordSuccess.value = ''
}


// =========================================================
// SAVE NEW PASSWORD
// =========================================================

const savePassword = async () => {

    passwordError.value = ''
    passwordSuccess.value = ''

    // Minimum password length
    if (
        passwordForm.value.password.length < 8
    ) {

        passwordError.value =
            'Password must be at least 8 characters.'

        return
    }

    // Confirm password
    if (
        passwordForm.value.password !==
        passwordForm.value.password_confirmation
    ) {

        passwordError.value =
            'Passwords do not match.'

        return
    }

    if (!passwordParent.value) {
        passwordError.value =
            'Parent information is missing.'

        return
    }

    changingPassword.value = true

    try {

        const response =
            await changeParentPassword(
                passwordParent.value.id,
                passwordForm.value.password,
                passwordForm.value.password_confirmation
            )

        passwordSuccess.value =
            response.data.message ||
            'Parent password changed successfully.'

        passwordForm.value = {
            password: '',
            password_confirmation: ''
        }

    } catch (error) {

        console.error(
            'Change parent password error:',
            error
        )

        if (
            error.response?.data?.errors?.password
        ) {

            passwordError.value =
                error.response.data.errors.password[0]

        } else {

            passwordError.value =
                error.response?.data?.message ||
                'Unable to change parent password.'
        }

    } finally {

        changingPassword.value = false
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

    const savedTheme =
        localStorage.getItem('theme')

    if (savedTheme === 'dark') {

        darkMode.value = true

        document.documentElement.classList.add(
            'dark'
        )
    }

    parentStore.fetchParents()
})

</script>


<template>

<div
    class="min-h-screen p-6 bg-gray-100 dark:bg-gray-950 transition-colors duration-200"
>

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6"
    >

        <div>

            <h1
                class="text-2xl font-bold text-gray-900 dark:text-white"
            >
                Parents
            </h1>

            <p
                class="text-gray-500 dark:text-gray-400"
            >
                Manage student parents
            </p>

        </div>


        <div class="flex items-center gap-3">

            <!-- DARK MODE -->

            <button
                @click="toggleDarkMode"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
            >

                {{ darkMode ? '☀️ Light' : '🌙 Dark' }}

            </button>


            <!-- ADD PARENT -->

            <button
                @click="openAddModal"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                + Add Parent
            </button>

        </div>

    </div>


    <!-- =====================================================
         SEARCH & FILTER
    ====================================================== -->

    <div
        class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-6"
    >

        <input
            v-model="searchQuery"
            type="text"
            placeholder="Search parents by name, email, or phone..."
            class="flex-1 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
        />


        <select
            v-model="relationshipFilter"
            class="border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
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


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
        v-if="parentStore.error"
        class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-lg"
    >
        {{ parentStore.error }}
    </div>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div
        class="overflow-x-auto bg-white dark:bg-gray-900 rounded-xl shadow border border-gray-200 dark:border-gray-800"
    >

        <table class="w-full">

            <thead
                class="bg-gray-100 dark:bg-gray-800"
            >

                <tr>

                    <th
                        class="px-4 py-3 text-left text-gray-700 dark:text-gray-200"
                    >
                        S.N.
                    </th>

                    <th
                        class="px-4 py-3 text-left text-gray-700 dark:text-gray-200"
                    >
                        Name
                    </th>

                    <th
                        class="px-4 py-3 text-left text-gray-700 dark:text-gray-200"
                    >
                        Email
                    </th>

                    <th
                        class="px-4 py-3 text-left text-gray-700 dark:text-gray-200"
                    >
                        Phone
                    </th>

                    <th
                        class="px-4 py-3 text-left text-gray-700 dark:text-gray-200"
                    >
                        Relationship
                    </th>

                    <th
                        class="px-4 py-3 text-left text-gray-700 dark:text-gray-200"
                    >
                        Students
                    </th>

                    <th
                        class="px-4 py-3 text-left text-gray-700 dark:text-gray-200"
                    >
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                <!-- LOADING -->

                <tr v-if="parentStore.loading">

                    <td
                        colspan="7"
                        class="text-center py-8 text-gray-500 dark:text-gray-400"
                    >
                        Loading parents...
                    </td>

                </tr>


                <!-- EMPTY -->

                <tr
                    v-else-if="parentStore.parents.length === 0"
                >

                    <td
                        colspan="7"
                        class="text-center py-8 text-gray-500 dark:text-gray-400"
                    >
                        No parents found.
                    </td>

                </tr>


                <!-- PARENTS -->

                <tr
                    v-else
                    v-for="(parent, index) in parentStore.parents"
                    :key="parent.id"
                    class="border-t border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50"
                >

                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{
                            (parentStore.pagination.currentPage - 1)
                            * parentStore.pagination.perPage
                            + index + 1
                        }}
                    </td>


                    <td
                        class="px-4 py-3 font-medium text-gray-900 dark:text-white"
                    >
                        {{ parent.name }}
                    </td>


                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{ parent.email || '-' }}
                    </td>


                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{ parent.phone || '-' }}
                    </td>


                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{ parent.relationship || '-' }}
                    </td>


                    <td
                        class="px-4 py-3 text-gray-700 dark:text-gray-300"
                    >
                        {{ parent.students?.length || 0 }}
                    </td>


                    <!-- ACTIONS -->

                    <td class="px-4 py-3">

                        <div
                            class="flex flex-wrap gap-2"
                        >

                            <!-- VIEW -->

                            <button
                                @click="openViewModal(parent)"
                                class="px-3 py-1.5 text-sm font-medium bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                            >
                                View
                            </button>


                            <!-- EDIT -->

                            <button
                                @click="openEditModal(parent)"
                                class="px-3 py-1.5 text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                            >
                                Edit
                            </button>


                            <!-- PASSWORD -->

                            <button
                                @click="openPasswordModal(parent)"
                                class="px-3 py-1.5 text-sm font-medium bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition"
                            >
                                Password
                            </button>


                            <!-- DELETE -->

                            <button
                                @click="removeParent(parent.id)"
                                class="px-3 py-1.5 text-sm font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition"
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
        class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4"
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


        <div class="flex items-center gap-2">

            <button
                @click="
                    changePage(
                        parentStore.pagination.currentPage - 1
                    )
                "
                :disabled="
                    parentStore.pagination.currentPage === 1
                "
                class="px-3 py-1.5 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-200 disabled:opacity-50"
            >
                Previous
            </button>


            <span
                class="px-3 py-1.5 text-gray-700 dark:text-gray-300"
            >

                Page
                {{ parentStore.pagination.currentPage }}
                of
                {{ parentStore.pagination.lastPage }}

            </span>


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
                class="px-3 py-1.5 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-200 disabled:opacity-50"
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
            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-lg p-6 border border-gray-200 dark:border-gray-800"
        >

            <div
                class="flex justify-between items-center mb-6"
            >

                <h2
                    class="text-xl font-bold text-gray-900 dark:text-white"
                >

                    {{
                        editingParent
                            ? 'Edit Parent'
                            : 'Add Parent'
                    }}

                </h2>


                <button
                    @click="closeModal"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white text-xl"
                >
                    ×
                </button>

            </div>


            <!-- ERROR -->

            <div
                v-if="errorMessage"
                class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg"
            >
                {{ errorMessage }}
            </div>


            <!-- NAME -->

            <div class="mb-4">

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    Name
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2"
                    placeholder="Enter parent name"
                >

            </div>


            <!-- EMAIL -->

            <div class="mb-4">

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    Email
                </label>

                <input
                    v-model="form.email"
                    type="email"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2"
                    placeholder="Enter email"
                >

            </div>


            <!-- PHONE -->

            <div class="mb-4">

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    Phone
                </label>

                <input
                    v-model="form.phone"
                    type="text"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2"
                    placeholder="Enter phone number"
                >

            </div>


            <!-- RELATIONSHIP -->

            <div class="mb-4">

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    Relationship
                </label>

                <select
                    v-model="form.relationship"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2"
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


            <!-- PASSWORD ONLY WHEN ADDING -->

            <div
                v-if="!editingParent"
                class="mb-6"
            >

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    Password
                </label>

                <input
                    v-model="form.password"
                    type="password"
                    minlength="8"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2"
                    placeholder="Enter password"
                >

                <p
                    class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                >
                    Minimum 8 characters.
                </p>

            </div>


            <!-- ACTIONS -->

            <div
                class="flex justify-end gap-3"
            >

                <button
                    @click="closeModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    Cancel
                </button>


                <button
                    @click="saveParent"
                    :disabled="parentStore.loading"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
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
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >

        <div
            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-800"
        >

            <!-- HEADER -->

            <div
                class="flex justify-between items-center mb-6"
            >

                <h2
                    class="text-xl font-bold text-gray-900 dark:text-white"
                >
                    Parent Details
                </h2>


                <button
                    @click="closeViewModal"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-xl"
                >
                    ×
                </button>

            </div>


            <!-- PARENT INFORMATION -->

            <div
                v-if="viewingParent"
                class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8"
            >

                <div>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        Name
                    </p>

                    <p
                        class="font-medium text-gray-900 dark:text-white"
                    >
                        {{ viewingParent.name }}
                    </p>

                </div>


                <div>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        Email
                    </p>

                    <p
                        class="font-medium text-gray-900 dark:text-white"
                    >
                        {{ viewingParent.email || '-' }}
                    </p>

                </div>


                <div>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        Phone
                    </p>

                    <p
                        class="font-medium text-gray-900 dark:text-white"
                    >
                        {{ viewingParent.phone || '-' }}
                    </p>

                </div>


                <div>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        Relationship
                    </p>

                    <p
                        class="font-medium text-gray-900 dark:text-white"
                    >
                        {{ viewingParent.relationship || '-' }}
                    </p>

                </div>

            </div>


            <!-- CHILDREN -->

            <div>

                <div
                    class="flex items-center justify-between mb-4"
                >

                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Children
                    </h3>

                </div>


                <div
                    v-if="
                        viewingParent?.students &&
                        viewingParent.students.length
                    "
                    class="space-y-3"
                >

                    <div
                        v-for="child in viewingParent.students"
                        :key="child.id"
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                    >

                        <div>

                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ child.name }}
                            </p>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Class:
                                {{ child.class || '-' }}

                                <span class="mx-1">
                                    •
                                </span>

                                Symbol:
                                {{ child.symbol_no || '-' }}
                            </p>

                        </div>


                        <button
                            @click="openReassignModal(child)"
                            class="px-3 py-1.5 text-sm bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition"
                        >
                            Reassign
                        </button>

                    </div>

                </div>


                <div
                    v-else
                    class="p-6 text-center bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-500 dark:text-gray-400"
                >
                    No children assigned.
                </div>

            </div>


            <!-- CLOSE -->

            <div
                class="flex justify-end mt-6"
            >

                <button
                    @click="closeViewModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    Close
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PASSWORD MODAL
         ADMIN CAN DIRECTLY SET A NEW PASSWORD
    ====================================================== -->

    <div
        v-if="showPasswordModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4"
    >

        <div
            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md p-6 border border-gray-200 dark:border-gray-800"
        >

            <!-- HEADER -->

            <div
                class="flex justify-between items-center mb-6"
            >

                <div>

                    <h2
                        class="text-xl font-bold text-gray-900 dark:text-white"
                    >
                        Change Parent Password
                    </h2>

                    <p
                        v-if="passwordParent"
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        {{ passwordParent.name }}
                    </p>

                </div>


                <button
                    @click="closePasswordModal"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-xl"
                >
                    ×
                </button>

            </div>


            <!-- SUCCESS -->

            <div
                v-if="passwordSuccess"
                class="mb-4 p-3 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-lg"
            >
                {{ passwordSuccess }}
            </div>


            <!-- ERROR -->

            <div
                v-if="passwordError"
                class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-lg"
            >
                {{ passwordError }}
            </div>


            <!-- NEW PASSWORD -->

            <div class="mb-4">

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    New Password
                </label>

                <input
                    v-model="passwordForm.password"
                    type="password"
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                    placeholder="Enter new password"
                >

                <p
                    class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                >
                    Password must be at least 8 characters.
                </p>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="mb-6">

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    Confirm New Password
                </label>

                <input
                    v-model="passwordForm.password_confirmation"
                    type="password"
                    minlength="8"
                    autocomplete="new-password"
                    @keyup.enter="savePassword"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                    placeholder="Confirm new password"
                >

            </div>


            <!-- ACTIONS -->

            <div
                class="flex justify-end gap-3"
            >

                <button
                    @click="closePasswordModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    Cancel
                </button>


                <button
                    @click="savePassword"
                    :disabled="changingPassword"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >

                    {{
                        changingPassword
                            ? 'Changing...'
                            : 'Change Password'
                    }}

                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         REASSIGN CHILD MODAL
    ====================================================== -->

    <div
        v-if="showReassignModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-[70] p-4"
    >

        <div
            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md p-6 border border-gray-200 dark:border-gray-800"
        >

            <div
                class="flex justify-between items-center mb-6"
            >

                <div>

                    <h2
                        class="text-xl font-bold text-gray-900 dark:text-white"
                    >
                        Reassign Child
                    </h2>

                    <p
                        v-if="reassigningChild"
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        {{ reassigningChild.name }}
                    </p>

                </div>


                <button
                    @click="closeReassignModal"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-xl"
                >
                    ×
                </button>

            </div>


            <div class="mb-6">

                <label
                    class="block mb-1 font-medium text-gray-700 dark:text-gray-200"
                >
                    New Parent
                </label>

                <select
                    v-model="reassignParentId"
                    class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2"
                >

                    <option value="">
                        Select parent
                    </option>

                    <option
                        v-for="parent in parentStore.parents"
                        :key="parent.id"
                        :value="parent.id"
                    >
                        {{ parent.name }}
                    </option>

                </select>

            </div>


            <div
                class="flex justify-end gap-3"
            >

                <button
                    @click="closeReassignModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    Cancel
                </button>


                <button
                    @click="saveReassignment"
                    :disabled="!reassignParentId"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    Reassign
                </button>

            </div>

        </div>

    </div>

</div>

</template>
