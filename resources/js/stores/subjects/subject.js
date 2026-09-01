import { defineStore } from 'pinia'

import {
    getSubjects,
    createSubject,
    updateSubject,
    deleteSubject
} from '../../services/subjects/subjectApi'


export const useSubjectStore = defineStore('subject', {

    // =========================================================
    // STATE
    // =========================================================

    state: () => ({

        subjects: [],

        loading: false,

        error: null,


        // =====================================================
        // PAGINATION
        // =====================================================

        pagination: {

            currentPage: 1,

            lastPage: 1,

            perPage: 10,

            totalSubjects: 0,

            from: 0,

            to: 0

        }

    }),


    // =========================================================
    // ACTIONS
    // =========================================================

    actions: {

        // =====================================================
        // FETCH SUBJECTS
        // =====================================================

        async fetchSubjects(page = 1) {

            this.loading = true

            this.error = null


            try {

                const response =
                    await getSubjects(page)


                console.log(
                    'Subjects response:',
                    response.data
                )


                this.subjects =
                    response.data.data || []


                this.pagination = {

                    currentPage:
                        response.data.current_page || 1,

                    lastPage:
                        response.data.last_page || 1,

                    perPage:
                        response.data.per_page || 10,

                    totalSubjects:
                        response.data.total || 0,

                    from:
                        response.data.from || 0,

                    to:
                        response.data.to || 0

                }


            } catch (error) {

                console.error(
                    'Error fetching subjects:',
                    error
                )


                this.error =
                    error.response?.data?.message ||
                    'Unable to load subjects.'

            } finally {

                this.loading = false

            }

        },


        // =====================================================
        // ADD SUBJECT
        // =====================================================

        async addSubject(subjectData) {

            this.error = null


            try {

                const response =
                    await createSubject(
                        subjectData
                    )


                console.log(
                    'Subject created:',
                    response.data
                )


                const subject =
                    response.data.subject ||
                    response.data


                this.subjects.unshift(
                    subject
                )


                return subject


            } catch (error) {

                console.error(
                    'Error creating subject:',
                    error
                )


                this.error =
                    error.response?.data?.message ||
                    'Error creating subject.'


                throw error

            }

        },


        // =====================================================
        // UPDATE SUBJECT
        // =====================================================

        async editSubject(id, subjectData) {

            this.error = null


            try {

                const response =
                    await updateSubject(
                        id,
                        subjectData
                    )


                console.log(
                    'Subject updated:',
                    response.data
                )


                const updatedSubject =
                    response.data.subject ||
                    response.data


                const index =
                    this.subjects.findIndex(
                        subject =>
                            subject.id === id
                    )


                if (index !== -1) {

                    this.subjects[index] =
                        updatedSubject

                }


                return updatedSubject


            } catch (error) {

                console.error(
                    'Error updating subject:',
                    error
                )


                this.error =
                    error.response?.data?.message ||
                    'Error updating subject.'


                throw error

            }

        },


        // =====================================================
        // DELETE SUBJECT
        // =====================================================

        async removeSubject(id) {

            this.error = null


            try {

                await deleteSubject(id)


                this.subjects =
                    this.subjects.filter(
                        subject =>
                            subject.id !== id
                    )


            } catch (error) {

                console.error(
                    'Error deleting subject:',
                    error
                )


                this.error =
                    error.response?.data?.message ||
                    'Error deleting subject.'


                throw error

            }

        }

    }

})