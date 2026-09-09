<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// =========================================================
// STATE
// =========================================================

const marksheets = ref([])

const loading = ref(false)

const error = ref('')

const searchQuery = ref('')


// =========================================================
// PAGINATION
// =========================================================

const currentPage = ref(1)
const lastPage = ref(1)
const totalMarksheets = ref(0)


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
// API
// =========================================================

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        Accept: 'application/json'
    }
})


// =========================================================
// FETCH MARKSHEETS
// =========================================================

const fetchMarksheets = async (page = 1) => {

    loading.value = true

    error.value = ''

    try {

        const token =
            localStorage.getItem('teacher_token')

        if (!token) {

            router.push('/login')

            return
        }

        const response = await api.get(
            '/teacher/marksheets',
            {
                params: {
                    page,
                    search: searchQuery.value
                },
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        marksheets.value =
            response.data.data ?? []

        currentPage.value =
            response.data.current_page ?? 1

        lastPage.value =
            response.data.last_page ?? 1

        totalMarksheets.value =
            response.data.total ?? 0

    } catch (err) {

        console.error(
            'Fetch teacher marksheets error:',
            err
        )

        if (err.response?.status === 401) {

            localStorage.removeItem('teacher_token')
            localStorage.removeItem('teacher_user')

            router.push('/login')

            return
        }

        error.value =
            err.response?.data?.message ||
            'Unable to load marksheets.'

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

        fetchMarksheets(1)

    }, 400)
}


// =========================================================
// VIEW MARKSHEET
// =========================================================

const viewMarksheet = (marksheet) => {

    router.push(
        `/marksheets/${marksheet.id}`
    )
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

    fetchMarksheets(page)
}


// =========================================================
// PAGE NUMBERS
// =========================================================

const pageNumbers = () => {

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
// INITIAL LOAD
// =========================================================

onMounted(() => {

    applyTheme()

    fetchMarksheets()

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

            <div
                class="max-w-7xl mx-auto px-6 py-5"
            >

                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >

                    <div>

                        <h1
                            class="text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            My Marksheets
                        </h1>

                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            View marksheets for your assigned subjects
                        </p>

                    </div>


                    <div
                        class="flex items-center gap-3"
                    >

                        <!-- DARK MODE -->

                        <button
                            @click="toggleDarkMode"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            {{ darkMode ? '☀️ Light' : '🌙 Dark' }}
                        </button>


                        <!-- DASHBOARD -->

                        <button
                            @click="router.push('/teacher/dashboard')"
                            class="px-4 py-2 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-sm font-semibold hover:bg-gray-800 dark:hover:bg-gray-600 transition"
                        >
                            Dashboard
                        </button>


                        <!-- LOGOUT -->

                        <button
                            @click="logout"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition"
                        >
                            Logout
                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- MAIN -->
        <!-- ================================================= -->

        <main
            class="max-w-7xl mx-auto px-6 py-8"
        >

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
                            Marksheets
                        </h2>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                        >
                            {{ totalMarksheets }}
                            marksheet{{ totalMarksheets === 1 ? '' : 's' }}
                            found
                        </p>

                    </div>


                    <!-- SEARCH -->

                    <div
                        class="w-full sm:w-80"
                    >

                        <input
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search student..."
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
                Loading marksheets...
            </div>


            <!-- ================================================= -->
            <!-- EMPTY -->
            <!-- ================================================= -->

            <div
                v-else-if="marksheets.length === 0"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl py-12 text-center"
            >

                <p
                    class="text-gray-500 dark:text-gray-400"
                >
                    No marksheets found.
                </p>

            </div>


            <!-- ================================================= -->
            <!-- TABLE -->
            <!-- ================================================= -->

            <div
                v-else
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm"
            >

                <div
                    class="overflow-x-auto"
                >

                    <table
                        class="w-full"
                    >

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
                                    Student
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    Symbol No.
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    Subjects
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                >
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody
                            class="divide-y divide-gray-200 dark:divide-gray-800"
                        >

                            <tr
                                v-for="(marksheet, index) in marksheets"
                                :key="marksheet.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
                            >

                                <!-- NUMBER -->

                                <td
                                    class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{
                                        (currentPage - 1) * 10 +
                                        index +
                                        1
                                    }}
                                </td>


                                <!-- STUDENT -->

                                <td
                                    class="px-6 py-4"
                                >

                                    <div
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            marksheet.student?.name ||
                                            'Unknown Student'
                                        }}
                                    </div>

                                    <div
                                        v-if="marksheet.student?.email"
                                        class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    >
                                        {{ marksheet.student.email }}
                                    </div>

                                </td>


                                <!-- SYMBOL -->

                                <td
                                    class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                                >
                                    {{
                                        marksheet.student?.symbol_no ||
                                        '-'
                                    }}
                                </td>


                                <!-- SUBJECTS -->

                                <td
                                    class="px-6 py-4"
                                >

                                    <div
                                        class="flex flex-wrap gap-2"
                                    >

                                        <span
                                            v-for="item in (marksheet.items || [])"
                                            :key="item.id"
                                            class="px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs"
                                        >
                                            {{
                                                item.subject_name ||
                                                item.subject?.name ||
                                                'Subject'
                                            }}
                                        </span>

                                    </div>

                                </td>


                                <!-- ACTION -->

                                <td
                                    class="px-6 py-4 text-right"
                                >

                                    <button
                                        @click="viewMarksheet(marksheet)"
                                        class="px-3 py-1.5 text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                                    >
                                        View
                                    </button>

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
                        Page {{ currentPage }}
                        of
                        {{ lastPage }}
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
                            v-for="page in pageNumbers()"
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

    </div>

</template>
