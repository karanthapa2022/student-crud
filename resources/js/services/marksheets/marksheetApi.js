import axios from 'axios'

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        Accept: 'application/json'
    }
})

// Add authentication token
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token')

    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }

    return config
})

// Get all marksheets
export const getMarksheets = () => {
    return api.get('/marksheets')
}

// Get one marksheet
export const getMarksheet = (id) => {
    return api.get(`/marksheets/${id}`)
}

// Create marksheet
export const createMarksheet = (data) => {
    return api.post('/marksheets', data)
}
// Delete marksheet
export const deleteMarksheet = (id) => {
    return api.delete(`/marksheets/${id}`)
}
// Update marksheet
export const updateMarksheet = (id, data) => {
    return api.put(`/marksheets/${id}`, data)
}
// Parent search marksheet
export const searchParentMarksheet = (data) => {
    return api.post('/marksheets/parent-search', data)
}