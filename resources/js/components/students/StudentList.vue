<script setup>

import BaseTable from '../BaseTable.vue'
import BaseButton from '../BaseButton.vue'

import {
    ref,
    computed,
    onMounted,
    onBeforeUnmount,
    nextTick,
    watch
} from 'vue'

import { useRouter } from 'vue-router'

import axios from 'axios'

import { useStudentStore } from '../../stores/students/student'
import { useParentStore } from '../../stores/parents/parent'
import { useAddressStore } from '../../stores/addresses/address'
import { useSubjectStore } from '../../stores/subjects/subject'


import {
    updateStudentWithPhoto,
    deleteStudent as deleteStudentApi,
    bulkDeleteStudents
} from '../../services/students/studentApi'


// =========================================================
// CHART.JS
// =========================================================

import {
    Chart,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend
} from 'chart.js'


Chart.register(
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend
)


// =========================================================
// ROUTER + STORE
// =========================================================

const router = useRouter()

const studentStore = useStudentStore()
const parentStore =useParentStore()
const addressStore = useAddressStore()
const subjectStore = useSubjectStore()


// =========================================================
// DARK MODE
// =========================================================

const isDark = ref(
    localStorage.getItem('theme') === 'dark'
)


const applyDarkMode = () => {

    if (isDark.value) {

        document.documentElement.classList.add('dark')

    } else {

        document.documentElement.classList.remove('dark')

    }

}


const toggleDarkMode = () => {

    isDark.value = !isDark.value

    localStorage.setItem(
        'theme',
        isDark.value ? 'dark' : 'light'
    )

    applyDarkMode()

}


// =========================================================
// TRASH
// =========================================================

const goToTrash = () => {

    router.push('/trash')

}


// =========================================================
// ADD STUDENT
// =========================================================

const showAddForm = ref(false)


const newStudent = ref({

    name: '',
    class: '',
    symbol_no:'',
    date_of_birth:'',
    email: '',
    phone: '',
    status: 'active',
    photo: null,
    parent_id: null,
    address_id: null,
    photo: null,
    subjects:[]

})


const handlePhotoChange = (event) => {

    newStudent.value.photo =
        event.target.files[0] || null

}


// =========================================================
// TABLE HEADERS
// =========================================================

const tableHeaders = [

    {
        key: 'select',
        label: ''
    },

    {
        key: 'sn',
        label: 'S.N.'
    },

    {
        key: 'id',
        label: 'ID'
    },

    {
        key: 'photo',
        label: 'Photo'
    },

    {
        key: 'name',
        label: 'Name'
    },
    {
        key: 'class',
        label: 'Class'
    },

    {
        key: 'email',
        label: 'Email'
    },

    {
        key: 'phone',
        label: 'Phone'
    },

    {
        key: 'status',
        label: 'Status'
    },

    {
        key: 'actions',
        label: 'Actions'
    }

]


// =========================================================
// EDIT + VIEW
// =========================================================

const editingStudent = ref(null)

const viewingStudent = ref(null)


const editStudent = (student) => {

    editingStudent.value = {

        ...student,

        // Keep subject ID and name so subjects can be edited manually
       subjects: student.subjects
    ? student.subjects.map(subject => ({
        subject_id: subject.id ?? null,
        subject_name: subject.pivot?.subject_name || subject.name || '',
        subject_code: subject.pivot?.subject_code || ''
    }))
    : [],

        newPhoto: null

    }

}


const viewStudent = (student) => {

    viewingStudent.value = {

        ...student

    }

}


// =========================================================
// SELECTED STUDENTS
// =========================================================

const selectedStudents = ref([])


const toggleStudent = (id) => {

    if (
        selectedStudents.value.includes(id)
    ) {

        selectedStudents.value =
            selectedStudents.value.filter(
                studentId => studentId !== id
            )

    } else {

        selectedStudents.value.push(id)

    }

}


// =========================================================
// SELECT ALL
// =========================================================

const allSelected = computed(() => {

    return (

        studentStore.students.length > 0 &&

        selectedStudents.value.length ===
        studentStore.students.length

    )

})


const toggleAll = () => {

    if (allSelected.value) {

        selectedStudents.value = []

    } else {

        selectedStudents.value =
            studentStore.students.map(
                student => student.id
            )

    }

}


// =========================================================
// SEARCH + FILTER
// =========================================================

const searchQuery = ref('')

const statusFilter = ref('all')


// =========================================================
// SEARCH + FILTER FROM API
// =========================================================

const applyFilters = async () => {

    selectedStudents.value = []

    await studentStore.fetchStudents(
        1,
        searchQuery.value,
        statusFilter.value
    )

}


watch(
    searchQuery,
    () => {

        applyFilters()

    }
)


watch(
    statusFilter,
    () => {

        applyFilters()

    }
)


// =========================================================
// PAGINATION
// =========================================================

const currentPage = computed(() => {

    return (
        studentStore.pagination.currentPage || 1
    )

})


const totalPages = computed(() => {

    return (
        studentStore.pagination.lastPage || 1
    )

})


const itemsPerPage = computed(() => {

    return (
        studentStore.pagination.perPage || 10
    )

})


