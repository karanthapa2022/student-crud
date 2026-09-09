<script setup>

import { ref, computed, onMounted } from 'vue'

import {
    getTeachers,
    getTeacher,
    createTeacher,
    updateTeacher,
    deleteTeacher,
    getSubjects,
    assignTeacherSubjects
} from '../../services/teachers/teacherApi'


// =========================================================
// STATE
// =========================================================

const teachers = ref([])

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)

const error = ref('')
const success = ref('')


// =========================================================
// SEARCH
// =========================================================

const searchQuery = ref('')


// =========================================================
// PAGINATION
// =========================================================

const currentPage = ref(1)
const lastPage = ref(1)
const totalTeachers = ref(0)


// =========================================================
// MODALS
// =========================================================

const showFormModal = ref(false)
const showViewModal = ref(false)

const editingTeacher = ref(null)
const viewingTeacher = ref(null)

// =========================================================
// SUBJECT ASSIGNMENT
// =========================================================

const showSubjectModal = ref(false)

const assigningTeacher = ref(null)

const allSubjects = ref([])

const selectedSubjectIds = ref([])

const loadingSubjects = ref(false)

const savingSubjects = ref(false)

// =========================================================
// FORM
// =========================================================

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: ''
})


// =========================================================
// FORM ERRORS
// =========================================================

const formErrors = ref({})


// =========================================================
// DARK MODE
// =========================================================

const darkMode = ref(
    localStorage.getItem('theme') === 'dark'
)

const applyTheme = () => {

    if (darkMode.value) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }

    localStorage.setItem(
        'theme',
        darkMode.value ? 'dark' : 'light'
    )
}

const toggleDarkMode = () => {

    darkMode.value = !darkMode.value

    applyTheme()
}


// =========================================================
// FETCH TEACHERS
// =========================================================

const fetchTeachers = async (page = 1) => {

    loading.value = true
    error.value = ''

    try {

        const response = await getTeachers(
            page,
            searchQuery.value
        )

        teachers.value =
            response.data.data ?? []

        currentPage.value =
            response.data.current_page ?? 1

        lastPage.value =
            response.data.last_page ?? 1

        totalTeachers.value =
            response.data.total ?? 0

    } catch (err) {

        console.error(
            'Fetch teachers error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Unable to load teachers.'

    } finally {

        loading.value = false

    }
}


// =========================================================
// SEARCH
// =========================================================

let searchTimeout = null

const handleSearch = () => {

    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {

        currentPage.value = 1

        fetchTeachers(1)

    }, 400)
}


// =========================================================
// OPEN ADD MODAL
// =========================================================

const openAddModal = () => {

    editingTeacher.value = null

    form.value = {
        name: '',
        email: '',
        phone: '',
        password: ''
    }

    formErrors.value = {}

    error.value = ''
    success.value = ''

    showFormModal.value = true
}


// =========================================================
// OPEN EDIT MODAL
// =========================================================

