<script setup>

import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

import { useTeacherStudentStore } from '../../stores/teachers/teacherStudent'
import { clearAuthSessions } from '../../services/apiConfig'


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
    clearAuthSessions()

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
    <main class="min-h-screen bg-paper">
        <!-- HEADER -->
        <header class="border-b border-hairline bg-paper">
            <div class="mx-auto max-w-7xl px-6 py-6 lg:px-8">
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center border border-forest bg-surface text-sm font-semibold text-forest"
                            >
                                MS
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-ink">
                                    Student Portal
                                </p>

                                <p class="text-xs text-ink-soft">
                                    Teacher workspace
                                </p>
                            </div>
                        </div>

                        <div class="mt-7">
                            <p class="text-sm font-medium text-forest">
                                Teaching records
                            </p>

                            <h1
                                class="mt-2 font-serif text-4xl font-medium tracking-tight text-ink sm:text-5xl"
                            >
                                My students
                            </h1>

                            <p class="mt-2 text-sm text-ink-soft">
                                Students assigned to your subjects.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- DARK MODE -->
                        <button
                            @click="toggleDarkMode"
                            class="border border-hairline bg-surface px-3 py-2 text-sm font-medium text-ink-soft hover:border-ink-soft hover:text-ink"
                        >
                            {{ darkMode ? '☀ Light' : '☾ Dark' }}
                        </button>

                        <!-- LOGOUT -->
                        <button
                            @click="logout"
                            class="border border-sienna/50 bg-surface px-4 py-2 text-sm font-medium text-sienna hover:border-sienna hover:bg-paper"
                        >
                            Sign out
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
            <!-- BACK -->
            <button
                @click="goBack"
                class="mb-6 text-sm font-medium text-forest hover:underline"
            >
                Back to dashboard
            </button>

            <!-- TOOLBAR -->
            <section class="border border-hairline bg-surface">
                <div
                    class="flex flex-col gap-5 px-5 py-5 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <h2
                            class="font-serif text-2xl font-medium text-ink"
                        >
                            Student records
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            Search and filter the students assigned to you.
                        </p>
                    </div>

                    <div
                        class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row"
                    >
                        <!-- SEARCH -->
                        <div class="w-full sm:w-72">
                            <label
                                for="student-search"
                                class="mb-2 block text-xs font-medium text-ink-soft"
                            >
                                Search
                            </label>

                            <input
                                id="student-search"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search students..."
                                class="w-full border border-hairline bg-surface px-4 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                            />
                        </div>

                        <!-- STATUS -->
                        <div class="w-full sm:w-40">
                            <label
                                for="status-filter"
                                class="mb-2 block text-xs font-medium text-ink-soft"
                            >
                                Status
                            </label>

                            <select
                                id="status-filter"
                                v-model="statusFilter"
                                class="w-full border border-hairline bg-surface px-4 py-2.5 text-sm text-ink outline-none focus:border-forest focus:ring-1 focus:ring-forest"
                            >
                                <option value="all">
                                    All status
                                </option>

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

                <!-- SUMMARY LEDGER -->
                <div class="grid border-t border-hairline sm:grid-cols-3">
                    <div
                        class="border-b border-hairline px-5 py-4 sm:border-b-0 sm:border-r"
                    >
                        <p class="text-xs text-ink-soft">
                            Students shown
                        </p>

                        <p class="mt-1 font-serif text-2xl text-ink">
                            {{ teacherStudentStore.students.length }}
                        </p>
                    </div>

                    <div
                        class="border-b border-hairline px-5 py-4 sm:border-b-0 sm:border-r"
                    >
                        <p class="text-xs text-ink-soft">
                            Current page
                        </p>

                        <p class="mt-1 font-serif text-2xl text-ink">
                            {{ teacherStudentStore.pagination.currentPage }}
                        </p>
                    </div>

                    <div class="px-5 py-4">
                        <p class="text-xs text-ink-soft">
                            Total students
                        </p>

                        <p class="mt-1 font-serif text-2xl text-ink">
                            {{ teacherStudentStore.pagination.totalStudents }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ERROR -->
            <div
                v-if="teacherStudentStore.error"
                class="mt-6 border border-sienna/30 bg-surface px-4 py-3 text-sm leading-5 text-sienna"
            >
                {{ teacherStudentStore.error }}
            </div>

            <!-- LOADING -->
            <div
                v-if="teacherStudentStore.loading"
                class="mt-6 border border-hairline bg-surface px-6 py-14 text-center text-sm text-ink-soft"
            >
                Loading your students...
            </div>

            <!-- TABLE -->
            <section
                v-else
                class="mt-6 border border-hairline bg-surface"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[950px]">
                        <thead class="border-b border-hairline bg-paper">
                            <tr>
                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Student
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Class
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Symbol no.
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Subjects
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-medium text-ink-soft"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="student in teacherStudentStore.students"
                                :key="student.id"
                                class="border-b border-hairline last:border-b-0 hover:bg-paper/60"
                            >
                                <!-- STUDENT -->
                                <td class="px-5 py-4">
                                    <div
                                        class="font-serif text-lg text-ink"
                                    >
                                        {{ student.name }}
                                    </div>

                                    <div
                                        class="mt-0.5 text-sm text-ink-soft"
                                    >
                                        {{ student.email }}
                                    </div>
                                </td>

                                <!-- CLASS -->
                                <td
                                    class="px-5 py-4 text-sm text-ink"
                                >
                                    {{ student.class }}
                                </td>

                                <!-- SYMBOL -->
                                <td
                                    class="px-5 py-4 text-sm text-ink-soft"
                                >
                                    {{ student.symbol_no || '—' }}
                                </td>

                                <!-- SUBJECTS -->
                                <td class="px-5 py-4">
                                    <div
                                        v-if="student.subjects?.length"
                                        class="divide-y divide-hairline border-y border-hairline"
                                    >
                                        <div
                                            v-for="subject in student.subjects"
                                            :key="`${student.id}-${subject.id}-${subject.name}`"
                                            class="py-1.5 text-sm text-ink"
                                        >
                                            {{ subject.name }}

                                            <span
                                                v-if="subject.code"
                                                class="ml-1 text-xs text-ink-soft"
                                            >
                                                ({{ subject.code }})
                                            </span>
                                        </div>
                                    </div>

                                    <span
                                        v-else
                                        class="text-sm text-ink-soft"
                                    >
                                        —
                                    </span>
                                </td>

                                <!-- STATUS -->
                                <td class="px-5 py-4">
                                    <span
                                        v-if="student.status === 'active'"
                                        class="border border-forest/40 px-2.5 py-1 text-xs font-medium text-forest"
                                    >
                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="border border-sienna/40 px-2.5 py-1 text-xs font-medium text-sienna"
                                    >
                                        Inactive
                                    </span>
                                </td>

                                <!-- ACTION -->
                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    <button
                                        @click="viewStudent(student)"
                                        class="border border-forest bg-surface px-3 py-1.5 text-sm font-medium text-forest hover:bg-paper"
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
                                    class="px-6 py-14 text-center"
                                >
                                    <p
                                        class="font-serif text-2xl text-ink"
                                    >
                                        No students assigned
                                    </p>

                                    <p
                                        class="mt-2 text-sm text-ink-soft"
                                    >
                                        No students match the current
                                        search or status filter.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="
                        teacherStudentStore.pagination.lastPage > 1
                    "
                    class="flex flex-col gap-4 border-t border-hairline px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-sm text-ink-soft">
                        Showing
                        {{ teacherStudentStore.pagination.from }}
                        –
                        {{ teacherStudentStore.pagination.to }}
                        of
                        {{ teacherStudentStore.pagination.totalStudents }}
                        students
                    </div>

                    <div class="flex items-center gap-1">
                        <button
                            @click="
                                goToPage(
                                    teacherStudentStore.pagination.currentPage - 1
                                )
                            "
                            :disabled="
                                teacherStudentStore.pagination.currentPage ===
                                1
                            "
                            class="border border-hairline bg-surface px-3 py-1.5 text-sm text-ink-soft hover:border-ink-soft hover:text-ink disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Previous
                        </button>

                        <span
                            class="px-3 py-1.5 text-sm text-ink-soft"
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
                            class="border border-hairline bg-surface px-3 py-1.5 text-sm text-ink-soft hover:border-ink-soft hover:text-ink disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- ================================================= -->
        <!-- VIEW STUDENT MODAL -->
        <!-- ================================================= -->

        <div
            v-if="showViewModal && selectedStudent"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4 py-6"
            @click.self="closeViewModal"
        >
            <div
                class="max-h-[90vh] w-full max-w-2xl overflow-y-auto border border-hairline bg-surface"
            >
                <!-- MODAL HEADER -->
                <div
                    class="flex items-center justify-between border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p class="text-xs font-medium text-ink-soft">
                            Student record
                        </p>

                        <h2
                            class="mt-2 font-serif text-3xl font-medium text-ink"
                        >
                            {{ selectedStudent.name }}
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            Student details
                        </p>
                    </div>

                    <button
                        @click="closeViewModal"
                        class="text-2xl leading-none text-ink-soft hover:text-ink"
                        aria-label="Close"
                    >
                        ×
                    </button>
                </div>

                <!-- MODAL BODY -->
                <div class="p-6">
                    <!-- BASIC INFORMATION -->
                    <section>
                        <h3
                            class="font-serif text-xl font-medium text-ink"
                        >
                            Basic information
                        </h3>

                        <div
                            class="mt-4 grid grid-cols-1 divide-y divide-hairline border-y border-hairline md:grid-cols-2 md:divide-y-0"
                        >
                            <div
                                class="border-b border-hairline py-4 md:border-r md:pr-5"
                            >
                                <p class="text-xs text-ink-soft">
                                    Email
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-ink"
                                >
                                    {{ selectedStudent.email || '—' }}
                                </p>
                            </div>

                            <div
                                class="border-b border-hairline py-4 md:pl-5"
                            >
                                <p class="text-xs text-ink-soft">
                                    Phone
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-ink"
                                >
                                    {{ selectedStudent.phone || '—' }}
                                </p>
                            </div>

                            <div
                                class="border-b border-hairline py-4 md:border-r md:pr-5"
                            >
                                <p class="text-xs text-ink-soft">
                                    Class
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-ink"
                                >
                                    {{ selectedStudent.class || '—' }}
                                </p>
                            </div>

                            <div
                                class="border-b border-hairline py-4 md:pl-5"
                            >
                                <p class="text-xs text-ink-soft">
                                    Symbol no.
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-ink"
                                >
                                    {{ selectedStudent.symbol_no || '—' }}
                                </p>
                            </div>

                            <div
                                class="border-b border-hairline py-4 md:border-r md:pr-5"
                            >
                                <p class="text-xs text-ink-soft">
                                    Date of birth
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-ink"
                                >
                                    {{
                                        selectedStudent.date_of_birth || '—'
                                    }}
                                </p>
                            </div>

                            <div
                                class="border-b border-hairline py-4 md:pl-5"
                            >
                                <p class="text-xs text-ink-soft">
                                    Status
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-ink"
                                >
                                    {{
                                        selectedStudent.status || '—'
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SUBJECTS -->
                    <section class="mt-8">
                        <h3
                            class="font-serif text-xl font-medium text-ink"
                        >
                            Assigned subjects
                        </h3>

                        <div
                            v-if="selectedStudent.subjects?.length"
                            class="mt-4 divide-y divide-hairline border-y border-hairline"
                        >
                            <div
                                v-for="subject in selectedStudent.subjects"
                                :key="`${subject.id}-${subject.name}`"
                                class="flex items-center justify-between gap-4 py-3"
                            >
                                <span
                                    class="text-sm font-medium text-ink"
                                >
                                    {{ subject.name }}
                                </span>

                                <span
                                    v-if="subject.code"
                                    class="text-xs text-ink-soft"
                                >
                                    {{ subject.code }}
                                </span>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-3 text-sm text-ink-soft"
                        >
                            No subjects assigned.
                        </p>
                    </section>

                    <!-- PARENT -->
                    <section
                        v-if="selectedStudent.parent"
                        class="mt-8"
                    >
                        <h3
                            class="font-serif text-xl font-medium text-ink"
                        >
                            Parent
                        </h3>

                        <div
                            class="mt-4 border border-hairline bg-paper px-4 py-4"
                        >
                            <p class="font-medium text-ink">
                                {{
                                    selectedStudent.parent.name || '—'
                                }}
                            </p>

                            <p class="mt-1 text-sm text-ink-soft">
                                {{
                                    selectedStudent.parent.email || '—'
                                }}
                            </p>
                        </div>
                    </section>
                </div>

                <!-- MODAL FOOTER -->
                <div
                    class="flex justify-end border-t border-hairline px-6 py-4"
                >
                    <button
                        @click="closeViewModal"
                        class="border border-ink bg-ink px-4 py-2.5 text-sm font-medium text-white hover:bg-ink/90"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </main>
</template>

