import { createRouter, createWebHistory } from 'vue-router'
import ParentList from './components/parents/ParentList.vue'
import AddressList from './components/addresses/AddressList.vue'
import Login from './components/Login.vue'
import Register from './components/Register.vue'
import StudentList from './components/students/StudentList.vue'
import Profile from './components/Profile.vue'
import Trash from './components/students/Trash.vue'
import SubjectList from './components/subjects/SubjectList.vue'

const routes = [
    {
        path: '/login',
        component: Login
    },

    {
        path: '/register',
        component: Register
    },
    

    {
        path:'/profile',
        component: Profile,
        meta:{
            requiresAuth: true
        }

    },
    {
        path:'/students',
        component:StudentList,
        meta:{
            requiresAuth: true
        }
    },
    {
    path: '/parents',
    component: ParentList,
    meta: {
        requiresAuth: true
    }
},
    {
        path: '/addresses',
        component: AddressList,
        meta: {
            requiresAuth: true
        }
    },
    {
    path: '/subjects',
    component: SubjectList,
    meta: {
        requiresAuth: true
    }
},

    {
        path: '/trash',
        component: Trash,
        meta: {
            requiresAuth: true
        }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Authentication Guard
router.beforeEach((to) => {

    const token = localStorage.getItem('token')

    // If page requires login and there is no token
    if (to.meta.requiresAuth && !token) {
        return '/login'
    }

    // Allow navigation
    return true
})

export default router