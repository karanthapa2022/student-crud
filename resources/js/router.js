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
import AdminDashboard from './components/AdminDashboard.vue'

const routes = [

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
            roles: ['admin', 'teacher', 'parent']
        }
    },

    {
        path:'/admin/dashboard',
        component: AdminDashboard,
        meta:{
            requiresAuth: true,
            roles: ['admin']
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


    // =====================================================
    // PARENT MARKSHEET
    // PARENT ONLY
    // =====================================================

    {
        path: '/parents/marksheet',
        component: ParentMarksheet,
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
            roles: ['admin', 'teacher', 'parent']
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
            roles: ['admin', 'teacher', 'parent']
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

    const token = localStorage.getItem('token')

    const storedUser = localStorage.getItem('user')

    const user = storedUser
        ? JSON.parse(storedUser)
        : null


    // =====================================================
    // NOT LOGGED IN
    // =====================================================

    if (to.meta.requiresAuth && !token) {
        return '/login'
    }


    // =====================================================
    // ROLE PROTECTION
    // =====================================================

    if (
        to.meta.roles &&
        (!user || !to.meta.roles.includes(user.role))
    ) {

        // Parent trying to access admin/teacher page
        if (user?.role === 'parent') {
            return '/marksheets'
        }

        // Teacher trying to access admin-only page
        if (user?.role === 'teacher') {
            return '/marksheets'
        }

        // Admin or unknown user
        return '/profile'
    }


    // =====================================================
    // ALLOW NAVIGATION
    // =====================================================

    return true
})


export default router