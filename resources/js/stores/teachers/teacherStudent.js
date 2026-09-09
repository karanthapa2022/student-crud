import { defineStore } from 'pinia'

import {
    getTeacherStudents,
    getTeacherStudent
} from '../../services/teachers/teacherStudentApi'

import router from '../../router'


export const useTeacherStudentStore = defineStore(
    'teacherStudent',
    {

        // =====================================================
        // STATE
        // =====================================================

        state: () => ({

            students: [],

            selectedStudent: null,

            loading: false,

            error: null,


            // =================================================
            // PAGINATION
            // =================================================

            pagination: {

                currentPage: 1,

                lastPage: 1,

                perPage: 10,

                totalStudents: 0,

                from: 0,

                to: 0

            },


            // =================================================
            // SEARCH + FILTER
            // =================================================

            search: '',

            status: 'all'

        }),


        // =====================================================
        // ACTIONS
        // =====================================================

        actions: {


            // =================================================
            // FETCH TEACHER STUDENTS
            // =================================================

            async fetchStudents(
                page = 1,
                search = '',
                status = 'all'
            ) {

                this.loading = true

                this.error = null

                try {

                    const token =
                        localStorage.getItem(
                            'teacher_token'
                        )


                    // -----------------------------------------
                    // NO TEACHER TOKEN
                    // -----------------------------------------

                    if (!token) {

                        router.push('/login')

                        return

                    }


                    // -----------------------------------------
                    // SAVE SEARCH + FILTER
                    // -----------------------------------------

                    this.search = search

                    this.status = status


                    // -----------------------------------------
                    // API
                    // -----------------------------------------

                    const response =
                        await getTeacherStudents(
                            page,
                            search,
                            status
                        )


                    // -----------------------------------------
                    // STUDENTS
                    // -----------------------------------------

                    this.students =
                        response.data.data || []


                    // -----------------------------------------
                    // PAGINATION
                    // -----------------------------------------

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

                } catch (error) {

                    console.error(
                        'Teacher students error:',
                        error
                    )


                    // -----------------------------------------
                    // UNAUTHORIZED
                    // -----------------------------------------

                    if (
                        error.response?.status === 401
                    ) {

                        localStorage.removeItem(
                            'teacher_token'
                        )

                        localStorage.removeItem(
                            'teacher_user'
                        )

                        router.push('/login')

                        return

                    }


                    // -----------------------------------------
                    // FORBIDDEN
                    // -----------------------------------------

                    if (
                        error.response?.status === 403
                    ) {

                        this.error =
                            error.response?.data?.message ||
                            'You do not have permission to view these students.'

                        return

                    }


                    // -----------------------------------------
                    // GENERAL ERROR
                    // -----------------------------------------

                    this.error =
                        error.response?.data?.message ||
                        'Unable to load your students.'

                } finally {

                    this.loading = false

                }

            },


            // =================================================
            // FETCH SINGLE STUDENT
            // =================================================

            async fetchStudent(id) {

                this.loading = true

                this.error = null

                try {

                    const token =
                        localStorage.getItem(
                            'teacher_token'
                        )


                    if (!token) {

                        router.push('/login')

                        return null

                    }


                    const response =
                        await getTeacherStudent(id)


                    this.selectedStudent =
                        response.data


                    return response.data

                } catch (error) {

                    console.error(
                        'Teacher student error:',
                        error
                    )


                    if (
                        error.response?.status === 401
                    ) {

                        localStorage.removeItem(
                            'teacher_token'
                        )

                        localStorage.removeItem(
                            'teacher_user'
                        )

                        router.push('/login')

                        return null

                    }


                    this.error =
                        error.response?.data?.message ||
                        'Unable to load student.'

                    return null

                } finally {

                    this.loading = false

                }

            },


            // =================================================
            // CLEAR SELECTED STUDENT
            // =================================================

            clearSelectedStudent() {

                this.selectedStudent = null

            }

        }

    }
)

