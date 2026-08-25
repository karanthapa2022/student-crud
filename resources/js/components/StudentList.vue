<script setup>

import BaseTable from './BaseTable.vue'
import BaseCard from './BaseCard.vue'
import BaseButton from './BaseButton.vue'
import StudentCard from './StudentCard.vue'
import StudentRow from './StudentRow.vue'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useStudentStore } from '../stores/student'

import {
    updateStudentWithPhoto,
    deleteStudent as deleteStudentApi,
    bulkDeleteStudents
} from '../services/studentApi'


const router = useRouter()

const studentStore = useStudentStore()


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
// EDIT STUDENT
// =========================================================

const editingStudent = ref(null)
const viewingStudent= ref(null)

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


// =========================================================
// TOGGLE STUDENT
// =========================================================

const toggleStudent = (id) => {

    if (selectedStudents.value.includes(id)) {

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

const allSelected = () => {

    return (
        studentStore.students.length > 0 &&
        selectedStudents.value.length ===
        studentStore.students.length
    )

}


const toggleAll = () => {

    if (allSelected()) {

        selectedStudents.value = []

    } else {

        selectedStudents.value =
            studentStore.students.map(
                student => student.id
            )

    }

}


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

        // Add new photo only if user selected one
        if (editingStudent.value.newPhoto) {

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
                s => s.id !== student.id
            )

        selectedStudents.value =
            selectedStudents.value.filter(
                id => id !== student.id
            )

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

    if (selectedStudents.value.length === 0) {

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
// ON LOAD
// =========================================================

onMounted(() => {

    studentStore.fetchStudents()

})

</script>


<template>

<div
    class="min-h-screen bg-gray-100 p-4 sm:p-6 lg:p-8"
>

    <div
        class="w-full max-w-7xl mx-auto"
    >


        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >

            <div>

                <h1
                    class="text-2xl sm:text-3xl font-bold text-gray-700"
                >
                    Student Management
                </h1>

                <p
                    class="mt-2 text-sm sm:text-base text-gray-500"
                >
                    Manage and view all registered students
                </p>

            </div>


            <!-- PROFILE + LOGOUT -->

            <div
                class="flex flex-col sm:flex-row gap-3"
            >

                <button
                    @click="router.push('/profile')"
                    class="w-full md:w-auto bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 transition"
                >
                    Profile
                </button>


                <button
                    @click="logout"
                    class="w-full md:w-auto bg-gray-800 text-white px-5 py-2.5 rounded-lg hover:bg-gray-900 transition"
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
            class="mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg"
        >

            {{ studentStore.error }}

        </div>


        <!-- ================================================= -->
        <!-- ACTION BUTTONS -->
        <!-- ================================================= -->

        <div
            class="mt-6 flex flex-col sm:flex-row gap-3"
        >

            <BaseButton
                variant="success"
                @click="showAddForm = true"
                class="w-full sm:w-auto"
            >
                + Add Student
            </BaseButton>


            <BaseButton
                variant="danger"
                @click="bulkDelete"
                class="w-full sm:w-auto"
            >
                Delete Selected
            </BaseButton>

        </div>


        <!-- ================================================= -->
        <!-- ADD STUDENT MODAL -->
        <!-- ================================================= -->

        <Teleport to="body">

            <div
                v-if="showAddForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            >

                <div
                    class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-6"
                >

                    <!-- HEADER -->

                    <div
                        class="flex items-center justify-between mb-6"
                    >

                        <h2
                            class="text-xl font-semibold text-gray-800"
                        >
                            Add New Student
                        </h2>


                        <button
                            type="button"
                            @click="showAddForm = false"
                            class="text-gray-500 hover:text-gray-800 text-2xl"
                        >
                            ×
                        </button>

                    </div>


                    <!-- FORM -->

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >

                        <!-- NAME -->

                        <input
                            v-model="newStudent.name"
                            type="text"
                            placeholder="Name"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
                        />


                        <!-- EMAIL -->

                        <input
                            v-model="newStudent.email"
                            type="email"
                            placeholder="Email"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
                        />


                        <!-- PHONE -->

                        <input
                            v-model="newStudent.phone"
                            type="text"
                            placeholder="Phone"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
                        />


                        <!-- STATUS -->

                        <select
                            v-model="newStudent.status"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
                        >

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>


                        <!-- PHOTO -->

                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="handlePhotoChange"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800 md:col-span-2"
                        />

                    </div>


                    <!-- BUTTONS -->

                    <div
                        class="flex flex-col sm:flex-row gap-3 mt-6"
                    >

                        <button
                            type="button"
                            @click="addStudent"
                            class="w-full sm:w-auto bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700"
                        >
                            Add Student
                        </button>


                        <button
                            type="button"
                            @click="showAddForm = false"
                            class="w-full sm:w-auto border border-gray-300 text-gray-700 px-5 py-2.5 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>

                    </div>

                </div>

            </div>

        </Teleport>
          <!--viewing Student-->

        <Teleport to="body">

    <div
        v-if="viewingStudent"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    >

        <div
            class="w-full max-w-lg bg-white rounded-2xl shadow-xl p-6"
        >

            <!-- HEADER -->

            <div
                class="flex items-center justify-between mb-6"
            >

                <h2
                    class="text-xl font-semibold text-gray-800"
                >
                    Student Details
                </h2>

                <button
                    type="button"
                    @click="viewingStudent = null"
                    class="text-gray-500 hover:text-gray-800 text-2xl"
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
                    class="w-28 h-28 rounded-full object-cover border-4 border-gray-200"
                />

                <div
                    v-else
                    class="w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center text-gray-500"
                >
                    No Photo
                </div>

            </div>


            <!-- DETAILS -->

            <div class="space-y-4">

                <div
                    class="flex justify-between border-b pb-3"
                >
                    <span class="font-medium text-gray-500">
                        Student ID
                    </span>

                    <span class="text-gray-800">
                        {{ viewingStudent.id }}
                    </span>
                </div>


                <div
                    class="flex justify-between border-b pb-3"
                >
                    <span class="font-medium text-gray-500">
                        Name
                    </span>

                    <span class="text-gray-800">
                        {{ viewingStudent.name }}
                    </span>
                </div>


                <div
                    class="flex justify-between border-b pb-3"
                >
                    <span class="font-medium text-gray-500">
                        Email
                    </span>

                    <span class="text-gray-800">
                        {{ viewingStudent.email }}
                    </span>
                </div>


                <div
                    class="flex justify-between border-b pb-3"
                >
                    <span class="font-medium text-gray-500">
                        Phone
                    </span>

                    <span class="text-gray-800">
                        {{ viewingStudent.phone }}
                    </span>
                </div>


                <div
                    class="flex justify-between items-center"
                >
                    <span class="font-medium text-gray-500">
                        Status
                    </span>

                    <span
                        class="px-3 py-1 rounded-full text-xs font-semibold"
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


            <!-- CLOSE -->

            <div class="mt-6">

                <button
                    type="button"
                    @click="viewingStudent = null"
                    class="w-full bg-gray-800 text-white px-5 py-2.5 rounded-lg hover:bg-gray-900"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

</Teleport>


        <!-- ================================================= -->
        <!-- EDIT STUDENT MODAL -->
        <!-- ================================================= -->

        <Teleport to="body">

            <div
                v-if="editingStudent"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            >

                <div
                    class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-6"
                >

                    <!-- HEADER -->

                    <div
                        class="flex items-center justify-between mb-6"
                    >

                        <h2
                            class="text-xl font-semibold text-gray-800"
                        >
                            Edit Student
                        </h2>


                        <button
                            type="button"
                            @click="editingStudent = null"
                            class="text-gray-500 hover:text-gray-800 text-2xl"
                        >
                            ×
                        </button>

                    </div>


                    <!-- FORM -->

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >

                        <!-- NAME -->

                        <input
                            v-model="editingStudent.name"
                            type="text"
                            placeholder="Name"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
                        />


                        <!-- EMAIL -->

                        <input
                            v-model="editingStudent.email"
                            type="email"
                            placeholder="Email"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
                        />


                        <!-- PHONE -->

                        <input
                            v-model="editingStudent.phone"
                            type="text"
                            placeholder="Phone"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
                        />


                        <!-- STATUS -->

                        <select
                            v-model="editingStudent.status"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
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
                            class="flex items-center gap-4 md:col-span-2"
                        >

                            <img
                                :src="`http://127.0.0.1:8000/storage/${editingStudent.photo}`"
                                :alt="editingStudent.name"
                                class="w-16 h-16 rounded-full object-cover border"
                            />

                            <div
                                class="text-sm text-gray-500"
                            >
                                Current Photo
                            </div>

                        </div>


                        <!-- NEW PHOTO -->

                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="editingStudent.newPhoto = $event.target.files[0] || null"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800 md:col-span-2"
                        />

                    </div>


                    <!-- BUTTONS -->

                    <div
                        class="flex flex-col sm:flex-row gap-3 mt-6"
                    >

                        <button
                            type="button"
                            @click="updateStudent"
                            class="w-full sm:w-auto bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700"
                        >
                            Update
                        </button>


                        <button
                            type="button"
                            @click="editingStudent = null"
                            class="w-full sm:w-auto border border-gray-300 text-gray-700 px-5 py-2.5 rounded-lg hover:bg-gray-50"
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
            class="mt-8 text-center text-gray-500"
        >
            Loading students...
        </div>


        <!-- ================================================= -->
        <!-- TABLE -->
        <!-- ================================================= -->

        <BaseTable
            v-else
            :headers="tableHeaders"
        >

            <!-- STUDENT ROWS -->

            <tr
                v-for="(student, index) in studentStore.students"
                :key="student.id"
                class="border-b hover:bg-gray-50"
            >

                <!-- CHECKBOX -->

                <td
                    class="px-5 py-4 text-center"
                >

                    <input
                        type="checkbox"
                        :checked="selectedStudents.includes(student.id)"
                        @change="toggleStudent(student.id)"
                    />

                </td>


                <!-- S.N. -->

                <td
                    class="px-5 py-4 text-sm text-gray-700"
                >
                    {{ index + 1 }}
                </td>


                <!-- ID -->

                <td
                    class="px-5 py-4 text-sm text-gray-700"
                >
                    {{ student.id }}
                </td>


                <!-- PHOTO -->

                <td
                    class="px-5 py-4"
                >

                    <img
                        v-if="student.photo"
                        :src="`http://127.0.0.1:8000/storage/${student.photo}`"
                        :alt="student.name"
                        class="w-12 h-12 rounded-full object-cover border"
                    />

                    <div
                        v-else
                        class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs"
                    >
                        No Photo
                    </div>

                </td>


                <!-- NAME -->

                <td
                    class="px-5 py-4 text-sm text-gray-700 font-medium"
                >
                    {{ student.name }}
                </td>


                <!-- EMAIL -->

                <td
                    class="px-5 py-4 text-sm text-gray-700"
                >
                    {{ student.email }}
                </td>


                <!-- PHONE -->

                <td
                    class="px-5 py-4 text-sm text-gray-700"
                >
                    {{ student.phone }}
                </td>


                <!-- STATUS -->

                <td
                    class="px-5 py-4"
                >

                    <span
                        class="px-3 py-1 rounded-full text-xs font-semibold"
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
                        class="flex gap-4"
                    >

                    <button
    type="button"
    @click="viewStudent(student)"
    class="text-green-600 hover:text-green-800 font-medium"
>
    View
</button>

                        <button
                            type="button"
                            @click="editStudent(student)"
                            class="text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Edit
                        </button>


                        <button
                            type="button"
                            @click="deleteStudent(student)"
                            class="text-red-600 hover:text-red-800 font-medium"
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
                    colspan="9"
                    class="text-center py-10 text-gray-500"
                >
                    No students found.
                </td>

            </tr>

        </BaseTable>


        <!-- ================================================= -->
        <!-- STUDENT CARDS -->
        <!-- ================================================= -->

        <div
            v-if="!studentStore.loading"
            class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5"
        >

        </div>


    </div>

</div>

</template>