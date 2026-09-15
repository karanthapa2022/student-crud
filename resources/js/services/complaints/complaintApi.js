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

        const token = getAuthToken('student')

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


export const createComplaint = (complaint) => {

    return api.post(
        '/complaints',
        complaint
    )

}