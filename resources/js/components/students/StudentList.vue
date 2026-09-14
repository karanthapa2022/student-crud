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
import { getTeachers } from '../../services/teachers/teacherApi'
import { getStudent } from '../../services/students/studentApi'


import {
    updateStudentWithPhoto,
    deleteStudent as deleteStudentApi,
    bulkDeleteStudents,
    bulkUpdateStudents
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
const teachers = ref([])

const classTeachers = computed(() =>
    teachers.value.filter(teacher => Number(teacher.class) === Number(newStudent.value.class))
)


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
    subjects:[],
    teacher_id: null

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
const actionMessage = ref('')
const actionError = ref('')
const newPhotoPreview = ref('')

const editingSubjectsValid = computed(() =>
    Boolean(editingStudent.value && editingStudent.value.subjects?.length >= 3)
)

watch(
    () => newStudent.value.parent_id,
    (parentId) => {
        const parent = parentStore.parents.find(item => Number(item.id) === Number(parentId))
        newStudent.value.address_id = parent?.address_id || null
    }
)

watch(
    () => editingStudent.value?.parent_id,
    (parentId) => {
        if (!editingStudent.value) return
        const parent = parentStore.parents.find(item => Number(item.id) === Number(parentId))
        editingStudent.value.address_id = parent?.address_id || null
    }
)


const editStudent = (student) => {

    editingStudent.value = {

        ...student,

        teacher_id: student.teachers?.[0]?.id || null,
        subjects: (student.subjects || []).map(subject => subject.id),

        newPhoto: null

    }

    newPhotoPreview.value = ''

}

const handleNewPhoto = (event) => {
    const file = event.target.files?.[0] || null
    editingStudent.value.newPhoto = file
    newPhotoPreview.value = file ? URL.createObjectURL(file) : ''
}


const viewStudent = async (student) => {
    actionError.value = ''
    try {
        const response = await getStudent(student.id)
        viewingStudent.value = response.data
    } catch (error) {
        actionError.value = error.response?.data?.message || 'Unable to load student details.'
    }
}


const editFromView = () => {
    editStudent(viewingStudent.value)
    viewingStudent.value = null
}


// =========================================================
// SELECTED STUDENTS
// =========================================================

const selectedStudents = ref([])
const showBulkEdit = ref(false)
const bulkEdit = ref({ status: '', parent_id: '' })

const saveBulkEdit = async () => {
    if (!selectedStudents.value.length) return
    if (!bulkEdit.value.status && bulkEdit.value.parent_id === '') {
        alert('Select a status or parent change before saving.')
        return
    }

    const payload = { ids: selectedStudents.value }
    if (bulkEdit.value.status) payload.status = bulkEdit.value.status
    if (bulkEdit.value.parent_id !== '') {
        payload.parent_id = bulkEdit.value.parent_id === '__clear__'
            ? null
            : bulkEdit.value.parent_id
    }

    try {
        await bulkUpdateStudents(payload)
        showBulkEdit.value = false
        bulkEdit.value = { status: '', parent_id: '' }
        selectedStudents.value = []
        await studentStore.fetchStudents(currentPage.value, searchQuery.value, statusFilter.value)
        await studentStore.fetchStatistics()
    } catch (error) {
        alert(error.response?.data?.message || 'Unable to update selected students.')
    }
}


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

                                    '#2F6F4E',

                                    '#B5563C'

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

    actionError.value = ''

        if (newStudent.value.subjects.length < 3) {
            alert('Assign at least 3 registered subjects.')
            return
        }

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

        if (newStudent.value.teacher_id) {
            formData.append('teacher_ids[]', newStudent.value.teacher_id)
        }

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


        const createdStudent = await studentStore.addStudent(
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
            subjects:[],
            teacher_id: null

        }


        // Keep current search/filter
        await studentStore.fetchStudents(

            currentPage.value,

            searchQuery.value,

            statusFilter.value

        )


        await studentStore.fetchStatistics()
        const createdResponse = await getStudent(createdStudent.id)
        viewingStudent.value = createdResponse.data
        actionMessage.value = 'Student created successfully.'


    } catch (error) {
        console.log('FULL SERVER ERROR:', error.response?.data)
console.log('VALIDATION ERRORS:', error.response?.data?.errors)

        console.error(
            'Error adding student:',
            error
        )


        actionError.value = error.response?.data?.message || 'Error adding student.'

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

        actionError.value = ''

        if (!editingSubjectsValid.value) {
            return
        }

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

        formData.append(
            'parent_id',
            editingStudent.value.parent_id || ''
        )

        if (editingStudent.value.teacher_id) {
            formData.append('teacher_ids[]', editingStudent.value.teacher_id)
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

    editingStudent.value.subjects.forEach(subjectId => {
        formData.append('subjects[]', subjectId)
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


        const updatedResponse = await updateStudentWithPhoto(

        
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


        const refreshedStudent = await getStudent(updatedResponse.data.student.id)
        viewingStudent.value = refreshedStudent.data
        actionMessage.value = 'Student updated successfully.'


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

        actionError.value = error.response?.data?.message || 'Error updating student.'

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
        await parentStore.fetchParents(1, '', 'all', 100)
        await addressStore.fetchAddresses()
        await subjectStore.fetchSubjects(1, '', 'all', 100)

    const teacherResponse = await getTeachers(1, '')
    teachers.value = teacherResponse.data.data || []


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
    class="min-h-screen bg-[#EFF1EA] dark:bg-[#141F19] p-4 text-[#1C2B24] dark:text-[#E8EBE4] transition-colors duration-300 sm:p-6 lg:p-8"
>
    <div
        class="w-full max-w-7xl mx-auto"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div
            class="flex flex-col gap-5 border-b border-[#D8DDD3] pb-6 dark:border-[#2E3B33] lg:flex-row lg:items-center lg:justify-between"
        >

            <div>

                <h1
                    class="text-2xl text-[#1C2B24] dark:text-[#E8EBE4] sm:text-3xl"
                >
                    Student register
                </h1>

                <p
                    class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    All students enrolled, in one place
                </p>

            </div>


            <div
                class="flex flex-col gap-2 sm:flex-row"
            >

            <!-- ADMIN DASHBOARD -->
    <button
        type="button"
        @click="router.push('/dashboard')"
        class="w-full rounded-md border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-medium text-[#1C2B24] transition hover:bg-[#EFF1EA] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#E8EBE4] dark:hover:bg-[#243329] sm:w-auto"
    >
        Dashboard
    </button>

                <button
                    type="button"
                    @click="router.push('/profile')"
                    class="w-full rounded-md border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-medium text-[#1C2B24] transition hover:bg-[#EFF1EA] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#E8EBE4] dark:hover:bg-[#243329] sm:w-auto"
                >
                    Profile
                </button>

                <button
                        type="button"
                        @click="toggleDarkMode"
                        class="rounded-md border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-medium text-[#1C2B24] transition hover:bg-[#EFF1EA] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#E8EBE4] dark:hover:bg-[#243329]"
                    >
                        {{ isDark ? 'Light mode' : 'Dark mode' }}
                    </button>

                <button
                    type="button"
                    @click="logout"
                    class="w-full rounded-md border border-[#B5563C]/35 bg-transparent px-4 py-2.5 text-sm font-medium text-[#B5563C] transition hover:bg-[#B5563C]/5 sm:w-auto"
                >
                    Sign out
                </button>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- ERROR -->
        <!-- ================================================= -->

        <div
            v-if="studentStore.error"
            class="mt-6 rounded-md border border-[#B5563C]/30 bg-[#B5563C]/5 px-4 py-3 text-sm text-[#8A3E2A]"
        >
            {{ studentStore.error }}
        </div>

        <div v-if="actionMessage" class="mt-6 rounded-md border border-[#2F6F4E]/30 bg-[#2F6F4E]/5 px-4 py-3 text-sm text-[#2F6F4E]">
            {{ actionMessage }}
        </div>

        <div v-if="actionError" class="mt-6 rounded-md border border-[#B5563C]/30 bg-[#B5563C]/5 px-4 py-3 text-sm text-[#8A3E2A]">
            {{ actionError }}
        </div>


        <!-- ================================================= -->
        <!-- STATISTICS -->
        <!-- ================================================= -->

        <div
            class="mt-8 flex flex-wrap overflow-hidden rounded-md border border-[#D8DDD3] bg-white dark:border-[#2E3B33] dark:bg-[#1E2B24]"
        >

            <!-- TOTAL -->

            <div
                class="min-w-[10rem] flex-1 border-b border-r border-[#D8DDD3] px-6 py-5 dark:border-[#2E3B33] last:border-r-0"
            >

                <p
                    class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    Total students
                </p>


                <h3
                    class="mt-2 text-3xl text-[#1C2B24] dark:text-[#E8EBE4]"
                >
                    {{ totalStudents }}
                </h3>

            </div>


            <!-- ACTIVE -->

            <div
                class="min-w-[10rem] flex-1 border-b border-r border-[#D8DDD3] px-6 py-5 dark:border-[#2E3B33] last:border-r-0"
            >

                <p
                    class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    Active
                </p>


                <h3
                    class="mt-2 text-3xl text-[#2F6F4E]"
                >
                    {{ activeStudents }}
                </h3>

            </div>


            <!-- INACTIVE -->

            <div
                class="min-w-[10rem] flex-1 border-b border-r border-[#D8DDD3] px-6 py-5 dark:border-[#2E3B33] last:border-r-0"
            >

                <p
                    class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    Inactive
                </p>


                <h3
                    class="mt-2 text-3xl text-[#B5563C]"
                >
                    {{ inactiveStudents }}
                </h3>

            </div>


            <!-- PHOTOS -->

            <div
                class="min-w-[10rem] flex-1 border-b border-r border-[#D8DDD3] px-6 py-5 dark:border-[#2E3B33] last:border-r-0"
            >

                <p
                    class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    With a photo
                </p>


                <h3
                    class="mt-2 text-3xl text-[#1C2B24] dark:text-[#E8EBE4]"
                >
                    {{ studentsWithPhotos }}
                </h3>


                <p
                    class="mt-1 text-xs text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    On this page
                </p>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- ACTION BAR -->
        <!-- ================================================= -->

        <div
            class="mt-8 rounded-md border border-[#D8DDD3] bg-white p-5 dark:border-[#2E3B33] dark:bg-[#1E2B24]"
        >

            <div
                class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between"
            >

                <div>

                    <h2
                        class="text-lg text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        Students
                    </h2>


                    <p
                        class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                    >
                        Search, filter and manage your students
                    </p>

                </div>


                <div
                    class="flex flex-col gap-2 sm:flex-row sm:flex-wrap"
                >

                    <BaseButton
                        variant="success"
                        @click="showAddForm = true"
                        class="w-full sm:w-auto"
                    >
                        Add student
                    </BaseButton>


                    <BaseButton
                        v-if="selectedStudents.length > 0"
                        variant="danger"
                        @click="bulkDelete"
                        class="w-full sm:w-auto"
                    >
                        Delete selected
                    </BaseButton>

                    <BaseButton
                        v-if="selectedStudents.length > 0"
                        variant="secondary"
                        @click="showBulkEdit = true"
                        class="w-full sm:w-auto"
                    >
                        Bulk edit
                    </BaseButton>


                    <button
                        type="button"
                        @click="goToTrash"
                        class="rounded-md border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-medium text-[#1C2B24] transition hover:bg-[#EFF1EA] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#E8EBE4] dark:hover:bg-[#243329]"
                    >
                        Trash
                    </button>

                    <button
    type="button"
    @click="router.push('/marksheets')"
    class="rounded-md border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-medium text-[#1C2B24] transition hover:bg-[#EFF1EA] dark:border-[#2E3B33] dark:bg-[#1E2B24] dark:text-[#E8EBE4] dark:hover:bg-[#243329]"
>
    Marksheets
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
                    placeholder="Search by name, email or phone"
                    class="w-full flex-1 rounded-md border border-[#D8DDD3] bg-[#F7F8F3] px-4 py-3 text-[#1C2B24] placeholder-[#5B6B62]/60 transition focus:border-[#2F6F4E]/50 focus:outline-none focus:ring-1 focus:ring-[#2F6F4E]/50 dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]"
                />


                <select
                    v-model="statusFilter"
                    class="w-full rounded-md border border-[#D8DDD3] bg-[#F7F8F3] px-4 py-3 text-[#1C2B24] transition focus:border-[#2F6F4E]/50 focus:outline-none focus:ring-1 focus:ring-[#2F6F4E]/50 dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4] lg:w-52"
                >

                    <option value="all">
                        All students
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
                class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >

                <p
                    class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >

                    Showing

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ showingFrom }}
                    </span>

                    &ndash;

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ showingTo }}
                    </span>

                    of

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ totalStudents }}
                    </span>

                    students

                </p>


                <p
                    v-if="selectedStudents.length > 0"
                    class="text-sm font-medium text-[#2F6F4E]"
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
            <div v-if="showBulkEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C2B24]/50 px-4">
                <div class="w-full max-w-lg rounded-md border border-[#D8DDD3] bg-white p-6 dark:border-[#2E3B33] dark:bg-[#1E2B24]">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl text-[#1C2B24] dark:text-[#E8EBE4]">Bulk edit students</h2>
                            <p class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]">Update {{ selectedStudents.length }} selected student(s).</p>
                        </div>
                        <button type="button" @click="showBulkEdit = false" class="text-2xl text-[#5B6B62] hover:text-[#1C2B24] dark:text-[#9AA79E] dark:hover:text-[#E8EBE4]">×</button>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-[#1C2B24] dark:text-[#E8EBE4]">Status (optional)</label>
                            <select v-model="bulkEdit.status" class="w-full rounded-md border border-[#D8DDD3] px-4 py-3 dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]">
                                <option value="">Leave status unchanged</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-[#1C2B24] dark:text-[#E8EBE4]">Parent (optional)</label>
                            <select v-model="bulkEdit.parent_id" class="w-full rounded-md border border-[#D8DDD3] px-4 py-3 dark:border-[#2E3B33] dark:bg-[#16211B] dark:text-[#E8EBE4]">
                                <option value="">Leave parent unchanged</option>
                                <option value="__clear__">No parent assigned</option>
                                <option v-for="parent in parentStore.parents" :key="parent.id" :value="parent.id">{{ parent.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button" @click="saveBulkEdit" class="rounded-md bg-[#2F6F4E] px-5 py-3 font-medium text-white hover:bg-[#24573E]">Save changes</button>
                        <button type="button" @click="showBulkEdit = false" class="rounded-md border border-[#D8DDD3] px-5 py-3 font-medium text-[#1C2B24] dark:border-[#2E3B33] dark:text-[#E8EBE4]">Cancel</button>
                    </div>
                </div>
            </div>
        </Teleport>

    
<Teleport to="body">

    <div
        v-if="showAddForm"
        class="fixed inset-0 z-50 overflow-y-auto bg-ink/60 px-4 py-8 backdrop-blur-[2px]"
    >

        <div
            class="mx-auto flex min-h-full max-w-3xl items-center justify-center"
        >

            <div
                class="w-full border border-hairline bg-paper text-ink shadow-xl"
            >

                <!-- ================================================= -->
                <!-- MODAL HEADER -->
                <!-- ================================================= -->

                <div
                    class="border-b border-hairline px-6 py-5 sm:px-8"
                >

                    <div
                        class="flex items-start justify-between gap-6"
                    >

                        <div>

                            <div
                                class="mb-2 flex items-center gap-3"
                            >
                                

                            </div>

                            <h2
                                class="text-2xl font-semibold tracking-tight text-ink sm:text-3xl"
                            >
                                Add New Student
                            </h2>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                Enter the student's information below.
                            </p>

                        </div>


                        <!-- CLOSE -->

                        <button
                            type="button"
                            @click="showAddForm = false"
                            class="flex h-9 w-9 shrink-0 items-center justify-center border border-hairline text-xl leading-none text-ink-soft transition hover:border-ink hover:bg-surface hover:text-ink"
                            aria-label="Close"
                        >
                            ×
                        </button>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- FORM -->
                <!-- ================================================= -->

                <div
                    class="px-6 py-6 sm:px-8"
                >

                    <!-- ================================================= -->
                    <!-- BASIC INFORMATION -->
                    <!-- ================================================= -->

                    <div class="mb-7">

                        <div
                            class="mb-4 flex items-center gap-3"
                        >

                            <h3
                                class=" font-bold"
                            >
                                Basic Information
                            </h3>

                            <div
                                class="h-px flex-1 bg-hairline"
                            ></div>

                        </div>


                        <div
                            class="grid grid-cols-1 gap-5 md:grid-cols-2"
                        >

                            <!-- NAME -->

                            <div class="md:col-span-2">

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Full Name
                                </label>

                                <input
                                    v-model="newStudent.name"
                                    type="text"
                                    placeholder="Enter student's full name"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                                />

                            </div>


                            <!-- CLASS -->

                            <div>

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Class
                                </label>

                                <select v-model="newStudent.class" class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none focus:border-forest focus:ring-1 focus:ring-forest">
                                    <option value="" disabled>Select class</option>
                                    <option v-for="classNumber in 10" :key="classNumber" :value="classNumber">Class {{ classNumber }}</option>
                                </select>

                            </div>


                            <!-- SYMBOL -->

                            <div>

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Symbol No.
                                </label>

                                <input
                                    v-model="newStudent.symbol_no"
                                    type="text"
                                    placeholder="Enter symbol number"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                                />

                            </div>


                            <!-- DATE OF BIRTH -->

                            <div>

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Date of Birth
                                </label>

                                <input
                                    v-model="newStudent.date_of_birth"
                                    type="date"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition focus:border-forest focus:ring-1 focus:ring-forest"
                                />

                            </div>


                            <!-- STATUS -->

                            <div>

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Status
                                </label>

                                <select
                                    v-model="newStudent.status"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition focus:border-forest focus:ring-1 focus:ring-forest"
                                >

                                    <option value="active">
                                        Active
                                    </option>

                                    <option value="inactive">
                                        Inactive
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- CONTACT -->
                    <!-- ================================================= -->

                    <div class="mb-7">

                        <div
                            class="mb-4 flex items-center gap-3"
                        >

                            <h3
                                class=" font-bold"
                            >
                                Contact Details
                            </h3>

                            <div
                                class="h-px flex-1 bg-hairline"
                            ></div>

                        </div>


                        <div
                            class="grid grid-cols-1 gap-5 md:grid-cols-2"
                        >

                            <!-- EMAIL -->

                            <div>

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Email Address
                                </label>

                                <input
                                    v-model="newStudent.email"
                                    type="email"
                                    placeholder="student@gmail.com"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                                />

                            </div>


                            <!-- PHONE -->

                            <div>

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Phone Number
                                </label>

                                <input
                                    v-model="newStudent.phone"
                                    type="text"
                                    placeholder="98XXXXXXXX"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                                />

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- FAMILY & ADDRESS -->
                    <!-- ================================================= -->

                    <div class="mb-7">

                        <div
                            class="mb-4 flex items-center gap-3"
                        >

                            <h3
                                class="font-bold"
                            >
                                Family & Address
                            </h3>

                            <div
                                class="h-px flex-1 bg-hairline"
                            ></div>

                        </div>


                        <div
                            class="grid grid-cols-1 gap-5"
                        >

                            <!-- PARENT -->

                            <div>

                                <label
                                    class="mb-2 block font-bold "
                                >
                                    Parent
                                    <span class="font-normal normal-case tracking-normal">
                                        (optional)
                                    </span>
                                </label>

                                <select
                                    v-model="newStudent.parent_id"
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition focus:border-forest focus:ring-1 focus:ring-forest"
                                >

                                    <option :value="null">
                                        No parent assigned
                                    </option>

                                    <option
                                        v-for="parent in parentStore.parents"
                                        :key="parent.id"
                                        :value="parent.id"
                                    >
                                        {{ parent.name }}
                                    </option>

                                </select>

                            </div>


                            <!-- ADDRESS -->

                            <div>

                                <label
                                    class="mb-2 block font-bold"
                                >
                                    Address
                                </label>

                                <select
                                    v-model="newStudent.address_id"
                                    disabled
                                    class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none transition focus:border-forest focus:ring-1 focus:ring-forest"
                                >

                                    <option :value="null">
                                        Select Address
                                    </option>

                                    <option
                                        v-for="address in addressStore.addresses"
                                        :key="address.id"
                                        :value="address.id"
                                    >
                                        {{ address.province }}
                                        -
                                        {{ address.district }}
                                        -
                                        {{ address.municipality }}
                                        -
                                        Ward {{ address.ward }}
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- ACADEMIC ASSIGNMENTS -->
                    <!-- ================================================= -->

                    <div class="mb-7 border-t border-hairline pt-6">
                        <h3 class="mb-1 font-bold">Academic assignments</h3>
                        <p class="mb-4 text-xs text-ink-soft">Choose the teachers and subjects connected to this student.</p>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <label class="block text-sm font-medium">
                                Teachers
                                <select v-model="newStudent.teacher_id" class="mt-2 h-12 w-full border border-hairline bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-forest">
                                    <option :value="null">Select class teacher</option>
                                    <option v-for="teacher in classTeachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }} (Class {{ teacher.class }})</option>
                                </select>
                            </label>

                            <label class="block text-sm font-medium">
                                Subjects
                                <select v-model="newStudent.subjects" multiple class="mt-2 h-32 w-full border border-hairline bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-forest">
                                    <option v-for="subject in subjectStore.subjects.filter(item => item.class == null || Number(item.class) === Number(newStudent.class))" :key="subject.id" :value="subject.id">
                                        {{ subject.name }}{{ subject.code ? ` (${subject.code})` : '' }}{{ subject.class == null ? ' (all classes)' : '' }}
                                    </option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- PHOTO -->
                    <!-- ================================================= -->

                    <div>

                        <div
                            class="mb-4 flex items-center gap-3"
                        >

                            <h3
                                class=" font-bold"
                            >
                                Student Photo
                            </h3>

                            <div
                                class="h-px flex-1 bg-hairline"
                            ></div>

                        </div>


                        <label
                            class="flex cursor-pointer items-center justify-between gap-4 border border-dashed border-hairline bg-surface px-4 py-5 transition hover:border-forest"
                        >

                            <div>

                                <p
                                    class="text-sm font-medium text-ink"
                                >
                                    Upload student photo
                                </p>

                                <p
                                    class="mt-1 text-xs text-ink-soft"
                                >
                                    JPG, PNG or WEBP
                                </p>

                            </div>


                            <span
                                class="shrink-0 border border-hairline px-4 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-forest"
                            >
                                Choose file
                            </span>


                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                @change="handlePhotoChange"
                                class="hidden"
                            />

                        </label>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- FOOTER -->
                <!-- ================================================= -->

                <div
                    class="flex flex-col-reverse gap-3 border-t border-hairline bg-surface px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8"
                >

                    <p
                        class="text-xs text-ink-soft"
                    >
                        All required information should be completed.
                    </p>


                    <div
                        class="flex flex-col gap-3 sm:flex-row"
                    >

                        <!-- CANCEL -->

                        <button
                            type="button"
                            @click="showAddForm = false"
                            class="border border-hairline px-6 py-3 text-xs font-bold uppercase tracking-[0.14em] text-ink transition hover:border-ink hover:bg-paper"
                        >
                            Cancel
                        </button>


                        <!-- ADD -->

                        <button
                            type="button"
                            @click="addStudent"
                            class="bg-forest px-7 py-3 text-xs font-bold uppercase tracking-[0.14em] text-white transition hover:opacity-90"
                        >
                            Add Student
                        </button>

                    </div>

                </div>

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


    <div v-if="viewingStudent.subjects?.length" class="space-y-2">

        <div
            v-for="subject in viewingStudent.subjects"
            :key="subject.id"
            class="flex items-center justify-between gap-4 border border-gray-200 px-4 py-3 dark:border-gray-700"
>
            <span class="font-medium text-gray-800 dark:text-white">{{ subject.name }}</span>
            <span class="text-right text-gray-600 dark:text-gray-300">{{ subject.teacher || 'No teacher assigned' }}</span>
</div>

    </div>

</div>

<div class="mt-4 flex flex-col gap-3 sm:flex-row">
    <button
        type="button"
        @click="router.push({ path: '/marksheets', query: { student_id: viewingStudent.id } })"
        class="flex-1 rounded-xl bg-gray-800 px-5 py-3 font-medium text-white"
    >
        View marksheets
    </button>
    <button
        type="button"
        @click="editFromView"
        class="flex-1 rounded-xl border border-forest px-5 py-3 font-medium text-forest"
    >
        Edit student
    </button>
    <button
        type="button"
        @click="viewingStudent = null"
        class="flex-1 rounded-xl border border-gray-300 px-5 py-3 font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200"
    >
        Close
    </button>
</div>




                        <div
                            class="hidden"
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


                </div>

            </div>

        </Teleport>


        <!-- ================================================= -->
        <!-- EDIT MODAL -->
        <!-- ================================================= -->

        
<Teleport to="body">

    <div
        v-if="editingStudent"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 px-4 py-8"
    >

        <div
            class="w-full max-w-3xl border border-hairline bg-paper text-ink shadow-xl"
        >

            <!-- ================================================= -->
            <!-- HEADER -->
            <!-- ================================================= -->

            <div
                class="flex items-center justify-between border-b border-hairline px-6 py-5"
            >

                <div>

                    <h2
                        class="text-2xl font-semibold text-ink"
                    >
                        Edit Student
                    </h2>

                    <p
                        class="mt-1 text-sm text-ink-soft"
                    >
                        Update the student's information below
                    </p>

                </div>


                <!-- CLOSE -->

                <button
                    type="button"
                    @click="editingStudent = null"
                    class="flex h-9 w-9 items-center justify-center border border-hairline text-xl text-ink-soft transition hover:bg-surface hover:text-ink"
                    aria-label="Close"
                >
                    ×
                </button>

            </div>


            <!-- ================================================= -->
            <!-- FORM -->
            <!-- ================================================= -->

            <div
                class="max-h-[75vh] overflow-y-auto px-6 py-6"
            >

                <!-- ================================================= -->
                <!-- BASIC INFORMATION -->
                <!-- ================================================= -->

                <div class="mb-7">

                    <h3
                        class="mb-4 text-sm font-semibold"
                    >
                        Basic Information
                    </h3>


                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2"
                    >

                        <!-- NAME -->

                        <div class="md:col-span-2">

                            <label
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Full Name
                            </label>

                            <input
                                v-model="editingStudent.name"
                                type="text"
                                placeholder="Enter student's full name"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                            />

                        </div>


                        <!-- CLASS -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Class
                            </label>

                            <input
                                v-model="editingStudent.class"
                                type="text"
                                placeholder="Enter class"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm outline-none placeholder:text-ink-soft focus:border-forest"
                            />

                        </div>


                        <!-- SYMBOL -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Symbol No.
                            </label>

                            <input
                                v-model="editingStudent.symbol_no"
                                type="text"
                                placeholder="Enter symbol number"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                            />

                        </div>


                        <!-- DATE OF BIRTH -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium"
                            >
                                Date of Birth
                            </label>

                            <input
                                v-model="editingStudent.date_of_birth"
                                type="date"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none focus:border-forest"
                            />

                        </div>


                        <!-- STATUS -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium text-ink"
                            >
                                Status
                            </label>

                            <select
                                v-model="editingStudent.status"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none focus:border-forest"
                            >

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- CONTACT -->
                <!-- ================================================= -->

                <div
                    class="mb-7 border-t border-hairline pt-6"
                >

                    <h3
                        class="mb-4 text-sm font-semibold text-ink"
                    >
                        Contact Information
                    </h3>


                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2"
                    >

                        <!-- EMAIL -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium text-ink"
                            >
                                Email Address
                            </label>

                            <input
                                v-model="editingStudent.email"
                                type="email"
                                placeholder="student@example.com"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                            />

                        </div>


                        <!-- PHONE -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium text-ink"
                            >
                                Phone Number
                            </label>

                            <input
                                v-model="editingStudent.phone"
                                type="text"
                                placeholder="Phone number"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                            />

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- FAMILY & ADDRESS -->
                <!-- ================================================= -->

                <div
                    class="mb-7 border-t border-hairline pt-6"
                >

                    <h3
                        class="mb-4 text-sm font-semibold text-ink"
                    >
                        Family & Address
                    </h3>


                    <div class="space-y-4">

                        <!-- PARENT -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium text-ink"
                            >
                                Parent
                                <span class="font-normal text-ink-soft">
                                    (optional)
                                </span>
                            </label>

                            <select
                                v-model="editingStudent.parent_id"
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none focus:border-forest"
                            >

                                <option :value="null">
                                    No parent assigned
                                </option>

                                <option
                                    v-for="parent in parentStore.parents"
                                    :key="parent.id"
                                    :value="parent.id"
                                >
                                    {{ parent.name }}
                                </option>

                            </select>

                        </div>


                        <!-- ADDRESS -->

                        <div>

                            <label
                                class="mb-1.5 block text-sm font-medium text-ink"
                            >
                                Address
                            </label>

                            <select
                                v-model="editingStudent.address_id"
                                disabled
                                class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none focus:border-forest"
                            >

                                <option value="">
                                    Select Address
                                </option>

                                <option
                                    v-for="address in addressStore.addresses"
                                    :key="address.id"
                                    :value="address.id"
                                >
                                    {{ address.municipality }},
                                    {{ address.district }}
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- TEACHER ASSIGNMENTS -->
                <!-- ================================================= -->

                <div class="mb-7 border-t border-hairline pt-6">
                    <h3 class="mb-1 text-sm font-semibold text-ink">Teacher assignments</h3>
                    <p class="mb-4 text-xs text-ink-soft">The teacher assigned to class {{ editingStudent.class || '-' }} is shown here.</p>
                    <select
                        v-model="editingStudent.teacher_id"
                        class="h-32 w-full border border-hairline bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-forest"
                    >
                        <option :value="null">No class teacher assigned</option>
                        <option v-for="teacher in teachers.filter(item => Number(item.class) === Number(editingStudent.class))" :key="teacher.id" :value="teacher.id">
                            {{ teacher.name }} (Class {{ teacher.class }})
                        </option>
                    </select>
                </div>

                <!-- ================================================= -->
                <!-- SUBJECTS -->
                <!-- ================================================= -->

                <div
                    class="mb-7 border-t border-hairline pt-6"
                >

                    <div
                        class="mb-4 flex items-center justify-between gap-4"
                    >

                        <div>

                            <h3
                                class="text-sm font-semibold text-ink"
                            >
                                Subjects
                            </h3>

                            <p
                                class="mt-1 text-xs text-ink-soft"
                            >
                                Manage the subjects assigned to this student.
                            </p>

                        </div>


                        <!-- ADD SUBJECT -->

                    </div>


                    <select
                        v-model="editingStudent.subjects"
                        multiple
                        class="h-40 w-full border border-hairline bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-forest"
                    >
                        <option v-for="subject in subjectStore.subjects.filter(item => item.class == null || Number(item.class) === Number(editingStudent.class))" :key="subject.id" :value="subject.id">
                            {{ subject.name }}{{ subject.code ? ` (${subject.code})` : '' }}{{ subject.class == null ? ' (all classes)' : '' }}
                        </option>
                    </select>

                    <p class="mt-2 text-xs text-ink-soft">Select at least 3 registered subjects. New subjects must be created in Subjects first.</p>
                    <p v-if="!editingSubjectsValid" class="mt-2 text-sm text-sienna">
                        Select at least 3 subjects before updating this student.
                    </p>

                </div>


                <!-- ================================================= -->
                <!-- CURRENT PHOTO -->
                <!-- ================================================= -->

                <div
                    v-if="editingStudent.photo"
                    class="mb-7 border-t border-hairline pt-6"
                >

                    <h3
                        class="mb-4 text-sm font-semibold text-ink"
                    >
                        Current Photo
                    </h3>


                    <div
                        class="flex items-center gap-4 border border-hairline bg-surface p-4"
                    >

                        <img
                            :src="newPhotoPreview || `http://127.0.0.1:8000/storage/${editingStudent.photo}`"
                            :alt="editingStudent.name"
                            class="h-16 w-16 object-cover"
                        />


                        <div>

                            <p
                                class="text-sm font-medium text-ink"
                            >
                                {{ editingStudent.name }}
                            </p>

                            <p
                                class="mt-1 text-xs text-ink-soft"
                            >
                                Select a new photo below to replace this image.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- NEW PHOTO -->
                <!-- ================================================= -->

                <div
                    class="border-t border-hairline pt-6"
                >

                    <h3
                        class="mb-4 text-sm font-semibold text-ink"
                    >
                        Replace Photo
                    </h3>

                    <img
                        v-if="newPhotoPreview"
                        :src="newPhotoPreview"
                        alt="New student photo preview"
                        class="mb-4 h-24 w-24 object-cover"
                    />


                    <label
                        class="flex cursor-pointer items-center justify-between border border-dashed border-hairline bg-surface px-4 py-4 transition hover:border-forest"
                    >

                        <div>

                            <p
                                class="text-sm font-medium text-ink"
                            >
                                Select a new student photo
                            </p>

                            <p
                                class="mt-1 text-xs text-ink-soft"
                            >
                                JPG, PNG or WEBP
                            </p>

                        </div>


                        <span
                            class="shrink-0 border border-hairline px-4 py-2 text-sm text-forest"
                        >
                            Choose file
                        </span>


                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="handleNewPhoto"
                            class="hidden"
                        />

                    </label>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- FOOTER -->
            <!-- ================================================= -->

            <div
                class="flex flex-col-reverse gap-3 border-t border-hairline bg-surface px-6 py-4 sm:flex-row sm:justify-end"
            >

                <!-- CANCEL -->

                <button
                    type="button"
                    @click="editingStudent = null"
                    class="w-full border border-hairline px-6 py-3 text-sm font-medium text-ink transition hover:bg-paper sm:w-auto"
                >
                    Cancel
                </button>


                <!-- UPDATE -->

                <button
                    type="button"
                    @click="updateStudent"
                    :disabled="!editingSubjectsValid"
                    class="w-full bg-forest px-6 py-3 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40 sm:w-auto"
                >
                    Update Student
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
                class="text-[#5B6B62] dark:text-[#9AA79E]"
            >
                Loading students…
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
                    class="transition hover:bg-[#F7F8F3] dark:hover:bg-[#243329]"
                >

                    <!-- CHECKBOX -->

                    <td
                        class="px-5 py-4 text-center"
                    >

                        <input
                            type="checkbox"
                            :checked="selectedStudents.includes(student.id)"
                            @change="toggleStudent(student.id)"
                            class="h-4 w-4 cursor-pointer accent-[#2F6F4E]"
                        />

                    </td>


                    <!-- S.N. -->

                    <td
                        class="px-5 py-4 font-['IBM_Plex_Mono',ui-monospace,monospace] text-sm text-[#5B6B62] dark:text-[#9AA79E]"
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
                        class="px-5 py-4 font-['IBM_Plex_Mono',ui-monospace,monospace] text-sm text-[#5B6B62] dark:text-[#9AA79E]"
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
                            class="h-11 w-11 rounded-full border border-[#D8DDD3] object-cover dark:border-[#2E3B33]"
                        />


                        <div
                            v-else
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-[#F7F8F3] text-xs text-[#5B6B62] dark:bg-[#16211B] dark:text-[#9AA79E]"
                        >
                            No photo
                        </div>

                    </td>


                    <!-- NAME -->

                    <td
                        class="px-5 py-4 text-sm font-semibold text-[#1C2B24] dark:text-[#E8EBE4]"
                    >

                        {{ student.name }}

                    </td>
                        <!--CLASS-->
                    <td
    class="whitespace-nowrap px-5 py-4 text-sm text-[#1C2B24] dark:text-[#E8EBE4]"
>
    {{ student.class || 'Not assigned' }}
</td>


                    <!-- EMAIL -->

                    <td
                        class="px-5 py-4 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                    >

                        {{ student.email }}

                    </td>


                    <!-- PHONE -->

                    <td
                        class="px-5 py-4 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                    >

                        {{ student.phone }}

                    </td>


                    <!-- STATUS -->

                    <td
                        class="px-5 py-4"
                    >

                        <span
                            class="rounded-full px-3 py-1 text-xs font-medium capitalize"
                            :class="
                                student.status === 'active'
                                    ? 'bg-[#2F6F4E]/10 text-[#2F6F4E]'
                                    : 'bg-[#B5563C]/10 text-[#B5563C]'
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
                            class="flex flex-wrap gap-4 text-sm font-medium"
                        >

                            <button
                                type="button"
                                @click="viewStudent(student)"
                                class="text-[#2F6F4E] hover:underline"
                            >
                                View
                            </button>


                            <button
                                type="button"
                                @click="editStudent(student)"
                                class="text-[#1C2B24] hover:underline dark:text-[#E8EBE4]"
                            >
                                Edit
                            </button>


                            <button
                                type="button"
                                @click="deleteStudent(student)"
                                class="text-[#B5563C] hover:underline"
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

                            <h3
                                class="text-lg text-[#1C2B24] dark:text-[#E8EBE4]"
                            >
                                No students found
                            </h3>


                            <p
                                class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
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
            class="mt-6 rounded-md border border-[#D8DDD3] bg-white p-4 dark:border-[#2E3B33] dark:bg-[#1E2B24]"
        >

            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >

                <!-- PAGINATION INFO -->

                <div
                    class="text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >

                    Showing

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ showingFrom }}
                    </span>

                    &ndash;

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ showingTo }}
                    </span>

                    of

                    <span
                        class="font-medium text-[#1C2B24] dark:text-[#E8EBE4]"
                    >
                        {{ totalStudents }}
                    </span>

                    students

                </div>


                <!-- BUTTONS -->

                <div
                    class="flex flex-wrap items-center gap-2"
                >

                    <!-- PREVIOUS -->

                    <button
                        type="button"
                        @click="previousPage"
                        :disabled="
                            currentPage === 1 ||
                            studentStore.loading
                        "
                        class="rounded-md border border-[#D8DDD3] px-4 py-2 text-sm text-[#1C2B24] transition hover:bg-[#EFF1EA] disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#2E3B33] dark:text-[#E8EBE4] dark:hover:bg-[#243329]"
                    >
                        Previous
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
                            class="h-9 w-9 rounded-md text-sm font-medium transition disabled:opacity-50"
                            :class="
                                currentPage === page
                                    ? 'bg-[#2F6F4E] text-white'
                                    : 'border border-[#D8DDD3] text-[#1C2B24] hover:bg-[#EFF1EA] dark:border-[#2E3B33] dark:text-[#E8EBE4] dark:hover:bg-[#243329]'
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
                        class="rounded-md border border-[#D8DDD3] px-4 py-2 text-sm text-[#1C2B24] transition hover:bg-[#EFF1EA] disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#2E3B33] dark:text-[#E8EBE4] dark:hover:bg-[#243329]"
                    >
                        Next
                    </button>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- CHART -->
        <!-- ================================================= -->

        <div
            class="mt-8 rounded-md border border-[#D8DDD3] bg-white p-6 dark:border-[#2E3B33] dark:bg-[#1E2B24]"
        >

            <div
                class="mb-6"
            >

                <h2
                    class="text-xl text-[#1C2B24] dark:text-[#E8EBE4]"
                >
                    Student statistics
                </h2>


                <p
                    class="mt-1 text-sm text-[#5B6B62] dark:text-[#9AA79E]"
                >
                    Overview of active and inactive students
                </p>

            </div>


            <div
                class="relative mx-auto h-80 w-full max-w-md"
            >

                <canvas
                    ref="statusChart"
                ></canvas>

            </div>

        </div>

    </div>

</div>

</template>