const totalStudents = computed(() => {

    return (
        studentStore.pagination.totalStudents || 0
    )

})


const showingFrom = computed(() => {

    return (
        studentStore.pagination.from || 0
    )

})


const showingTo = computed(() => {

    return (
        studentStore.pagination.to || 0
    )

})


// =========================================================
// GO TO PAGE
// =========================================================

const goToPage = async (page) => {

    if (
        page < 1 ||
        page > totalPages.value
    ) {

        return

    }


    if (
        page === currentPage.value
    ) {

        return

    }


    selectedStudents.value = []


    await studentStore.fetchStudents(

        page,

        searchQuery.value,

        statusFilter.value

    )

}


// =========================================================
// PREVIOUS PAGE
// =========================================================

const previousPage = async () => {

    if (
        currentPage.value > 1
    ) {

        await goToPage(
            currentPage.value - 1
        )

    }

}


// =========================================================
// NEXT PAGE
// =========================================================

const nextPage = async () => {

    if (
        currentPage.value <
        totalPages.value
    ) {

        await goToPage(
            currentPage.value + 1
        )

    }

}


// =========================================================
// DASHBOARD STATISTICS
// =========================================================

const activeStudents = computed(() => {

    return (
        studentStore.statistics.active || 0
    )

})


const inactiveStudents = computed(() => {

    return (
        studentStore.statistics.inactive || 0
    )

})


const studentsWithPhotos = computed(() => {

    return studentStore.students.filter(
        student => student.photo
    ).length

})


// =========================================================
// CHART
// =========================================================

const statusChart = ref(null)

let statusChartInstance = null


