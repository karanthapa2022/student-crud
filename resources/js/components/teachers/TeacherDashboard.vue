<script setup>

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'


// =========================================================
// ROUTER
// =========================================================

const router = useRouter()


// =========================================================
// TEACHER DATA
// =========================================================

const teacher = ref(
    JSON.parse(localStorage.getItem('teacher_user') || 'null')
)


// =========================================================
// STATISTICS
// =========================================================

const statistics = ref({
    students: 0,
    subjects: 0,
    marksheets: 0
})


// =========================================================
// DARK MODE
// =========================================================

const darkMode = ref(
    localStorage.getItem('theme') === 'dark'
)

const toggleDarkMode = () => {

    darkMode.value = !darkMode.value

    localStorage.setItem(
        'theme',
        darkMode.value ? 'dark' : 'light'
    )

}


// =========================================================
// TEACHER INFORMATION
// =========================================================

const teacherName = computed(() => {

    return teacher.value?.name || 'Teacher'

})

const teacherEmail = computed(() => {

    return teacher.value?.email || '-'

})

const teacherId = computed(() => {

    return teacher.value?.teacher_id ||
        teacher.value?.id ||
        '-'

})


// =========================================================
// NAVIGATION
// =========================================================

const goToStudents = () => {

    router.push('/teacher/students')

}

const goToSubjects = () => {

    router.push('/teacher/subjects')

}

const goToMarksheets = () => {

    router.push('/teacher/marksheets')

}


// =========================================================
// LOGOUT
// =========================================================

const logout = () => {

    localStorage.removeItem('teacher_token')
    localStorage.removeItem('teacher_user')

    router.push('/login')

}


// =========================================================
// LOAD DASHBOARD
// =========================================================

const loadDashboard = async () => {

    try {

        const token = localStorage.getItem('teacher_token')

        if (!token) {

            router.push('/login')

            return

        }


        const response = await fetch(
            'http://127.0.0.1:8000/api/teacher/dashboard',
            {
                headers: {
                    Accept: 'application/json',
                    Authorization: `Bearer ${token}`
                }
            }
        )


        // =====================================================
        // UNAUTHORIZED
        // =====================================================

        if (response.status === 401) {

            localStorage.removeItem('teacher_token')
            localStorage.removeItem('teacher_user')

            router.push('/login')

            return

        }


        // =====================================================
        // OTHER API ERROR
        // =====================================================

        if (!response.ok) {

            throw new Error(
                'Failed to load teacher dashboard.'
            )

        }


        const data = await response.json()


        // =====================================================
        // UPDATE TEACHER PROFILE
        // =====================================================

        /*
         * IMPORTANT:
         *
         * Do NOT overwrite teacher_user here.
         *
         * teacher_user must remain the authenticated
         * User model containing:
         *
         * id
         * name
         * email
         * role
         *
         * The API's teacher object is the Teacher profile,
         * not the authenticated User.
         */


        const currentUser = JSON.parse(
            localStorage.getItem('teacher_user') || 'null'
        )


        teacher.value = {
            ...currentUser,
            teacher_id: data.teacher?.id,
            name: data.teacher?.name || currentUser?.name,
            email: data.teacher?.email || currentUser?.email,
            phone: data.teacher?.phone
        }


        // =====================================================
        // UPDATE DASHBOARD STATISTICS
        // =====================================================

        statistics.value = {

            students:
                data.statistics?.students ?? 0,

            subjects:
                data.statistics?.subjects ?? 0,

            marksheets:
                data.statistics?.marksheets ?? 0

        }


    } catch (err) {

        console.error(
            'Teacher dashboard error:',
            err
        )

    }

}


// =========================================================
// MOUNT
// =========================================================

onMounted(() => {

    loadDashboard()

})

</script>


