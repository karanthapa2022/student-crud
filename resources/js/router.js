import { createRouter, createWebHistory } from 'vue-router'

import ParentList from './components/parents/ParentList.vue'
import AddressList from './components/addresses/AddressList.vue'
import Login from './components/Login.vue'
import Register from './components/Register.vue'
import StudentList from './components/students/StudentList.vue'
import Profile from './components/Profile.vue'
import Trash from './components/students/Trash.vue'
import SubjectList from './components/subjects/SubjectList.vue'
import MarksheetList from './components/marksheets/MarksheetList.vue'
import CreateMarksheet from './components/marksheets/CreateMarksheet.vue'
import ViewMarksheet from './components/marksheets/ViewMarksheet.vue'
import EditMarksheet from './components/marksheets/EditMarksheet.vue'
import ParentMarksheet from './components/parents/ParentMarksheet.vue'
import ParentLogin from './components/parents/ParentLogin.vue'
import ParentProfile from './components/parents/ParentProfile.vue'
import TeacherList from './components/teachers/TeacherList.vue'
import TeacherStudents from './components/teachers/TeacherStudents.vue'
import TeacherSubjects from './components/teachers/TeacherSubjects.vue'
import Dashboard from './components/Dashboard.vue'
import UserManagement from './components/users/UserManagement.vue'

const routes = [

    {
        path: '/',
        redirect: '/login'
    },

    // =====================================================
    // PUBLIC ROUTES
    // =====================================================

    {
        path: '/login',
        component: Login
    },

    {
        path: '/parents/login',
        component: ParentLogin
    },

    {
        path: '/register',
        component: Register
    },


    // =====================================================
    // PROFILE
    // =====================================================

    {
        path: '/profile',
        component: Profile,
        meta: {
            requiresAuth: true,
            roles: ['admin', 'teacher', 'student', 'parent']
        }
    },

    {
        path: '/dashboard',
        component: Dashboard,
        meta: {
            requiresAuth: true,
            roles: ['admin', 'teacher', 'student', 'parent']
        }
    },

    {
        path: '/users',
        component: UserManagement,
        meta: {
            requiresAuth: true,
            roles: ['admin']
        }
    },

    {
    path: '/teacher/students',
    component: TeacherStudents,
    meta: {
        requiresAuth: true,
        roles: ['teacher']
    }
},

{
    path: '/teacher/subjects',
    component: TeacherSubjects,
    meta: {
        requiresAuth: true,
        roles: ['teacher']
    }
},

    // =====================================================
    // STUDENTS
    // ADMIN ONLY
    // =====================================================

    {
        path: '/students',
        component: StudentList,
        meta: {
            requiresAuth: true,
            roles: ['admin']
        }
    },


    // =====================================================
    // PARENTS
    // ADMIN ONLY
    // =====================================================

    {
        path: '/parents',
        component: ParentList,
        meta: {
            requiresAuth: true,
            roles: ['admin']
        }
    },

    {
    path: '/teachers',
    component: TeacherList,
    meta: {
        requiresAuth: true,
        roles: ['admin']
    }
},
    // =====================================================
    // PARENT MARKSHEET
    // PARENT ONLY
    // =====================================================

    {
    path: '/parents/marksheet/:studentId',
    component: ParentMarksheet,
    meta: {
        requiresAuth: true,
        roles: ['parent']
    }
},

{
    path: '/parents/profile',
    component: ParentProfile,
    meta: {
        requiresAuth: true,
        roles: ['parent']
    }
},


    // =====================================================
    // ADDRESSES
    // ADMIN ONLY
    // =====================================================

    {
        path: '/addresses',
        component: AddressList,
        meta: {
            requiresAuth: true,
            roles: ['admin']
        }
    },


    // =====================================================
    // SUBJECTS
    // ADMIN + TEACHER
    // =====================================================

    {
        path: '/subjects',
        component: SubjectList,
        meta: {
            requiresAuth: true,
            roles: ['admin', 'teacher']
        }
    },


    // =====================================================
    // MARKSHEETS LIST
    // ADMIN + TEACHER + PARENT
    // =====================================================

    {
        path: '/marksheets',
        component: MarksheetList,
        meta: {
            requiresAuth: true,
            roles: ['admin', 'teacher', 'student', 'parent']
        }
    },


    // =====================================================
    // CREATE MARKSHEET
    // ADMIN + TEACHER
    // =====================================================

    {
        path: '/marksheets/create',
        component: CreateMarksheet,
        meta: {
            requiresAuth: true,
            roles: ['admin', 'teacher']
        }
    },


    // =====================================================
    // VIEW MARKSHEET
    // ADMIN + TEACHER + PARENT
    // =====================================================

    {
        path: '/marksheets/:id',
        component: ViewMarksheet,
        meta: {
            requiresAuth: true,
            roles: ['admin', 'teacher', 'student', 'parent']
        }
    },


    // =====================================================
    // EDIT MARKSHEET
    // ADMIN + TEACHER
    // =====================================================

    {
        path: '/marksheets/:id/edit',
        component: EditMarksheet,
        meta: {
            requiresAuth: true,
            roles: ['admin', 'teacher']
        }
    },


    // =====================================================
    // TRASH
    // ADMIN ONLY
    // =====================================================

    {
        path: '/trash',
        component: Trash,
        meta: {
            requiresAuth: true,
            roles: ['admin']
        }
    }
]


