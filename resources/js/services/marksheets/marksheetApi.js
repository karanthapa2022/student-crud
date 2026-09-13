import axios from 'axios'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        Accept: 'application/json'
    }
})


// =========================================================
// AUTHENTICATION TOKEN
// =========================================================

api.interceptors.request.use(config => {

    const path = window.location.pathname

    let token = null


    // =========================================================
    // PARENT
    // =========================================================

    if (path.startsWith('/parents')) {

        token = localStorage.getItem('parent_token')

    }


    // =========================================================
    // TEACHER
    // =========================================================

    else if (
        path.startsWith('/teacher') ||
        localStorage.getItem('teacher_token')
    ) {

        token = localStorage.getItem('teacher_token')

    }


    // =========================================================
    // ADMIN
    // =========================================================

    else {

        token = localStorage.getItem('token')

    }


    // =========================================================
    // ATTACH TOKEN
    // =========================================================

    if (token) {

        config.headers.Authorization = `Bearer ${token}`

    }

    return config

})


// =========================================================
// GET ALL MARKSHEETS
// =========================================================

export const getMarksheets = () => {

    return api.get('/marksheets')

}


// =========================================================
// GET ONE MARKSHEET
// =========================================================

export const getMarksheet = (id) => {

    return api.get(`/marksheets/${id}`)

}


// =========================================================
// CREATE MARKSHEET
// =========================================================

export const createMarksheet = (data) => {

    return api.post('/marksheets', data)

}


// =========================================================
// DELETE MARKSHEET
// =========================================================

export const deleteMarksheet = (id) => {

    return api.delete(`/marksheets/${id}`)

}


// =========================================================
// UPDATE MARKSHEET
// =========================================================

export const updateMarksheet = (id, data) => {

    return api.put(`/marksheets/${id}`, data)

}

export const importMarksheets = (formData) => {
    return api.post('/marksheets/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
}

export const exportMarksheets = (id = null) => {
    return api.get(id ? `/marksheets/${id}/export` : '/marksheets/export', {
        responseType: 'blob',
    })
}


// =========================================================
// PARENT SEARCH MARKSHEET
// =========================================================

export const searchParentMarksheet = (data) => {

    return api.post('/marksheets/parent-search', data)

}


// =========================================================
// PARENT STUDENT MARKSHEET
// =========================================================

export const getParentStudentMarksheet = (studentId) => {

    return api.get(
        `/marksheets/parent/student/${studentId}`
    )

}

