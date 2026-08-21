<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useStudentStore } from '../stores/student'
const studentStore=useStudentStore()

import {
  updateStudent as updateStudentApi,
  deleteStudent as deleteStudentApi,
  bulkDeleteStudents
} from '../services/studentApi'
const router = useRouter()

// =========================
// STUDENTS
// =========================




// =========================
// ADD STUDENT
// =========================

const showAddForm = ref(false)

const newStudent = ref({
  name: '',
  email: '',
  phone: '',
  status: 'active'
})


// =========================
// EDIT STUDENT
// =========================

const editingStudent = ref(null)


// =========================
// SELECTED STUDENTS
// =========================

const selectedStudents = ref([])


// =========================
// TOGGLE STUDENT
// =========================

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


// =========================
// SELECT ALL
// =========================

const allSelected = () => {

  return (
    studentStore.students.length > 0 &&
    selectedStudents.value.length === studentStore.students.length
  )

}

const toggleAll = () => {

  if (allSelected()) {

    selectedStudents.value = []

  } else {

    selectedStudents.value =
      studentStore.students.map(student => student.id)

  }

}


// =========================
// ADD STUDENT
// =========================

const addStudent = async () => {

  try {

    await studentStore.addStudent(
      newStudent.value
    )

    showAddForm.value = false

    newStudent.value = {
      name: '',
      email: '',
      phone: '',
      status: 'active'
    }

    alert('Student added successfully!')

  } catch (error) {

    console.error(
      'Error adding student:',
      error
    )

  }

}


// =========================
// EDIT STUDENT
// =========================

const editStudent = (student) => {

  editingStudent.value = {
    ...student
  }

}


// =========================
// UPDATE STUDENT
// =========================

