import axios from 'axios'

export const API_BASE_URL =
    import.meta.env.VITE_API_URL ||
    'http://127.0.0.1:8000/api'

const sessions = {
    admin: { token: 'token', user: 'user', role: 'admin' },
    teacher: { token: 'teacher_token', user: 'teacher_user', role: 'teacher' },
    parent: { token: 'parent_token', user: 'parent_user', role: 'parent' },
    student: { token: 'student_token', user: 'student_user', role: 'student' },
}

export const clearAuthSessions = () => {
    Object.values(sessions).forEach(({ token, user }) => {
        localStorage.removeItem(token)
        localStorage.removeItem(user)
    })
}

const readSession = (name) => {
    const session = sessions[name]
    const token = localStorage.getItem(session.token)
    const userData = localStorage.getItem(session.user)

    if (!token || !userData) {
        return null
    }

    try {
        const user = JSON.parse(userData)
        return user.role === session.role ? { token, user } : null
    } catch {
        localStorage.removeItem(session.user)
        localStorage.removeItem(session.token)
        return null
    }
}

export const getAuthToken = (preferredRole = null) => {
    const path = typeof window === 'undefined'
        ? ''
        : window.location.pathname

    const pathRole = path.startsWith('/parents')
        ? 'parent'
        : path.startsWith('/teacher')
            ? 'teacher'
            : null

    const roles = [
        preferredRole,
        pathRole,
        'admin',
        'teacher',
        'parent',
        'student',
    ].filter((role, index, list) => role && list.indexOf(role) === index)

    for (const role of roles) {
        const session = readSession(role)
        if (session) {
            return session.token
        }
    }

    return null
}

export const createApiClient = () => axios.create({
    baseURL: API_BASE_URL,
    headers: {
        Accept: 'application/json',
    },
})
