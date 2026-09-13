<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

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
    baseURL: 'http://127.0.0.1:8000/api',
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

            localStorage.removeItem(
                'teacher_token'
            )

            localStorage.removeItem(
                'teacher_user'
            )

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

            localStorage.removeItem(
                'teacher_token'
            )

            localStorage.removeItem(
                'teacher_user'
            )

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

    localStorage.removeItem(
        'teacher_token'
    )

    localStorage.removeItem(
        'teacher_user'
    )

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

    <div
        class="
            min-h-screen
            bg-gray-100
            dark:bg-gray-950
            text-gray-900
            dark:text-gray-100
            p-6
        "
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div
            class="
                max-w-7xl
                mx-auto
                flex
                flex-col
                md:flex-row
                md:items-center
                md:justify-between
                gap-4
                mb-8
            "
        >

            <div>

                <button
                    @click="router.push('/dashboard')"
                    class="
                        text-sm
                        text-blue-600
                        dark:text-blue-400
                        hover:underline
                        mb-2
                    "
                >
                    ← Back to Dashboard
                </button>

                <h1
                    class="
                        text-3xl
                        font-bold
                    "
                >
                    My Subjects
                </h1>

                <p
                    v-if="teacher"
                    class="
                        text-gray-600
                        dark:text-gray-400
                        mt-1
                    "
                >
                    {{ teacher.name }}
                </p>

            </div>


            <button
                @click="logout"
                class="
                    px-5
                    py-2.5
                    rounded-lg
                    bg-red-600
                    text-white
                    hover:bg-red-700
                    transition
                "
            >
                Logout
            </button>

        </div>


        <!-- ================================================= -->
        <!-- ERROR -->
        <!-- ================================================= -->

        <div
            v-if="error"
            class="
                max-w-7xl
                mx-auto
                mb-6
                p-4
                rounded-lg
                bg-red-100
                dark:bg-red-900/30
                text-red-700
                dark:text-red-300
            "
        >
            {{ error }}
        </div>


        <!-- ================================================= -->
        <!-- LOADING -->
        <!-- ================================================= -->

        <div
            v-if="loading"
            class="
                max-w-7xl
                mx-auto
                text-center
                py-16
                text-gray-500
                dark:text-gray-400
            "
        >
            Loading your subjects...
        </div>


        <!-- ================================================= -->
        <!-- EMPTY -->
        <!-- ================================================= -->

        <div
            v-else-if="subjects.length === 0"
            class="
                max-w-7xl
                mx-auto
                text-center
                py-16
                bg-white
                dark:bg-gray-900
                rounded-xl
                shadow
            "
        >

            <div class="text-5xl mb-4">
                📚
            </div>

            <h2
                class="
                    text-xl
                    font-semibold
                    mb-2
                "
            >
                No Subjects Assigned
            </h2>

            <p
                class="
                    text-gray-500
                    dark:text-gray-400
                "
            >
                You currently don't have any subjects assigned to you.
            </p>

        </div>


        <!-- ================================================= -->
        <!-- SUBJECT CARDS -->
        <!-- ================================================= -->

        <div
            v-else
            class="
                max-w-7xl
                mx-auto
                grid
                grid-cols-1
                md:grid-cols-2
                lg:grid-cols-3
                gap-6
            "
        >

            <div
                v-for="subject in subjects"
                :key="subject.id"
                class="
                    bg-white
                    dark:bg-gray-900
                    rounded-xl
                    shadow
                    p-6
                    border
                    border-gray-200
                    dark:border-gray-800
                    hover:shadow-lg
                    transition
                "
            >

                <!-- SUBJECT ICON -->

                <div
                    class="
                        w-12
                        h-12
                        rounded-lg
                        bg-blue-100
                        dark:bg-blue-900/30
                        flex
                        items-center
                        justify-center
                        text-2xl
                        mb-4
                    "
                >
                    📚
                </div>


                <!-- NAME -->

                <h2
                    class="
                        text-xl
                        font-bold
                        mb-1
                    "
                >
                    {{ subject.name }}
                </h2>


                <!-- CODE -->

                <p
                    class="
                        text-sm
                        text-blue-600
                        dark:text-blue-400
                        font-medium
                        mb-4
                    "
                >
                    {{ subject.code }}
                </p>


                <!-- DESCRIPTION -->

                <p
                    class="
                        text-gray-600
                        dark:text-gray-400
                        text-sm
                        min-h-[40px]
                        mb-5
                    "
                >
                    {{ subject.description || 'No description available.' }}
                </p>


                <!-- STUDENTS -->

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-t
                        border-gray-200
                        dark:border-gray-800
                        pt-4
                    "
                >

                    <div>

                        <p
                            class="
                                text-xs
                                text-gray-500
                                dark:text-gray-400
                            "
                        >
                            Students
                        </p>

                        <p
                            class="
                                text-lg
                                font-bold
                            "
                        >
                            {{ subject.students_count ?? 0 }}
                        </p>

                    </div>


                    <button
                        @click="viewSubject(subject)"
                        class="
                            px-4
                            py-2
                            rounded-lg
                            bg-blue-600
                            text-white
                            hover:bg-blue-700
                            transition
                            text-sm
                            font-medium
                        "
                    >
                        View
                    </button>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- SUBJECT DETAILS MODAL -->
        <!-- ================================================= -->

        <div
            v-if="showModal && selectedSubject"
            class="
                fixed
                inset-0
                z-50
                bg-black/50
                flex
                items-center
                justify-center
                p-4
            "
            @click.self="closeModal"
        >

            <div
                class="
                    w-full
                    max-w-3xl
                    max-h-[90vh]
                    overflow-y-auto
                    bg-white
                    dark:bg-gray-900
                    rounded-xl
                    shadow-2xl
                    p-6
                "
            >

                <!-- MODAL HEADER -->

                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-4
                        mb-6
                    "
                >

                    <div>

                        <h2
                            class="
                                text-2xl
                                font-bold
                            "
                        >
                            {{ selectedSubject.name }}
                        </h2>

                        <p
                            class="
                                text-blue-600
                                dark:text-blue-400
                                mt-1
                            "
                        >
                            {{ selectedSubject.code }}
                        </p>

                    </div>


                    <button
                        @click="closeModal"
                        class="
                            text-gray-500
                            hover:text-gray-900
                            dark:hover:text-white
                            text-2xl
                        "
                    >
                        ×
                    </button>

                </div>


                <!-- DESCRIPTION -->

                <div
                    class="
                        mb-6
                        p-4
                        rounded-lg
                        bg-gray-50
                        dark:bg-gray-800
                    "
                >

                    <p
                        class="
                            text-sm
                            text-gray-500
                            dark:text-gray-400
                            mb-1
                        "
                    >
                        Description
                    </p>

                    <p>
                        {{
                            selectedSubject.description ||
                            'No description available.'
                        }}
                    </p>

                </div>


                <!-- STUDENTS -->

                <div>

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            mb-4
                        "
                    >

                        <h3
                            class="
                                text-lg
                                font-semibold
                            "
                        >
                            Students
                        </h3>

                        <span
                            class="
                                px-3
                                py-1
                                rounded-full
                                bg-blue-100
                                dark:bg-blue-900/30
                                text-blue-700
                                dark:text-blue-300
                                text-sm
                                font-medium
                            "
                        >
                            {{
                                selectedSubject.students?.length || 0
                            }}
                        </span>

                    </div>


                    <div
                        v-if="
                            !selectedSubject.students ||
                            selectedSubject.students.length === 0
                        "
                        class="
                            text-center
                            py-8
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        No students assigned to this subject.
                    </div>


                    <div
                        v-else
                        class="
                            overflow-x-auto
                            border
                            border-gray-200
                            dark:border-gray-800
                            rounded-lg
                        "
                    >

                        <table
                            class="
                                min-w-full
                                divide-y
                                divide-gray-200
                                dark:divide-gray-800
                            "
                        >

                            <thead
                                class="
                                    bg-gray-50
                                    dark:bg-gray-800
                                "
                            >

                                <tr>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wider
                                        "
                                    >
                                        Student
                                    </th>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wider
                                        "
                                    >
                                        Email
                                    </th>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wider
                                        "
                                    >
                                        Symbol No.
                                    </th>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wider
                                        "
                                    >
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody
                                class="
                                    divide-y
                                    divide-gray-200
                                    dark:divide-gray-800
                                "
                            >

                                <tr
                                    v-for="
                                        student in selectedSubject.students
                                    "
                                    :key="student.id"
                                    class="
                                        hover:bg-gray-50
                                        dark:hover:bg-gray-800
                                    "
                                >

                                    <td
                                        class="
                                            px-4
                                            py-3
                                            font-medium
                                        "
                                    >
                                        {{ student.name }}
                                    </td>

                                    <td
                                        class="
                                            px-4
                                            py-3
                                            text-sm
                                            text-gray-600
                                            dark:text-gray-400
                                        "
                                    >
                                        {{ student.email || '—' }}
                                    </td>

                                    <td
                                        class="
                                            px-4
                                            py-3
                                            text-sm
                                        "
                                    >
                                        {{ student.symbol_no || '—' }}
                                    </td>

                                    <td
                                        class="
                                            px-4
                                            py-3
                                        "
                                    >

                                        <span
                                            class="
                                                px-2.5
                                                py-1
                                                rounded-full
                                                text-xs
                                                font-medium
                                            "
                                            :class="
                                                student.status === 'active'
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
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

                </div>


                <!-- CLOSE -->

                <div
                    class="
                        flex
                        justify-end
                        mt-6
                    "
                >

                    <button
                        @click="closeModal"
                        class="
                            px-5
                            py-2.5
                            rounded-lg
                            bg-gray-200
                            dark:bg-gray-800
                            hover:bg-gray-300
                            dark:hover:bg-gray-700
                            transition
                        "
                    >
                        Close
                    </button>

                </div>

            </div>

        </div>

    </div>

</template>