const createStatusChart = async () => {

    await nextTick()


    if (!statusChart.value) {

        return

    }


    if (statusChartInstance) {

        statusChartInstance.destroy()

        statusChartInstance = null

    }


    const active =
        studentStore.statistics.active || 0


    const inactive =
        studentStore.statistics.inactive || 0


    try {

        statusChartInstance =
            new Chart(
                statusChart.value,
                {

                    type: 'doughnut',

                    data: {

                        labels: [

                            'Active Students',

                            'Inactive Students'

                        ],

                        datasets: [

                            {

                                data: [

                                    active,

                                    inactive

                                ],

                                backgroundColor: [

                                    'green',

                                    'red'

                                ],

                                borderWidth: 3,

                                hoverOffset: 8

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        cutout: '65%',

                        animation: {

                            duration: 800

                        },

                        plugins: {

                            legend: {

                                display: true,

                                position: 'bottom',

                                labels: {

                                    padding: 20,

                                    font: {

                                        size: 14

                                    }

                                }

                            },

                            tooltip: {

                                enabled: true

                            }

                        }

                    }

                }

            )

    } catch (error) {

        console.error(
            'Chart creation failed:',
            error
        )

    }

}


// =========================================================
// WATCH STATISTICS
// =========================================================

watch(

    () => studentStore.statistics,

    async () => {

        await createStatusChart()

    },

    {
        deep: true
    }

)


// =========================================================
// ADD STUDENT
// =========================================================

const addStudent = async () => {

    try {

        const formData = new FormData()


        formData.append(
            'name',
            newStudent.value.name
        )
        formData.append(
            'class',
            newStudent.value.class
        )
        formData.append(
            'symbol_no',
            newStudent.value.symbol_no
        )
        formData.append('date_of_birth', newStudent.value.date_of_birth)


        formData.append(
            'email',
            newStudent.value.email
        )


        formData.append(
            'phone',
            newStudent.value.phone
        )


        formData.append(
            'status',
            newStudent.value.status
        )
        if(newStudent.value.parent_id){
        formData.append(
            'parent_id',
            newStudent.value.parent_id
        )}
        if(newStudent.value.address_id){
        formData.append(
            'address_id',
            newStudent.value.address_id
        )}

        // Always send subjects
const selectedSubjects = newStudent.value.subjects || []

console.log('SELECTED SUBJECTS:', selectedSubjects)

selectedSubjects.forEach(subjectId => {

    console.log(
        'SUBJECT ID:',
        subjectId,
        'TYPE:',
        typeof subjectId
    )

    formData.append(
        'subjects[]',
        subjectId
    )

})



        if (
            newStudent.value.photo
        ) {

            formData.append(
                'photo',
                newStudent.value.photo
            )

        }


        await studentStore.addStudent(
            formData
        )


        showAddForm.value = false


        newStudent.value = {

            name: '',
            class: '',
            symbol_no:'',
            date_of_birth:'',
            email: '',
            phone: '',
            status: 'active',
            photo: null,
            parent_id: null,
            address_id: null,
            subjects:[]

        }


        // Keep current search/filter
        await studentStore.fetchStudents(

            currentPage.value,

            searchQuery.value,

            statusFilter.value

        )


        await studentStore.fetchStatistics()


        alert(
            'Student added successfully!'
        )


    } catch (error) {
        console.log('FULL SERVER ERROR:', error.response?.data)
console.log('VALIDATION ERRORS:', error.response?.data?.errors)

        console.error(
            'Error adding student:',
            error
        )


        alert(
            error.response?.data?.message ||
            'Error adding student.'
        )

    }

}


// =========================================================
// UPDATE STUDENT
// =========================================================

const updateStudent = async () => {

    if (
        !editingStudent.value
    ) {

        return

    }


    try {

        const formData = new FormData()


        formData.append(
            'name',
            editingStudent.value.name
        )

        formData.append(
            'class',
            editingStudent.value.class
        )
        formData.append(
            'symbol_no',
            editingStudent.value.symbol_no ||''

        )

        formData.append('date_of_birth', editingStudent.value.date_of_birth || '')

        formData.append(
            'email',
            editingStudent.value.email
        )
        console.log('Symbol No being sent:',editingStudent.value.symbol_no)


        formData.append(
            'phone',
            editingStudent.value.phone
        )


        formData.append(
            'status',
            editingStudent.value.status
        )

        if (editingStudent.value.parent_id) {

    formData.append(
        'parent_id',
        editingStudent.value.parent_id
    )

}

if (editingStudent.value.address_id) {

    formData.append(
        'address_id',
        editingStudent.value.address_id
    )

}



// Add editable subjects
console.log(
    'EDITING SUBJECTS:',
    editingStudent.value.subjects
)

if (Array.isArray(editingStudent.value.subjects)) {

    editingStudent.value.subjects.forEach((subject, index) => {

        formData.append(
            `subjects[${index}][subject_id]`,
            subject.subject_id ?? ''
        )

        formData.append(
            `subjects[${index}][subject_name]`,
            subject.subject_name
        )

        formData.append(
            `subjects[${index}][subject_code]`,
            subject.subject_code ?? ''
        )

    })

}

        if (
            editingStudent.value.newPhoto
        ) {

            formData.append(
                'photo',
                editingStudent.value.newPhoto
            )

        }


        await updateStudentWithPhoto(

        
            editingStudent.value.id,

            formData

        )

        console.log('Selected subject IDs:', editingStudent.value.subjects)

        editingStudent.value = null


        // Keep current search/filter
        await studentStore.fetchStudents(

            currentPage.value,

            searchQuery.value,

            statusFilter.value

        )


        await studentStore.fetchStatistics()


        alert(
            'Student updated successfully!'
        )


    } catch (error) {

        console.error(
            'Error updating student:',
            error
        )
        console.log(
    'FULL SERVER ERROR:',
    error.response?.data
)
        console.log(
    'VALIDATION ERRORS:',
    error.response?.data?.errors
)

        alert(
            error.response?.data?.message ||
            'Error updating student.'
        )

    }

}


// =========================================================
// DELETE STUDENT
// =========================================================

const deleteStudent = async (student) => {

    const confirmed =
        confirm(
            `Are you sure you want to delete ${student.name}?`
        )


    if (!confirmed) {

        return

    }


    try {

        await deleteStudentApi(
            student.id
        )


        selectedStudents.value =
            selectedStudents.value.filter(
                id => id !== student.id
            )


        let pageToLoad =
            currentPage.value


        // If last student on page was deleted,
        // go back one page.
        if (
            studentStore.students.length === 1 &&
            currentPage.value > 1
        ) {

            pageToLoad =
                currentPage.value - 1

        }


        // Keep current search/filter
        await studentStore.fetchStudents(

            pageToLoad,

            searchQuery.value,

            statusFilter.value

        )


        await studentStore.fetchStatistics()


        alert(
            'Student deleted successfully!'
        )


    } catch (error) {

        console.error(
            'Error deleting student:',
            error
        )


        alert(
            error.response?.data?.message ||
            'Error deleting student.'
        )

    }

}


// =========================================================
// BULK DELETE
// =========================================================

const bulkDelete = async () => {

    if (
        selectedStudents.value.length === 0
    ) {

        alert(
            'Please select at least one student.'
        )

        return

    }


    const confirmed =
        confirm(
            'Are you sure you want to delete the selected students?'
        )


    if (!confirmed) {

        return

    }


    try {

        await bulkDeleteStudents(
            selectedStudents.value
        )


        selectedStudents.value = []


        let pageToLoad =
            currentPage.value


        // Keep current search/filter
        await studentStore.fetchStudents(

            pageToLoad,

            searchQuery.value,

            statusFilter.value

        )


        // If current page becomes empty,
        // go to previous page.
        if (
            studentStore.students.length === 0 &&
            currentPage.value > 1
        ) {

            await studentStore.fetchStudents(

                currentPage.value - 1,

                searchQuery.value,

                statusFilter.value

            )

        }


        await studentStore.fetchStatistics()


        alert(
            'Selected students deleted successfully!'
        )


    } catch (error) {

        console.error(
            'Bulk delete error:',
            error
        )


        alert(
            error.response?.data?.message ||
            'Error deleting selected students.'
        )

    }

}


// =========================================================
// LOGOUT
// =========================================================

const logout = async () => {

    const token =
        localStorage.getItem('token')


    try {

        await axios.post(

            'http://127.0.0.1:8000/api/logout',

            {},

            {

                headers: {

                    Authorization:
                        `Bearer ${token}`

                }

            }

        )

    } catch (error) {

        console.error(
            'Logout error:',
            error
        )

    }


    localStorage.removeItem('token')

    localStorage.removeItem('user')


    router.push('/login')

}


// =========================================================
// ON MOUNTED
// =========================================================

onMounted(async () => {

    applyDarkMode()


    try {

        await studentStore.fetchStudents(

            1,

            searchQuery.value,

            statusFilter.value

        )


        await studentStore.fetchStatistics()
        await parentStore.fetchParents()
        await addressStore.fetchAddresses()
        await subjectStore.fetchSubjects()


        await nextTick()

        await createStatusChart()


    } catch (error) {

        console.error(
            'StudentList initialization error:',
            error
        )

    }

})


// =========================================================
// BEFORE UNMOUNT
// =========================================================

onBeforeUnmount(() => {

    if (statusChartInstance) {

        statusChartInstance.destroy()

        statusChartInstance = null

    }

})

</script>


<template>

<div
    class="min-h-screen bg-gray-100 dark:bg-gray-950 p-4 sm:p-6 lg:p-8 transition-colors duration-300"
>

    <div
        class="w-full max-w-7xl mx-auto"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div
            class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5"
        >

            <div>

                <h1
                    class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white"
                >
                    Student Management
                </h1>

                <p
                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                >
                    Manage and view all registered students
                </p>

            </div>


            <div
                class="flex flex-col sm:flex-row gap-3"
            >

            <!-- ADMIN DASHBOARD -->
    <button
        type="button"
        @click="router.push('/admin/dashboard')"
        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-600 dark:bg-purple-700 text-white font-medium hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-sm"
    >
        Dashboard
    </button>

                <button
                    type="button"
                    @click="router.push('/profile')"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 dark:bg-blue-700 text-white font-medium hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-sm"
                >
                    Profile
                </button>


                <button
                    type="button"
                    @click="logout"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gray-800 dark:bg-gray-700 text-white font-medium hover:bg-gray-900 dark:hover:bg-gray-600 transition shadow-sm"
                >
                    Logout
                </button>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- ERROR -->
        <!-- ================================================= -->

        <div
            v-if="studentStore.error"
            class="mt-6 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl"
        >
            {{ studentStore.error }}
        </div>


        <!-- ================================================= -->
        <!-- STATISTICS -->
        <!-- ================================================= -->

        <div
            class="mt-8 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5"
        >

            <!-- TOTAL -->

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
            >

                <p
                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                >
                    Total Students
                </p>


                <h3
                    class="mt-2 text-3xl font-bold text-gray-800 dark:text-white"
                >
                    {{ totalStudents }}
                </h3>


                <p
                    class="mt-2 text-xs text-gray-400 dark:text-gray-500"
                >
                    All registered students
                </p>

            </div>


            <!-- ACTIVE -->

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
            >

                <p
                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                >
                    Active Students
                </p>


                <h3
                    class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400"
                >
                    {{ activeStudents }}
                </h3>


                <p
                    class="mt-2 text-xs text-gray-400 dark:text-gray-500"
                >
                    Currently active
                </p>

            </div>


            <!-- INACTIVE -->

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
            >

                <p
                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                >
                    Inactive Students
                </p>


                <h3
                    class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400"
                >
                    {{ inactiveStudents }}
                </h3>


                <p
                    class="mt-2 text-xs text-gray-400 dark:text-gray-500"
                >
                    Currently inactive
                </p>

            </div>


            <!-- PHOTOS -->

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm"
            >

                <p
                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                >
                    Students With Photos
                </p>


                <h3
                    class="mt-2 text-3xl font-bold text-purple-600 dark:text-purple-400"
                >
                    {{ studentsWithPhotos }}
                </h3>


                <p
                    class="mt-2 text-xs text-gray-400 dark:text-gray-500"
                >
                    Current page
                </p>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- ACTION BAR -->
        <!-- ================================================= -->

        <div
            class="mt-8 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm"
        >

            <div
                class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5"
            >

                <div>

                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white"
                    >
                        Students
                    </h2>


                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Search, filter and manage your students
                    </p>

                </div>


                <div
                    class="flex flex-col sm:flex-row gap-3"
                >

                    <BaseButton
                        variant="success"
                        @click="showAddForm = true"
                        class="w-full sm:w-auto"
                    >
                        Add Student
                    </BaseButton>


                    <BaseButton
                        variant="danger"
                        @click="bulkDelete"
                        class="w-full sm:w-auto"
                    >
                        Delete Selected
                    </BaseButton>


                    <button
                        type="button"
                        @click="goToTrash"
                        class="px-4 py-2.5 rounded-xl bg-gray-800 dark:bg-gray-700 text-white font-medium hover:bg-gray-900 dark:hover:bg-gray-600 transition"
                    >
                        Trash
                    </button>

                    <button
    type="button"
    @click="router.push('/marksheets')"
    class="px-4 py-2.5 rounded-xl bg-purple-600 dark:bg-purple-700 text-white font-medium hover:bg-purple-700 dark:hover:bg-purple-600 transition"
>
    Marksheets
</button>


                    <button
                        type="button"
                        @click="toggleDarkMode"
                        class="px-4 py-2.5 rounded-xl bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 font-medium hover:bg-gray-900 dark:hover:bg-white transition"
                    >
                        {{ isDark ? '☀️ Light' : '🌙 Dark' }}
                    </button>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- SEARCH + FILTER -->
            <!-- ================================================= -->

            <div
                class="mt-5 flex flex-col lg:flex-row gap-3"
            >

                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by name, email or phone..."
                    class="w-full flex-1 pl-4 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                />


                <select
                    v-model="statusFilter"
                    class="w-full lg:w-52 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                >

                    <option value="all">
                        All Students
                    </option>

                    <option value="active">
                        Active
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>

                </select>

            </div>


            <!-- ================================================= -->
            <!-- COUNT -->
            <!-- ================================================= -->

            <div
                class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
            >

                <p
                    class="text-sm text-gray-500 dark:text-gray-400"
                >

                    Showing

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ showingFrom }}
                    </span>

                    -

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ showingTo }}
                    </span>

                    of

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ totalStudents }}
                    </span>

                    students

                </p>


                <p
                    v-if="selectedStudents.length > 0"
                    class="text-sm font-medium text-blue-600 dark:text-blue-400"
                >

                    {{ selectedStudents.length }}

                    selected

                </p>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- ADD MODAL -->
        <!-- ================================================= -->

        <Teleport to="body">

            <div
                v-if="showAddForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 px-4"
            >

                <div
                    class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6"
                >

                    <div
                        class="flex items-center justify-between mb-6"
                    >

                        <div>

                            <h2
                                class="text-xl font-bold text-gray-800 dark:text-white"
                            >
                                Add New Student
                            </h2>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Enter student information below
                            </p>

                        </div>


                        <button
                            type="button"
                            @click="showAddForm = false"
                            class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 text-xl"
                        >
                            ×
                        </button>

                    </div>


                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >

                        <input
                            v-model="newStudent.name"
                            type="text"
                            placeholder="Full Name"
                            
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        />
                        <div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Class
    </label>

    <input
        v-model="newStudent.class"
        type="text"
        placeholder="Enter Class"
        class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-800 dark:text-white bg-white dark:bg-gray-800"
    />
    <div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Symbol No.
    </label>

    <input
        v-model="newStudent.symbol_no"
        type="text"
        placeholder="Enter symbol number"
        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
    >
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Date of Birth
    </label>

    <input
        v-model="newStudent.date_of_birth"
        type="date"
        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
    >
