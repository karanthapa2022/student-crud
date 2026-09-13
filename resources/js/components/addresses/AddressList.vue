<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAddressStore } from '../../stores/addresses/address'
import { nepalLocations } from '../../data/nepalLocations'

const addressStore = useAddressStore()

// =========================================================
// SEARCH AND FILTER
// =========================================================

const searchQuery = ref('')
const provinceFilter = ref('all')

watch(
    [
        searchQuery,
        provinceFilter
    ],
    () => {
        addressStore.fetchAddresses(
            1,
            searchQuery.value,
            provinceFilter.value
        )
    }
)

// =========================================================
// FORM STATE
// =========================================================

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = ref({
    province: '',
    district: '',
    municipality: '',
    ward: '',
    city: '',
    street: ''
})

watch(
    () => form.value.province,
    () => {

        const districts =
            nepalLocations[form.value.province] || {}

        // Only reset district if it is not valid
        // for the selected province
        if (
            !Object.prototype.hasOwnProperty.call(
                districts,
                form.value.district
            )
        ) {
            form.value.district = ''
        }

    }
)

const successMessage = ref('')

// =========================================================
// FETCH ADDRESSES
// =========================================================

onMounted(() => {

    addressStore.fetchAddresses(
        1,
        searchQuery.value,
        provinceFilter.value
    )

})

// =========================================================
// RESET FORM
// =========================================================

const resetForm = () => {

    form.value = {
        province: '',
        district: '',
        municipality: '',
        ward: '',
        city: '',
        street: ''
    }

    isEditing.value = false
    editingId.value = null

}

// =========================================================
// OPEN ADD MODAL
// =========================================================

const openAddModal = () => {

    resetForm()

    showModal.value = true

}

// =========================================================
// CLOSE MODAL
// =========================================================

const closeModal = () => {

    showModal.value = false

    resetForm()

}

// =========================================================
// EDIT ADDRESS
// =========================================================

const openEditModal = (address) => {

    isEditing.value = true

    editingId.value = address.id

    form.value = {
        province: address.province || '',
        district: address.district || '',
        municipality: address.municipality || '',
        ward: address.ward || '',
        city: address.city || '',
        street: address.street || ''
    }

    showModal.value = true

}

// =========================================================
// SAVE ADDRESS
// =========================================================

const saveAddress = async () => {

    successMessage.value = ''

    try {

        if (isEditing.value) {

            await addressStore.editAddress(
                editingId.value,
                form.value
            )

            successMessage.value =
                'Address updated successfully.'

        } else {

            await addressStore.addAddress(form.value)

            successMessage.value =
                'Address added successfully.'

        }

        closeModal()

        setTimeout(() => {
            successMessage.value = ''
        }, 3000)

    } catch (error) {

        console.error(
            'Address save error:',
            error
        )

    }

}

// =========================================================
// DELETE ADDRESS
// =========================================================

const deleteAddress = async (id) => {

    if (
        !confirm(
            'Are you sure you want to delete this address?'
        )
    ) {
        return
    }

    try {

        await addressStore.removeAddress(id)

        successMessage.value =
            'Address deleted successfully.'

        setTimeout(() => {
            successMessage.value = ''
        }, 3000)

    } catch (error) {

        console.error(
            'Address delete error:',
            error
        )

    }

}

// =========================================================
// PAGINATION
// =========================================================

const changePage = (page) => {

    if (
        page < 1 ||
        page > addressStore.pagination.lastPage
    ) {
        return
    }

    addressStore.fetchAddresses(
        page,
        searchQuery.value,
        provinceFilter.value
    )

}

const paginationPages = computed(() => {

    const pages = []

    for (
        let i = 1;
        i <= addressStore.pagination.lastPage;
        i++
    ) {
        pages.push(i)
    }

    return pages

})

</script>


