
import axios from 'axios'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        Accept: 'application/json'
    }
})

// =========================================================
// GET ALL TEACHERS
// =========================================================

export const getTeachers = (
    page = 1,
    search = ''
) => {

    const token = localStorage.getItem('token')

    return api.get('/teachers', {
        params: {
            page,
            search
        },
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })
}


// =========================================================
// GET SINGLE TEACHER
// =========================================================

export const getTeacher = (id) => {

    const token = localStorage.getItem('token')

    return api.get(`/teachers/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })
}


// =========================================================
// CREATE TEACHER
// =========================================================

export const createTeacher = (teacherData) => {

    const token = localStorage.getItem('token')

    return api.post('/teachers', teacherData, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })
}


// =========================================================
// UPDATE TEACHER
// =========================================================

export const updateTeacher = (id, teacherData) => {

    const token = localStorage.getItem('token')

    return api.put(`/teachers/${id}`, teacherData, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })
}


// =========================================================
// DELETE TEACHER
// =========================================================

export const deleteTeacher = (id) => {

    const token = localStorage.getItem('token')

    return api.delete(`/teachers/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })
    
}


// =========================================================
// GET ALL SUBJECTS
// =========================================================

export const getSubjects = () => {

    const token = localStorage.getItem('token')

    return api.get('/subjects', {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })
}


// =========================================================
// ASSIGN SUBJECTS TO TEACHER
// =========================================================

export const assignTeacherSubjects = (teacherId, subjectIds) => {

    const token = localStorage.getItem('token')

    return api.put(
        `/teachers/${teacherId}/subjects`,
        {
            subject_ids: subjectIds
        },
        {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json'
            }
        }
    )
}