</div>
</div>


                        <input
                            v-model="newStudent.email"
                            type="email"
                            placeholder="Email Address"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        />


                        <input
                            v-model="newStudent.phone"
                            type="text"
                            placeholder="Phone Number"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        />


                        <select
                            v-model="newStudent.status"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        >

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>
                        <select
    v-model="newStudent.parent_id"
    class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
>

    <option :value="null">
        Select Parent
    </option>

    <option
        v-for="parent in parentStore.parents"
        :key="parent.id"
        :value="parent.id"
    >
        {{ parent.name }}
    </option>

</select>
<select
    v-model="newStudent.address_id"
    class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
>

    <option :value="null">
        Select Address
    </option>

    <option
        v-for="address in addressStore.addresses"
        :key="address.id"
        :value="address.id"
    >
        {{ address.province }} - {{ address.district }} - {{ address.municipality }} - Ward {{ address.ward }}
    </option>

</select>




                        <div
                            class="md:col-span-2"
                        >

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Student Photo
                            </label>


                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                @change="handlePhotoChange"
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-800 dark:text-white bg-white dark:bg-gray-800"
                            />

                        </div>

                    </div>


                    <div
                        class="flex flex-col sm:flex-row gap-3 mt-6"
                    >

                        <button
                            type="button"
                            @click="addStudent"
                            class="w-full sm:w-auto bg-green-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-green-700 transition"
                        >
                            Add Student
                        </button>


                        <button
                            type="button"
                            @click="showAddForm = false"
                            class="w-full sm:w-auto border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-xl font-medium"
                        >
                            Cancel
                        </button>

                    </div>

                </div>

            </div>

        </Teleport>


        <!-- ================================================= -->
        <!-- VIEW MODAL -->
        <!-- ================================================= -->

        <Teleport to="body">

            <div
                v-if="viewingStudent"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 px-4"
            >

                <div
                    class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 max-h-[80vh] overflow-y-auto"
                >

                    <div
                        class="flex items-center justify-between mb-6"
                    >

                        <h2
                            class="text-xl font-bold text-gray-800 dark:text-white"
                        >
                            Student Details
                        </h2>


                        <button
                            type="button"
                            @click="viewingStudent = null"
                            class="w-9 h-9 rounded-lg text-gray-500 text-xl"
                        >
                            ×
                        </button>

                    </div>


                    <div
                        class="flex justify-center mb-6"
                    >

                        <img
                            v-if="viewingStudent.photo"
                            :src="`http://127.0.0.1:8000/storage/${viewingStudent.photo}`"
                            :alt="viewingStudent.name"
                            class="w-28 h-28 rounded-full object-cover border-4 border-gray-100 dark:border-gray-700"
                        />


                        <div
                            v-else
                            class="w-28 h-28 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400"
                        >
                            No Photo
                        </div>

                    </div>


                    <div
                        class="space-y-4"
                    >

                        <div
                            class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3"
                        >

                            <span
                                class="font-medium text-gray-500"
                            >
                                Student ID
                            </span>

                            <span
                                class="font-semibold text-gray-800 dark:text-white"
                            >
                                {{ viewingStudent.id }}
                            </span>

                        </div>


                        <div
                            class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3"
                        >

                            <span
                                class="font-medium text-gray-500"
                            >
                                Name
                            </span>

                            <span
                                class="font-semibold text-gray-800 dark:text-white"
                            >
                                {{ viewingStudent.name }}
                            </span>

                        </div>
                        <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
    <span class="font-medium text-gray-500">
        Class
    </span>

    <span class="font-semibold text-gray-800 dark:text-white">
        {{ viewingStudent.class || 'Not assigned' }}
    </span>
