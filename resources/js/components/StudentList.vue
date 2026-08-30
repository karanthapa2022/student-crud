
<script setup>

import BaseTable from './BaseTable.vue'
import BaseButton from './BaseButton.vue'

import {
    ref,
    computed,
    onMounted,
    onBeforeUnmount,
    watch,
    nextTick
} from 'vue'

import { useRouter } from 'vue-router'
import axios from 'axios'
import { useStudentStore } from '../stores/student'

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

import {
    updateStudentWithPhoto,
    deleteStudent as deleteStudentApi,
    bulkDeleteStudents
} from '../services/studentApi'


// =========================================================
// ROUTER + STORE
// =========================================================

const router = useRouter()

const isDark = ref(false)

const toggleDarkMode = () => {
    isDark.value = !isDark.value

    if (isDark.value) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

const studentStore = useStudentStore()

const goToTrash=()=>{
    router.push('/trash')
}


// =========================================================
// ADD STUDENT
// =========================================================

const showAddForm = ref(false)

const newStudent = ref({
    name: '',
    email: '',
    phone: '',
    status: 'active',
    photo: null
})


const handlePhotoChange = (event) => {

    newStudent.value.photo =
        event.target.files[0] || null

}


// =========================================================
// TABLE HEADERS
// =========================================================

const tableHeaders = [
    { key: 'select', label: '' },
    { key: 'sn', label: 'S.N.' },
    { key: 'id', label: 'ID' },
    { key: 'photo', label: 'Photo' },
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions' }
]


// =========================================================
// EDIT + VIEW STUDENT
// =========================================================

const editingStudent = ref(null)

const viewingStudent = ref(null)


const editStudent = (student) => {

    console.log('Edit clicked:', student)

    editingStudent.value = {
        ...student,
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
                studentId =>
                    studentId !== id
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


const filteredStudents = computed(() => {

    return studentStore.students.filter(student => {

        const search =
            searchQuery.value
                .toLowerCase()
                .trim()


        const matchesSearch =
            student.name
                ?.toLowerCase()
                .includes(search) ||

            student.email
                ?.toLowerCase()
                .includes(search) ||

            student.phone
                ?.toLowerCase()
                .includes(search)


        const matchesStatus =
            statusFilter.value === 'all' ||
            student.status === statusFilter.value


        return matchesSearch && matchesStatus

    })

})


// =========================================================
// PAGINATION
// =========================================================

const currentPage = ref(1)

const itemsPerPage = 10


const totalPages = computed(() => {

    return Math.ceil(
        filteredStudents.value.length /
        itemsPerPage
    )

})


const paginatedStudents = computed(() => {

    const start =
        (currentPage.value - 1) *
        itemsPerPage

    const end =
        start + itemsPerPage

    return filteredStudents.value.slice(
        start,
        end
    )

})


// =========================================================
// RESET PAGE WHEN SEARCH / FILTER CHANGES
// =========================================================

watch(
    [searchQuery, statusFilter],
    () => {

        currentPage.value = 1

    }
)


// =========================================================
// FIX PAGE AFTER DELETE / FILTER
// =========================================================

watch(
    totalPages,
    (pages) => {

        if (
            pages > 0 &&
            currentPage.value > pages
        ) {

            currentPage.value = pages

        }

    }
)


// =========================================================
// DASHBOARD STATISTICS
// =========================================================

const totalStudents = computed(() => {

    return studentStore.students.length

})


const activeStudents = computed(() => {

    return studentStore.students.filter(
        student =>
            student.status === 'active'
    ).length

})


const inactiveStudents = computed(() => {

    return studentStore.students.filter(
        student =>
            student.status === 'inactive'
    ).length

})


const studentsWithPhotos = computed(() => {

    return studentStore.students.filter(
        student => student.photo
    ).length

})


// =========================================================
// DASHBOARD CHART
// =========================================================

const statusChart = ref(null)

let statusChartInstance = null


const createStatusChart = async () => {

    await nextTick()

    console.log('=================================')
    console.log(' Creating Status Chart')
    console.log('Canvas:', statusChart.value)
    console.log('Students:', studentStore.students)
    console.log('=================================')


    if (!statusChart.value) {

        console.log(
            ' Chart canvas not found'
        )

        return

    }


    if (statusChartInstance) {

        statusChartInstance.destroy()

        statusChartInstance = null

    }


    const active =
        studentStore.students.filter(
            student =>
                student.status === 'active'
        ).length


    const inactive =
        studentStore.students.filter(
            student =>
                student.status === 'inactive'
        ).length


    console.log(
        ' Active Students:',
        active
    )

    console.log(
        ' Inactive Students:',
        inactive
    )


    try {

        statusChartInstance = new Chart(
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


        console.log(
            ' Chart created successfully'
        )

    } catch (error) {

        console.error(
            ' Chart creation failed:',
            error
        )

    }

}


// =========================================================
// WATCH STUDENTS
// =========================================================

watch(

    () => studentStore.students,

    async () => {

        console.log(
            ' Students changed - updating chart'
        )

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


        if (newStudent.value.photo) {

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

            email: '',

            phone: '',

            status: 'active',

            photo: null

        }


        await studentStore.fetchStudents()

        await nextTick()

        await createStatusChart()


        alert(
            'Student added successfully!'
        )

    } catch (error) {

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

    if (!editingStudent.value) {

        return

    }


    try {

        const formData = new FormData()


        formData.append(
            'name',
            editingStudent.value.name
        )


        formData.append(
            'email',
            editingStudent.value.email
        )


        formData.append(
            'phone',
            editingStudent.value.phone
        )


        formData.append(
            'status',
            editingStudent.value.status
        )


        if (
            editingStudent.value.newPhoto
        ) {

            formData.append(
                'photo',
                editingStudent.value.newPhoto
            )

        }


        const response =
            await updateStudentWithPhoto(
                editingStudent.value.id,
                formData
            )


        const updatedStudent =
            response.data.student ||
            response.data


        const index =
            studentStore.students.findIndex(
                student =>
                    student.id ===
                    editingStudent.value.id
            )


        if (index !== -1) {

            studentStore.students[index] =
                updatedStudent

        }


        editingStudent.value = null


        await studentStore.fetchStudents()

        await nextTick()

        await createStatusChart()


        alert(
            'Student updated successfully!'
        )

    } catch (error) {

        console.error(
            'Error updating student:',
            error
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

    const confirmed = confirm(
        `Are you sure you want to delete ${student.name}?`
    )


    if (!confirmed) {

        return

    }


    try {

        await deleteStudentApi(
            student.id
        )


        studentStore.students =
            studentStore.students.filter(
                s =>
                    s.id !== student.id
            )


        selectedStudents.value =
            selectedStudents.value.filter(
                id =>
                    id !== student.id
            )


        await nextTick()

        await createStatusChart()


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


    const confirmed = confirm(
        'Are you sure you want to delete the selected students?'
    )


    if (!confirmed) {

        return

    }


    try {

        await bulkDeleteStudents(
            selectedStudents.value
        )


        studentStore.students =
            studentStore.students.filter(
                student =>
                    !selectedStudents.value.includes(
                        student.id
                    )
            )


        selectedStudents.value = []


        await nextTick()

        await createStatusChart()


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

    console.log(
        ' StudentList mounted'
    )

    try {

        await studentStore.fetchStudents()


        console.log(
            ' Students fetched:',
            studentStore.students
        )


        await nextTick()


        await createStatusChart()

    } catch (error) {

        console.error(
            ' StudentList initialization error:',
            error
        )

    }

})


// =========================================================
// BEFORE UNMOUNT
// =========================================================

onBeforeUnmount(() => {

    console.log(
        ' Destroying chart'
    )


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

    <div class="w-full max-w-7xl mx-auto">

        <!-- HEADER -->

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


            <div class="flex flex-col sm:flex-row gap-3">

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


        <!-- ERROR -->

        <div
            v-if="studentStore.error"
            class="mt-6 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl"
        >

            {{ studentStore.error }}

        </div>


        <!-- STATISTICS -->

        <div
            class="mt-8 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5"
        >

            <!-- TOTAL -->

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm hover:shadow-md dark:shadow-gray-950/40 transition"
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
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition"
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
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition"
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
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition"
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
                    Profiles with photos
                </p>

            </div>

        </div>


        <!-- ACTION BAR -->

        <div
            class="mt-8 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm transition-colors"
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


                    <!-- TRASH -->

                    <button
                        type="button"
                        @click="goToTrash"
                        class="px-4 py-2.5 rounded-xl bg-gray-800 dark:bg-gray-700 text-white font-medium hover:bg-gray-900 dark:hover:bg-gray-600 transition"
                    >
                         Trash
                    </button>


                    <!-- DARK MODE -->

                    <button
                        type="button"
                        @click="toggleDarkMode"
                        class="px-4 py-2.5 rounded-xl bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 font-medium hover:bg-gray-900 dark:hover:bg-white transition"
                    >
                        {{ isDark ? '☀️ Light' : '🌙 Dark' }}
                    </button>

                </div>

            </div>


            <!-- SEARCH + FILTER -->

            <div
                class="mt-5 flex flex-col lg:flex-row gap-3"
            >

                <div class="relative flex-1">

                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name, email or phone..."
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    />

                </div>


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


            <!-- COUNT -->

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
                        {{ filteredStudents.length }}
                    </span>

                    of

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ studentStore.students.length }}
                    </span>

                    students

                </p>


                <p
                    v-if="selectedStudents.length > 0"
                    class="text-sm font-medium text-blue-600 dark:text-blue-400"
                >
                    {{ selectedStudents.length }} selected
                </p>

            </div>

        </div>


        <!-- ADD STUDENT MODAL -->

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
                            class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white text-xl transition"
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
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />


                        <input
                            v-model="newStudent.email"
                            type="email"
                            placeholder="Email Address"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />


                        <input
                            v-model="newStudent.phone"
                            type="text"
                            placeholder="Phone Number"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />


                        <select
                            v-model="newStudent.status"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>


                        <div class="md:col-span-2">

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
                            class="w-full sm:w-auto border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            Cancel
                        </button>

                    </div>

                </div>

            </div>

        </Teleport>


        <!-- VIEW STUDENT MODAL -->

        <Teleport to="body">

            <div
                v-if="viewingStudent"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 px-4"
            >

                <div
                    class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6"
                >

                    <div
                        class="flex items-center justify-between mb-6"
                    >

                        <div>

                            <h2
                                class="text-xl font-bold text-gray-800 dark:text-white"
                            >
                                Student Details
                            </h2>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Student information
                            </p>

                        </div>


                        <button
                            type="button"
                            @click="viewingStudent = null"
                            class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white text-xl"
                        >
                            ×
                        </button>

                    </div>


                    <!-- PHOTO -->

                    <div class="flex justify-center mb-6">

                        <img
                            v-if="viewingStudent.photo"
                            :src="`http://127.0.0.1:8000/storage/${viewingStudent.photo}`"
                            :alt="viewingStudent.name"
                            class="w-28 h-28 rounded-full object-cover border-4 border-gray-100 dark:border-gray-700 shadow-sm"
                        />


                        <div
                            v-else
                            class="w-28 h-28 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm"
                        >
                            No Photo
                        </div>

                    </div>


                    <div class="space-y-4">

                        <div
                            class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-3 gap-4"
                        >

                            <span
                                class="font-medium text-gray-500 dark:text-gray-400"
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
                            class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-3 gap-4"
                        >

                            <span
                                class="font-medium text-gray-500 dark:text-gray-400"
                            >
                                Name
                            </span>

                            <span
                                class="font-semibold text-gray-800 dark:text-white text-right"
                            >
                                {{ viewingStudent.name }}
                            </span>

                        </div>


                        <div
                            class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-3 gap-4"
                        >

                            <span
                                class="font-medium text-gray-500 dark:text-gray-400"
                            >
                                Email
                            </span>

                            <span
                                class="text-gray-800 dark:text-gray-200 text-right break-all"
                            >
                                {{ viewingStudent.email }}
                            </span>

                        </div>


                        <div
                            class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-3 gap-4"
                        >

                            <span
                                class="font-medium text-gray-500 dark:text-gray-400"
                            >
                                Phone
                            </span>

                            <span
                                class="text-gray-800 dark:text-gray-200"
                            >
                                {{ viewingStudent.phone }}
                            </span>

                        </div>


                        <div class="flex justify-between items-center">

                            <span
                                class="font-medium text-gray-500 dark:text-gray-400"
                            >
                                Status
                            </span>


                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                                :class="
                                    viewingStudent.status === 'active'
                                        ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400'
                                        : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400'
                                "
                            >
                                {{ viewingStudent.status }}
                            </span>

                        </div>

                    </div>


                    <div class="mt-6">

                        <button
                            type="button"
                            @click="viewingStudent = null"
                            class="w-full bg-gray-800 dark:bg-gray-700 text-white px-5 py-3 rounded-xl font-medium hover:bg-gray-900 dark:hover:bg-gray-600 transition"
                        >
                            Close
                        </button>

                    </div>

                </div>

            </div>

        </Teleport>


        <!-- EDIT STUDENT MODAL -->

        <Teleport to="body">

            <div
                v-if="editingStudent"
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
                                Edit Student
                            </h2>

                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                Update student information
                            </p>

                        </div>


                        <button
                            type="button"
                            @click="editingStudent = null"
                            class="w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white text-xl"
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
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />


                        <input
                            v-model="editingStudent.email"
                            type="email"
                            placeholder="Email Address"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />


                        <input
                            v-model="editingStudent.phone"
                            type="text"
                            placeholder="Phone Number"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />


                        <select
                            v-model="editingStudent.status"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>


                        <!-- CURRENT PHOTO -->

                        <div
                            v-if="editingStudent.photo"
                            class="md:col-span-2 flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
                        >

                            <img
                                :src="`http://127.0.0.1:8000/storage/${editingStudent.photo}`"
                                :alt="editingStudent.name"
                                class="w-16 h-16 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow"
                            />


                            <div>

                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Current Photo
                                </p>

                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                >
                                    Select a new photo below to replace it.
                                </p>

                            </div>

                        </div>


                        <!-- NEW PHOTO -->

                        <div class="md:col-span-2">

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
                            class="w-full sm:w-auto bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700 transition"
                        >
                            Update Student
                        </button>


                        <button
                            type="button"
                            @click="editingStudent = null"
                            class="w-full sm:w-auto border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            Cancel
                        </button>

                    </div>

                </div>

            </div>

        </Teleport>


        <!-- LOADING -->

        <div
            v-if="studentStore.loading"
            class="mt-8 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-10 text-center shadow-sm"
        >

            <div
                class="text-gray-500 dark:text-gray-400"
            >
                Loading students...
            </div>

        </div>


        <!-- TABLE -->

        <div
            v-else
            class="mt-6"
        >

            <BaseTable
                :headers="tableHeaders"
            >

                <tr
                    v-for="(student, index) in paginatedStudents"
                    :key="student.id"
                    class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition"
                >

                    <!-- CHECKBOX -->

                    <td class="px-5 py-4 text-center">

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
                            (currentPage - 1) *
                            itemsPerPage +
                            index +
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

                    <td class="px-5 py-4">

                        <img
                            v-if="student.photo"
                            :src="`http://127.0.0.1:8000/storage/${student.photo}`"
                            :alt="student.name"
                            class="w-11 h-11 rounded-full object-cover border border-gray-200 dark:border-gray-700"
                        />


                        <div
                            v-else
                            class="w-11 h-11 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500 text-xs"
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

                    <td class="px-5 py-4">

                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                            :class="
                                student.status === 'active'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400'
                                    : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400'
                            "
                        >
                            {{ student.status }}
                        </span>

                    </td>


                    <!-- ACTIONS -->

                    <td class="px-5 py-4">

                        <div class="flex flex-wrap gap-3">

                            <button
                                type="button"
                                @click="viewStudent(student)"
                                class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium text-sm"
                            >
                                View
                            </button>


                            <button
                                type="button"
                                @click="editStudent(student)"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium text-sm"
                            >
                                Edit
                            </button>


                            <button
                                type="button"
                                @click="deleteStudent(student)"
                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium text-sm"
                            >
                                Delete
                            </button>

                        </div>

                    </td>

                </tr>


                <!-- EMPTY -->

                <tr
                    v-if="filteredStudents.length === 0"
                >

                    <td
                        colspan="9"
                        class="py-14 text-center"
                    >

                        <div class="flex flex-col items-center">

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


        <!-- PAGINATION -->

        <div
            v-if="totalPages > 1"
            class="mt-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm"
        >

            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
            >

                <div
                    class="text-sm text-gray-500 dark:text-gray-400"
                >

                    Showing

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{
                            (currentPage - 1) *
                            itemsPerPage +
                            1
                        }}
                    </span>

                    -

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{
                            Math.min(
                                currentPage * itemsPerPage,
                                filteredStudents.length
                            )
                        }}
                    </span>

                    of

                    <span
                        class="font-semibold text-gray-800 dark:text-white"
                    >
                        {{ filteredStudents.length }}
                    </span>

                    students

                </div>


                <div
                    class="flex items-center gap-2 flex-wrap"
                >

                    <!-- PREVIOUS -->

                    <button
                        type="button"
                        @click="currentPage--"
                        :disabled="currentPage === 1"
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
                            @click="currentPage = page"
                            class="w-9 h-9 rounded-lg text-sm font-medium transition"
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
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        Next →
                    </button>

                </div>

            </div>

        </div>


        <!-- DASHBOARD CHART -->

        <div
            class="mt-8 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 transition-colors"
        >

            <!-- HEADER -->

            <div class="mb-6">

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


            <!-- CHART -->

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

