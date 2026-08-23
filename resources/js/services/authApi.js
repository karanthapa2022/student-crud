import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
})

// Register
export const registerUser = (user) => {
  return api.post('/register', user)
}

// Login
export const loginUser = (user) => {
  return api.post('/login', user)
}

// Logout
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

// Get logged-in user
export const getUser = (token) => {
  return api.get('/user', {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  })
}

// Update profile
export const updateProfile = (token, user) => {
  return api.put('/profile', user, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  })
}
// Update profilephoto
export const updateProfilePhoto=(token,file)=>{
  const formData=new formData()
  formData.append('profile_photo',file)
  return api.post('/profile/photo',formData,{
    headers:{
      Authorization:`Bearer ${token}`,
      'Content-Type':'multipart/form-data',
    },
  })
}