</div>
<div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
    <span class="font-medium text-gray-500">
        Symbol No.
    </span>

    <span class="font-semibold text-gray-800 dark:text-white">
        {{ viewingStudent.symbol_no || 'Not assigned' }}
    </span>
</div>
<div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
    <span class="font-medium text-gray-500">
        Date of Birth
    </span>

    <span class="font-semibold text-gray-800 dark:text-white">
        {{ viewingStudent.date_of_birth || 'Not assigned' }}
    </span>
</div>


                        <div
                            class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3 gap-4"
                        >

                            <span
                                class="font-medium text-gray-500"
                            >
                                Email
                            </span>

                            <span
                                class="text-gray-800 dark:text-gray-200 break-all text-right"
                            >
                                {{ viewingStudent.email }}
                            </span>

                        </div>


                        <div
                            class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3"
                        >

                            <span
                                class="font-medium text-gray-500"
                            >
                                Phone
                            </span>


                            <span
                                class="text-gray-800 dark:text-gray-200"
                            >
                                {{ viewingStudent.phone }}
                            </span>

                        </div>

                        <div
    class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3 gap-4"
>

    <span
        class="font-medium text-gray-500"
    >
        Parent
    </span>

    <span
        class="text-gray-800 dark:text-gray-200 text-right"
    >
        {{ viewingStudent.parent?.name || 'Not assigned' }}
    </span>

