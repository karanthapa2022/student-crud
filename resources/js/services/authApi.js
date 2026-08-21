import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
})

export const registerUser = (user) => {
  return api.post('/register', user)
}

export const loginUser = (user) => {
  return api.post('/login', user)
}

export const logoutUser = (token) => {
  return api.post(
    '/logout',
    {},
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  )
}