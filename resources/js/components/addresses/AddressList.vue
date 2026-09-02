<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAddressStore } from '../../stores/addresses/address'
import { nepalLocations } from '../../data/nepalLocations'

const addressStore = useAddressStore()

// search and filter

const searchQuery = ref('')
const provinceFilter = ref('all')
watch(
    [
        searchQuery,
        provinceFilter
    ],()=>{
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

    if (!confirm('Are you sure you want to delete this address?')) {
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

    addressStore.fetchAddresses(page,
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

    <div class="min-h-screen bg-gray-100 p-6">

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Addresses
                </h1>

                <p class="text-gray-500 mt-1">
                    Manage student addresses
                </p>
            </div>

            

            <button
                @click="openAddModal"
                class="bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 transition"
            >
                + Add Address
            </button>

        </div>

        <!-- SEARCH & FILTER -->
<div class="flex gap-4 mb-6">

    <!-- SEARCH -->
    <input
        v-model="searchQuery"
        type="text"
        placeholder="Search province, district, municipality, city or street..."
        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
    />

    <!-- PROVINCE FILTER -->
    <select
        v-model="provinceFilter"
        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
    >
        <option value="all">All Provinces</option>
        <option value="Koshi">Koshi</option>
        <option value="Madhesh">Madhesh</option>
        <option value="Bagmati">Bagmati</option>
        <option value="Gandaki">Gandaki</option>
        <option value="Lumbini">Lumbini</option>
        <option value="Karnali">Karnali</option>
        <option value="Sudurpashchim">Sudurpashchim</option>
    </select>

</div>

        <!-- ================================================= -->
        <!-- SUCCESS MESSAGE -->
        <!-- ================================================= -->

        <div
            v-if="successMessage"
            class="mb-5 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3"
        >
            {{ successMessage }}
        </div>


        <!-- ================================================= -->
        <!-- ERROR MESSAGE -->
        <!-- ================================================= -->

        <div
            v-if="addressStore.error"
            class="mb-5 rounded-lg bg-red-100 border border-red-300 text-red-700 px-4 py-3"
        >
            {{ addressStore.error }}
        </div>


        <!-- ================================================= -->
        <!-- TABLE -->
        <!-- ================================================= -->

        <div class="bg-white rounded-xl shadow overflow-hidden">

            <!-- Loading -->

            <div
                v-if="addressStore.loading"
                class="p-10 text-center text-gray-500"
            >
                Loading addresses...
            </div>


            <!-- Table -->

            <div
                v-else
                class="overflow-x-auto"
            >

                <table class="w-full">

                    <thead class="bg-gray-50 border-b">

                        <tr>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                S.N.
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Province
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                District
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Municipality/VDC
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Ward
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                City
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Street
                            </th>

                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y">

                        <tr
                            v-for="(address, index) in addressStore.addresses"
                            :key="address.id"
                            class="hover:bg-gray-50"
                        >

                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{
                                    addressStore.pagination.from +
                                    index
                                }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ address.province }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ address.district }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ address.municipality }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ address.ward }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ address.city || '-' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ address.street || '-' }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-center gap-2">

                                    <button
                                        @click="openEditModal(address)"
                                        class="px-3 py-1.5 text-sm rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        @click="deleteAddress(address.id)"
                                        class="px-3 py-1.5 text-sm rounded-lg bg-red-100 text-red-700 hover:bg-red-200"
                                    >
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- Empty -->

                        <tr
                            v-if="addressStore.addresses.length === 0"
                        >

                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-gray-500"
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

            <button
                @click="changePage(addressStore.pagination.currentPage - 1)"
                :disabled="addressStore.pagination.currentPage === 1"
                class="px-4 py-2 rounded-lg border bg-white disabled:opacity-50"
            >
                Previous
            </button>

            <button
                v-for="page in paginationPages"
                :key="page"
                @click="changePage(page)"
                :class="[
                    'px-4 py-2 rounded-lg border',
                    page === addressStore.pagination.currentPage
                        ? 'bg-blue-600 text-white'
                        : 'bg-white hover:bg-gray-50'
                ]"
            >
                {{ page }}
            </button>

            <button
                @click="changePage(addressStore.pagination.currentPage + 1)"
                :disabled="
                    addressStore.pagination.currentPage ===
                    addressStore.pagination.lastPage
                "
                class="px-4 py-2 rounded-lg border bg-white disabled:opacity-50"
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
                class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
            >

                <!-- Modal Header -->

                <div class="flex items-center justify-between px-6 py-5 border-b">

                    <h2 class="text-xl font-bold text-gray-800">
                        {{
                            isEditing
                                ? 'Edit Address'
                                : 'Add Address'
                        }}
                    </h2>

                    <button
                        @click="closeModal"
                        class="text-gray-500 hover:text-gray-800 text-2xl"
                    >
                        ×
                    </button>

                </div>


                <!-- Form -->

                <form
                    @submit.prevent="saveAddress"
                    class="p-6 space-y-5"
                >

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <!-- Province -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Province
                            </label>

                            <select
    v-model="form.province"
    required
    class="w-full border rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
>
    <option value="">Select province</option>

    <option
        v-for="(districts, province) in nepalLocations"
        :key="province"
        :value="province"
    >
        {{ province }}
    </option>
</select>

                        </div>


                       <!-- District -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        District
    </label>

    <select
        v-model="form.district"
        required
        class="w-full border rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
    >
        <option value="">Select district</option>

        <option
            v-for="(value, district) in nepalLocations[form.province] || {}"
            :key="district"
            :value="district"
        >
            {{ district }}
        </option>
    </select>
</div>


                        <!-- Municipality -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Municipality
                            </label>

                            <input
                                v-model="form.municipality"
                                type="text"
                                required
                                class="w-full border rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
                                placeholder="Enter municipality"
                            />

                        </div>


                        <!-- Ward -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ward
                            </label>

                            <input
                                v-model="form.ward"
                                type="text"
                                required
                                class="w-full border rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
                                placeholder="Enter ward number"
                            />

                        </div>


                        <!-- City -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                City
                            </label>

                            <input
                                v-model="form.city"
                                type="text"
                                class="w-full border rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
                                placeholder="Enter city"
                            />

                        </div>


                        <!-- Street -->

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Street
                            </label>

                            <input
                                v-model="form.street"
                                type="text"
                                class="w-full border rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
                                placeholder="Enter street"
                            />

                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="flex justify-end gap-3 pt-4 border-t">

                        <button
                            type="button"
                            @click="closeModal"
                            class="px-5 py-2.5 rounded-lg border hover:bg-gray-50"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="addressStore.loading"
                            class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
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