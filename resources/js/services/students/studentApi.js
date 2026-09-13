import axios from 'axios'
import { API_BASE_URL, getAuthToken } from '../apiConfig'


// =========================================================
// AXIOS INSTANCE
// =========================================================

const api = axios.create({

    baseURL: API_BASE_URL,

    headers: {

        Accept: 'application/json'

    }

})


// =========================================================
// AUTH TOKEN
// =========================================================

// =========================================================
// AXIOS INTERCEPTOR
// =========================================================

api.interceptors.request.use(

    (config) => {

        const token =
            getAuthToken()

        if (token) {

            config.headers.Authorization =
                `Bearer ${token}`

        }

        return config

    },

    (error) => {

        return Promise.reject(error)

    }

)


// =========================================================
// GET STUDENTS - PAGINATION + SEARCH + FILTER
// =========================================================

export const getStudents = (
    page = 1,
    search = '',
    status = 'all'
) => {

    return api.get('/students', {

        params: {

            page: page,

            search: search,

            status: status

        }

    })

}


// =========================================================
// CREATE STUDENT
// =========================================================

export const createStudent = (student) => {

    return api.post(
        '/students',
        student
    )

}


// =========================================================
// UPDATE STUDENT
// =========================================================

export const updateStudent = (
    id,
    student
) => {

    return api.put(
        `/students/${id}`,
        student
    )

}


// =========================================================
// DELETE STUDENT
// =========================================================

export const deleteStudent = (id) => {

    return api.delete(
        `/students/${id}`
    )

}


// =========================================================
// BULK DELETE
// =========================================================

export const bulkDeleteStudents = (ids) => {

    return api.post(
        '/students/bulk-delete',
        {
            ids: ids
        }
    )

}

export const bulkUpdateStudents = (data) => {
    return api.put('/students/bulk-update', data)
}


// =========================================================
// CREATE STUDENT WITH PHOTO
// =========================================================

export const createStudentWithPhoto = (
    formData
) => {

    return api.post(
        '/students',
        formData
    )

}


// =========================================================
// UPDATE STUDENT WITH PHOTO
// =========================================================

export const updateStudentWithPhoto = (
    id,
    formData
) => {

    formData.append(
        '_method',
        'PUT'
    )

    return api.post(
        `/students/${id}`,
        formData
    )

}


// =========================================================
// GET STUDENT STATISTICS
// =========================================================

export const getStudentStatistics = () => {

    return api.get(
        '/students/statistics'
    )

}


// =========================================================
// GET DELETED STUDENTS
// =========================================================

export const getTrashedStudents = () => {

    return api.get(
        '/students/trash'
    )

}


// =========================================================
// RESTORE STUDENT
// =========================================================

export const restoreStudent = (id) => {

    return api.post(
        `/students/${id}/restore`
    )

}


// =========================================================
// PERMANENT DELETE STUDENT
// =========================================================

export const forceDeleteStudent = (id) => {

    return api.delete(
        `/students/${id}/force-delete`
    )

}


// =========================================================
// EXPORT AXIOS INSTANCE
// =========================================================

export default api

