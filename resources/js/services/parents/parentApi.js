import axios from 'axios'
import { API_BASE_URL, getAuthToken } from '../apiConfig'

const api = axios.create({
    baseURL: API_BASE_URL,
})


// =========================================================
// PARENT LOGIN
// =========================================================

export const parentLogin = (credentials) => {

    return api.post('/parent/login', credentials, {
        headers: {
            Accept: 'application/json'
        }
    })

}
// =========================================================
// GET ALL PARENTS
// =========================================================

export const getParents = (page = 1,
    search='',
    relationshipFilter='all',
    perPage = 5
) => {

    const token = getAuthToken('admin')

    return api.get('/parents', {
        params: {
            page,
            search,
            relationship_filter: relationshipFilter,
            per_page: perPage
        },
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// GET SINGLE PARENT
// =========================================================

export const getParent = (id) => {

    const token = getAuthToken('admin')

    return api.get(`/parents/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// CREATE PARENT
// =========================================================

export const createParent = (parentData) => {

    const token = getAuthToken('admin')

    return api.post('/parents', parentData, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// UPDATE PARENT
// =========================================================

export const updateParent = (id, parentData) => {

    const token = getAuthToken('admin')

    return api.put(`/parents/${id}`, parentData, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// DELETE PARENT
// =========================================================

export const deleteParent = (id) => {

    const token = getAuthToken('admin')

    return api.delete(`/parents/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}

// =========================================================
// UPDATE PARENT'S CHILDREN
// =========================================================

export const updateParentChildren = (id, studentIds) => {

    const token = getAuthToken('admin')

    return api.put(`/parents/${id}/children`, {
        student_ids: studentIds
    }, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })
    

}



export const changeParentPassword = (
    parentId,
    password,
    passwordConfirmation
) => {

    const token = getAuthToken('admin')

    return api.put(
        `/parents/${parentId}/change-password`,
        {
            password,
            password_confirmation: passwordConfirmation
        },
        {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json'
            }
        }
    )
}