<template>

    <div
        class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 transition-colors"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6"
        >

            <div>

                <h1
                    class="text-3xl font-bold text-gray-900 dark:text-white"
                >
                    Addresses
                </h1>

                <p
                    class="text-gray-500 dark:text-gray-400 mt-1"
                >
                    Manage student addresses
                </p>

            </div>


            <!-- HEADER ACTIONS -->

            <div
                class="flex flex-col sm:flex-row gap-3"
            >

                <!-- ADMIN DASHBOARD -->

                <button
                    type="button"
                    @click="$router.push('/dashboard')"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-600 dark:bg-purple-700 text-white font-medium hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-sm"
                >
                    Dashboard
                </button>


                <!-- ADD ADDRESS -->

                <button
                    type="button"
                    @click="openAddModal"
                    class="w-full sm:w-auto bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition shadow-sm"
                >
                    + Add Address
                </button>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- SEARCH & FILTER -->
        <!-- ================================================= -->

        <div
            class="flex flex-col md:flex-row gap-4 mb-6"
        >

            <!-- SEARCH -->

            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search province, district, municipality, city or street..."
                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors"
            />


            <!-- PROVINCE FILTER -->

            <select
                v-model="provinceFilter"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors"
            >

                <option value="all">
                    All Provinces
                </option>

                <option value="Koshi">
                    Koshi
                </option>

                <option value="Madhesh">
                    Madhesh
                </option>

                <option value="Bagmati">
                    Bagmati
                </option>

                <option value="Gandaki">
                    Gandaki
                </option>

                <option value="Lumbini">
                    Lumbini
                </option>

                <option value="Karnali">
                    Karnali
                </option>

                <option value="Sudurpashchim">
                    Sudurpashchim
                </option>

            </select>

        </div>


        <!-- ================================================= -->
        <!-- SUCCESS MESSAGE -->
        <!-- ================================================= -->

        <div
            v-if="successMessage"
            class="mb-5 rounded-lg bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3"
        >
            {{ successMessage }}
        </div>


        <!-- ================================================= -->
        <!-- ERROR MESSAGE -->
        <!-- ================================================= -->

        <div
            v-if="addressStore.error"
            class="mb-5 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3"
        >
            {{ addressStore.error }}
        </div>


        <!-- ================================================= -->
        <!-- TABLE -->
        <!-- ================================================= -->

        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700 transition-colors"
        >

            <!-- LOADING -->

            <div
                v-if="addressStore.loading"
                class="p-10 text-center text-gray-500 dark:text-gray-400"
            >
                Loading addresses...
            </div>


            <!-- TABLE -->

            <div
                v-else
                class="overflow-x-auto"
            >

                <table class="w-full">

                    <!-- TABLE HEADER -->

                    <thead
                        class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600"
                    >

                        <tr>

                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                S.N.
                            </th>

                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                Province
                            </th>

                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                District
                            </th>

                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                Municipality/VDC
                            </th>

                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                Ward
                            </th>

                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                City
                            </th>

                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                Street
                            </th>

                            <th
                                class="px-6 py-4 text-center text-sm font-semibold text-gray-600 dark:text-gray-200"
                            >
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <!-- TABLE BODY -->

                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-700"
                    >

                        <tr
                            v-for="(address, index) in addressStore.addresses"
                            :key="address.id"
                            class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >

                            <!-- S.N. -->

                            <td
                                class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                            >

                                {{
                                    addressStore.pagination.from +
                                    index
                                }}

                            </td>


                            <!-- PROVINCE -->

                            <td
                                class="px-6 py-4 text-sm text-gray-800 dark:text-gray-100"
                            >
                                {{ address.province }}
                            </td>


                            <!-- DISTRICT -->

                            <td
                                class="px-6 py-4 text-sm text-gray-800 dark:text-gray-100"
                            >
                                {{ address.district }}
                            </td>


                            <!-- MUNICIPALITY -->

                            <td
                                class="px-6 py-4 text-sm text-gray-800 dark:text-gray-100"
                            >
                                {{ address.municipality }}
                            </td>


                            <!-- WARD -->

                            <td
                                class="px-6 py-4 text-sm text-gray-800 dark:text-gray-100"
                            >
                                {{ address.ward }}
                            </td>


                            <!-- CITY -->

                            <td
                                class="px-6 py-4 text-sm text-gray-800 dark:text-gray-100"
                            >
                                {{ address.city || '-' }}
                            </td>


                            <!-- STREET -->

                            <td
                                class="px-6 py-4 text-sm text-gray-800 dark:text-gray-100"
                            >
                                {{ address.street || '-' }}
                            </td>


                            <!-- ACTIONS -->

                            <td class="px-6 py-4">

                                <div
                                    class="flex justify-center gap-2"
                                >

                                    <!-- EDIT -->

                                    <button
                                        type="button"
                                        @click="openEditModal(address)"
                                        class="px-3 py-1.5 text-sm rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition"
                                    >
                                        Edit
                                    </button>


                                    <!-- DELETE -->

                                    <button
                                        type="button"
                                        @click="deleteAddress(address.id)"
                                        class="px-3 py-1.5 text-sm rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50 transition"
                                    >
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- EMPTY -->

                        <tr
                            v-if="addressStore.addresses.length === 0"
                        >

                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                            >
                                No addresses found.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- PAGINATION -->
        <!-- ================================================= -->

        <div
            v-if="addressStore.pagination.lastPage > 1"
            class="flex flex-wrap justify-center items-center gap-2 mt-6"
        >

            <!-- PREVIOUS -->

            <button
                type="button"
                @click="
                    changePage(
                        addressStore.pagination.currentPage - 1
                    )
                "
                :disabled="
                    addressStore.pagination.currentPage === 1
                "
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition"
            >
                Previous
            </button>


            <!-- PAGE NUMBERS -->

            <button
                v-for="page in paginationPages"
                :key="page"
                type="button"
                @click="changePage(page)"
                :class="[
                    'px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 transition',
                    page === addressStore.pagination.currentPage
                        ? 'bg-blue-600 text-white'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
            >
                {{ page }}
            </button>


            <!-- NEXT -->

            <button
                type="button"
                @click="
                    changePage(
                        addressStore.pagination.currentPage + 1
                    )
                "
                :disabled="
                    addressStore.pagination.currentPage ===
                    addressStore.pagination.lastPage
                "
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition"
            >
                Next
            </button>

        </div>


        <!-- ================================================= -->
        <!-- ADD / EDIT MODAL -->
        <!-- ================================================= -->

        <div
            v-if="showModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
        >

            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-700 transition-colors"
            >

                <!-- MODAL HEADER -->

                <div
                    class="flex items-center justify-between px-6 py-5 border-b border-gray-200 dark:border-gray-700"
                >

                    <h2
                        class="text-xl font-bold text-gray-900 dark:text-white"
                    >

                        {{
                            isEditing
                                ? 'Edit Address'
                                : 'Add Address'
                        }}

                    </h2>


                    <button
                        type="button"
                        @click="closeModal"
                        class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white text-2xl transition"
                    >
                        ×
                    </button>

                </div>


                <!-- FORM -->

                <form
                    @submit.prevent="saveAddress"
                    class="p-6 space-y-5"
                >

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-5"
                    >

                        <!-- PROVINCE -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Province
                            </label>

                            <select
                                v-model="form.province"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
                            >

                                <option value="">
                                    Select province
                                </option>

                                <option
                                    v-for="(
                                        districts,
                                        province
                                    ) in nepalLocations"
                                    :key="province"
                                    :value="province"
                                >
                                    {{ province }}
                                </option>

                            </select>

                        </div>


                        <!-- DISTRICT -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                District
                            </label>

                            <select
                                v-model="form.district"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
                            >

                                <option value="">
                                    Select district
                                </option>

                                <option
                                    v-for="(
                                        value,
                                        district
                                    ) in nepalLocations[
                                        form.province
                                    ] || {}"
                                    :key="district"
                                    :value="district"
                                >
                                    {{ district }}
                                </option>

                            </select>

                        </div>


                        <!-- MUNICIPALITY -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Municipality
                            </label>

                            <input
                                v-model="form.municipality"
                                type="text"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
                                placeholder="Enter municipality"
                            />

                        </div>


                        <!-- WARD -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Ward
                            </label>

                            <input
                                v-model="form.ward"
                                type="text"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
                                placeholder="Enter ward number"
                            />

                        </div>


                        <!-- CITY -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                City
                            </label>

                            <input
                                v-model="form.city"
                                type="text"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
                                placeholder="Enter city"
                            />

                        </div>


                        <!-- STREET -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Street
                            </label>

                            <input
                                v-model="form.street"
                                type="text"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 outline-none transition-colors"
                                placeholder="Enter street"
                            />

                        </div>

                    </div>


                    <!-- MODAL BUTTONS -->

                    <div
                        class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700"
                    >

                        <!-- CANCEL -->

                        <button
                            type="button"
                            @click="closeModal"
                            class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                        >
                            Cancel
                        </button>


                        <!-- SUBMIT -->

                        <button
                            type="submit"
                            :disabled="addressStore.loading"
                            class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 transition"
                        >

                            {{
                                isEditing
                                    ? 'Update Address'
                                    : 'Add Address'
                            }}

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</template>