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
    class: '',
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
        class: '',
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
            class: data.class ?? '',
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
        class: '',
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
                phone: form.value.phone,
                class: form.value.class
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
                class: form.value.class,
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
                                TM
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-ink">
                                    Student Portal
                                </p>

                                <p class="text-xs text-ink-soft">
                                    Administration
                                </p>
                            </div>
                        </div>

                        <div class="mt-7">
                            <p class="text-sm font-medium text-forest">
                                Staff management
                            </p>

                            <h1
                                class="mt-2 text-4xl font-medium tracking-tight text-ink sm:text-5xl"
                            >
                                Manage teachers
                            </h1>

                            <p class="mt-2 text-sm text-ink-soft">
                                Add, edit and manage teachers and their subjects.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">

                        <!-- ADD TEACHER -->
                        <button
                            @click="openAddModal"
                            class="border border-forest bg-forest px-4 py-2 text-sm font-medium text-white hover:bg-forest/90"
                        >
                            + Add teacher
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
            <!-- SUCCESS -->
            <div
                v-if="success"
                class="mb-6 border border-forest/30 bg-surface px-4 py-3 text-sm text-forest"
            >
                {{ success }}
            </div>

            <!-- ERROR -->
            <div
                v-if="error"
                class="mb-6 border border-sienna/30 bg-surface px-4 py-3 text-sm text-sienna"
            >
                {{ error }}
            </div>

            <!-- SEARCH / SUMMARY -->
            <section class="border border-hairline bg-surface">
                <div
                    class="flex flex-col gap-5 px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-2xl font-medium text-ink"
                        >
                            Teachers
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            {{ totalTeachers }}
                            teacher{{ totalTeachers === 1 ? '' : 's' }}
                            found
                        </p>
                    </div>

                    <div class="w-full sm:w-80">
                        <label
                            for="teacher-search"
                            class="mb-2 block text-xs font-medium text-ink-soft"
                        >
                            Search
                        </label>

                        <input
                            id="teacher-search"
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search teachers..."
                            class="w-full border border-hairline bg-surface px-4 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                        />
                    </div>
                </div>

                <!-- LEDGER LINE -->
                <div
                    class="grid border-t border-hairline sm:grid-cols-3"
                >
                    <div class="border-b border-hairline px-5 py-4 sm:border-b-0 sm:border-r">
                        <p class="text-xs text-ink-soft">
                            Total teachers
                        </p>

                        <p class="mt-1 text-2xl text-ink">
                            {{ totalTeachers }}
                        </p>
                    </div>

                    <div class="border-b border-hairline px-5 py-4 sm:border-b-0 sm:border-r">
                        <p class="text-xs text-ink-soft">
                            Current page
                        </p>

                        <p class="mt-1 text-2xl text-ink">
                            {{ currentPage }}
                        </p>
                    </div>

                    <div class="px-5 py-4">
                        <p class="text-xs text-ink-soft">
                            Total pages
                        </p>

                        <p class="mt-1 text-2xl text-ink">
                            {{ lastPage }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- LOADING -->
            <div
                v-if="loading"
                class="mt-6 border border-hairline bg-surface px-6 py-14 text-center text-sm text-ink-soft"
            >
                Loading teachers...
            </div>

            <!-- EMPTY -->
            <div
                v-else-if="teachers.length === 0"
                class="mt-6 border border-hairline bg-surface px-6 py-14 text-center"
            >
                <p class="text-2xl text-ink">
                    No teachers found
                </p>

                <p class="mt-2 text-sm text-ink-soft">
                    There are currently no teachers matching your search.
                </p>

                <button
                    @click="openAddModal"
                    class="mt-6 border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-forest/90"
                >
                    Add teacher
                </button>
            </div>

            <!-- TABLE -->
            <section
                v-else
                class="mt-6 border border-hairline bg-surface"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead class="border-b border-hairline bg-paper">
                            <tr>
                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    S.N.
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Name
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Email
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-medium text-ink-soft"
                                >
                                    Phone
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-medium text-ink-soft">
                                    Class
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-medium text-ink-soft"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(teacher, index) in teachers"
                                :key="teacher.id"
                                class="border-b border-hairline last:border-b-0 hover:bg-paper/60"
                            >
                                <td
                                    class="px-5 py-4 text-sm text-ink-soft"
                                >
                                    {{
                                        (currentPage - 1) * 10 +
                                        index +
                                        1
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="text-lg text-ink"
                                    >
                                        {{ teacher.name }}
                                    </div>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-ink-soft"
                                >
                                    {{ teacher.email }}
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-ink-soft"
                                >
                                    {{ teacher.phone }}
                                </td>

                                <td class="px-5 py-4 text-sm text-ink-soft">
                                    {{ teacher.class ? `Class ${teacher.class}` : 'Not assigned' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <!-- VIEW -->
                                        <button
                                            @click="openViewModal(teacher)"
                                            class="border border-hairline bg-surface px-3 py-1.5 text-sm font-medium text-ink-soft hover:border-ink-soft hover:text-ink"
                                        >
                                            View
                                        </button>

                                        <!-- EDIT -->
                                        <button
                                            @click="openEditModal(teacher)"
                                            class="border border-forest/40 bg-surface px-3 py-1.5 text-sm font-medium text-forest hover:border-forest hover:bg-paper"
                                        >
                                            Edit
                                        </button>

                                        <!-- SUBJECTS -->
                                        <button
                                            @click="openSubjectModal(teacher)"
                                            class="border border-hairline bg-surface px-3 py-1.5 text-sm font-medium text-ink-soft hover:border-forest hover:text-forest"
                                        >
                                            Subjects
                                        </button>

                                        <!-- DELETE -->
                                        <button
                                            @click="removeTeacher(teacher)"
                                            :disabled="deleting"
                                            class="border border-sienna/40 bg-surface px-3 py-1.5 text-sm font-medium text-sienna hover:border-sienna hover:bg-paper disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="lastPage > 1"
                    class="flex flex-col gap-4 border-t border-hairline px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-ink-soft">
                        Page {{ currentPage }} of {{ lastPage }}
                    </p>

                    <div class="flex items-center gap-1">
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="border border-hairline bg-surface px-3 py-1.5 text-sm text-ink-soft hover:border-ink-soft hover:text-ink disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Previous
                        </button>

                        <button
                            v-for="page in pageNumbers"
                            :key="page"
                            @click="goToPage(page)"
                            :class="[
                                'px-3 py-1.5 text-sm',
                                page === currentPage
                                    ? 'border border-forest bg-forest text-white'
                                    : 'border border-hairline bg-surface text-ink-soft hover:border-ink-soft hover:text-ink'
                            ]"
                        >
                            {{ page }}
                        </button>

                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === lastPage"
                            class="border border-hairline bg-surface px-3 py-1.5 text-sm text-ink-soft hover:border-ink-soft hover:text-ink disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- ===================================================== -->
        <!-- ADD / EDIT MODAL -->
        <!-- ===================================================== -->

        <div
            v-if="showFormModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4 py-6"
        >
            <div
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto border border-hairline bg-surface"
            >
                <!-- HEADER -->
                <div class="border-b border-hairline px-6 py-5">
                    <p class="text-xs font-medium text-ink-soft">
                        Teacher record
                    </p>

                    <h2
                        class="mt-2 text-3xl font-medium text-ink"
                    >
                        {{
                            editingTeacher
                                ? 'Edit teacher'
                                : 'Add teacher'
                        }}
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-ink-soft">
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
                    class="space-y-5 p-6"
                >
                    <!-- NAME -->
                    <div>
                        <label
                            for="teacher-name"
                            class="mb-2 block text-xs font-medium text-ink-soft"
                        >
                            Name
                        </label>

                        <input
                            id="teacher-name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Enter teacher name"
                            class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                        />

                        <p
                            v-if="formErrors.name"
                            class="mt-1.5 text-sm text-sienna"
                        >
                            {{ formErrors.name[0] }}
                        </p>
                    </div>

                    <!-- EMAIL -->
                    <div>
                        <label
                            for="teacher-email"
                            class="mb-2 block text-xs font-medium text-ink-soft"
                        >
                            Email
                        </label>

                        <input
                            id="teacher-email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="Enter teacher email"
                            class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                        />

                        <p
                            v-if="formErrors.email"
                            class="mt-1.5 text-sm text-sienna"
                        >
                            {{ formErrors.email[0] }}
                        </p>
                    </div>

                    <!-- PHONE -->
                    <div>
                        <label
                            for="teacher-phone"
                            class="mb-2 block text-xs font-medium text-ink-soft"
                        >
                            Phone
                        </label>

                        <input
                            id="teacher-phone"
                            v-model="form.phone"
                            type="text"
                            required
                            placeholder="Enter teacher phone"
                            class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                        />

                        <p
                            v-if="formErrors.phone"
                            class="mt-1.5 text-sm text-sienna"
                        >
                            {{ formErrors.phone[0] }}
                        </p>
                    </div>

                    <!-- CLASS -->
                    <div>
                        <label for="teacher-class" class="mb-2 block text-xs font-medium text-ink-soft">
                            Assigned class
                        </label>
                        <select
                            id="teacher-class"
                            v-model="form.class"
                            required
                            class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none focus:border-forest focus:ring-1 focus:ring-forest"
                        >
                            <option value="" disabled>Select class</option>
                            <option v-for="classNumber in 10" :key="classNumber" :value="classNumber">
                                Class {{ classNumber }}
                            </option>
                        </select>
                        <p class="mt-1.5 text-xs text-ink-soft">Only one teacher can be assigned to each class from 1 to 10.</p>
                        <p v-if="formErrors.class" class="mt-1.5 text-sm text-sienna">{{ formErrors.class[0] }}</p>
                    </div>

                    <!-- PASSWORD -->
                    <div>
                        <label
                            for="teacher-password"
                            class="mb-2 block text-xs font-medium text-ink-soft"
                        >
                            Password

                            <span
                                v-if="editingTeacher"
                                class="ml-1 font-normal text-ink-soft"
                            >
                                (leave blank to keep current password)
                            </span>
                        </label>

                        <input
                            id="teacher-password"
                            v-model="form.password"
                            type="password"
                            :required="!editingTeacher"
                            autocomplete="new-password"
                            :placeholder="
                                editingTeacher
                                    ? 'Enter new password if changing it'
                                    : 'Enter teacher password'
                            "
                            class="w-full border border-hairline bg-surface px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/50 focus:border-forest focus:ring-1 focus:ring-forest"
                        />

                        <p
                            v-if="formErrors.password"
                            class="mt-1.5 text-sm text-sienna"
                        >
                            {{ formErrors.password[0] }}
                        </p>

                        <p class="mt-1.5 text-xs text-ink-soft">
                            Minimum 8 characters.
                        </p>
                    </div>

                    <!-- ACTIONS -->
                    <div
                        class="flex justify-end gap-3 border-t border-hairline pt-5"
                    >
                        <button
                            type="button"
                            @click="closeFormModal"
                            class="border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink-soft hover:border-ink-soft hover:text-ink"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="saving"
                            class="border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-forest/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                saving
                                    ? 'Saving...'
                                    : 'Save teacher'
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4 py-6"
        >
            <div
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto border border-hairline bg-surface"
            >
                <!-- HEADER -->
                <div
                    class="flex items-center justify-between border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p class="text-xs font-medium text-ink-soft">
                            Staff record
                        </p>

                        <h2
                            class="mt-2 text-3xl font-medium text-ink"
                        >
                            Teacher details
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            Teacher information
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

                <!-- CONTENT -->
                <div class="p-6">
                    <div
                        v-if="!viewingTeacher"
                        class="py-8 text-center text-sm text-ink-soft"
                    >
                        Loading teacher...
                    </div>

                    <div
                        v-else
                        class="divide-y divide-hairline border-y border-hairline"
                    >
                        <!-- NAME -->
                        <div class="py-4">
                            <p class="text-xs font-medium text-ink-soft">
                                Name
                            </p>

                            <p
                                class="mt-1 text-xl text-ink"
                            >
                                {{ viewingTeacher.name }}
                            </p>
                        </div>

                        <!-- EMAIL -->
                        <div class="py-4">
                            <p class="text-xs font-medium text-ink-soft">
                                Email
                            </p>

                            <p class="mt-1 text-sm text-ink">
                                {{ viewingTeacher.email }}
                            </p>
                        </div>

                        <!-- PHONE -->
                        <div class="py-4">
                            <p class="text-xs font-medium text-ink-soft">
                                Phone
                            </p>

                            <p class="mt-1 text-sm text-ink">
                                {{ viewingTeacher.phone }}
                            </p>
                        </div>

                        <!-- SUBJECTS -->
                        <div class="py-4">
                            <p class="text-xs font-medium text-ink-soft">
                                Subjects
                            </p>

                            <div
                                v-if="viewingTeacher.subjects?.length"
                                class="mt-3 divide-y divide-hairline border-y border-hairline"
                            >
                                <div
                                    v-for="subject in viewingTeacher.subjects"
                                    :key="subject.id"
                                    class="flex items-center justify-between py-3"
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
                                class="mt-2 text-sm text-ink-soft"
                            >
                                No subjects assigned.
                            </p>
                        </div>
                    </div>

                    <!-- CLOSE -->
                    <div class="flex justify-end pt-6">
                        <button
                            @click="closeViewModal"
                            class="border border-ink bg-ink px-4 py-2.5 text-sm font-medium text-white hover:bg-ink/90"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- ASSIGN SUBJECTS MODAL -->
        <!-- ===================================================== -->

        <div
            v-if="showSubjectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4 py-6"
        >
            <div
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto border border-hairline bg-surface"
            >
                <!-- HEADER -->
                <div class="border-b border-hairline px-6 py-5">
                    <p class="text-xs font-medium text-ink-soft">
                        Teaching assignments
                    </p>

                    <h2
                        class="mt-2 text-3xl font-medium text-ink"
                    >
                        Assign subjects
                    </h2>

                    <p
                        v-if="assigningTeacher"
                        class="mt-1 text-sm text-ink-soft"
                    >
                        Assign subjects to
                        <span class="font-medium text-ink">
                            {{ assigningTeacher.name }}
                        </span>
                    </p>
                </div>

                <!-- CONTENT -->
                <div class="p-6">
                    <div
                        v-if="loadingSubjects"
                        class="py-8 text-center text-sm text-ink-soft"
                    >
                        Loading subjects...
                    </div>

                    <div
                        v-else-if="allSubjects.length === 0"
                        class="py-8 text-center text-sm text-ink-soft"
                    >
                        No subjects available.
                    </div>

                    <div
                        v-else
                        class="divide-y divide-hairline border-y border-hairline"
                    >
                        <label
                            v-for="subject in allSubjects"
                            :key="subject.id"
                            class="flex cursor-pointer items-center gap-4 py-4 hover:bg-paper/60"
                        >
                            <input
                                type="checkbox"
                                :value="subject.id"
                                v-model="selectedSubjectIds"
                                class="h-4 w-4 border-hairline text-forest focus:ring-forest"
                            />

                            <div class="flex-1">
                                <p
                                    class="font-medium text-ink"
                                >
                                    {{ subject.name }}
                                </p>

                                <p
                                    v-if="subject.code"
                                    class="mt-0.5 text-xs text-ink-soft"
                                >
                                    {{ subject.code }}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- FOOTER -->
                <div
                    class="flex flex-col gap-4 border-t border-hairline px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-ink-soft">
                        {{ selectedSubjectIds.length }}
                        subject{{ selectedSubjectIds.length === 1 ? '' : 's' }}
                        selected
                    </p>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="closeSubjectModal"
                            class="border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink-soft hover:border-ink-soft hover:text-ink"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            @click="saveSubjectAssignments"
                            :disabled="savingSubjects || loadingSubjects"
                            class="border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-forest/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                savingSubjects
                                    ? 'Saving...'
                                    : 'Save subjects'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>