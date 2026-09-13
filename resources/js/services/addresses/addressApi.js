import axios from 'axios'
import { API_BASE_URL, getAuthToken } from '../apiConfig'

const api = axios.create({
    baseURL: API_BASE_URL,
})

// =========================================================
// GET ALL ADDRESSES
// =========================================================

export const getAddresses = (page = 1,
    search='',
    provinceFilter='all'
) => {

    const token = getAuthToken('admin')

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

    const token = getAuthToken('admin')

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

    const token = getAuthToken('admin')

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

    const token = getAuthToken('admin')

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

    const token = getAuthToken('admin')

    return api.delete(`/addresses/${id}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    })

}
