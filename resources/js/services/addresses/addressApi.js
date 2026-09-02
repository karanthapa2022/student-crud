import axios from 'axios'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
})

// =========================================================
// GET ALL ADDRESSES
// =========================================================

export const getAddresses = (page = 1,
    search='',
    provinceFilter='all'
) => {

    const token = localStorage.getItem('token')

    return api.get('/addresses', {
        params: {
            page,
            search,
            province_filter:provinceFilter
        },
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// GET SINGLE ADDRESS
// =========================================================

export const getAddress = (id) => {

    const token = localStorage.getItem('token')

    return api.get(`/addresses/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// CREATE ADDRESS
// =========================================================

export const createAddress = (data) => {

    const token = localStorage.getItem('token')

    return api.post('/addresses', data, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// UPDATE ADDRESS
// =========================================================

export const updateAddress = (id, data) => {

    const token = localStorage.getItem('token')

    return api.put(`/addresses/${id}`, data, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}


// =========================================================
// DELETE ADDRESS
// =========================================================

export const deleteAddress = (id) => {

    const token = localStorage.getItem('token')

    return api.delete(`/addresses/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}
