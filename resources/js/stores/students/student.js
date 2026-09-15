import { defineStore } from 'pinia'

import {
    getStudents,
    createStudent,
    getStudentStatistics
} from '../../services/students/studentApi'

import router from '../../router'


export const useStudentStore = defineStore('student', {

    // =========================================================
    // STATE
    // =========================================================

    state: () => ({

        students: [],

        loading: false,

        error: null,


        // =====================================================
        // PAGINATION
        // =====================================================

        pagination: {

            currentPage: 1,

            lastPage: 1,

            perPage: 10,

            totalStudents: 0,

            from: 0,

            to: 0

        },


        // =====================================================
        // SEARCH + FILTER
        // =====================================================

        search: '',

        status: 'all',


        // =====================================================
        // STATISTICS
        // =====================================================

        statistics: {

            total: 0,

            active: 0,

            inactive: 0

        }

    }),


    // =========================================================
    // ACTIONS
    // =========================================================

    actions: {


        // =====================================================
        // FETCH STUDENTS
        // =====================================================

        async fetchStudents(
            page = 1,
            search = '',
            status = 'all'
        ) {

            this.loading = true

            this.error = null


            try {

                // =============================================
                // SAVE SEARCH + FILTER
                // =============================================

                this.search = search

                this.status = status


                // =============================================
                // CALL API
                // =============================================

                const response =
                    await getStudents(
                        page,
                        search,
                        status
                    )


                console.log(
                    'Paginated students response:',
                    response.data
                )


                // =============================================
                // STORE STUDENTS
                // =============================================

                this.students =
                    response.data.data || []


                // =============================================
                // STORE PAGINATION
                // =============================================

                this.pagination = {

                    currentPage:
                        response.data.current_page || 1,

                    lastPage:
                        response.data.last_page || 1,

                    perPage:
                        response.data.per_page || 10,

                    totalStudents:
                        response.data.total || 0,

                    from:
                        response.data.from || 0,

                    to:
                        response.data.to || 0

                }


                console.log(
                    'Students:',
                    this.students
                )

                console.log(
                    'Pagination:',
                    this.pagination
                )


            } catch (error) {

                console.error(
                    'Error fetching students:',
                    error
                )


                // =============================================
                // UNAUTHORIZED
                // =============================================

                if (
                    error.response?.status === 401
                ) {

                    localStorage.removeItem(
                        'token'
                    )

                    localStorage.removeItem(
                        'user'
                    )

                    router.push('/login')

                    return

                }


                // =============================================
                // ERROR
                // =============================================

                this.error =
                    error.response?.data?.message ||
                    'Unable to load students.'

            } finally {

                this.loading = false

            }

        },


        // =====================================================
        // FETCH STUDENT STATISTICS
        // =====================================================

        async fetchStatistics() {

            try {

                const response =
                    await getStudentStatistics()


                console.log(
                    'Statistics API response:',
                    response.data
                )


                this.statistics = {

                    total:
                        response.data.total || 0,

                    active:
                        response.data.active || 0,

                    inactive:
                        response.data.inactive || 0

                }


                console.log(
                    'Statistics loaded:',
                    this.statistics
                )


            } catch (error) {

                console.error(
                    'Statistics error:',
                    error
                )

            }

        },


        // =====================================================
        // ADD STUDENT
        // =====================================================

        async addStudent(studentData) {

            this.error = null


            try {

                const response =
                    await createStudent(
                        studentData
                    )


                console.log(
                    'Add student response:',
                    response.data
                )


                const student =
                    response.data.student ||
                    response.data


                console.log(
                    'Student added:',
                    student
                )


                return {
                    ...student,
                    login: response.data.login || null
                }


            } catch (error) {

                console.error(
                    'Error adding student:',
                    error
                )


                this.error =
                    error.response?.data?.message ||
                    'Error adding student.'


                throw error

            }

        }

    }

})

