import axios from 'axios'

const API_URL = 'http://127.0.0.1:8000/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    Accept: 'application/json'
  }
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})


// =========================================================
// GET STUDENTS
// =========================================================

export const getStudents = () => {
  return api.get('/students')
}


// =========================================================
// CREATE STUDENT
// =========================================================

export const createStudent = (student) => {
  return api.post('/students', student)
}


// =========================================================
// UPDATE STUDENT
// =========================================================

export const updateStudent = (id, student) => {
  return api.put(`/students/${id}`, student)
}


// =========================================================
// DELETE STUDENT
// =========================================================

export const deleteStudent = (id) => {
  return api.delete(`/students/${id}`)
}


// =========================================================
// BULK DELETE
// =========================================================

export const bulkDeleteStudents = (ids) => {
  return api.post('/students/bulk-delete', {
    ids: ids
  })
}


// =========================================================
// CREATE STUDENT WITH PHOTO
// =========================================================

export const createStudentWithPhoto = (formData) => {
  return api.post(
    '/students',
    formData
  )
}


// =========================================================
// UPDATE STUDENT WITH PHOTO
// =========================================================

export const updateStudentWithPhoto = (id, formData) => {

  formData.append('_method', 'PUT')

  return api.post(
    `/students/${id}`,
    formData
  )
}

// =========================================================
// GET STUDENT STATISTICS
// =========================================================

export const getStudentStatistics = () => {
    return api.get('/students/statistics')
}

export default api