</div>
<div
    class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-3 gap-4"
>

    <span
        class="font-medium text-gray-500"
    >
        Address
    </span>

    <span
        class="text-gray-800 dark:text-gray-200 text-right"
    >
        {{
            viewingStudent.address
                ? `${viewingStudent.address.municipality}, ${viewingStudent.address.district}`
                : 'Not assigned'
        }}
    </span>

</div>

<div
    class="border-b border-gray-100 dark:border-gray-800 pb-3"
>

    <div class="flex justify-between gap-4 mb-2">

        <span
            class="font-medium text-gray-500"
        >
            Subjects
        </span>

        <span
            v-if="!viewingStudent.subjects?.length"
            class="text-gray-800 dark:text-gray-200"
        >
            Not assigned
        </span>

    </div>


    <div
        v-if="viewingStudent.subjects?.length"
        class="flex flex-wrap justify-end gap-2"
    >

        <div
    v-for="(subject, index) in viewingStudent.subjects"
    :key="index"
    class="grid grid-cols-12 gap-3 items-center border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3"
>
    <!-- SUBJECT -->

    <div class="col-span-6">
        <p class="font-medium text-gray-800 dark:text-white">
            {{ subject.name }}
        </p>
    </div>

    <!-- SUBJECT CODE -->

    <div class="col-span-5">
        <p class="text-gray-600 dark:text-gray-300">
            {{ subject.pivot?.subject_code || '—' }}
        </p>
    </div>

    <!-- EMPTY -->

    <div class="col-span-1"></div>
</div>

    </div>

</div>




                        <div
                            class="flex justify-between"
                        >

                            <span
                                class="font-medium text-gray-500"
                            >
                                Status
                            </span>

                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                                :class="
                                    viewingStudent.status === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                {{ viewingStudent.status }}
                            </span>

                        </div>

                    </div>


                    <button
                        type="button"
                        @click="viewingStudent = null"
                        class="w-full mt-6 mb-2 bg-gray-800 text-white px-5 py-3 rounded-xl font-medium"
                    >
                        Close
                    </button>

                </div>

            </div>

        </Teleport>


        <!-- ================================================= -->
        <!-- EDIT MODAL -->
        <!-- ================================================= -->

        <Teleport to="body">

            <div
                v-if="editingStudent"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 px-4"
            >

                <div
                    class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 max-h-[80vh] overflow-y-auto"
>
                    <div
                        class="flex items-center justify-between mb-6"
                    >

                        <h2
                            class="text-xl font-bold text-gray-800 dark:text-white"
                        >
                            Edit Student
                        </h2>


                        <button
                            type="button"
                            @click="editingStudent = null"
                            class="w-9 h-9 rounded-lg text-gray-500 text-xl"
                        >
                            ×
                        </button>

                    </div>


                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >

                        <input
                            v-model="editingStudent.name"
                            type="text"
                            placeholder="Full Name"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        />
                        <div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Class
    </label>

    <input
        v-model="editingStudent.class"
        type="text"
        placeholder="Enter Class"
        class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-800 dark:text-white bg-white dark:bg-gray-800"
    />
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Symbol No.
    </label>

    <input
        v-model="editingStudent.symbol_no"
        type="text"
        placeholder="Enter symbol number"
        class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-800 dark:text-white bg-white dark:bg-gray-800"
    />
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Date of Birth
    </label>

    <input
        v-model="editingStudent.date_of_birth"
        type="date"
        class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-800 dark:text-white bg-white dark:bg-gray-800"
    />
