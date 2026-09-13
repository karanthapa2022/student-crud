<script setup>

import { ref, onMounted, watch } from 'vue'
import { useSubjectStore } from '../../stores/subjects/subject'

const subjectStore = useSubjectStore()

// =========================================================
// SEARCH & FILTER
// =========================================================

const searchQuery = ref('')
const teacherFilter = ref('all')

watch(
    [searchQuery, teacherFilter],
    () => {

        subjectStore.fetchSubjects(
            1,
            searchQuery.value,
            teacherFilter.value
        )

    }
)

// =========================================================
// FORM
// =========================================================

const showModal = ref(false)

const editingSubject = ref(null)
const viewingSubject = ref(null)

const form = ref({
    name: '',
    code: '',
    description: ''
})

const errorMessage = ref('')

// =========================================================
// OPEN VIEW MODAL
// =========================================================

const openViewModal = (subject) => {

    viewingSubject.value = subject

}

// =========================================================
// OPEN ADD MODAL
// =========================================================

const openAddModal = () => {

    editingSubject.value = null

    form.value = {
        name: '',
        code: '',
        description: ''
    }

    errorMessage.value = ''

    showModal.value = true

}

// =========================================================
// OPEN EDIT MODAL
// =========================================================

const openEditModal = (subject) => {

    editingSubject.value = subject

    form.value = {
        name: subject.name || '',
        code: subject.code || '',
        description: subject.description || ''
    }

    errorMessage.value = ''

    showModal.value = true

}

// =========================================================
// CLOSE MODAL
// =========================================================

const closeModal = () => {

    showModal.value = false

    editingSubject.value = null

    errorMessage.value = ''

}

// =========================================================
// SAVE SUBJECT
// =========================================================

const saveSubject = async () => {

    errorMessage.value = ''

    try {

        if (editingSubject.value) {

            await subjectStore.editSubject(
                editingSubject.value.id,
                form.value
            )

        } else {

            await subjectStore.addSubject(
                form.value
            )

        }

        closeModal()

    } catch (error) {

        console.error(
            'Save subject error:',
            error
        )

        errorMessage.value =
            error.response?.data?.message ||
            'Something went wrong.'

    }

}

// =========================================================
// DELETE SUBJECT
// =========================================================

const removeSubject = async (id) => {

    if (
        !confirm(
            'Are you sure you want to delete this subject?'
        )
    ) {
        return
    }

    try {

        await subjectStore.removeSubject(id)

    } catch (error) {

        console.error(
            'Delete subject error:',
            error
        )

    }

}

// =========================================================
// PAGINATION
// =========================================================

const changePage = async (page) => {

    if (
        page < 1 ||
        page > subjectStore.pagination.lastPage
    ) {
        return
    }

    await subjectStore.fetchSubjects(
        page,
        searchQuery.value,
        teacherFilter.value
    )

}

// =========================================================
// INITIAL LOAD
// =========================================================

onMounted(() => {

    const savedTheme = localStorage.getItem('theme')

    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }

    subjectStore.fetchSubjects(
        1,
        searchQuery.value,
        teacherFilter.value
    )

})

</script>


