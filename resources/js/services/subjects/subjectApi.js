import axios from 'axios'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
})

// =========================================================
// GET ALL SUBJECTS
// =========================================================

export const getSubjects = (page = 1) => {

    const token = localStorage.getItem('token')

    return api.get('/subjects', {
        params: {
            page
        },
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// GET SINGLE SUBJECT
// =========================================================

export const getSubject = (id) => {

    const token = localStorage.getItem('token')

    return api.get(`/subjects/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// CREATE SUBJECT
// =========================================================

export const createSubject = (subjectData) => {

    const token = localStorage.getItem('token')

    return api.post('/subjects', subjectData, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// UPDATE SUBJECT
// =========================================================

export const updateSubject = (id, subjectData) => {

    const token = localStorage.getItem('token')

    return api.put(`/subjects/${id}`, subjectData, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// DELETE SUBJECT
// =========================================================

export const deleteSubject = (id) => {

    const token = localStorage.getItem('token')

    return api.delete(`/subjects/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}