</div>


                        <input
                            v-model="editingStudent.email"
                            type="email"
                            placeholder="Email Address"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        />


                        <input
                            v-model="editingStudent.phone"
                            type="text"
                            placeholder="Phone Number"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        />


                        <select
                            v-model="editingStudent.status"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                        >

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>
                        <select
    v-model="editingStudent.parent_id"
    class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
>

    <option value="">
        Select Parent
    </option>

    <option
        v-for="parent in parentStore.parents"
        :key="parent.id"
        :value="parent.id"
    >
        {{ parent.name }}
    </option>

</select>

<select
    v-model="editingStudent.address_id"
    class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
>

    <option value="">
        Select Address
    </option>

    <option
        v-for="address in addressStore.addresses"
        :key="address.id"
        :value="address.id"
    >
        {{ address.municipality }}, {{ address.district }}
    </option>

</select>





<!-- SUBJECTS -->

<div class="md:col-span-2">

    <div class="flex items-center justify-between mb-3">

        <label
            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            Subjects
        </label>

        <button
            type="button"
            @click="editingStudent.subjects.push({
                subject_id: null,
                subject_name: '',
                subject_code: ''
            })"
            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition"
        >
            + Add Subject
        </button>

    </div>

    <!-- HEADERS -->

    <div
        v-if="editingStudent.subjects.length"
        class="grid grid-cols-12 gap-3 mb-2"
    >

        <div
            class="col-span-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase"
        >
            Subject
        </div>

        <div
            class="col-span-5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase"
        >
            Subject Code
        </div>

        <div class="col-span-1"></div>

    </div>

    <!-- SUBJECT ROWS -->

    <div
        v-if="editingStudent.subjects.length"
        class="space-y-3"
    >

        <div
            v-for="(subject, index) in editingStudent.subjects"
            :key="index"
            class="grid grid-cols-12 gap-3 items-center"
        >

            <!-- SUBJECT NAME -->

            <input
                v-model="subject.subject_name"
                type="text"
                placeholder="Enter subject name"
                class="col-span-6 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />

            <!-- SUBJECT CODE -->

            <input
                v-model="subject.subject_code"
                type="text"
                placeholder="Subject code"
                class="col-span-5 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />

            <!-- REMOVE -->

            <button
                type="button"
                @click="editingStudent.subjects.splice(index, 1)"
                class="col-span-1 px-3 py-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition"
                title="Remove subject"
            >
                ✕
            </button>

        </div>

    </div>

    <!-- NO SUBJECTS -->

    <div
        v-else
        class="border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-6 text-center"
    >
        <p class="text-sm text-gray-500 dark:text-gray-400">
            No subjects assigned.
        </p>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
        Subject names and subject codes can be edited manually.
    </p>

</div>

                        <div
                            v-if="editingStudent.photo"
                            class="md:col-span-2 flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl"
                        >

                            <img
                                :src="`http://127.0.0.1:8000/storage/${editingStudent.photo}`"
                                :alt="editingStudent.name"
                                class="w-16 h-16 rounded-full object-cover"
                            />


                            <div>

                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Current Photo
                                </p>

                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Select a new photo to replace it.
                                </p>

                            </div>

                        </div>


                        <div
                            class="md:col-span-2"
                        >

                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                New Photo
                            </label>


                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                @change="editingStudent.newPhoto = $event.target.files[0] || null"
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-800 dark:text-white bg-white dark:bg-gray-800"
                            />

                        </div>

                    </div>


                    <div
                        class="flex flex-col sm:flex-row gap-3 mt-6"
                    >

                        <button
                            type="button"
                            @click="updateStudent"
                            class="w-full sm:w-auto bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700"
                        >
                            Update Student
                        </button>


                        <button
                            type="button"
                            @click="editingStudent = null"
                            class="w-full sm:w-auto border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-xl font-medium"
                        >
                            Cancel
                        </button>

                    </div>

                </div>

            </div>

        </Teleport>


        <!-- ================================================= -->
        <!-- LOADING -->
        <!-- ================================================= -->

        <div
            v-if="studentStore.loading"
            class="mt-8 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-10 text-center"
        >

            <div
                class="text-gray-500 dark:text-gray-400"
            >
                Loading students...
            </div>

        </div>


        <!-- ================================================= -->
        <!-- TABLE -->
        <!-- ================================================= -->

        <div
            v-else
            class="mt-6"
        >

            <BaseTable
                :headers="tableHeaders"
            >

                <tr
                    v-for="(student, index) in studentStore.students"
                    :key="student.id"
                    class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition"
                >

                    <!-- CHECKBOX -->

                    <td
                        class="px-5 py-4 text-center"
                    >

                        <input
                            type="checkbox"
                            :checked="selectedStudents.includes(student.id)"
                            @change="toggleStudent(student.id)"
                            class="w-4 h-4 accent-blue-600 cursor-pointer"
                        />

                    </td>


                    <!-- S.N. -->

                    <td
                        class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400"
                    >

                        {{
                            (currentPage - 1)
                            *
                            itemsPerPage
                            +
                            index
                            +
                            1
                        }}

                    </td>


                    <!-- ID -->

                    <td
                        class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-300"
                    >

                        #{{ student.id }}

                    </td>


                    <!-- PHOTO -->

                    <td
                        class="px-5 py-4"
                    >

                        <img
                            v-if="student.photo"
                            :src="`http://127.0.0.1:8000/storage/${student.photo}`"
                            :alt="student.name"
                            class="w-11 h-11 rounded-full object-cover border border-gray-200 dark:border-gray-700"
                        />


                        <div
                            v-else
                            class="w-11 h-11 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 text-xs"
                        >
                            No Photo
                        </div>

                    </td>


                    <!-- NAME -->

                    <td
                        class="px-5 py-4 text-sm font-semibold text-gray-800 dark:text-white"
                    >

                        {{ student.name }}

                    </td>
                        <!--CLASS-->
                    <td
    class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200"
