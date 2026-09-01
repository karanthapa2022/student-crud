import { defineStore } from 'pinia'

import {
    getAddresses,
    createAddress,
    updateAddress,
    deleteAddress
} from '../../services/addresses/addressApi'


export const useAddressStore = defineStore('address', {

    // =========================================================
    // STATE
    // =========================================================

    state: () => ({

        addresses: [],

        loading: false,

        error: null,

        pagination: {

            currentPage: 1,

            lastPage: 1,

            perPage: 10,

            totalAddresses: 0,

            from: 0,

            to: 0

        }

    }),


    // =========================================================
    // ACTIONS
    // =========================================================

    actions: {

        // =====================================================
        // FETCH ADDRESSES
        // =====================================================

        async fetchAddresses(page = 1) {

            this.loading = true

            this.error = null

            try {

                const response =
                    await getAddresses(page)

                console.log(
                    'Addresses response:',
                    response.data
                )

                this.addresses =
                    response.data.data || []

                this.pagination = {

                    currentPage:
                        response.data.current_page || 1,

                    lastPage:
                        response.data.last_page || 1,

                    perPage:
                        response.data.per_page || 10,

                    totalAddresses:
                        response.data.total || 0,

                    from:
                        response.data.from || 0,

                    to:
                        response.data.to || 0

                }

            } catch (error) {

                console.error(
                    'Error fetching addresses:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Unable to load addresses.'

            } finally {

                this.loading = false

            }

        },


        // =====================================================
        // ADD ADDRESS
        // =====================================================

        async addAddress(addressData) {

            this.error = null

            try {

                const response =
                    await createAddress(addressData)

                console.log(
                    'Address created:',
                    response.data
                )

                const address =
                    response.data.address ||
                    response.data

                this.addresses.unshift(address)

                return address

            } catch (error) {

                console.error(
                    'Error creating address:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Error creating address.'

                throw error

            }

        },


        // =====================================================
        // UPDATE ADDRESS
        // =====================================================

        async editAddress(id, addressData) {

            this.error = null

            try {

                const response =
                    await updateAddress(
                        id,
                        addressData
                    )

                console.log(
                    'Address updated:',
                    response.data
                )

                const updatedAddress =
                    response.data.address ||
                    response.data

                const index =
                    this.addresses.findIndex(
                        address => address.id === id
                    )

                if (index !== -1) {

                    this.addresses[index] =
                        updatedAddress

                }

                return updatedAddress

            } catch (error) {

                console.error(
                    'Error updating address:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Error updating address.'

                throw error

            }

        },


        // =====================================================
        // DELETE ADDRESS
        // =====================================================

        async removeAddress(id) {

            this.error = null

            try {

                await deleteAddress(id)

                this.addresses =
                    this.addresses.filter(
                        address => address.id !== id
                    )

            } catch (error) {

                console.error(
                    'Error deleting address:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Error deleting address.'

                throw error

            }

        }

    }

})
