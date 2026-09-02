<script setup>

import { ref, onMounted, watch } from 'vue'
import { useParentStore } from '../../stores/parents/parent'
import { getParent } from '../../services/parents/parentApi'

const parentStore = useParentStore()

//search & filter

const searchQuery = ref('')
const relationshipFilter = ref('all')
watch(
    [searchQuery, relationshipFilter],()=>{
        parentStore.fetchParents(
            1,
            searchQuery.value,
            relationshipFilter.value
        )
    }

)

// =========================================================
// FORM
// =========================================================

const showModal = ref(false)
const editingParent = ref(null)
const showViewModal = ref(false)
const viewingParent = ref(null)

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

const openEditModal = (parent) => {

    editingParent.value = parent

    form.value = {
        name: parent.name || '',
        email: parent.email || '',
        phone: parent.phone || '',
        relationship: parent.relationship || ''
    }

    errorMessage.value = ''

    showModal.value = true
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


// =========================================================
// CLOSE MODAL
// =========================================================

const closeModal = () => {

    showModal.value = false

    editingParent.value = null

    errorMessage.value = ''

}


// =========================================================
// SAVE PARENT
// =========================================================

const saveParent = async () => {

    errorMessage.value = ''

    try {

        if (editingParent.value) {

            await parentStore.editParent(
                editingParent.value.id,
                form.value
            )

        } else {

            await parentStore.addParent(
                form.value
            )

        }

        closeModal()

    } catch (error) {

        errorMessage.value =
            error.response?.data?.message ||
            'Something went wrong.'

    }

}


// =========================================================
// DELETE PARENT
// =========================================================

const removeParent = async (id) => {

    if (!confirm('Are you sure you want to delete this parent?')) {
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
// PAGINATION
// =========================================================

const changePage = async (page) => {

    if (
        page < 1 ||
        page > parentStore.pagination.lastPage
    ) {
        return
    }

    await parentStore.fetchParents(page,
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

<div class="p-6">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="flex items-center justify-between mb-6">

        <div>

            <h1 class="text-2xl font-bold text-gray-800">
                Parents
            </h1>

            <p class="text-gray-500">
                Manage student parents
            </p>

        </div>


        <button
            @click="openAddModal"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
        >
            + Add Parent
        </button>

    </div>

    <!--search & filter-->

    <div
    class="flex items-center gap-4 mb-6">

    <!--search-->
    <input
        v-model="searchQuery"
        type="text"
        placeholder="Search parents by name, email, or phone...."
        class=" flex-1 border rounded-lg px-4 py-2 w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-green-500"
    />

    <select
    v-model="relationshipFilter"
    class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
    >

        <option value="all">All Relationships</option>
        <option value="Father">Father</option>
        <option value="Mother">Mother</option>
        <option value="Guardian">Guardian</option>
        <option value="Other">Other</option>

    </select>
    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
        v-if="parentStore.error"
        class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"
    >
        {{ parentStore.error }}
    </div>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="overflow-x-auto bg-white rounded-lg shadow">

        <table class="w-full min-w-[700px]">

            <thead class="bg-gray-100">

                <tr>

                    <th class="px-4 py-3 text-left whitespace-nowrap">
                        S.N.
                    </th>

                    <th class="px-4 py-3 text-left whitespace-nowrap">
                        Name
                    </th>

                    <th class="px-4 py-3 text-left whitespace-nowrap">
    Class
</th>

                    <th class="px-4 py-3 text-left whitespace-nowrap">
                        Email
                    </th>

                    <th class="px-4 py-3 text-left whitespace-nowrap">
                        Phone
                    </th>

                    <th class="px-4 py-3 text-left">
                        Relationship
                    </th>

                    <th class="px-4 py-3 text-left">
                        Students
                    </th>

                    <th class="px-4 py-3 text-left">
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                <!-- LOADING -->

                <tr v-if="parentStore.loading">

                    <td
                        colspan="7"
                        class="text-center py-8 text-gray-500"
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
                        class="text-center py-8 text-gray-500"
                    >
                        No parents found.
                    </td>

                </tr>


                <!-- PARENTS -->

                <tr
                    v-else
                    v-for="(parent, index) in parentStore.parents"
                    :key="parent.id"
                    class="border-t hover:bg-gray-50"
                >

                    <td class="px-4 py-3">

                        {{
                            (parentStore.pagination.currentPage - 1)
                            * parentStore.pagination.perPage
                            + index + 1
                        }}

                    </td>


                    <td class="px-4 py-3 font-medium">

                        {{ parent.name }}

                    </td>


                    <td class="px-4 py-3">

                        {{ parent.email || '-' }}

                    </td>


                    <td class="px-4 py-3">

                        {{ parent.phone }}

                    </td>


                    <td class="px-4 py-3">

                        {{ parent.relationship }}

                    </td>


                    <td class="px-4 py-3">

                        {{ parent.students?.length || 0 }}

                    </td>


                    <td class="px-4 py-3">

                        <div class="flex gap-2">
                            <button
                            @click="openViewModal(parent)"
                            class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">
                                View
                            </button>

                            <button
                                @click="openEditModal(parent)"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                            >
                                Edit
                            </button>

                            <button
                                @click="removeParent(parent.id)"
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
        v-if="parentStore.pagination.lastPage > 1"
        class="flex items-center justify-between mt-4"
    >

        <p class="text-sm text-gray-500">

            Showing
            {{ parentStore.pagination.from }}
            -
            {{ parentStore.pagination.to }}
            of
            {{ parentStore.pagination.totalParents }}

        </p>


        <div class="flex gap-2">

            <button
                @click="changePage(parentStore.pagination.currentPage - 1)"
                :disabled="parentStore.pagination.currentPage === 1"
                class="px-3 py-1 border rounded disabled:opacity-50"
            >
                Previous
            </button>


            <span class="px-3 py-1">

                Page
                {{ parentStore.pagination.currentPage }}
                of
                {{ parentStore.pagination.lastPage }}

            </span>


            <button
                @click="changePage(parentStore.pagination.currentPage + 1)"
                :disabled="
                    parentStore.pagination.currentPage ===
                    parentStore.pagination.lastPage
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
                        editingParent
                            ? 'Edit Parent'
                            : 'Add Parent'
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
                    Name
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border rounded-lg px-3 py-2"
                    placeholder="Enter parent name"
                >

            </div>


            <!-- EMAIL -->

            <div class="mb-4">

                <label class="block mb-1 font-medium">
                    Email
                </label>

                <input
                    v-model="form.email"
                    type="email"
                    class="w-full border rounded-lg px-3 py-2"
                    placeholder="Enter email"
                >

            </div>


            <!-- PHONE -->

            <div class="mb-4">

                <label class="block mb-1 font-medium">
                    Phone
                </label>

                <input
                    v-model="form.phone"
                    type="text"
                    class="w-full border rounded-lg px-3 py-2"
                    placeholder="Enter phone number"
                >

            </div>


            <!-- RELATIONSHIP -->

            <div class="mb-6">

                <label class="block mb-1 font-medium">
                    Relationship
                </label>

                <select
                    v-model="form.relationship"
                    class="w-full border rounded-lg px-3 py-2"
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


            <!-- ACTIONS -->

            <div class="flex justify-end gap-3">

                <button
                    @click="closeModal"
                    class="px-4 py-2 border rounded-lg"
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
        class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto"
    >

        <!-- HEADER -->

        <div class="flex justify-between items-center mb-6">

            <h2 class="text-xl font-bold text-gray-800">
                Parent Details
            </h2>

            <button
                @click="showViewModal = false"
                class="text-gray-500 hover:text-gray-800 text-xl"
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
                <p class="text-sm text-gray-500">
                    Name
                </p>

                <p class="font-medium text-gray-800">
                    {{ viewingParent.name }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Email
                </p>

                <p class="font-medium text-gray-800">
                    {{ viewingParent.email || '-' }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Phone
                </p>

                <p class="font-medium text-gray-800">
                    {{ viewingParent.phone }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Relationship
                </p>

                <p class="font-medium text-gray-800">
                    {{ viewingParent.relationship }}
                </p>
            </div>

        </div>


        <!-- CHILDREN -->

        <div>

            <div class="flex items-center justify-between mb-4">

                <h3 class="text-lg font-semibold text-gray-800">
                    Children
                </h3>

                <span class="text-sm text-gray-500">
                    {{ viewingParent?.students?.length || 0 }}
                    child(ren)
                </span>

            </div>


            <!-- NO CHILDREN -->

            <div
                v-if="!viewingParent?.students?.length"
                class="text-center py-8 bg-gray-50 rounded-lg text-gray-500"
            >

                This parent has no children assigned.

            </div>


            <!-- CHILDREN TABLE -->

            <div
                v-else
                class="overflow-x-auto border rounded-lg"
            >

                <table class="w-full">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="px-4 py-3 text-left">
                                S.N.
                            </th>

                            <th class="px-4 py-3 text-left">
                                Name
                            </th>

                            <th class="px-4 py-3 text-left">
    Class
</th>

                            <th class="px-4 py-3 text-left">
                                Email
                            </th>

                            <th class="px-4 py-3 text-left">
                                Phone
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr
                            v-for="(student, index) in viewingParent.students"
                            :key="student.id"
                            class="border-t hover:bg-gray-50"
                        >

                            <td class="px-4 py-3">
                                {{ index + 1 }}
                            </td>

                            <td class="px-4 py-3 font-medium">
                                {{ student.name }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ student.class || 'Not assigned' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ student.email || '-' }}
                            </td>

                            <td class="px-4 py-3">
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
                @click="showViewModal = false"
                class="px-4 py-2 border rounded-lg hover:bg-gray-50"
            >
                Close
            </button>

        </div>

    </div>

</div>

</div>

</template>