>
    {{ student.class || 'Not assigned' }}
</td>


                    <!-- EMAIL -->

                    <td
                        class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400"
                    >

                        {{ student.email }}

                    </td>


                    <!-- PHONE -->

                    <td
                        class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400"
                    >

                        {{ student.phone }}

                    </td>


                    <!-- STATUS -->

                    <td
                        class="px-5 py-4"
                    >

                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                            :class="
                                student.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'
                            "
                        >

                            {{ student.status }}

                        </span>

                    </td>


                    <!-- ACTIONS -->

                    <td
                        class="px-5 py-4"
                    >

                        <div
                            class="flex flex-wrap gap-3"
                        >

                            <button
                                type="button"
                                @click="viewStudent(student)"
                                class="text-green-600 hover:text-green-800 font-medium text-sm"
                            >
                                View
                            </button>


                            <button
                                type="button"
                                @click="editStudent(student)"
                                class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                            >
                                Edit
                            </button>


                            <button
                                type="button"
                                @click="deleteStudent(student)"
                                class="text-red-600 hover:text-red-800 font-medium text-sm"
                            >
                                Delete
                            </button>

                        </div>

                    </td>

                </tr>


                <!-- EMPTY -->

                <tr
                    v-if="studentStore.students.length === 0"
                >

                    <td
                        colspan="10"
                        class="py-14 text-center"
                    >

                        <div
                            class="flex flex-col items-center"
                        >

                            <div
                                class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-2xl"
                            >
                                🔍
                            </div>


                            <h3
                                class="mt-4 text-lg font-semibold text-gray-700 dark:text-gray-300"
                            >
                                No students found
                            </h3>


                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Try changing your search or filter.
                            </p>

                        </div>

                    </td>

                </tr>

            </BaseTable>

        </div>


        <!-- ================================================= -->
        <!-- PAGINATION -->
        <!-- ================================================= -->

        <div
            v-if="totalPages > 1"
            class="mt-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm"
        >

            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
            >

                <!-- PAGINATION INFO -->

                <div
                    class="text-sm text-gray-500 dark:text-gray-400"
                >

                    Showing

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ showingFrom }}
                    </span>

                    -

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ showingTo }}
                    </span>

                    of

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ totalStudents }}
                    </span>

                    students

                </div>


                <!-- BUTTONS -->

                <div
                    class="flex items-center gap-2 flex-wrap"
                >

                    <!-- PREVIOUS -->

                    <button
                        type="button"
                        @click="previousPage"
                        :disabled="
                            currentPage === 1 ||
                            studentStore.loading
                        "
                        class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        ← Previous
                    </button>


                    <!-- PAGE NUMBERS -->

                    <div
                        class="flex items-center gap-1"
                    >

                        <button
                            v-for="page in totalPages"
                            :key="page"
                            type="button"
                            @click="goToPage(page)"
                            :disabled="studentStore.loading"
                            class="w-9 h-9 rounded-lg text-sm font-medium transition disabled:opacity-50"
                            :class="
                                currentPage === page
                                    ? 'bg-blue-600 text-white'
                                    : 'border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                            "
                        >

                            {{ page }}

                        </button>

                    </div>


                    <!-- NEXT -->

                    <button
                        type="button"
                        @click="nextPage"
                        :disabled="
                            currentPage === totalPages ||
                            studentStore.loading
                        "
                        class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        Next →
                    </button>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- CHART -->
        <!-- ================================================= -->

        <div
            class="mt-8 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6"
        >

            <div
                class="mb-6"
            >

                <h2
                    class="text-xl font-bold text-gray-800 dark:text-white"
                >
                    Student Statistics
                </h2>


                <p
                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                >
                    Overview of active and inactive students
                </p>

            </div>


            <div
                class="relative h-80 w-full max-w-md mx-auto"
            >

                <canvas
                    ref="statusChart"
                ></canvas>

            </div>

        </div>

    </div>

</div>

</template>

