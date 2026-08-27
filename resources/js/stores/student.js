import { defineStore } from 'pinia'
import {
    getStudents,
    createStudent,
    getStudentStatistics
} from '../services/studentApi'

import router from '../router'

export const useStudentStore = defineStore('student', {

    state: () => ({
        students: [],
        loading: false,
        error: null,

        statistics: {
            total: 0,
            active: 0,
            inactive: 0
        }
    }),

    actions: {

        // =========================================================
        // FETCH STUDENTS
        // =========================================================

        async fetchStudents() {

            this.loading = true
            this.error = null

            try {

                const response = await getStudents()

                this.students = response.data

            } catch (error) {

                console.error(
                    'Error fetching students:',
                    error
                )

                if (error.response?.status === 401) {

                    localStorage.removeItem('token')
                    localStorage.removeItem('user')

                    router.push('/login')

                    return
                }

                this.error =
                    'Unable to load students.'

            } finally {

                this.loading = false

            }
        },


        // =========================================================
        // FETCH STUDENT STATISTICS
        // =========================================================

        async fetchStatistics() {

            try {

                const response =
                    await getStudentStatistics()

                this.statistics = response.data

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


        // =========================================================
        // ADD STUDENT
        // =========================================================

        async addStudent(studentData) {

            this.error = null

            try {

                const response =
                    await createStudent(studentData)

                console.log(
                    'Add student response:',
                    response.data
                )

                const student =
                    response.data.student ||
                    response.data

                console.log(
                    'Student added to Pinia:',
                    student
                )

                this.students.unshift(student)

                return student

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