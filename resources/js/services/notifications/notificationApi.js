import axios from 'axios'
import { API_BASE_URL, getAuthToken } from '../apiConfig'

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        Accept: 'application/json'
    }
})

api.interceptors.request.use(
    (config) => {
        const token = getAuthToken()

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

export const getNotifications = () => {
    return api.get('/notifications')
}

export const getUnreadNotificationCount = () => {
    return api.get('/notifications/unread-count')
}

export const markNotificationAsRead = (id) => {
    return api.patch(
        `/notifications/${id}/read`
    )
}