const openEditModal = async (teacher) => {

    editingTeacher.value = teacher

    formErrors.value = {}
    error.value = ''
    success.value = ''

    try {

        const response = await getTeacher(
            teacher.id
        )

        const data = response.data

        form.value = {
            name: data.name ?? '',
            email: data.email ?? '',
            phone: data.phone ?? '',
            password: ''
        }

        showFormModal.value = true

    } catch (err) {

        console.error(
            'Get teacher error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Unable to load teacher.'

    }
}


// =========================================================
// CLOSE FORM MODAL
// =========================================================

const closeFormModal = () => {

    showFormModal.value = false

    editingTeacher.value = null

    form.value = {
        name: '',
        email: '',
        phone: '',
        password: ''
    }

    formErrors.value = {}
}


// =========================================================
// SAVE TEACHER
// =========================================================

const saveTeacher = async () => {

    saving.value = true

    error.value = ''
    success.value = ''
    formErrors.value = {}

    try {

        let response

        // =================================================
        // EDIT TEACHER
        // =================================================

        if (editingTeacher.value) {

            const teacherData = {
                name: form.value.name,
                email: form.value.email,
                phone: form.value.phone
            }

            /*
             * Password is optional when editing.
             *
             * If admin enters a password,
             * it will change the teacher's login password.
             */
            if (
                form.value.password &&
                form.value.password.trim()
            ) {

                teacherData.password =
                    form.value.password

            }

            response = await updateTeacher(
                editingTeacher.value.id,
                teacherData
            )

        }

        // =================================================
        // CREATE TEACHER
        // =================================================

        else {

            response = await createTeacher({
                name: form.value.name,
                email: form.value.email,
                phone: form.value.phone,
                password: form.value.password
            })

        }


        // =================================================
        // SUCCESS
        // =================================================

        success.value =
            response.data.message ||
            (
                editingTeacher.value
                    ? 'Teacher updated successfully.'
                    : 'Teacher created successfully.'
            )

        closeFormModal()

        await fetchTeachers(
            currentPage.value
        )

    } catch (err) {

        console.error(
            'Save teacher error:',
            err
        )

        if (
            err.response?.status === 422 &&
            err.response?.data?.errors
        ) {

            formErrors.value =
                err.response.data.errors

        } else {

            error.value =
                err.response?.data?.message ||
                'Unable to save teacher.'

        }

    } finally {

        saving.value = false

    }
}


// =========================================================
// VIEW TEACHER
// =========================================================

const openViewModal = async (teacher) => {

    viewingTeacher.value = null

    error.value = ''

    showViewModal.value = true

    try {

        const response = await getTeacher(
            teacher.id
        )

        viewingTeacher.value =
            response.data

    } catch (err) {

        console.error(
            'View teacher error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Unable to load teacher.'

        showViewModal.value = false

    }
}


// =========================================================
// CLOSE VIEW MODAL
// =========================================================

const closeViewModal = () => {

    showViewModal.value = false

    viewingTeacher.value = null
}


// =========================================================
// OPEN ASSIGN SUBJECTS MODAL
// =========================================================

const openSubjectModal = async (teacher) => {

    assigningTeacher.value = teacher

    selectedSubjectIds.value = []

    error.value = ''

    showSubjectModal.value = true

    loadingSubjects.value = true

    try {

        const [subjectsResponse, teacherResponse] = await Promise.all([
            getSubjects(),
            getTeacher(teacher.id)
        ])

        allSubjects.value =
            subjectsResponse.data.data ??
            subjectsResponse.data ??
            []

        selectedSubjectIds.value =
            teacherResponse.data.subjects?.map(
                subject => subject.id
            ) ?? []

    } catch (err) {

        console.error(
            'Load subject assignment error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Unable to load subjects.'

        showSubjectModal.value = false

    } finally {

        loadingSubjects.value = false

    }
}


// =========================================================
// CLOSE SUBJECT MODAL
// =========================================================

const closeSubjectModal = () => {

    showSubjectModal.value = false

    assigningTeacher.value = null

    selectedSubjectIds.value = []

}


// =========================================================
// SAVE SUBJECT ASSIGNMENTS
// =========================================================

const saveSubjectAssignments = async () => {

    if (!assigningTeacher.value) {
        return
    }

    savingSubjects.value = true

    error.value = ''
    success.value = ''

    try {

        const response = await assignTeacherSubjects(
            assigningTeacher.value.id,
            selectedSubjectIds.value
        )

        success.value =
            response.data.message ||
            'Teacher subjects updated successfully.'

        closeSubjectModal()

        await fetchTeachers(
            currentPage.value
        )

    } catch (err) {

        console.error(
            'Assign subjects error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Unable to update teacher subjects.'

    } finally {

        savingSubjects.value = false

    }
}

// =========================================================
// DELETE TEACHER
// =========================================================

const removeTeacher = async (teacher) => {

    if (
        !confirm(
            `Are you sure you want to delete ${teacher.name}?`
        )
    ) {
        return
    }

    deleting.value = true

    error.value = ''
    success.value = ''

    try {

        const response = await deleteTeacher(
            teacher.id
        )

        success.value =
            response.data.message ||
            'Teacher deleted successfully.'

        if (
            teachers.value.length === 1 &&
            currentPage.value > 1
        ) {

            currentPage.value--

        }

        await fetchTeachers(
            currentPage.value
        )

    } catch (err) {

        console.error(
            'Delete teacher error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Unable to delete teacher.'

    } finally {

        deleting.value = false

    }
}


// =========================================================
// PAGINATION
// =========================================================

const goToPage = (page) => {

    if (
        page < 1 ||
        page > lastPage.value ||
        page === currentPage.value
    ) {
        return
    }

    fetchTeachers(page)
}


// =========================================================
// PAGE NUMBERS
// =========================================================

const pageNumbers = computed(() => {

    const pages = []

    const start = Math.max(
        1,
        currentPage.value - 2
    )

    const end = Math.min(
        lastPage.value,
        currentPage.value + 2
    )

    for (
        let page = start;
        page <= end;
        page++
    ) {

        pages.push(page)

    }

    return pages
})


// =========================================================
// INITIAL LOAD
// =========================================================

onMounted(() => {

    applyTheme()

    fetchTeachers()

})

</script>


<template>

    <div
        class="min-h-screen bg-gray-100 dark:bg-gray-950 transition-colors"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div
            class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800"
        >

            <div class="max-w-7xl mx-auto px-6 py-5">

                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >

                    <div>

                        <h1
                            class="text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            Manage Teachers
                        </h1>

                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Add, edit and manage teachers
                        </p>

                    </div>


                    <div class="flex items-center gap-3">

                        <!-- DARK MODE -->

                        <button
                            @click="toggleDarkMode"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            {{ darkMode ? '☀️ Light' : '🌙 Dark' }}
                        </button>


                        <!-- ADD TEACHER -->

                        <button
                            @click="openAddModal"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition"
                        >
                            + Add Teacher
                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- MAIN -->
        <!-- ================================================= -->

        <main class="max-w-7xl mx-auto px-6 py-8">


            <!-- SUCCESS -->

            <div
                v-if="success"
                class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700 dark:border-green-900 dark:bg-green-950/30 dark:text-green-400"
            >
                {{ success }}
            </div>


            <!-- ERROR -->

            <div
                v-if="error"
                class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400"
            >
                {{ error }}
            </div>


            <!-- ================================================= -->
            <!-- SEARCH / INFO -->
            <!-- ================================================= -->

            <div
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 mb-6"
            >

                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >

                    <div>

                        <h2
                            class="font-semibold text-gray-900 dark:text-white"
                        >
                            Teachers
                        </h2>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                        >
                            {{ totalTeachers }}
                            teacher{{ totalTeachers === 1 ? '' : 's' }}
                            found
                        </p>

                    </div>


                    <div class="w-full sm:w-80">

                        <input
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search teachers..."
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- LOADING -->
            <!-- ================================================= -->

            <div
                v-if="loading"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl py-12 text-center text-gray-500 dark:text-gray-400"
            >
                Loading teachers...
            </div>


            <!-- ================================================= -->
            <!-- EMPTY -->
            <!-- ================================================= -->

            <div
                v-else-if="teachers.length === 0"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl py-12 text-center"
            >

                <p
                    class="text-gray-500 dark:text-gray-400"
                >
                    No teachers found.
                </p>

                <button
                    @click="openAddModal"
                    class="mt-4 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition"
                >
                    Add Teacher
                </button>

            </div>


            <!-- ================================================= -->
            <!-- TABLE -->
            <!-- ================================================= -->

            <div
                v-else
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm"
            >

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead
                            class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800"
                        >

                            <tr>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    #
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    Name
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    Email
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    Phone
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody
                            class="divide-y divide-gray-200 dark:divide-gray-800"
                        >

                            <tr
                                v-for="(teacher, index) in teachers"
                                :key="teacher.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
                            >

                                <td
                                    class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{
                                        (currentPage - 1) * 10 +
                                        index +
                                        1
                                    }}
                                </td>


                                <td class="px-6 py-4">

                                    <div
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ teacher.name }}
                                    </div>

                                </td>


                                <td
                                    class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                                >
                                    {{ teacher.email }}
                                </td>


                                <td
                                    class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                                >
                                    {{ teacher.phone }}
                                </td>


                                <td class="px-6 py-4">

                                    <div
                                        class="flex justify-end gap-2"
                                    >

                                        <!-- VIEW -->

                                        <button
                                            @click="openViewModal(teacher)"
                                            class="px-3 py-1.5 text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                                        >
                                            View
                                        </button>


                                        <!-- EDIT -->

                                        <button
                                            @click="openEditModal(teacher)"
                                            class="px-3 py-1.5 text-sm font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition"
                                        >
                                            Edit
                                        </button>

                                        <!-- ASSIGN SUBJECTS -->

<button
    @click="openSubjectModal(teacher)"
    class="px-3 py-1.5 text-sm font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition"
>
    Subjects
</button>


                                        <!-- DELETE -->

                                        <button
                                            @click="removeTeacher(teacher)"
                                            :disabled="deleting"
                                            class="px-3 py-1.5 text-sm font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition disabled:opacity-50"
                                        >
                                            Delete
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- ================================================= -->
                <!-- PAGINATION -->
                <!-- ================================================= -->

                <div
                    v-if="lastPage > 1"
                    class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        Page {{ currentPage }} of {{ lastPage }}
                    </p>


                    <div
                        class="flex items-center gap-1"
                    >

                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40"
                        >
                            Previous
                        </button>


                        <button
                            v-for="page in pageNumbers"
                            :key="page"
                            @click="goToPage(page)"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-sm',
                                page === currentPage
                                    ? 'bg-purple-600 text-white'
                                    : 'border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800'
                            ]"
                        >
                            {{ page }}
                        </button>


                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === lastPage"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40"
                        >
                            Next
                        </button>

                    </div>

                </div>

            </div>


        </main>


        <!-- ===================================================== -->
        <!-- ADD / EDIT MODAL -->
        <!-- ===================================================== -->

        <div
            v-if="showFormModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        >

            <div
                class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800"
            >

                <!-- MODAL HEADER -->

                <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-800"
                >

                    <h2
                        class="text-xl font-bold text-gray-900 dark:text-white"
                    >
                        {{
                            editingTeacher
                                ? 'Edit Teacher'
                                : 'Add Teacher'
                        }}
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                    >
                        {{
                            editingTeacher
                                ? 'Update teacher information.'
                                : 'Enter teacher information and create login credentials.'
                        }}
                    </p>

                </div>


                <!-- FORM -->

                <form
                    @submit.prevent="saveTeacher"
                    class="p-6 space-y-5"
                >

                    <!-- NAME -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter teacher name"
                        />

                        <p
                            v-if="formErrors.name"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ formErrors.name[0] }}
                        </p>

                    </div>


                    <!-- EMAIL -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter teacher email"
                        />

                        <p
                            v-if="formErrors.email"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ formErrors.email[0] }}
                        </p>

                    </div>


                    <!-- PHONE -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Phone
                        </label>

                        <input
                            v-model="form.phone"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter teacher phone"
                        />

                        <p
                            v-if="formErrors.phone"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ formErrors.phone[0] }}
                        </p>

                    </div>


                    <!-- PASSWORD -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Password

                            <span
                                v-if="editingTeacher"
                                class="text-xs font-normal text-gray-500 dark:text-gray-400"
                            >
                                (leave blank to keep current password)
                            </span>
                        </label>

                        <input
                            v-model="form.password"
                            type="password"
                            :required="!editingTeacher"
                            autocomplete="new-password"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                            :placeholder="
                                editingTeacher
                                    ? 'Enter new password if changing it'
                                    : 'Enter teacher password'
                            "
                        />

                        <p
                            v-if="formErrors.password"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ formErrors.password[0] }}
                        </p>

                        <p
                            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                        >
                            Minimum 8 characters.
                        </p>

                    </div>


                    <!-- ACTIONS -->

                    <div
                        class="flex justify-end gap-3 pt-2"
                    >

                        <button
                            type="button"
                            @click="closeFormModal"
                            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="saving"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition disabled:opacity-50"
                        >
                            {{
                                saving
                                    ? 'Saving...'
                                    : 'Save Teacher'
                            }}
                        </button>

                    </div>

                </form>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- VIEW MODAL -->
        <!-- ===================================================== -->

        <div
            v-if="showViewModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        >

            <div
                class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800"
            >

                <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between"
                >

                    <div>

                        <h2
                            class="text-xl font-bold text-gray-900 dark:text-white"
                        >
                            Teacher Details
                        </h2>

                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Teacher information
                        </p>

                    </div>

                    <button
                        @click="closeViewModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl"
                    >
                        ×
                    </button>

                </div>


                <div class="p-6">

                    <div
                        v-if="!viewingTeacher"
                        class="text-center py-8 text-gray-500 dark:text-gray-400"
                    >
                        Loading teacher...
                    </div>


                    <div
                        v-else
                        class="space-y-5"
                    >

                        <!-- NAME -->

                        <div>

                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Name
                            </p>

                            <p
                                class="mt-1 text-base font-medium text-gray-900 dark:text-white"
                            >
                                {{ viewingTeacher.name }}
                            </p>

                        </div>


                        <!-- EMAIL -->

                        <div>

                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Email
                            </p>

                            <p
                                class="mt-1 text-base text-gray-700 dark:text-gray-300"
                            >
                                {{ viewingTeacher.email }}
                            </p>

                        </div>


                        <!-- PHONE -->

                        <div>

                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Phone
                            </p>

                            <p
                                class="mt-1 text-base text-gray-700 dark:text-gray-300"
                            >
                                {{ viewingTeacher.phone }}
                            </p>

                        </div>


                        <!-- SUBJECTS -->

                        <div>

                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Subjects
                            </p>

                            <div
                                v-if="viewingTeacher.subjects?.length"
                                class="mt-2 flex flex-wrap gap-2"
                            >

                                <span
                                    v-for="subject in viewingTeacher.subjects"
                                    :key="subject.id"
                                    class="px-3 py-1 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-sm"
                                >
                                    {{ subject.name }}
                                </span>

                            </div>


                            <p
                                v-else
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                No subjects assigned.
                            </p>

                        </div>


                        <!-- CLOSE -->

                        <div
                            class="flex justify-end pt-2"
                        >

                            <button
                                @click="closeViewModal"
                                class="px-4 py-2 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-sm font-semibold hover:bg-gray-800 dark:hover:bg-gray-600 transition"
                            >
                                Close
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>
        <!-- ===================================================== -->
