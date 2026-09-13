
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
    <div class="min-h-screen bg-paper text-ink">
        <!-- HEADER -->
        <header class="border-b border-hairline">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-5 px-6 py-6 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center border border-hairline bg-surface text-lg"
                        >
                            👨‍👩‍👧
                        </span>

                        <span class="text-sm font-medium text-ink-soft">
                            Student Administration
                        </span>
                    </div>

                    <h1
                        class="font-serif text-4xl font-medium tracking-tight text-ink"
                    >
                        Parents
                    </h1>

                    <p class="mt-1 text-sm text-ink-soft">
                        Manage student parents and guardians
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- DARK MODE -->
                    <button
                        @click="toggleDarkMode"
                        class="border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink hover:bg-paper"
                    >
                        {{ darkMode ? '☀️ Light' : '🌙 Dark' }}
                    </button>

                    <!-- ADD PARENT -->
                    <button
                        @click="openAddModal"
                        class="border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-ink"
                    >
                        + Add Parent
                    </button>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="mx-auto max-w-7xl px-6 py-8">
            <!-- SEARCH & FILTER -->
            <section class="mb-6 border border-hairline bg-surface">
                <div
                    class="flex flex-col gap-3 p-4 sm:flex-row"
                >
                    <div class="relative flex-1">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search parents by name, email, or phone..."
                            class="w-full border border-hairline bg-paper px-4 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />
                    </div>

                    <select
                        v-model="relationshipFilter"
                        class="border border-hairline bg-paper px-4 py-2.5 text-sm text-ink outline-none focus:border-forest"
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
            </section>

            <!-- ERROR -->
            <div
                v-if="parentStore.error"
                class="mb-6 border border-sienna bg-surface px-5 py-4 text-sm text-sienna"
            >
                <div class="flex items-start gap-3">
                    <span class="font-semibold">
                        Error
                    </span>

                    <span>
                        {{ parentStore.error }}
                    </span>
                </div>
            </div>

            <!-- SUMMARY -->
            <div
                class="mb-6 grid grid-cols-2 border border-hairline bg-surface sm:grid-cols-4"
            >
                <div class="border-r border-hairline px-5 py-4">
                    <p class="text-xs text-ink-soft">
                        Parents shown
                    </p>

                    <p
                        class="mt-1 font-serif text-2xl font-medium text-ink"
                    >
                        {{ parentStore.parents.length }}
                    </p>
                </div>

                <div
                    class="border-r border-hairline px-5 py-4 sm:border-r"
                >
                    <p class="text-xs text-ink-soft">
                        Total parents
                    </p>

                    <p
                        class="mt-1 font-serif text-2xl font-medium text-ink"
                    >
                        {{ parentStore.pagination.totalParents }}
                    </p>
                </div>

                <div
                    class="border-r border-hairline px-5 py-4"
                >
                    <p class="text-xs text-ink-soft">
                        Page
                    </p>

                    <p
                        class="mt-1 font-serif text-2xl font-medium text-ink"
                    >
                        {{ parentStore.pagination.currentPage }}
                    </p>
                </div>

                <div class="px-5 py-4">
                    <p class="text-xs text-ink-soft">
                        Total pages
                    </p>

                    <p
                        class="mt-1 font-serif text-2xl font-medium text-ink"
                    >
                        {{ parentStore.pagination.lastPage }}
                    </p>
                </div>
            </div>

            <!-- TABLE -->
            <div class="overflow-x-auto border border-hairline bg-surface">
                <table class="w-full min-w-[900px]">
                    <thead class="border-b border-hairline bg-paper">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                            >
                                S.N.
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                            >
                                Name
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                            >
                                Email
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                            >
                                Phone
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                            >
                                Relationship
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                            >
                                Students
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-hairline">
                        <!-- LOADING -->
                        <tr v-if="parentStore.loading">
                            <td
                                colspan="7"
                                class="px-4 py-14 text-center text-sm text-ink-soft"
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
                                class="px-4 py-14 text-center"
                            >
                                <div
                                    class="mx-auto mb-4 flex h-12 w-12 items-center justify-center border border-hairline bg-paper text-xl"
                                >
                                    👨‍👩‍👧
                                </div>

                                <p
                                    class="font-serif text-xl font-medium text-ink"
                                >
                                    No parents found
                                </p>

                                <p
                                    class="mt-1 text-sm text-ink-soft"
                                >
                                    Try changing your search or relationship filter.
                                </p>
                            </td>
                        </tr>

                        <!-- PARENTS -->
                        <tr
                            v-else
                            v-for="(parent, index) in parentStore.parents"
                            :key="parent.id"
                            class="hover:bg-paper"
                        >
                            <!-- S.N. -->
                            <td
                                class="px-4 py-4 text-sm text-ink-soft"
                            >
                                {{
                                    (parentStore.pagination.currentPage - 1)
                                    * parentStore.pagination.perPage
                                    + index + 1
                                }}
                            </td>

                            <!-- NAME -->
                            <td class="px-4 py-4">
                                <p
                                    class="font-medium text-ink"
                                >
                                    {{ parent.name }}
                                </p>
                            </td>

                            <!-- EMAIL -->
                            <td
                                class="px-4 py-4 text-sm text-ink-soft"
                            >
                                {{ parent.email || '-' }}
                            </td>

                            <!-- PHONE -->
                            <td
                                class="px-4 py-4 text-sm text-ink-soft"
                            >
                                {{ parent.phone || '-' }}
                            </td>

                            <!-- RELATIONSHIP -->
                            <td class="px-4 py-4">
                                <span
                                    class="text-sm font-medium text-forest"
                                >
                                    {{ parent.relationship || '-' }}
                                </span>
                            </td>

                            <!-- STUDENTS -->
                            <td class="px-4 py-4">
                                <span
                                    class="font-serif text-lg font-medium text-ink"
                                >
                                    {{ parent.students?.length || 0 }}
                                </span>
                            </td>

                            <!-- ACTIONS -->
                            <td class="px-4 py-4">
                                <div
                                    class="flex flex-wrap gap-2"
                                >
                                    <!-- VIEW -->
                                    <button
                                        @click="openViewModal(parent)"
                                        class="border border-hairline px-3 py-1.5 text-sm font-medium text-ink hover:border-ink hover:bg-paper"
                                    >
                                        View
                                    </button>

                                    <!-- EDIT -->
                                    <button
                                        @click="openEditModal(parent)"
                                        class="border border-forest px-3 py-1.5 text-sm font-medium text-forest hover:bg-forest hover:text-white"
                                    >
                                        Edit
                                    </button>

                                    <!-- PASSWORD -->
                                    <button
                                        @click="openPasswordModal(parent)"
                                        class="border border-hairline px-3 py-1.5 text-sm font-medium text-ink-soft hover:border-ink hover:bg-paper hover:text-ink"
                                    >
                                        Password
                                    </button>

                                    <!-- DELETE -->
                                    <button
                                        @click="removeParent(parent.id)"
                                        class="border border-sienna px-3 py-1.5 text-sm font-medium text-sienna hover:bg-sienna hover:text-white"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div
                v-if="parentStore.pagination.lastPage > 1"
                class="flex flex-col gap-4 border-b border-hairline py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-sm text-ink-soft">
                    Showing
                    <span class="font-medium text-ink">
                        {{ parentStore.pagination.from }}
                    </span>
                    -
                    <span class="font-medium text-ink">
                        {{ parentStore.pagination.to }}
                    </span>
                    of
                    <span class="font-medium text-ink">
                        {{ parentStore.pagination.totalParents }}
                    </span>
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
                        class="border border-hairline px-3 py-1.5 text-sm font-medium text-ink hover:bg-surface disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Previous
                    </button>

                    <span
                        class="px-3 py-1.5 text-sm text-ink-soft"
                    >
                        Page
                        <span class="font-medium text-ink">
                            {{ parentStore.pagination.currentPage }}
                        </span>
                        of
                        <span class="font-medium text-ink">
                            {{ parentStore.pagination.lastPage }}
                        </span>
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
                        class="border border-hairline px-3 py-1.5 text-sm font-medium text-ink hover:bg-surface disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Next
                    </button>
                </div>
            </div>
        </main>

        <!-- =====================================================
             ADD / EDIT MODAL
        ====================================================== -->

        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4"
        >
            <div
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto border border-hairline bg-surface"
            >
                <!-- HEADER -->
                <div
                    class="flex items-start justify-between gap-4 border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p class="mb-1 text-xs text-ink-soft">
                            Parent record
                        </p>

                        <h2
                            class="font-serif text-2xl font-medium text-ink"
                        >
                            {{
                                editingParent
                                    ? 'Edit Parent'
                                    : 'Add Parent'
                            }}
                        </h2>
                    </div>

                    <button
                        @click="closeModal"
                        class="flex h-9 w-9 items-center justify-center border border-hairline text-xl text-ink-soft hover:border-ink hover:text-ink"
                    >
                        ×
                    </button>
                </div>

                <div class="px-6 py-6">
                    <!-- ERROR -->
                    <div
                        v-if="errorMessage"
                        class="mb-5 border border-sienna bg-paper px-4 py-3 text-sm text-sienna"
                    >
                        {{ errorMessage }}
                    </div>

                    <!-- NAME -->
                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-sm font-medium text-ink"
                        >
                            Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Enter parent name"
                            class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />
                    </div>

                    <!-- EMAIL -->
                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-sm font-medium text-ink"
                        >
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Enter email"
                            class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />
                    </div>

                    <!-- PHONE -->
                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-sm font-medium text-ink"
                        >
                            Phone
                        </label>

                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="Enter phone number"
                            class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />
                    </div>

                    <!-- RELATIONSHIP -->
                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-sm font-medium text-ink"
                        >
                            Relationship
                        </label>

                        <select
                            v-model="form.relationship"
                            class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none focus:border-forest"
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

                    <!-- PASSWORD -->
                    <div
                        v-if="!editingParent"
                        class="mb-6"
                    >
                        <label
                            class="mb-1.5 block text-sm font-medium text-ink"
                        >
                            Password
                        </label>

                        <input
                            v-model="form.password"
                            type="password"
                            minlength="8"
                            placeholder="Enter password"
                            class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />

                        <p
                            class="mt-1.5 text-xs text-ink-soft"
                        >
                            Minimum 8 characters.
                        </p>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div
                    class="flex justify-end gap-3 border-t border-hairline px-6 py-4"
                >
                    <button
                        @click="closeModal"
                        class="border border-hairline px-4 py-2 text-sm font-medium text-ink hover:bg-paper"
                    >
                        Cancel
                    </button>

                    <button
                        @click="saveParent"
                        :disabled="parentStore.loading"
                        class="border border-forest bg-forest px-4 py-2 text-sm font-medium text-white hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50"
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4"
        >
            <div
                class="max-h-[90vh] w-full max-w-3xl overflow-y-auto border border-hairline bg-surface"
            >
                <!-- HEADER -->
                <div
                    class="flex items-start justify-between gap-4 border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p class="mb-1 text-xs text-ink-soft">
                            Parent record
                        </p>

                        <h2
                            class="font-serif text-2xl font-medium text-ink"
                        >
                            Parent Details
                        </h2>
                    </div>

                    <button
                        @click="closeViewModal"
                        class="flex h-9 w-9 items-center justify-center border border-hairline text-xl text-ink-soft hover:border-ink hover:text-ink"
                    >
                        ×
                    </button>
                </div>

                <div
                    v-if="viewingParent"
                    class="px-6 py-6"
                >
                    <!-- INFORMATION -->
                    <section class="mb-8">
                        <div
                            class="mb-4 border-b border-hairline pb-2"
                        >
                            <h3
                                class="font-serif text-xl font-medium text-ink"
                            >
                                Parent Information
                            </h3>
                        </div>

                        <div
                            class="grid grid-cols-1 divide-y divide-hairline border border-hairline md:grid-cols-2 md:divide-x md:divide-y-0"
                        >
                            <div
                                class="px-5 py-4 md:border-b md:border-hairline"
                            >
                                <p class="text-xs text-ink-soft">
                                    Name
                                </p>

                                <p
                                    class="mt-1 font-medium text-ink"
                                >
                                    {{ viewingParent.name }}
                                </p>
                            </div>

                            <div
                                class="px-5 py-4 md:border-b md:border-hairline"
                            >
                                <p class="text-xs text-ink-soft">
                                    Email
                                </p>

                                <p
                                    class="mt-1 font-medium text-ink"
                                >
                                    {{ viewingParent.email || '-' }}
                                </p>
                            </div>

                            <div class="px-5 py-4">
                                <p class="text-xs text-ink-soft">
                                    Phone
                                </p>

                                <p
                                    class="mt-1 font-medium text-ink"
                                >
                                    {{ viewingParent.phone || '-' }}
                                </p>
                            </div>

                            <div class="px-5 py-4">
                                <p class="text-xs text-ink-soft">
                                    Relationship
                                </p>

                                <p
                                    class="mt-1 font-medium text-forest"
                                >
                                    {{
                                        viewingParent.relationship || '-'
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- CHILDREN -->
                    <section>
                        <div
                            class="mb-4 flex items-end justify-between border-b border-hairline pb-2"
                        >
                            <div>
                                <h3
                                    class="font-serif text-xl font-medium text-ink"
                                >
                                    Children
                                </h3>

                                <p
                                    class="mt-1 text-xs text-ink-soft"
                                >
                                    Students currently linked to this parent
                                </p>
                            </div>

                            <span
                                class="font-serif text-xl font-medium text-ink"
                            >
                                {{
                                    viewingParent?.students?.length || 0
                                }}
                            </span>
                        </div>

                        <!-- CHILDREN LIST -->
                        <div
                            v-if="
                                viewingParent?.students &&
                                viewingParent.students.length
                            "
                            class="divide-y divide-hairline border border-hairline"
                        >
                            <div
                                v-for="child in viewingParent.students"
                                :key="child.id"
                                class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between hover:bg-paper"
                            >
                                <div>
                                    <p
                                        class="font-medium text-ink"
                                    >
                                        {{ child.name }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-ink-soft"
                                    >
                                        Class:
                                        <span class="text-ink">
                                            {{ child.class || '-' }}
                                        </span>

                                        <span class="mx-2">
                                            •
                                        </span>

                                        Symbol:
                                        <span class="text-ink">
                                            {{ child.symbol_no || '-' }}
                                        </span>
                                    </p>
                                </div>

                                <button
                                    @click="openReassignModal(child)"
                                    class="self-start border border-sienna px-3 py-1.5 text-sm font-medium text-sienna hover:bg-sienna hover:text-white sm:self-auto"
                                >
                                    Reassign
                                </button>
                            </div>
                        </div>

                        <!-- NO CHILDREN -->
                        <div
                            v-else
                            class="border border-hairline bg-paper px-5 py-10 text-center"
                        >
                            <p
                                class="text-sm text-ink-soft"
                            >
                                No children assigned.
                            </p>
                        </div>
                    </section>
                </div>

                <!-- FOOTER -->
                <div
                    class="flex justify-end border-t border-hairline px-6 py-4"
                >
                    <button
                        @click="closeViewModal"
                        class="border border-hairline px-5 py-2 text-sm font-medium text-ink hover:bg-paper"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- =====================================================
             PASSWORD MODAL
        ====================================================== -->

        <div
            v-if="showPasswordModal"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-ink/50 p-4"
        >
            <div
                class="w-full max-w-md border border-hairline bg-surface"
            >
                <!-- HEADER -->
                <div
                    class="flex items-start justify-between gap-4 border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p class="mb-1 text-xs text-ink-soft">
                            Account security
                        </p>

                        <h2
                            class="font-serif text-2xl font-medium text-ink"
                        >
                            Change Parent Password
                        </h2>

                        <p
                            v-if="passwordParent"
                            class="mt-1 text-sm text-ink-soft"
                        >
                            {{ passwordParent.name }}
                        </p>
                    </div>

                    <button
                        @click="closePasswordModal"
                        class="flex h-9 w-9 items-center justify-center border border-hairline text-xl text-ink-soft hover:border-ink hover:text-ink"
                    >
                        ×
                    </button>
                </div>

                <div class="px-6 py-6">
                    <!-- SUCCESS -->
                    <div
                        v-if="passwordSuccess"
                        class="mb-4 border border-forest bg-paper px-4 py-3 text-sm text-forest"
                    >
                        {{ passwordSuccess }}
                    </div>

                    <!-- ERROR -->
                    <div
                        v-if="passwordError"
                        class="mb-4 border border-sienna bg-paper px-4 py-3 text-sm text-sienna"
                    >
                        {{ passwordError }}
                    </div>

                    <!-- NEW PASSWORD -->
                    <div class="mb-5">
                        <label
                            class="mb-1.5 block text-sm font-medium text-ink"
                        >
                            New Password
                        </label>

                        <input
                            v-model="passwordForm.password"
                            type="password"
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Enter new password"
                            class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />

                        <p
                            class="mt-1.5 text-xs text-ink-soft"
                        >
                            Password must be at least 8 characters.
                        </p>
                    </div>

                    <!-- CONFIRM -->
                    <div class="mb-2">
                        <label
                            class="mb-1.5 block text-sm font-medium text-ink"
                        >
                            Confirm New Password
                        </label>

                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            minlength="8"
                            autocomplete="new-password"
                            @keyup.enter="savePassword"
                            placeholder="Confirm new password"
                            class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />
                    </div>
                </div>

                <!-- ACTIONS -->
                <div
                    class="flex justify-end gap-3 border-t border-hairline px-6 py-4"
                >
                    <button
                        @click="closePasswordModal"
                        class="border border-hairline px-4 py-2 text-sm font-medium text-ink hover:bg-paper"
                    >
                        Cancel
                    </button>

                    <button
                        @click="savePassword"
                        :disabled="changingPassword"
                        class="border border-forest bg-forest px-4 py-2 text-sm font-medium text-white hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50"
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
            class="fixed inset-0 z-[70] flex items-center justify-center bg-ink/50 p-4"
        >
            <div
                class="w-full max-w-md border border-hairline bg-surface"
            >
                <!-- HEADER -->
                <div
                    class="flex items-start justify-between gap-4 border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p class="mb-1 text-xs text-ink-soft">
                            Student relationship
                        </p>

                        <h2
                            class="font-serif text-2xl font-medium text-ink"
                        >
                            Reassign Child
                        </h2>

                        <p
                            v-if="reassigningChild"
                            class="mt-1 text-sm text-ink-soft"
                        >
                            {{ reassigningChild.name }}
                        </p>
                    </div>

                    <button
                        @click="closeReassignModal"
                        class="flex h-9 w-9 items-center justify-center border border-hairline text-xl text-ink-soft hover:border-ink hover:text-ink"
                    >
                        ×
                    </button>
                </div>

                <div class="px-6 py-6">
                    <label
                        class="mb-1.5 block text-sm font-medium text-ink"
                    >
                        New Parent
                    </label>

                    <select
                        v-model="reassignParentId"
                        class="w-full border border-hairline bg-paper px-3 py-2.5 text-sm text-ink outline-none focus:border-forest"
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

                <!-- ACTIONS -->
                <div
                    class="flex justify-end gap-3 border-t border-hairline px-6 py-4"
                >
                    <button
                        @click="closeReassignModal"
                        class="border border-hairline px-4 py-2 text-sm font-medium text-ink hover:bg-paper"
                    >
                        Cancel
                    </button>

                    <button
                        @click="saveReassignment"
                        :disabled="!reassignParentId"
                        class="border border-forest bg-forest px-4 py-2 text-sm font-medium text-white hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Reassign
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>