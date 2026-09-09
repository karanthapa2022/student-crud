import axios from 'axios'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
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