<template>
    <main class="min-h-screen bg-paper px-4 py-8 text-ink sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-7xl">

            <!-- =====================================================
                 LETTERHEAD
            ====================================================== -->

            <header class="border-b border-hairline pb-6">
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
                >

                    <div>
                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-10 w-10 items-center justify-center border border-forest bg-surface text-sm font-semibold text-forest"
                            >
                                SB
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-ink">
                                    Student Records
                                </p>

                                <p class="text-xs text-ink-soft">
                                    Academic administration
                                </p>
                            </div>

                        </div>

                        <h1
                            class="mt-6 text-4xl font-medium tracking-tight text-ink sm:text-5xl"
                        >
                            Subjects
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-ink-soft"
                        >
                            Manage subjects, course information, and teacher
                            assignments across the student management system.
                        </p>
                    </div>


                    <!-- HEADER ACTIONS -->

                    <div class="flex flex-col gap-2 sm:flex-row">

                        <button
                            type="button"
                            @click="$router.push('/admin/dashboard')"
                            class="inline-flex w-fit items-center border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                        >
                            ← Dashboard
                        </button>

                        <button
                            type="button"
                            @click="openAddModal"
                            class="inline-flex w-fit items-center border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-forest/90"
                        >
                            <span class="mr-2 text-base leading-none">
                                +
                            </span>

                            Add subject
                        </button>

                    </div>

                </div>
            </header>


            <!-- =====================================================
                 SEARCH & FILTER
            ====================================================== -->

            <section
                class="mt-8 border border-hairline bg-surface"
            >

                <div
                    class="border-b border-hairline px-5 py-5"
                >
                    <h2 class="text-2xl font-medium text-ink">
                        Find subjects
                    </h2>

                    <p class="mt-1 text-sm text-ink-soft">
                        Search by subject name or code and filter by teacher
                        assignment.
                    </p>
                </div>


                <div
                    class="grid gap-5 p-5 md:grid-cols-[1fr_280px]"
                >

                    <!-- SEARCH -->

                    <div>
                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Search
                        </label>

                        <div class="relative">

                            <span
                                class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-ink-soft"
                            >
                                /
                            </span>

                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by subject name or code"
                                class="w-full border border-hairline bg-surface px-3 py-2.5 pl-8 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                            />

                        </div>
                    </div>


                    <!-- TEACHER FILTER -->

                    <div>
                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Teacher assignment
                        </label>

                        <select
                            v-model="teacherFilter"
                            class="w-full border border-hairline bg-surface px-3 py-2.5 text-sm text-ink outline-none focus:border-forest focus:ring-1 focus:ring-forest"
                        >

                            <option value="all">
                                All subjects
                            </option>

                            <option value="with_teacher">
                                With teacher
                            </option>

                            <option value="without_teacher">
                                Without teacher
                            </option>

                        </select>
                    </div>

                </div>

            </section>


            <!-- =====================================================
                 ERROR
            ====================================================== -->

            <div
                v-if="subjectStore.error"
                class="mt-6 border border-sienna/30 bg-surface px-4 py-3"
            >

                <div class="flex items-start gap-3">

                    <span
                        class="text-sm font-semibold text-sienna"
                    >
                        !
                    </span>

                    <p class="text-sm text-sienna">
                        {{ subjectStore.error }}
                    </p>

                </div>

            </div>


            <!-- =====================================================
                 SUBJECT LEDGER
            ====================================================== -->

            <section
                class="mt-6 border border-hairline bg-surface"
            >

                <!-- SECTION HEADER -->

                <div
                    class="flex flex-col gap-2 border-b border-hairline px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
                >

                    <div>
                        <h2 class="text-2xl font-medium text-ink">
                            Subject register
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            Subjects currently registered in the system.
                        </p>
                    </div>

                    <p
                        v-if="!subjectStore.loading"
                        class="text-xs text-ink-soft"
                    >
                        {{ subjectStore.pagination.totalSubjects }}
                        subject{{
                            subjectStore.pagination.totalSubjects === 1
                                ? ''
                                : 's'
                        }}
                    </p>

                </div>


                <!-- TABLE -->

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[850px] text-left">

                        <!-- TABLE HEAD -->

                        <thead>
                            <tr
                                class="border-b border-hairline bg-paper/60"
                            >

                                <th
                                    class="w-20 px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    S.N.
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Subject
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Code
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Description
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Actions
                                </th>

                            </tr>
                        </thead>


                        <tbody>

                            <!-- =================================================
                                 LOADING
                            ================================================== -->

                            <tr v-if="subjectStore.loading">

                                <td
                                    colspan="5"
                                    class="px-5 py-12 text-center"
                                >

                                    <div
                                        class="mx-auto mb-4 h-7 w-7 animate-spin rounded-full border-2 border-hairline border-t-forest"
                                    ></div>

                                    <p class="text-sm text-ink-soft">
                                        Loading subjects...
                                    </p>

                                </td>

                            </tr>


                            <!-- =================================================
                                 EMPTY
                            ================================================== -->

                            <tr
                                v-else-if="
                                    subjectStore.subjects.length === 0
                                "
                            >

                                <td
                                    colspan="5"
                                    class="px-5 py-14 text-center"
                                >

                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center border border-hairline bg-paper text-lg text-forest"
                                    >
                                        —
                                    </div>

                                    <p
                                        class="mt-4 text-sm font-medium text-ink"
                                    >
                                        No subjects found
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-ink-soft"
                                    >
                                        Try changing your search or filter.
                                    </p>

                                </td>

                            </tr>


                            <!-- =================================================
                                 SUBJECT ROWS
                            ================================================== -->

                            <tr
                                v-else
                                v-for="(subject, index) in subjectStore.subjects"
                                :key="subject.id"
                                class="border-b border-hairline last:border-b-0 hover:bg-paper/40"
                            >

                                <!-- S.N. -->

                                <td
                                    class="px-5 py-4 text-sm text-ink-soft"
                                >
                                    {{
                                        (subjectStore.pagination.currentPage - 1)
                                        * subjectStore.pagination.perPage
                                        + index + 1
                                    }}
                                </td>


                                <!-- SUBJECT -->

                                <td class="px-5 py-4">

                                    <p
                                        class="text-lg font-medium text-ink"
                                    >
                                        {{ subject.name }}
                                    </p>

                                </td>


                                <!-- CODE -->

                                <td class="px-5 py-4">

                                    <span
                                        class="inline-flex border border-forest/30 bg-paper px-2.5 py-1 text-xs font-medium text-forest"
                                    >
                                        {{ subject.code }}
                                    </span>

                                </td>


                                <!-- DESCRIPTION -->

                                <td
                                    class="max-w-sm px-5 py-4"
                                >

                                    <p
                                        class="truncate text-sm text-ink-soft"
                                        :title="
                                            subject.description || '-'
                                        "
                                    >
                                        {{ subject.description || '-' }}
                                    </p>

                                </td>


                                <!-- ACTIONS -->

                                <td class="px-5 py-4">

                                    <div
                                        class="flex items-center gap-4"
                                    >

                                        <button
                                            type="button"
                                            @click="openViewModal(subject)"
                                            class="text-sm font-medium text-ink hover:text-forest hover:underline"
                                        >
                                            View
                                        </button>

                                        <button
                                            type="button"
                                            @click="openEditModal(subject)"
                                            class="text-sm font-medium text-forest hover:underline"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            @click="removeSubject(subject.id)"
                                            class="text-sm font-medium text-sienna hover:underline"
                                        >
                                            Delete
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </section>


            <!-- =====================================================
                 PAGINATION
            ====================================================== -->

            <div
                v-if="subjectStore.pagination.lastPage > 1"
                class="flex flex-col gap-4 border-b border-hairline py-5 sm:flex-row sm:items-center sm:justify-between"
            >

                <p class="text-sm text-ink-soft">

                    Showing

                    <span class="font-medium text-ink">
                        {{ subjectStore.pagination.from }}
                    </span>

                    -

                    <span class="font-medium text-ink">
                        {{ subjectStore.pagination.to }}
                    </span>

                    of

                    <span class="font-medium text-ink">
                        {{ subjectStore.pagination.totalSubjects }}
                    </span>

                </p>


                <div class="flex items-center">

                    <button
                        type="button"
                        @click="
                            changePage(
                                subjectStore.pagination.currentPage - 1
                            )
                        "
                        :disabled="
                            subjectStore.pagination.currentPage === 1
                        "
                        class="border border-hairline bg-surface px-3 py-2 text-sm font-medium text-ink hover:border-forest hover:text-forest disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        ← Previous
                    </button>


                    <span
                        class="border-y border-hairline bg-paper px-4 py-2 text-sm text-ink-soft"
                    >
                        Page
                        <span class="font-medium text-ink">
                            {{ subjectStore.pagination.currentPage }}
                        </span>
                        of
                        <span class="font-medium text-ink">
                            {{ subjectStore.pagination.lastPage }}
                        </span>
                    </span>


                    <button
                        type="button"
                        @click="
                            changePage(
                                subjectStore.pagination.currentPage + 1
                            )
                        "
                        :disabled="
                            subjectStore.pagination.currentPage ===
                            subjectStore.pagination.lastPage
                        "
                        class="border border-hairline bg-surface px-3 py-2 text-sm font-medium text-ink hover:border-forest hover:text-forest disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Next →
                    </button>

                </div>

            </div>


            <!-- =====================================================
                 FOOTER NOTE
            ====================================================== -->

            <div class="border-t border-hairline py-5">

                <p class="text-xs leading-5 text-ink-soft">
                    Subjects define the academic structure of the student
                    management system. Keep subject codes consistent and
                    review teacher assignments regularly.
                </p>

            </div>

        </div>


        <!-- =========================================================
             ADD / EDIT MODAL
        ========================================================== -->

        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4 py-6"
        >

            <div
                class="w-full max-w-lg border border-hairline bg-surface"
            >

                <!-- MODAL HEADER -->

                <div
                    class="border-b border-hairline px-6 py-5"
                >

                    <div
                        class="flex items-start justify-between gap-4"
                    >

                        <div>

                            <h2
                                class="text-2xl font-medium text-ink"
                            >
                                {{
                                    editingSubject
                                        ? 'Edit subject'
                                        : 'Add subject'
                                }}
                            </h2>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                {{
                                    editingSubject
                                        ? 'Update subject information.'
                                        : 'Add a new subject to the system.'
                                }}
                            </p>

                        </div>


                        <button
                            type="button"
                            @click="closeModal"
                            class="text-2xl leading-none text-ink-soft hover:text-ink"
                        >
                            ×
                        </button>

                    </div>

                </div>


                <!-- MODAL BODY -->

                <div class="p-6">

                    <!-- ERROR -->

                    <div
                        v-if="errorMessage"
                        class="mb-5 border border-sienna/30 bg-paper px-4 py-3"
                    >

                        <div class="flex items-start gap-3">

                            <span
                                class="font-semibold text-sienna"
                            >
                                !
                            </span>

                            <p class="text-sm text-sienna">
                                {{ errorMessage }}
                            </p>

                        </div>

                    </div>


                    <!-- NAME -->

                    <div class="mb-5">

                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Subject name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Enter subject name"
                            class="w-full border border-hairline bg-surface px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                        />

                    </div>


                    <!-- CODE -->

                    <div class="mb-5">

                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Subject code
                        </label>

                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="Enter subject code"
                            class="w-full border border-hairline bg-surface px-3 py-2.5 text-sm uppercase text-ink outline-none placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                        />

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="mb-6">

                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Description
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Enter subject description"
                            class="w-full resize-none border border-hairline bg-surface px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                        ></textarea>

                    </div>


                    <!-- ACTIONS -->

                    <div
                        class="flex flex-col-reverse gap-2 border-t border-hairline pt-5 sm:flex-row sm:justify-end"
                    >

                        <button
                            type="button"
                            @click="closeModal"
                            class="border border-hairline bg-surface px-5 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                        >
                            Cancel
                        </button>


                        <button
                            type="button"
                            @click="saveSubject"
                            :disabled="subjectStore.loading"
                            class="border border-forest bg-forest px-5 py-2.5 text-sm font-medium text-white hover:bg-forest/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                editingSubject
                                    ? 'Update subject'
                                    : 'Add subject'
                            }}
                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================================================
             VIEW SUBJECT MODAL
        ========================================================== -->

        <div
            v-if="viewingSubject"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4 py-6"
        >

            <div
                class="w-full max-w-lg border border-hairline bg-surface"
            >

                <!-- MODAL HEADER -->

                <div
                    class="border-b border-hairline px-6 py-5"
                >

                    <div
                        class="flex items-start justify-between gap-4"
                    >

                        <div>

                            <h2
                                class="text-2xl font-medium text-ink"
                            >
                                Subject details
                            </h2>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                Subject and teacher assignment information.
                            </p>

                        </div>


                        <button
                            type="button"
                            @click="viewingSubject = null"
                            class="text-2xl leading-none text-ink-soft hover:text-ink"
                        >
                            ×
                        </button>

                    </div>

                </div>


                <!-- MODAL BODY -->

                <div class="p-6">

                    <!-- SUBJECT -->

                    <div
                        class="border-b border-hairline pb-5"
                    >

                        <p
                            class="text-xs font-medium text-ink-soft"
                        >
                            Subject name
                        </p>

                        <p
                            class="mt-1 text-xl font-medium text-ink"
                        >
                            {{ viewingSubject.name }}
                        </p>

                    </div>


                    <!-- CODE -->

                    <div
                        class="border-b border-hairline py-5"
                    >

                        <p
                            class="text-xs font-medium text-ink-soft"
                        >
                            Subject code
                        </p>

                        <span
                            class="mt-2 inline-flex border border-forest/30 bg-paper px-2.5 py-1 text-xs font-medium text-forest"
                        >
                            {{ viewingSubject.code }}
                        </span>

                    </div>


                    <!-- DESCRIPTION -->

                    <div
                        class="border-b border-hairline py-5"
                    >

                        <p
                            class="text-xs font-medium text-ink-soft"
                        >
                            Description
                        </p>

                        <p
                            class="mt-2 text-sm leading-6 text-ink-soft"
                        >
                            {{ viewingSubject.description || '-' }}
                        </p>

                    </div>


                    <!-- TEACHERS -->

                    <div class="pt-5">

                        <div
                            class="flex items-center justify-between"
                        >

                            <p
                                class="text-sm font-medium text-ink"
                            >
                                Teacher assignment
                            </p>

                            <span
                                class="text-xs text-ink-soft"
                            >
                                {{
                                    viewingSubject.teachers?.length || 0
                                }}
                                assigned
                            </span>

                        </div>


                        <!-- TEACHERS -->

                        <div
                            v-if="
                                viewingSubject.teachers &&
                                viewingSubject.teachers.length > 0
                            "
                            class="mt-4 divide-y divide-hairline border-y border-hairline"
                        >

                            <div
                                v-for="teacher in viewingSubject.teachers"
                                :key="teacher.id"
                                class="py-4"
                            >

                                <p
                                    class="text-sm font-medium text-ink"
                                >
                                    {{ teacher.name }}
                                </p>

                                <p
                                    class="mt-1 text-sm text-ink-soft"
                                >
                                    {{ teacher.email }}
                                </p>

                                <p
                                    v-if="teacher.phone"
                                    class="mt-0.5 text-sm text-ink-soft"
                                >
                                    {{ teacher.phone }}
                                </p>

                            </div>

                        </div>


                        <!-- NO TEACHER -->

                        <div
                            v-else
                            class="mt-4 border border-hairline bg-paper px-4 py-4"
                        >

                            <p
                                class="text-sm text-ink-soft"
                            >
                                No teacher assigned.
                            </p>

                        </div>

                    </div>


                    <!-- CLOSE -->

                    <div
                        class="mt-6 flex justify-end border-t border-hairline pt-5"
                    >

                        <button
                            type="button"
                            @click="viewingSubject = null"
                            class="border border-hairline bg-surface px-5 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                        >
                            Close
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </main>
</template>