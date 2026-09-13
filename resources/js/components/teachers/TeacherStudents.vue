<script setup>

import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

import { useTeacherStudentStore } from '../../stores/teachers/teacherStudent'


const router = useRouter()

const teacherStudentStore =
    useTeacherStudentStore()


// =========================================================
// STATE
// =========================================================

const searchQuery = ref('')

const statusFilter = ref('all')

const showViewModal = ref(false)

const selectedStudent = ref(null)

const darkMode = ref(
    localStorage.getItem('teacher_dark_mode') === 'true'
)


// =========================================================
// FETCH STUDENTS
// =========================================================

const fetchStudents = async (
    page = 1
) => {

    await teacherStudentStore.fetchStudents(
        page,
        searchQuery.value,
        statusFilter.value
    )

}


// =========================================================
// SEARCH DEBOUNCE
// =========================================================

let searchTimeout = null

watch(
    searchQuery,
    () => {

        clearTimeout(searchTimeout)

        searchTimeout = setTimeout(() => {

            fetchStudents(1)

        }, 400)

    }
)


// =========================================================
// STATUS FILTER
// =========================================================

watch(
    statusFilter,
    () => {

        fetchStudents(1)

    }
)


// =========================================================
// VIEW STUDENT
// =========================================================

const viewStudent = async (student) => {

    const data =
        await teacherStudentStore.fetchStudent(
            student.id
        )

    if (data) {

        selectedStudent.value = data

        showViewModal.value = true

    }

}


// =========================================================
// CLOSE VIEW MODAL
// =========================================================

const closeViewModal = () => {

    showViewModal.value = false

    selectedStudent.value = null

    teacherStudentStore.clearSelectedStudent()

}


// =========================================================
// PAGINATION
// =========================================================

const goToPage = (page) => {

    if (
        page < 1 ||
        page > teacherStudentStore.pagination.lastPage
    ) {
        return
    }

    fetchStudents(page)

}


// =========================================================
// DARK MODE
// =========================================================

const toggleDarkMode = () => {

    darkMode.value = !darkMode.value

    localStorage.setItem(
        'teacher_dark_mode',
        darkMode.value
    )

}


// =========================================================
// BACK TO DASHBOARD
// =========================================================

const goBack = () => {

    router.push('/dashboard')

}


// =========================================================
// LOGOUT
// =========================================================

const logout = () => {

    localStorage.removeItem(
        'teacher_token'
    )

    localStorage.removeItem(
        'teacher_user'
    )

    router.push('/login')

}


// =========================================================
// INITIAL LOAD
// =========================================================

onMounted(() => {

    fetchStudents()

})

</script>


