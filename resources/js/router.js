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
    path: '/parents/marksheet',
    component: ParentMarksheet
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
        path: '/marksheets',
        component: MarksheetList,
        meta: {
            requiresAuth: true
        }
    },
    {
        path: '/marksheets/create',
        component: CreateMarksheet,
        meta:{
            requiresAuth: true
        }
    },
    {
    path: '/marksheets/:id',
    component: ViewMarksheet,
    meta: {
        requiresAuth: true
    }
},
{
    path: '/marksheets/:id/edit',
    component: EditMarksheet,
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