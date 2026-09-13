import axios from 'axios'
import { API_BASE_URL } from '../apiConfig'

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        Accept: 'application/json'
    }
})


// =========================================================
// AUTH TOKEN
// =========================================================

const getTeacherToken = () => {

    return localStorage.getItem('teacher_token')

}


// =========================================================
// GET TEACHER STUDENTS
// =========================================================

export const getTeacherStudents = (
    page = 1,
    search = '',
    status = 'all'
) => {

    const token = getTeacherToken()

    return api.get('/teacher/students', {

        params: {
            page,
            search,
            status
        },

        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }

    })

}


// =========================================================
// GET SINGLE TEACHER STUDENT
// =========================================================

export const getTeacherStudent = (id) => {

    const token = getTeacherToken()

    return api.get(
        `/teacher/students/${id}`,
        {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json'
            }
        }
    )

}

