<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { API_BASE_URL, clearAuthSessions } from '../../services/apiConfig'

const router = useRouter()

// =========================================================
// STATE
// =========================================================

const teacher = ref(null)
const subjects = ref([])

const loading = ref(false)
const error = ref('')

const selectedSubject = ref(null)
const showModal = ref(false)


// =========================================================
// API
// =========================================================

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        Accept: 'application/json'
    }
})


// =========================================================
// AUTH
// =========================================================

const getTeacherToken = () => {
    return localStorage.getItem('teacher_token')
}


// =========================================================
// FETCH SUBJECTS
// =========================================================

const fetchSubjects = async () => {

    loading.value = true
    error.value = ''

    const token = getTeacherToken()

    if (!token) {
        router.push('/login')
        return
    }

    try {

        const response = await api.get(
            '/teacher/subjects',
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        teacher.value = response.data.teacher || null

        subjects.value =
            response.data.subjects || []

    } catch (err) {

        console.error(
            'Teacher subjects error:',
            err
        )

        if (err.response?.status === 401) {

            clearAuthSessions()

            router.push('/login')
            return
        }

        error.value =
            err.response?.data?.message ||
            'Unable to load your subjects.'

    } finally {

        loading.value = false

    }
}


// =========================================================
// VIEW SUBJECT
// =========================================================

const viewSubject = async (subject) => {

    const token = getTeacherToken()

    if (!token) {
        router.push('/login')
        return
    }

    try {

        const response = await api.get(
            `/teacher/subjects/${subject.id}`,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        selectedSubject.value =
            response.data

        showModal.value = true

    } catch (err) {

        console.error(
            'Subject details error:',
            err
        )

        if (err.response?.status === 401) {

            clearAuthSessions()

            router.push('/login')
            return
        }

        error.value =
            err.response?.data?.message ||
            'Unable to load subject details.'
    }
}


// =========================================================
// CLOSE MODAL
// =========================================================

const closeModal = () => {

    showModal.value = false
    selectedSubject.value = null

}


// =========================================================
// LOGOUT
// =========================================================

const logout = async () => {

    const token =
        localStorage.getItem('teacher_token')

    try {

        if (token) {

            await api.post(
                '/logout',
                {},
                {
                    headers: {
                        Authorization:
                            `Bearer ${token}`
                    }
                }
            )

        }

    } catch (err) {

        console.error(
            'Logout error:',
            err
        )

    }

    clearAuthSessions()

    router.push('/login')
}


// =========================================================
// ON MOUNT
// =========================================================

onMounted(() => {
    fetchSubjects()
})

</script>


<template>
    <div class="min-h-screen bg-paper text-ink">
        <!-- HEADER -->
        <header class="border-b border-hairline">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-5 px-6 py-6 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <button
                        @click="router.push('/dashboard')"
                        class="mb-3 text-sm font-medium text-forest hover:underline"
                    >
                        ← Back to Dashboard
                    </button>

                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center border border-hairline bg-surface text-xl"
                        >
                            📚
                        </div>

                        <div>
                            <h1
                                class="font-serif text-4xl font-medium tracking-tight text-ink"
                            >
                                My Subjects
                            </h1>

                            <p
                                v-if="teacher"
                                class="mt-1 text-sm text-ink-soft"
                            >
                                {{ teacher.name }}
                            </p>
                        </div>
                    </div>
                </div>

                <button
                    @click="logout"
                    class="border border-sienna px-5 py-2.5 text-sm font-medium text-sienna hover:bg-sienna hover:text-white"
                >
                    Logout
                </button>
            </div>
        </header>

        <!-- MAIN -->
        <main class="mx-auto max-w-7xl px-6 py-8">
            <!-- ERROR -->
            <div
                v-if="error"
                class="mb-6 border border-sienna bg-surface px-5 py-4 text-sm text-sienna"
            >
                <div class="flex items-start gap-3">
                    <span class="font-semibold">Error</span>
                    <span>{{ error }}</span>
                </div>
            </div>

            <!-- LOADING -->
            <div
                v-if="loading"
                class="border-y border-hairline py-16 text-center"
            >
                <p class="text-sm text-ink-soft">
                    Loading your subjects...
                </p>
            </div>

            <!-- EMPTY -->
            <div
                v-else-if="subjects.length === 0"
                class="border border-hairline bg-surface"
            >
                <div class="px-6 py-16 text-center">
                    <div
                        class="mx-auto mb-5 flex h-14 w-14 items-center justify-center border border-hairline text-2xl"
                    >
                        📚
                    </div>

                    <h2
                        class="font-serif text-2xl font-medium text-ink"
                    >
                        No Subjects Assigned
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-ink-soft">
                        You currently don't have any subjects assigned to you.
                    </p>
                </div>
            </div>

            <!-- SUBJECTS -->
            <div v-else>
                <!-- SECTION HEADING -->
                <div
                    class="mb-4 flex items-end justify-between border-b border-hairline pb-3"
                >
                    <div>
                        <h2
                            class="font-serif text-2xl font-medium text-ink"
                        >
                            Assigned Subjects
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            Your current teaching assignments
                        </p>
                    </div>

                    <div
                        class="text-sm font-medium text-ink-soft"
                    >
                        {{ subjects.length }} subject<span
                            v-if="subjects.length !== 1"
                        >s</span>
                    </div>
                </div>

                <!-- SUBJECT GRID -->
                <div
                    class="grid grid-cols-1 gap-px border border-hairline bg-hairline md:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="subject in subjects"
                        :key="subject.id"
                        class="group flex min-h-[260px] flex-col bg-surface p-6"
                    >
                        <!-- SUBJECT MARK -->
                        <div
                            class="mb-6 flex items-start justify-between gap-4"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center border border-hairline bg-paper text-lg"
                            >
                                📚
                            </div>

                            <span
                                class="border border-hairline px-2.5 py-1 text-xs font-medium text-ink-soft"
                            >
                                {{ subject.code }}
                            </span>
                        </div>

                        <!-- NAME -->
                        <h3
                            class="font-serif text-2xl font-medium leading-tight text-ink"
                        >
                            {{ subject.name }}
                        </h3>

                        <!-- DESCRIPTION -->
                        <p
                            class="mt-3 line-clamp-3 text-sm leading-6 text-ink-soft"
                        >
                            {{
                                subject.description ||
                                'No description available.'
                            }}
                        </p>

                        <!-- FOOTER -->
                        <div
                            class="mt-auto flex items-end justify-between border-t border-hairline pt-5"
                        >
                            <div>
                                <p class="text-xs text-ink-soft">
                                    Students
                                </p>

                                <p
                                    class="mt-1 font-serif text-2xl font-medium text-ink"
                                >
                                    {{ subject.students_count ?? 0 }}
                                </p>
                            </div>

                            <button
                                @click="viewSubject(subject)"
                                class="border border-forest px-4 py-2 text-sm font-medium text-forest hover:bg-forest hover:text-white"
                            >
                                View
                            </button>
                        </div>
                    </article>
                </div>
            </div>
        </main>

        <!-- SUBJECT DETAILS MODAL -->
        <div
            v-if="showModal && selectedSubject"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4"
            @click.self="closeModal"
        >
            <div
                class="max-h-[90vh] w-full max-w-4xl overflow-y-auto border border-hairline bg-surface"
            >
                <!-- MODAL HEADER -->
                <div
                    class="flex items-start justify-between gap-6 border-b border-hairline px-6 py-5"
                >
                    <div>
                        <p
                            class="mb-2 text-xs font-medium text-ink-soft"
                        >
                            Subject details
                        </p>

                        <h2
                            class="font-serif text-3xl font-medium text-ink"
                        >
                            {{ selectedSubject.name }}
                        </h2>

                        <p
                            class="mt-1 text-sm font-medium text-forest"
                        >
                            {{ selectedSubject.code }}
                        </p>
                    </div>

                    <button
                        @click="closeModal"
                        class="flex h-9 w-9 shrink-0 items-center justify-center border border-hairline text-xl text-ink-soft hover:border-ink hover:text-ink"
                        aria-label="Close"
                    >
                        ×
                    </button>
                </div>

                <div class="px-6 py-6">
                    <!-- DESCRIPTION -->
                    <section class="mb-8">
                        <div
                            class="mb-3 border-b border-hairline pb-2"
                        >
                            <h3
                                class="font-serif text-xl font-medium text-ink"
                            >
                                Description
                            </h3>
                        </div>

                        <p
                            class="text-sm leading-7 text-ink-soft"
                        >
                            {{
                                selectedSubject.description ||
                                'No description available.'
                            }}
                        </p>
                    </section>

                    <!-- STUDENTS -->
                    <section>
                        <div
                            class="mb-4 flex items-end justify-between border-b border-hairline pb-3"
                        >
                            <div>
                                <h3
                                    class="font-serif text-xl font-medium text-ink"
                                >
                                    Students
                                </h3>

                                <p
                                    class="mt-1 text-xs text-ink-soft"
                                >
                                    Students currently assigned to this subject
                                </p>
                            </div>

                            <span
                                class="font-serif text-xl font-medium text-ink"
                            >
                                {{
                                    selectedSubject.students?.length || 0
                                }}
                            </span>
                        </div>

                        <!-- NO STUDENTS -->
                        <div
                            v-if="
                                !selectedSubject.students ||
                                selectedSubject.students.length === 0
                            "
                            class="border border-hairline bg-paper px-5 py-10 text-center"
                        >
                            <p
                                class="text-sm text-ink-soft"
                            >
                                No students assigned to this subject.
                            </p>
                        </div>

                        <!-- STUDENT TABLE -->
                        <div
                            v-else
                            class="overflow-x-auto border border-hairline"
                        >
                            <table
                                class="min-w-full divide-y divide-hairline"
                            >
                                <thead class="bg-paper">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                                        >
                                            Student
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                                        >
                                            Email
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                                        >
                                            Symbol No.
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-ink-soft"
                                        >
                                            Status
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-hairline"
                                >
                                    <tr
                                        v-for="student in selectedSubject.students"
                                        :key="student.id"
                                        class="hover:bg-paper"
                                    >
                                        <td class="px-4 py-4">
                                            <div
                                                class="font-medium text-ink"
                                            >
                                                {{ student.name }}
                                            </div>
                                        </td>

                                        <td
                                            class="px-4 py-4 text-sm text-ink-soft"
                                        >
                                            {{ student.email || '—' }}
                                        </td>

                                        <td
                                            class="px-4 py-4 text-sm text-ink"
                                        >
                                            {{ student.symbol_no || '—' }}
                                        </td>

                                        <td class="px-4 py-4">
                                            <span
                                                class="text-sm font-medium"
                                                :class="
                                                    student.status === 'active'
                                                        ? 'text-forest'
                                                        : 'text-sienna'
                                                "
                                            >
                                                {{
                                                    student.status || 'Unknown'
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- MODAL FOOTER -->
                <div
                    class="flex justify-end border-t border-hairline px-6 py-4"
                >
                    <button
                        @click="closeModal"
                        class="border border-hairline px-5 py-2.5 text-sm font-medium text-ink hover:border-ink hover:bg-paper"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>