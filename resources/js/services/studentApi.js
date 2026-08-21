import axios from 'axios'

const API_URL = 'http://127.0.0.1:8000/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

export const getStudents = () => {
  return api.get('/students')
}

export const createStudent = (student) => {
  return api.post('/students', student)
}

export const updateStudent = (id, student) => {
  return api.put(`/students/${id}`, student)
}

export const deleteStudent = (id) => {
  return api.delete(`/students/${id}`)
}

export const bulkDeleteStudents = (ids) => {
  return api.post('/students/bulk-delete', {
    ids: ids
  })
}

export default api