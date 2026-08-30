<script setup>

import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const trashedStudents = ref([])
const loading = ref(false)
const error = ref('')

// =========================================================
// FETCH TRASHED STUDENTS
// =========================================================

const fetchTrash = async () => {

    loading.value = true
    error.value = ''

    try {

        const token = localStorage.getItem('token')

        const response = await axios.get(
            'http://127.0.0.1:8000/api/students/trash',
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        trashedStudents.value = response.data

    } catch (err) {

        console.error('Error fetching trash:', err)

        error.value =
            err.response?.data?.message ||
            'Unable to load trash.'

    } finally {

        loading.value = false

    }

}


// =========================================================
// RESTORE STUDENT
// =========================================================

const restoreStudent = async (student) => {

    const confirmed = confirm(
        `Restore ${student.name}?`
    )

    if (!confirmed) {
        return
    }

    try {

        const token = localStorage.getItem('token')

        await axios.post(
            `http://127.0.0.1:8000/api/students/${student.id}/restore`,
            {},
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        alert('Student restored successfully!')

        await fetchTrash()

    } catch (err) {

        console.error('Restore error:', err)

        alert(
            err.response?.data?.message ||
            'Unable to restore student.'
        )

    }

}


// =========================================================
// PERMANENT DELETE
// =========================================================

const forceDeleteStudent = async (student) => {

    const confirmed = confirm(
        `WARNING!\n\nAre you sure you want to permanently delete ${student.name}?\n\nThis cannot be undone.`
    )

    if (!confirmed) {
        return
    }

    try {

        const token = localStorage.getItem('token')

        await axios.delete(
            `http://127.0.0.1:8000/api/students/${student.id}/force-delete`,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        alert('Student permanently deleted!')

        await fetchTrash()

    } catch (err) {

        console.error(
            'Permanent delete error:',
            err
        )

        alert(
            err.response?.data?.message ||
            'Unable to permanently delete student.'
        )

    }

}


// =========================================================
// GO BACK
// =========================================================

const goBack = () => {

    router.push('/students')

}


// =========================================================
// ON MOUNTED
// =========================================================

onMounted(() => {

    fetchTrash()

})

</script>


<template>

<div
    class="min-h-screen bg-gray-100 p-4 sm:p-6 lg:p-8"
>

    <div
        class="w-full max-w-7xl mx-auto"
    >

        <!-- HEADER -->

        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8"
        >

            <div>

                <h1
                    class="text-2xl sm:text-3xl font-bold text-gray-800"
                >
                    Trash
                </h1>

                <p
                    class="mt-1 text-sm text-gray-500"
                >
                    Restore deleted students or permanently remove them.
                </p>

            </div>


            <button
                type="button"
                @click="goBack"
                class="px-5 py-2.5 rounded-xl bg-gray-800 text-white font-medium hover:bg-gray-900 transition"
            >
                ← Back to Students
            </button>

        </div>


        <!-- ERROR -->

        <div
            v-if="error"
            class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl"
        >

            {{ error }}

        </div>


        <!-- LOADING -->

        <div
            v-if="loading"
            class="bg-white rounded-2xl border border-gray-200 p-10 text-center shadow-sm"
        >

            <p class="text-gray-500">
                Loading trash...
            </p>

        </div>


        <!-- EMPTY TRASH -->

        <div
            v-else-if="trashedStudents.length === 0"
            class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm"
        >

            <div
                class="text-5xl mb-4"
            >
                🗑️
            </div>

            <h2
                class="text-xl font-bold text-gray-800"
            >
                Trash is empty
            </h2>

            <p
                class="mt-2 text-sm text-gray-500"
            >
                Deleted students will appear here.
            </p>

        </div>


        <!-- TRASH TABLE -->

        <div
            v-else
            class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-x-auto"
        >

            <table
                class="w-full text-left"
            >

                <thead
                    class="bg-gray-50 border-b border-gray-200"
                >

                    <tr>

                        <th
                            class="px-5 py-4 text-sm font-semibold text-gray-700"
                        >
                            ID
                        </th>

                        <th
                            class="px-5 py-4 text-sm font-semibold text-gray-700"
                        >
                            Name
                        </th>

                        <th
                            class="px-5 py-4 text-sm font-semibold text-gray-700"
                        >
                            Email
                        </th>

                        <th
                            class="px-5 py-4 text-sm font-semibold text-gray-700"
                        >
                            Phone
                        </th>

                        <th
                            class="px-5 py-4 text-sm font-semibold text-gray-700"
                        >
                            Status
                        </th>

                        <th
                            class="px-5 py-4 text-sm font-semibold text-gray-700"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <tr
                        v-for="student in trashedStudents"
                        :key="student.id"
                        class="border-b border-gray-100 hover:bg-gray-50 transition"
                    >

                        <td
                            class="px-5 py-4 text-sm font-medium text-gray-700"
                        >
                            #{{ student.id }}
                        </td>


                        <td
                            class="px-5 py-4 text-sm font-semibold text-gray-800"
                        >
                            {{ student.name }}
                        </td>


                        <td
                            class="px-5 py-4 text-sm text-gray-600"
                        >
                            {{ student.email }}
                        </td>


                        <td
                            class="px-5 py-4 text-sm text-gray-600"
                        >
                            {{ student.phone }}
                        </td>


                        <td
                            class="px-5 py-4"
                        >

                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600"
                            >
                                Deleted
                            </span>

                        </td>


                        <td
                            class="px-5 py-4"
                        >

                            <div
                                class="flex flex-wrap gap-3"
                            >

                                <button
                                    type="button"
                                    @click="restoreStudent(student)"
                                    class="text-green-600 hover:text-green-800 font-medium text-sm"
                                >
                                    Restore
                                </button>


                                <button
                                    type="button"
                                    @click="forceDeleteStudent(student)"
                                    class="text-red-600 hover:text-red-800 font-medium text-sm"
                                >
                                    Delete Permanently
                                </button>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

</template>