<template>

    <div
        :class="[
            'min-h-screen p-6 transition-colors duration-300',
            darkMode
                ? 'bg-gray-950 text-gray-100'
                : 'bg-gray-100 text-gray-900'
        ]"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div
            class="max-w-7xl mx-auto mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
        >

            <div>

                <button
                    @click="goBack"
                    class="mb-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400"
                >
                    ← Back to Dashboard
                </button>

                <h1
                    class="text-3xl font-bold"
                >
                    My Students
                </h1>

                <p
                    :class="[
                        'mt-1',
                        darkMode
                            ? 'text-gray-400'
                            : 'text-gray-600'
                    ]"
                >
                    Students assigned to your subjects
                </p>

            </div>


            <div class="flex items-center gap-2">

                <button
                    @click="toggleDarkMode"
                    class="px-4 py-2 rounded-lg border transition"
                    :class="
                        darkMode
                            ? 'border-gray-700 bg-gray-900 hover:bg-gray-800'
                            : 'border-gray-300 bg-white hover:bg-gray-50'
                    "
                >
                    {{ darkMode ? '☀️ Light' : '🌙 Dark' }}
                </button>

                <button
                    @click="logout"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                >
                    Logout
                </button>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- MAIN CARD -->
        <!-- ================================================= -->

        <div
            class="max-w-7xl mx-auto rounded-xl shadow-sm border overflow-hidden"
            :class="
                darkMode
                    ? 'bg-gray-900 border-gray-800'
                    : 'bg-white border-gray-200'
            "
        >

            <!-- ============================================= -->
            <!-- TOOLBAR -->
            <!-- ============================================= -->

            <div
                class="p-4 border-b flex flex-col md:flex-row gap-3 md:items-center md:justify-between"
                :class="
                    darkMode
                        ? 'border-gray-800'
                        : 'border-gray-200'
                "
            >

                <!-- SEARCH -->

                <div class="flex-1">

                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search students..."
                        class="w-full px-4 py-2 rounded-lg border outline-none focus:ring-2 focus:ring-blue-500"
                        :class="
                            darkMode
                                ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400'
                                : 'bg-white border-gray-300 text-gray-900'
                        "
                    />

                </div>


                <!-- STATUS -->

                <select
                    v-model="statusFilter"
                    class="px-4 py-2 rounded-lg border outline-none focus:ring-2 focus:ring-blue-500"
                    :class="
                        darkMode
                            ? 'bg-gray-800 border-gray-700 text-white'
                            : 'bg-white border-gray-300 text-gray-900'
                    "
                >

                    <option value="all">
                        All Status
                    </option>

                    <option value="active">
                        Active
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>

                </select>

            </div>


            <!-- ============================================= -->
            <!-- ERROR -->
            <!-- ============================================= -->

            <div
                v-if="teacherStudentStore.error"
                class="m-4 p-4 rounded-lg bg-red-100 text-red-700 border border-red-200"
            >
                {{ teacherStudentStore.error }}
            </div>


            <!-- ============================================= -->
            <!-- LOADING -->
            <!-- ============================================= -->

            <div
                v-if="teacherStudentStore.loading"
                class="p-10 text-center"
                :class="
                    darkMode
                        ? 'text-gray-400'
                        : 'text-gray-500'
                "
            >
                Loading your students...
            </div>


            <!-- ============================================= -->
            <!-- TABLE -->
            <!-- ============================================= -->

            <div
                v-else
                class="overflow-x-auto"
            >

                <table class="w-full">

                    <thead
                        :class="
                            darkMode
                                ? 'bg-gray-800'
                                : 'bg-gray-50'
                        "
                    >

                        <tr>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                            >
                                Student
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                            >
                                Class
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                            >
                                Symbol No.
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                            >
                                Subjects
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                            >
                                Status
                            </th>

                            <th
                                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider"
                            >
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody
                        class="divide-y"
                        :class="
                            darkMode
                                ? 'divide-gray-800'
                                : 'divide-gray-200'
                        "
                    >

                        <tr
                            v-for="student in teacherStudentStore.students"
                            :key="student.id"
                            class="transition"
                            :class="
                                darkMode
                                    ? 'hover:bg-gray-800'
                                    : 'hover:bg-gray-50'
                            "
                        >

                            <!-- STUDENT -->

                            <td class="px-6 py-4">

                                <div
                                    class="font-medium"
                                >
                                    {{ student.name }}
                                </div>

                                <div
                                    class="text-sm"
                                    :class="
                                        darkMode
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    "
                                >
                                    {{ student.email }}
                                </div>

                            </td>


                            <!-- CLASS -->

                            <td class="px-6 py-4">

                                {{ student.class }}

                            </td>


                            <!-- SYMBOL -->

                            <td class="px-6 py-4">

                                {{ student.symbol_no || '—' }}

                            </td>


                            <!-- SUBJECTS -->

                            <td class="px-6 py-4">

                                <div
                                    v-if="student.subjects?.length"
                                    class="flex flex-wrap gap-1"
                                >

                                    <span
                                        v-for="subject in student.subjects"
                                        :key="`${student.id}-${subject.id}-${subject.name}`"
                                        class="px-2 py-1 rounded-md text-xs font-medium"
                                        :class="
                                            darkMode
                                                ? 'bg-blue-900/40 text-blue-300'
                                                : 'bg-blue-100 text-blue-700'
                                        "
                                    >
                                        {{ subject.name }}
                                    </span>

                                </div>

                                <span
                                    v-else
                                    :class="
                                        darkMode
                                            ? 'text-gray-500'
                                            : 'text-gray-400'
                                    "
                                >
                                    —
                                </span>

                            </td>


                            <!-- STATUS -->

                            <td class="px-6 py-4">

                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :class="
                                        student.status === 'active'
                                            ? (
                                                darkMode
                                                    ? 'bg-green-900/40 text-green-300'
                                                    : 'bg-green-100 text-green-700'
                                            )
                                            : (
                                                darkMode
                                                    ? 'bg-red-900/40 text-red-300'
                                                    : 'bg-red-100 text-red-700'
                                            )
                                    "
                                >
                                    {{
                                        student.status === 'active'
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>

                            </td>


                            <!-- ACTION -->

                            <td
                                class="px-6 py-4 text-right"
                            >

                                <button
                                    @click="viewStudent(student)"
                                    class="px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm"
                                >
                                    View
                                </button>

                            </td>

                        </tr>


                        <!-- EMPTY -->

                        <tr
                            v-if="
                                teacherStudentStore.students.length === 0 &&
                                !teacherStudentStore.loading
                            "
                        >

                            <td
                                colspan="6"
                                class="px-6 py-12 text-center"
                                :class="
                                    darkMode
                                        ? 'text-gray-400'
                                        : 'text-gray-500'
                                "
                            >

                                No students assigned to you.

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- ============================================= -->
            <!-- PAGINATION -->
            <!-- ============================================= -->

            <div
                v-if="
                    teacherStudentStore.pagination.lastPage > 1
                "
                class="px-6 py-4 border-t flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                :class="
                    darkMode
                        ? 'border-gray-800'
                        : 'border-gray-200'
                "
            >

                <div
                    class="text-sm"
                    :class="
                        darkMode
                            ? 'text-gray-400'
                            : 'text-gray-500'
                    "
                >

                    Showing
                    {{ teacherStudentStore.pagination.from }}
                    –
                    {{ teacherStudentStore.pagination.to }}
                    of
                    {{ teacherStudentStore.pagination.totalStudents }}
                    students

                </div>


                <div class="flex items-center gap-2">

                    <button
                        @click="
                            goToPage(
                                teacherStudentStore.pagination.currentPage - 1
                            )
                        "
                        :disabled="
                            teacherStudentStore.pagination.currentPage === 1
                        "
                        class="px-3 py-2 rounded-lg border disabled:opacity-40"
                        :class="
                            darkMode
                                ? 'border-gray-700 hover:bg-gray-800'
                                : 'border-gray-300 hover:bg-gray-50'
                        "
                    >
                        Previous
                    </button>


                    <span
                        class="px-3 py-2 text-sm"
                    >
                        Page
                        {{ teacherStudentStore.pagination.currentPage }}
                        of
                        {{ teacherStudentStore.pagination.lastPage }}
                    </span>


                    <button
                        @click="
                            goToPage(
                                teacherStudentStore.pagination.currentPage + 1
                            )
                        "
                        :disabled="
                            teacherStudentStore.pagination.currentPage ===
                            teacherStudentStore.pagination.lastPage
                        "
                        class="px-3 py-2 rounded-lg border disabled:opacity-40"
                        :class="
                            darkMode
                                ? 'border-gray-700 hover:bg-gray-800'
                                : 'border-gray-300 hover:bg-gray-50'
                        "
                    >
                        Next
                    </button>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- VIEW STUDENT MODAL -->
        <!-- ================================================= -->

        <div
            v-if="showViewModal && selectedStudent"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
            @click.self="closeViewModal"
        >

            <div
                class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl"
                :class="
                    darkMode
                        ? 'bg-gray-900 text-white'
                        : 'bg-white text-gray-900'
                "
            >

                <!-- MODAL HEADER -->

                <div
                    class="p-6 border-b flex items-center justify-between"
                    :class="
                        darkMode
                            ? 'border-gray-800'
                            : 'border-gray-200'
                    "
                >

                    <div>

                        <h2
                            class="text-2xl font-bold"
                        >
                            {{ selectedStudent.name }}
                        </h2>

                        <p
                            class="text-sm mt-1"
                            :class="
                                darkMode
                                    ? 'text-gray-400'
                                    : 'text-gray-500'
                            "
                        >
                            Student Details
                        </p>

                    </div>


                    <button
                        @click="closeViewModal"
                        class="text-2xl opacity-70 hover:opacity-100"
                    >
                        ×
                    </button>

                </div>


                <!-- MODAL BODY -->

                <div class="p-6 space-y-6">

                    <!-- BASIC INFORMATION -->

                    <div>

                        <h3
                            class="text-sm font-semibold uppercase tracking-wide mb-3"
                        >
                            Basic Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>

                                <p
                                    class="text-xs"
                                    :class="
                                        darkMode
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    "
                                >
                                    Email
                                </p>

                                <p class="font-medium">
                                    {{ selectedStudent.email || '—' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs"
                                    :class="
                                        darkMode
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    "
                                >
                                    Phone
                                </p>

                                <p class="font-medium">
                                    {{ selectedStudent.phone || '—' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs"
                                    :class="
                                        darkMode
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    "
                                >
                                    Class
                                </p>

                                <p class="font-medium">
                                    {{ selectedStudent.class || '—' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs"
                                    :class="
                                        darkMode
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    "
                                >
                                    Symbol No.
                                </p>

                                <p class="font-medium">
                                    {{ selectedStudent.symbol_no || '—' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs"
                                    :class="
                                        darkMode
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    "
                                >
                                    Date of Birth
                                </p>

                                <p class="font-medium">
                                    {{ selectedStudent.date_of_birth || '—' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs"
                                    :class="
                                        darkMode
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    "
                                >
                                    Status
                                </p>

                                <p class="font-medium">
                                    {{
                                        selectedStudent.status || '—'
                                    }}
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- SUBJECTS -->

                    <div>

                        <h3
                            class="text-sm font-semibold uppercase tracking-wide mb-3"
                        >
                            Assigned Subjects
                        </h3>

                        <div
                            v-if="selectedStudent.subjects?.length"
                            class="flex flex-wrap gap-2"
                        >

                            <span
                                v-for="subject in selectedStudent.subjects"
                                :key="`${subject.id}-${subject.name}`"
                                class="px-3 py-2 rounded-lg text-sm"
                                :class="
                                    darkMode
                                        ? 'bg-blue-900/40 text-blue-300'
                                        : 'bg-blue-100 text-blue-700'
                                "
                            >

                                {{ subject.name }}

                                <span
                                    v-if="subject.code"
                                    class="opacity-70"
                                >
                                    ({{ subject.code }})
                                </span>

                            </span>

                        </div>

                        <p
                            v-else
                            :class="
                                darkMode
                                    ? 'text-gray-500'
                                    : 'text-gray-400'
                            "
                        >
                            No subjects assigned.

                        </p>

                    </div>


                    <!-- PARENT -->

                    <div
                        v-if="selectedStudent.parent"
                    >

                        <h3
                            class="text-sm font-semibold uppercase tracking-wide mb-3"
                        >
                            Parent
                        </h3>

                        <div
                            class="rounded-xl p-4"
                            :class="
                                darkMode
                                    ? 'bg-gray-800'
                                    : 'bg-gray-50'
                            "
                        >

                            <p class="font-medium">
                                {{
                                    selectedStudent.parent.name
                                    || '—'
                                }}
                            </p>

                            <p
                                class="text-sm mt-1"
                                :class="
                                    darkMode
                                        ? 'text-gray-400'
                                        : 'text-gray-500'
                                "
                            >
                                {{
                                    selectedStudent.parent.email
                                    || '—'
                                }}
                            </p>

                        </div>

                    </div>

                </div>


                <!-- MODAL FOOTER -->

                <div
                    class="px-6 py-4 border-t flex justify-end"
                    :class="
                        darkMode
                            ? 'border-gray-800'
                            : 'border-gray-200'
                    "
                >

                    <button
                        @click="closeViewModal"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                    >
                        Close
                    </button>

                </div>

            </div>

        </div>

    </div>

</template>