<!-- ASSIGN SUBJECTS MODAL -->
<!-- ===================================================== -->

<div
    v-if="showSubjectModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
>

    <div
        class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800"
    >

        <!-- HEADER -->

        <div
            class="px-6 py-5 border-b border-gray-200 dark:border-gray-800"
        >

            <h2
                class="text-xl font-bold text-gray-900 dark:text-white"
            >
                Assign Subjects
            </h2>

            <p
                v-if="assigningTeacher"
                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
            >
                Assign subjects to
                <span class="font-semibold">
                    {{ assigningTeacher.name }}
                </span>
            </p>

        </div>


        <!-- CONTENT -->

        <div class="p-6">

            <div
                v-if="loadingSubjects"
                class="py-8 text-center text-gray-500 dark:text-gray-400"
            >
                Loading subjects...
            </div>


            <div
                v-else-if="allSubjects.length === 0"
                class="py-8 text-center text-gray-500 dark:text-gray-400"
            >
                No subjects available.
            </div>


            <div
                v-else
                class="space-y-3"
            >

                <label
                    v-for="subject in allSubjects"
                    :key="subject.id"
                    class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition"
                >

                    <input
                        type="checkbox"
                        :value="subject.id"
                        v-model="selectedSubjectIds"
                        class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                    />

                    <div class="flex-1">

                        <p
                            class="font-medium text-gray-900 dark:text-white"
                        >
                            {{ subject.name }}
                        </p>

                        <p
                            v-if="subject.code"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ subject.code }}
                        </p>

                    </div>

                </label>

            </div>

        </div>


        <!-- FOOTER -->

        <div
            class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 flex justify-between items-center"
        >

            <p
                class="text-sm text-gray-500 dark:text-gray-400"
            >
                {{ selectedSubjectIds.length }}
                subject{{ selectedSubjectIds.length === 1 ? '' : 's' }}
                selected
            </p>


            <div class="flex gap-3">

                <button
                    type="button"
                    @click="closeSubjectModal"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    @click="saveSubjectAssignments"
                    :disabled="savingSubjects || loadingSubjects"
                    class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition disabled:opacity-50"
                >
                    {{
                        savingSubjects
                            ? 'Saving...'
                            : 'Save Subjects'
                    }}
                </button>

            </div>

        </div>

    </div>

</div>

    </div>

</template>