// =========================================================
// CREATE ROUTER
// =========================================================

const router = createRouter({
    history: createWebHistory(),
    routes
})


// =========================================================
// AUTHENTICATION + ROLE GUARD
// =========================================================




router.beforeEach((to) => {

    // =========================================================
    // AUTHENTICATION DATA
    // =========================================================

    const adminToken = localStorage.getItem('token')
    const adminUserData = localStorage.getItem('user')

    const teacherToken = localStorage.getItem('teacher_token')
    const teacherUserData = localStorage.getItem('teacher_user')

    const parentToken = localStorage.getItem('parent_token')
    const parentUserData = localStorage.getItem('parent_user')

    const studentToken = localStorage.getItem('student_token')
    const studentUserData = localStorage.getItem('student_user')


    // =========================================================
    // PARSE USERS
    // =========================================================

    let adminUser = null
    let teacherUser = null
    let parentUser = null
    let studentUser = null

    try {

        adminUser = adminUserData
            ? JSON.parse(adminUserData)
            : null

    } catch (error) {

        console.error('Invalid admin user data:', error)

        localStorage.removeItem('user')
        localStorage.removeItem('token')

    }


    try {

        teacherUser = teacherUserData
            ? JSON.parse(teacherUserData)
            : null

    } catch (error) {

        console.error('Invalid teacher user data:', error)

        localStorage.removeItem('teacher_user')
        localStorage.removeItem('teacher_token')

    }


    try {

        parentUser = parentUserData
            ? JSON.parse(parentUserData)
            : null

    } catch (error) {

        console.error('Invalid parent user data:', error)

        localStorage.removeItem('parent_user')
        localStorage.removeItem('parent_token')

    }

    try {

        studentUser = studentUserData
            ? JSON.parse(studentUserData)
            : null

    } catch (error) {

        console.error('Invalid student user data:', error)

        localStorage.removeItem('student_user')
        localStorage.removeItem('student_token')

    }


    // =========================================================
    // DETERMINE CURRENT USER
    // =========================================================

    let currentUser = null
    let currentToken = null

    // Login stores the active session in the generic keys. Prefer that
    // session so stale role-specific tokens cannot change route access.
    if (
        adminToken &&
        adminUser &&
        ['admin', 'teacher', 'student', 'parent'].includes(adminUser.role)
    ) {
        currentUser = adminUser
        currentToken = adminToken
    } else if (
        teacherToken &&
        teacherUser &&
        teacherUser.role === 'teacher'
    ) {
        currentUser = teacherUser
        currentToken = teacherToken
    } else if (
        studentToken &&
        studentUser &&
        studentUser.role === 'student'
    ) {
        currentUser = studentUser
        currentToken = studentToken
    } else if (
        parentToken &&
        parentUser &&
        parentUser.role === 'parent'
    ) {
        currentUser = parentUser
        currentToken = parentToken
    }


    // =========================================================
    // PUBLIC ROUTES
    // =========================================================

    if (!to.meta.requiresAuth) {
        return true
    }


    // =========================================================
    // NO AUTHENTICATION
    // =========================================================

    if (!currentToken || !currentUser) {

        if (
            to.meta.roles?.includes('parent') &&
            !to.meta.roles?.includes('admin') &&
            !to.meta.roles?.includes('teacher')
        ) {

            return '/parents/login'

        }

        return '/login'
    }


    // =========================================================
    // ROLE PROTECTION
    // =========================================================

    if (
        to.meta.roles &&
        !to.meta.roles.includes(currentUser.role)
    ) {

        if (currentUser.role === 'teacher') {
            return '/dashboard'
        }

        if (currentUser.role === 'student') {
            return '/dashboard'
        }

        if (currentUser.role === 'parent') {
            return '/dashboard'
        }

        if (currentUser.role === 'admin') {
            return '/dashboard'
        }

        return '/login'
    }


    // =========================================================
    // AUTHORIZED
    // =========================================================

    return true
})


export default router