import { defineStore } from 'pinia'

import {
    getParents,
    createParent,
    updateParent,
    deleteParent
} from '../../services/parents/parentApi'


export const useParentStore = defineStore('parent', {

    // =========================================================
    // STATE
    // =========================================================

    state: () => ({

        parents: [],

        loading: false,

        error: null,

        pagination: {

            currentPage: 1,

            lastPage: 1,

            perPage: 10,

            totalParents: 0,

            from: 0,

            to: 0

        }

    }),


    // =========================================================
    // ACTIONS
    // =========================================================

    actions: {

        // =====================================================
        // FETCH PARENTS
        // =====================================================

        async fetchParents(page = 1) {

            this.loading = true

            this.error = null

            try {

                const response =
                    await getParents(page)

                console.log(
                    'Parents response:',
                    response.data
                )

                this.parents =
                    response.data.data || []

                this.pagination = {

                    currentPage:
                        response.data.current_page || 1,

                    lastPage:
                        response.data.last_page || 1,

                    perPage:
                        response.data.per_page || 10,

                    totalParents:
                        response.data.total || 0,

                    from:
                        response.data.from || 0,

                    to:
                        response.data.to || 0

                }

            } catch (error) {

                console.error(
                    'Error fetching parents:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Unable to load parents.'

            } finally {

                this.loading = false

            }

        },


        // =====================================================
        // ADD PARENT
        // =====================================================

        async addParent(parentData) {

            this.error = null

            try {

                const response =
                    await createParent(parentData)

                console.log(
                    'Parent created:',
                    response.data
                )

                const parent =
                    response.data.parent ||
                    response.data

                this.parents.unshift(parent)

                return parent

            } catch (error) {

                console.error(
                    'Error creating parent:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Error creating parent.'

                throw error

            }

        },


        // =====================================================
        // UPDATE PARENT
        // =====================================================

        async editParent(id, parentData) {

            this.error = null

            try {

                const response =
                    await updateParent(
                        id,
                        parentData
                    )

                console.log(
                    'Parent updated:',
                    response.data
                )

                const updatedParent =
                    response.data.parent ||
                    response.data

                const index =
                    this.parents.findIndex(
                        parent => parent.id === id
                    )

                if (index !== -1) {

                    this.parents[index] =
                        updatedParent

                }

                return updatedParent

            } catch (error) {

                console.error(
                    'Error updating parent:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Error updating parent.'

                throw error

            }

        },


        // =====================================================
        // DELETE PARENT
        // =====================================================

        async removeParent(id) {

            this.error = null

            try {

                await deleteParent(id)

                this.parents =
                    this.parents.filter(
                        parent => parent.id !== id
                    )

            } catch (error) {

                console.error(
                    'Error deleting parent:',
                    error
                )

                this.error =
                    error.response?.data?.message ||
                    'Error deleting parent.'

                throw error

            }

        }

    }

})