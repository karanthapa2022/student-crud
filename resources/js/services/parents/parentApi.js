import axios from 'axios'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
})

// =========================================================
// GET ALL PARENTS
// =========================================================

export const getParents = (page = 1,
    search='',
    relationshipFilter='all'
) => {

    const token = localStorage.getItem('token')

    return api.get('/parents', {
        params: {
            page,
            search,
            relationship_filter: relationshipFilter
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

    const token = localStorage.getItem('token')

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

    const token = localStorage.getItem('token')

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

    const token = localStorage.getItem('token')

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

    const token = localStorage.getItem('token')

    return api.delete(`/parents/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}