const updateStudent = async () => {

  if (!editingStudent.value) {
    return
  }

  try {

    const response =
      await updateStudentApi(
        editingStudent.value.id,
        editingStudent.value
      )

    const updatedStudent =
      response.data.student || response.data

    const index =
      students.value.findIndex(
        student =>
          student.id === editingStudent.value.id
      )

    if (index !== -1) {

      students.value[index] = updatedStudent

    }

    editingStudent.value = null

    alert('Student updated successfully!')

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


// =========================
// DELETE STUDENT
// =========================

const deleteStudent = async (student) => {

  const confirmed = confirm(
    `Are you sure you want to delete ${student.name}?`
  )

  if (!confirmed) {
    return
  }

  try {

    await deleteStudentApi(student.id)

    students.value =
      students.value.filter(
        s => s.id !== student.id
      )

    selectedStudents.value =
      selectedStudents.value.filter(
        id => id !== student.id
      )

    alert('Student deleted successfully!')

  } catch (error) {

    console.error(
      'Error deleting student:',
      error
    )

  }

}


// =========================
// BULK DELETE
// =========================

const bulkDelete = async () => {

  if (selectedStudents.value.length === 0) {

    alert('Please select at least one student.')

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

    students.value =
      students.value.filter(
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

  }

}


// =========================
// FETCH STUDENTS
// =========================




// =========================
// LOGOUT
// =========================

const logout = async () => {

  const token =
    localStorage.getItem('token')

  try {

    await axios.post(
      'http://127.0.0.1:8000/api/logout',
      {},
      {
        headers: {
          Authorization: `Bearer ${token}`
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


// =========================
// ON LOAD
// =========================

onMounted(() => {

  studentStore.fetchStudents()

})

</script>


<template>

  <div class="min-h-screen bg-gray-100 p-4 sm:p-6 lg:p-8">

    <div class="w-full max-w-7xl mx-auto">

      <!-- ========================= -->
      <!-- HEADER -->
      <!-- ========================= -->

      <div
        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
      >

        <div>

          <h1
            class="text-2xl sm:text-3xl font-bold text-gray-700"
          >
            Student Management
          </h1>

          <p class="mt-2 text-sm sm:text-base text-gray-500">
            Manage and view all registered students
          </p>

        </div>


        <!-- LOGOUT -->

        <div class="flex flex-col sm:flex-row gap-3">

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


      <!-- ========================= -->
      <!-- ERROR -->
      <!-- ========================= -->

      <div
        v-if="studentStore.error"
        class="mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg"
      >
        {{studentStore.error }}
      </div>


      <!-- ========================= -->
      <!-- ACTION BUTTONS -->
      <!-- ========================= -->

      <div
        class="mt-6 flex flex-col sm:flex-row gap-3"
      >

        <button
          @click="showAddForm = true"
          class="w-full sm:w-auto bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700 transition"
        >
          + Add Student
        </button>


        <button
          @click="bulkDelete"
          class="w-full sm:w-auto bg-red-600 text-white px-5 py-2.5 rounded-lg hover:bg-red-700 transition"
        >
          Delete Selected
        </button>

      </div>


      <!-- ========================= -->
      <!-- ADD FORM -->
      <!-- ========================= -->

      <div
        v-if="showAddForm"
        class="mt-6 bg-white rounded-xl shadow-md p-5 sm:p-6"
      >

        <h2 class="text-xl font-semibold text-gray-800 mb-5">
          Add New Student
        </h2>


        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >

          <input
            v-model="newStudent.name"
            type="text"
            placeholder="Name"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
          />


          <input
            v-model="newStudent.email"
            type="email"
            placeholder="Email"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
          />


          <input
            v-model="newStudent.phone"
            type="text"
            placeholder="Phone"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
          />


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

        </div>


        <div
          class="mt-5 flex flex-col sm:flex-row gap-3"
        >

          <button
            @click="addStudent"
            class="w-full sm:w-auto bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700"
          >
            Add Student
          </button>


          <button
            @click="showAddForm = false"
            class="w-full sm:w-auto border border-gray-300 text-gray-700 px-5 py-2.5 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>

        </div>

      </div>


      <!-- ========================= -->
      <!-- EDIT FORM -->
      <!-- ========================= -->

      <div
        v-if="editingStudent"
        class="mt-6 bg-white rounded-xl shadow-md p-5 sm:p-6"
      >

        <h2 class="text-xl font-semibold text-gray-800 mb-5">
          Edit Student
        </h2>


        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >

          <input
            v-model="editingStudent.name"
            type="text"
            placeholder="Name"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
          />


          <input
            v-model="editingStudent.email"
            type="email"
            placeholder="Email"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
          />


          <input
            v-model="editingStudent.phone"
            type="text"
            placeholder="Phone"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800"
          />


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

        </div>


        <div
          class="mt-5 flex flex-col sm:flex-row gap-3"
        >

          <button
            @click="updateStudent"
            class="w-full sm:w-auto bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700"
          >
            Update
          </button>


          <button
            @click="editingStudent = null"
            class="w-full sm:w-auto border border-gray-300 text-gray-700 px-5 py-2.5 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>

        </div>

      </div>


      <!-- ========================= -->
      <!-- LOADING -->
      <!-- ========================= -->

      <div
        v-if="studentStore.loading"
        class="mt-8 text-center text-gray-500"
      >
        Loading students...
      </div>


      <!-- ========================= -->
      <!-- TABLE -->
      <!-- ========================= -->

      <div
        v-else
        class="mt-8 bg-white rounded-xl shadow-md overflow-x-auto"
      >

        <table
          class="w-full min-w-[1000px]"
        >

          <thead>

            <tr class="bg-gray-50 border-b">

              <th class="px-5 py-4 text-center">
                <input
                  type="checkbox"
                  :checked="allSelected()"
                  @change="toggleAll"
                />
              </th>


              <th
                class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
              >
                S.N.
              </th>


              <th
                class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
              >
                ID
              </th>


              <th
                class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
              >
                Name
              </th>


              <th
                class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
              >
                Email
              </th>


              <th
                class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
              >
                Phone
              </th>


              <th
                class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
              >
                Status
              </th>


              <th
                class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
              >
                Actions
              </th>

            </tr>

          </thead>


          <tbody>

            <tr
              v-for="(student, index) in studentStore.students"
              :key="student.id"
              class="border-b hover:bg-gray-50"
            >

              <!-- CHECKBOX -->

              <td class="px-5 py-4 text-center">

                <input
                  type="checkbox"
                  :checked="selectedStudents.includes(student.id)"
                  @change="toggleStudent(student.id)"
                />

              </td>


              <!-- S.N. -->

              <td class="px-5 py-4 text-sm text-gray-700">
                {{ index + 1 }}
              </td>


              <!-- ID -->

              <td class="px-5 py-4 text-sm text-gray-700">
                {{ student.id }}
              </td>


              <!-- NAME -->

              <td class="px-5 py-4 text-sm text-gray-700 font-medium">
                {{ student.name }}
              </td>


              <!-- EMAIL -->

              <td class="px-5 py-4 text-sm text-gray-700">
                {{ student.email }}
              </td>


              <!-- PHONE -->

              <td class="px-5 py-4 text-sm text-gray-700">
                {{ student.phone }}
              </td>


              <!-- STATUS -->

              <td class="px-5 py-4">

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

              <td class="px-5 py-4">

                <div class="flex gap-4">

                  <button
                    @click="editStudent(student)"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    Edit
                  </button>


                  <button
                    @click="deleteStudent(student)"
                    class="text-red-600 hover:text-red-800 font-medium"
                  >
                    Delete
                  </button>

                </div>

              </td>

            </tr>


            <!-- EMPTY -->

            <tr v-if="studentStore.students.length === 0">

              <td
                colspan="8"
                class="text-center py-10 text-gray-500"
              >
                No students found.
              </td>

            </tr>

          </tbody>

        </table>

      </div>

    </div>

  </div>

</template>