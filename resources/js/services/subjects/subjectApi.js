import axios from 'axios'
import { API_BASE_URL, getAuthToken } from '../apiConfig'

const api = axios.create({
    baseURL: API_BASE_URL,
})


// =========================================================
// GET AUTH TOKEN
// =========================================================

// =========================================================
// GET ALL SUBJECTS
// =========================================================

export const getSubjects = (
    page = 1,
    search = '',
    teacherFilter = 'all'
) => {

    const token = getAuthToken()

    return api.get('/subjects', {

        params: {
            page,
            search,
            teacher_filter: teacherFilter
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

    const token = getAuthToken()

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

    const token = getAuthToken()

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

    const token = getAuthToken()

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

    const token = getAuthToken()

    return api.delete(`/subjects/${id}`, {

        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }

    })

}

