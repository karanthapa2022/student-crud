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
    <div class="min-h-screen bg-paper text-ink">

        <!-- ================================================= -->
        <!-- HEADER / LETTERHEAD -->
        <!-- ================================================= -->

        <header class="border-b border-hairline">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-6 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8"
            >
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center border border-hairline bg-surface text-lg"
                        >
                            📍
                        </div>

                        <span class="text-sm font-medium text-forest">
                            Student Records
                        </span>
                    </div>

                    <h1
                        class="font-serif text-4xl font-medium leading-tight text-ink"
                    >
                        Addresses
                    </h1>

                    <p class="mt-2 text-sm text-ink-soft">
                        Manage student addresses
                    </p>
                </div>


                <!-- HEADER ACTIONS -->

                <div class="flex flex-col gap-3 sm:flex-row">

                    <!-- DASHBOARD -->

                    <button
                        type="button"
                        @click="$router.push('/dashboard')"
                        class="border border-hairline bg-surface px-5 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                    >
                        Dashboard
                    </button>


                    <!-- ADD ADDRESS -->

                    <button
                        type="button"
                        @click="openAddModal"
                        class="border border-forest bg-forest px-5 py-2.5 text-sm font-medium text-white hover:bg-ink"
                    >
                        + Add Address
                    </button>

                </div>
            </div>
        </header>


        <!-- ================================================= -->
        <!-- MAIN -->
        <!-- ================================================= -->

        <main
            class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
        >

            <!-- ================================================= -->
            <!-- SEARCH & FILTER -->
            <!-- ================================================= -->

            <section
                class="border border-hairline bg-surface"
            >
                <div
                    class="flex flex-col gap-4 p-5 md:flex-row"
                >

                    <!-- SEARCH -->

                    <div class="flex-1">
                        <label
                            class="mb-2 block text-xs font-medium text-ink-soft"
                        >
                            Search addresses
                        </label>

                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search province, district, municipality, city or street..."
                            class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                        />
                    </div>


                    <!-- PROVINCE FILTER -->

                    <div class="w-full md:w-56">
                        <label
                            class="mb-2 block text-xs font-medium text-ink-soft"
                        >
                            Province
                        </label>

                        <select
                            v-model="provinceFilter"
                            class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none focus:border-forest"
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

                </div>
            </section>


            <!-- ================================================= -->
            <!-- STATUS MESSAGES -->
            <!-- ================================================= -->

            <div
                v-if="successMessage"
                class="mt-5 border border-forest bg-paper px-4 py-3 text-sm text-forest"
            >
                {{ successMessage }}
            </div>


            <div
                v-if="addressStore.error"
                class="mt-5 border border-sienna bg-paper px-4 py-3 text-sm text-sienna"
            >
                {{ addressStore.error }}
            </div>


            <!-- ================================================= -->
            <!-- ADDRESS LEDGER -->
            <!-- ================================================= -->

            <section
                class="mt-6 border border-hairline bg-surface"
            >

                <!-- SECTION HEADER -->

                <div
                    class="flex flex-col gap-2 border-b border-hairline px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <div>
                        <p class="text-sm font-medium text-forest">
                            Address Directory
                        </p>

                        <h2
                            class="mt-1 font-serif text-2xl font-medium text-ink"
                        >
                            Student Addresses
                        </h2>
                    </div>

                    <p class="text-xs text-ink-soft">
                        {{ addressStore.pagination.total || 0 }} records
                    </p>
                </div>


                <!-- ================================================= -->
                <!-- LOADING -->
                <!-- ================================================= -->

                <div
                    v-if="addressStore.loading"
                    class="px-6 py-16 text-center"
                >
                    <p class="text-sm text-ink-soft">
                        Loading addresses...
                    </p>
                </div>


                <!-- ================================================= -->
                <!-- TABLE -->
                <!-- ================================================= -->

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table class="w-full min-w-[1050px] text-sm">

                        <!-- TABLE HEADER -->

                        <thead>
                            <tr
                                class="border-b border-hairline bg-paper"
                            >
                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    S.N.
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Province
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    District
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Municipality/VDC
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Ward
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    City
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Street
                                </th>

                                <th
                                    class="px-5 py-4 text-center text-xs font-medium text-ink-soft"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>


                        <!-- TABLE BODY -->

                        <tbody>

                            <tr
                                v-for="(address, index) in addressStore.addresses"
                                :key="address.id"
                                class="border-b border-hairline last:border-b-0 hover:bg-paper"
                            >

                                <!-- S.N. -->

                                <td
                                    class="px-5 py-4 text-ink-soft"
                                >
                                    {{
                                        addressStore.pagination.from +
                                        index
                                    }}
                                </td>


                                <!-- PROVINCE -->

                                <td
                                    class="px-5 py-4 font-medium text-ink"
                                >
                                    {{ address.province }}
                                </td>


                                <!-- DISTRICT -->

                                <td
                                    class="px-5 py-4 text-ink"
                                >
                                    {{ address.district }}
                                </td>


                                <!-- MUNICIPALITY -->

                                <td
                                    class="px-5 py-4 text-ink"
                                >
                                    {{ address.municipality }}
                                </td>


                                <!-- WARD -->

                                <td
                                    class="px-5 py-4 text-ink"
                                >
                                    {{ address.ward }}
                                </td>


                                <!-- CITY -->

                                <td
                                    class="px-5 py-4 text-ink"
                                >
                                    {{ address.city || '-' }}
                                </td>


                                <!-- STREET -->

                                <td
                                    class="px-5 py-4 text-ink"
                                >
                                    {{ address.street || '-' }}
                                </td>


                                <!-- ACTIONS -->

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-center gap-2"
                                    >

                                        <!-- EDIT -->

                                        <button
                                            type="button"
                                            @click="openEditModal(address)"
                                            class="border border-hairline bg-surface px-3 py-1.5 text-xs font-medium text-ink hover:border-forest hover:text-forest"
                                        >
                                            Edit
                                        </button>


                                        <!-- DELETE -->

                                        <button
                                            type="button"
                                            @click="deleteAddress(address.id)"
                                            class="border border-sienna bg-surface px-3 py-1.5 text-xs font-medium text-sienna hover:bg-sienna hover:text-white"
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
                                    class="px-6 py-14 text-center"
                                >
                                    <div
                                        class="font-serif text-xl text-ink"
                                    >
                                        No addresses found
                                    </div>

                                    <p
                                        class="mt-2 text-sm text-ink-soft"
                                    >
                                        Try adjusting your search or
                                        province filter.
                                    </p>
                                </td>
                            </tr>

                        </tbody>

                    </table>
                </div>
            </section>


            <!-- ================================================= -->
            <!-- PAGINATION -->
            <!-- ================================================= -->

            <div
                v-if="addressStore.pagination.lastPage > 1"
                class="mt-6 flex flex-wrap items-center justify-center gap-2"
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
                    class="border border-hairline bg-surface px-4 py-2 text-sm text-ink hover:border-forest hover:text-forest disabled:cursor-not-allowed disabled:opacity-40"
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
                        'border px-4 py-2 text-sm transition',
                        page === addressStore.pagination.currentPage
                            ? 'border-forest bg-forest text-white'
                            : 'border-hairline bg-surface text-ink hover:border-forest hover:text-forest'
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
                    class="border border-hairline bg-surface px-4 py-2 text-sm text-ink hover:border-forest hover:text-forest disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Next
                </button>

            </div>

        </main>


        <!-- ================================================= -->
        <!-- ADD / EDIT MODAL -->
        <!-- ================================================= -->

        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4"
        >

            <div
                class="max-h-[90vh] w-full max-w-2xl overflow-y-auto border border-hairline bg-surface"
            >

                <!-- MODAL HEADER -->

                <div
                    class="flex items-center justify-between border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p class="text-sm font-medium text-forest">
                            Address Record
                        </p>

                        <h2
                            class="mt-1 font-serif text-2xl font-medium text-ink"
                        >
                            {{
                                isEditing
                                    ? 'Edit Address'
                                    : 'Add Address'
                            }}
                        </h2>
                    </div>


                    <button
                        type="button"
                        @click="closeModal"
                        class="text-2xl leading-none text-ink-soft hover:text-ink"
                    >
                        ×
                    </button>
                </div>


                <!-- FORM -->

                <form
                    @submit.prevent="saveAddress"
                    class="p-6 sm:p-8"
                >

                    <div
                        class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2"
                    >

                        <!-- PROVINCE -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Province
                            </label>

                            <select
                                v-model="form.province"
                                required
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none focus:border-forest"
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
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                District
                            </label>

                            <select
                                v-model="form.district"
                                required
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none focus:border-forest"
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
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Municipality
                            </label>

                            <input
                                v-model="form.municipality"
                                type="text"
                                required
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="Enter municipality"
                            />
                        </div>


                        <!-- WARD -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Ward
                            </label>

                            <input
                                v-model="form.ward"
                                type="text"
                                required
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="Enter ward number"
                            />
                        </div>


                        <!-- CITY -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                City
                            </label>

                            <input
                                v-model="form.city"
                                type="text"
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="Enter city"
                            />
                        </div>


                        <!-- STREET -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Street
                            </label>

                            <input
                                v-model="form.street"
                                type="text"
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="Enter street"
                            />
                        </div>

                    </div>


                    <!-- MODAL BUTTONS -->

                    <div
                        class="mt-8 flex justify-end gap-3 border-t border-hairline pt-6"
                    >

                        <!-- CANCEL -->

                        <button
                            type="button"
                            @click="closeModal"
                            class="border border-hairline bg-surface px-5 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                        >
                            Cancel
                        </button>


                        <!-- SUBMIT -->

                        <button
                            type="submit"
                            :disabled="addressStore.loading"
                            class="border border-forest bg-forest px-5 py-2.5 text-sm font-medium text-white hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50"
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