<template>

    <div
        :class="darkMode
            ? 'min-h-screen bg-gray-950 text-white'
            : 'min-h-screen bg-gray-100 text-gray-900'"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <header
            :class="darkMode
                ? 'bg-gray-900 border-gray-800'
                : 'bg-white border-gray-200'"
            class="border-b"
        >

            <div class="max-w-7xl mx-auto px-6 py-5">

                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >

                    <div>

                        <p
                            :class="darkMode
                                ? 'text-gray-400'
                                : 'text-gray-500'"
                            class="text-sm"
                        >
                            Teacher Portal
                        </p>

                        <h1
                            :class="darkMode
                                ? 'text-white'
                                : 'text-gray-900'"
                            class="text-2xl font-bold mt-1"
                        >
                            Welcome, {{ teacherName }}
                        </h1>

                    </div>


                    <div class="flex items-center gap-3">

                        <!-- Dark Mode -->

                        <button
                            @click="toggleDarkMode"
                            class="px-4 py-2 rounded-lg border transition"
                            :class="darkMode
                                ? 'border-gray-700 bg-gray-800 text-gray-200 hover:bg-gray-700'
                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                        >
                            {{ darkMode ? '☀️ Light' : '🌙 Dark' }}
                        </button>


                        <!-- Logout -->

                        <button
                            @click="logout"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
                        >
                            Logout
                        </button>

                    </div>

                </div>

            </div>

        </header>


        <!-- ================================================= -->
        <!-- MAIN -->
        <!-- ================================================= -->

        <main class="max-w-7xl mx-auto px-6 py-8">


            <!-- ================================================= -->
            <!-- STATISTICS -->
            <!-- ================================================= -->

            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
            >

                <!-- Students -->

                <div
                    :class="darkMode
                        ? 'bg-gray-900 border-gray-800'
                        : 'bg-white border-gray-200'"
                    class="border rounded-xl p-6 shadow-sm"
                >

                    <p
                        :class="darkMode
                            ? 'text-gray-400'
                            : 'text-gray-500'"
                        class="text-sm"
                    >
                        Assigned Students
                    </p>

                    <p class="text-3xl font-bold mt-2">
                        {{ statistics.students }}
                    </p>

                </div>


                <!-- Subjects -->

                <div
                    :class="darkMode
                        ? 'bg-gray-900 border-gray-800'
                        : 'bg-white border-gray-200'"
                    class="border rounded-xl p-6 shadow-sm"
                >

                    <p
                        :class="darkMode
                            ? 'text-gray-400'
                            : 'text-gray-500'"
                        class="text-sm"
                    >
                        Assigned Subjects
                    </p>

                    <p class="text-3xl font-bold mt-2">
                        {{ statistics.subjects }}
                    </p>

                </div>


                <!-- Marksheets -->

                <div
                    :class="darkMode
                        ? 'bg-gray-900 border-gray-800'
                        : 'bg-white border-gray-200'"
                    class="border rounded-xl p-6 shadow-sm"
                >

                    <p
                        :class="darkMode
                            ? 'bg-gray-900 border-gray-800'
                            : 'bg-white border-gray-200'"
                        class="hidden"
                    ></p>

                    <p
                        :class="darkMode
                            ? 'text-gray-400'
                            : 'text-gray-500'"
                        class="text-sm"
                    >
                        Marksheets
                    </p>

                    <p class="text-3xl font-bold mt-2">
                        {{ statistics.marksheets }}
                    </p>

                </div>


                <!-- Teacher ID -->

                <div
                    :class="darkMode
                        ? 'bg-gray-900 border-gray-800'
                        : 'bg-white border-gray-200'"
                    class="border rounded-xl p-6 shadow-sm"
                >

                    <p
                        :class="darkMode
                            ? 'text-gray-400'
                            : 'text-gray-500'"
                        class="text-sm"
                    >
                        Teacher ID
                    </p>

                    <p class="text-2xl font-bold mt-2">
                        {{ teacherId }}
                    </p>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- QUICK ACTIONS -->
            <!-- ================================================= -->

            <div class="mt-10">

                <h2
                    :class="darkMode
                        ? 'text-white'
                        : 'text-gray-900'"
                    class="text-xl font-bold mb-5"
                >
                    Quick Actions
                </h2>


                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-6"
                >

                    <!-- Students -->

                    <button
                        @click="goToStudents"
                        :class="darkMode
                            ? 'bg-gray-900 border-gray-800 hover:bg-gray-800'
                            : 'bg-white border-gray-200 hover:shadow-md'"
                        class="text-left border rounded-xl p-6 transition"
                    >

                        <h3 class="font-semibold text-lg">
                            Students
                        </h3>

                        <p
                            :class="darkMode
                                ? 'text-gray-400'
                                : 'text-gray-500'"
                            class="text-sm mt-2"
                        >
                            View students assigned to you.
                        </p>

                    </button>


                    <!-- Subjects -->

                    <button
                        @click="goToSubjects"
                        :class="darkMode
                            ? 'bg-gray-900 border-gray-800 hover:bg-gray-800'
                            : 'bg-white border-gray-200 hover:shadow-md'"
                        class="text-left border rounded-xl p-6 transition"
                    >

                        <h3 class="font-semibold text-lg">
                            My Subjects
                        </h3>

                        <p
                            :class="darkMode
                                ? 'text-gray-400'
                                : 'text-gray-500'"
                            class="text-sm mt-2"
                        >
                            View your assigned subjects.
                        </p>

                    </button>


                    <!-- Marksheets -->

                    <button
                        @click="goToMarksheets"
                        :class="darkMode
                            ? 'bg-gray-900 border-gray-800 hover:bg-gray-800'
                            : 'bg-white border-gray-200 hover:shadow-md'"
                        class="text-left border rounded-xl p-6 transition"
                    >

                        <h3 class="font-semibold text-lg">
                            Marksheets
                        </h3>

                        <p
                            :class="darkMode
                                ? 'text-gray-400'
                                : 'text-gray-500'"
                            class="text-sm mt-2"
                        >
                            Manage student marksheets.
                        </p>

                    </button>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- PROFILE -->
            <!-- ================================================= -->

            <div class="mt-10">

                <h2
                    :class="darkMode
                        ? 'text-white'
                        : 'text-gray-900'"
                    class="text-xl font-bold mb-5"
                >
                    My Profile
                </h2>


                <div
                    :class="darkMode
                        ? 'bg-gray-900 border-gray-800'
                        : 'bg-white border-gray-200'"
                    class="border rounded-xl p-6"
                >

                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-6"
                    >

                        <div>

                            <p
                                :class="darkMode
                                    ? 'text-gray-500'
                                    : 'text-gray-400'"
                                class="text-sm"
                            >
                                Name
                            </p>

                            <p class="font-medium mt-1">
                                {{ teacherName }}
                            </p>

                        </div>


                        <div>

                            <p
                                :class="darkMode
                                    ? 'text-gray-500'
                                    : 'text-gray-400'"
                                class="text-sm"
                            >
                                Email
                            </p>

                            <p class="font-medium mt-1 break-all">
                                {{ teacherEmail }}
                            </p>

                        </div>


                        <div>

                            <p
                                :class="darkMode
                                    ? 'text-gray-500'
                                    : 'text-gray-400'"
                                class="text-sm"
                            >
                                Role
                            </p>

                            <p class="font-medium mt-1">
                                Teacher
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</template>
