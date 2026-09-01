<script setup>

import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'


const router = useRouter()

const trashedStudents = ref([])
const loading = ref(false)
const error = ref('')
const selectedStudents=ref([])

//dark mode

const isDarkMode = ref(
    localStorage.getItem('theme') === 'dark'
)
const applyDarkMode = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

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

        trashedStudents.value = response.data.students ||[]

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
// Bulk selection
// =========================================================

const toggleStudent=(id)=>{
    if(selectedStudents.value.includes(id)){
        selectedStudents.value=
        selectedStudents.value.filter(
            studentId=> studentId !==id
        )
    } else{
        selectedStudents.value.push(id)
    }
}
const toggleALlStudents =()=>{
    if(
        selectedStudents.value.length===
        trashedStudents.value.length
    ){
        selectedStudents.value=[]
    }else{
        selectedStudents.value=
        trashedStudents.value.map(
            student=>student.id
        )
    }
}
const isSelected=(id)=>{
    return selectedStudents.value.includes(id)
}
// =========================================================
// Bulk Restore
// =========================================================

const bulkRestore =async()=>{
    if(
        selectedStudents.value.length===0){
            alert('please select at least one student.')
            return
        }
        const confirmed=confirm(
            `Restore ${selectedStudents.value.length} selected student(s)?`
        )
        if(!confirmed){
            return
        }
        try{
            const token=localStorage.getItem('token')
            await axios.post(
                'http://127.0.0.1:8000/api/students/bulk-restore',{
                    ids: selectedStudents.value
                },
                {
                    headers:{
                        Authorization:`Bearer ${token}`,
                        Accept:'application/json'
                    }
                }
            )
            alert('Selected students restored successfully!')
            selectedStudents.value=[]
            await fetchTrash()

        } catch(err){
            console.error('Bulk restore error:',err)
            alert(
                err.response?.data?.message||
                'unable to restore selected students.'
            )
        }
    
}
// =========================================================
// BULK PERMANENT DELETE
// =========================================================

const bulkForceDelete = async () => {

    if (selectedStudents.value.length === 0) {
        alert('Please select at least one student.')
        return
    }

    const confirmed = confirm(
        `WARNING!\n\nAre you sure you want to permanently delete ${selectedStudents.value.length} selected student(s)?\n\nThis cannot be undone.`
    )

    if (!confirmed) {
        return
    }

    try {

        const token = localStorage.getItem('token')

        await axios.post(
            'http://127.0.0.1:8000/api/students/bulk-force-delete',
            {
                ids: selectedStudents.value
            },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        alert('Selected students permanently deleted!')

        selectedStudents.value = []

        await fetchTrash()

    } catch (err) {

        console.error(
            'Bulk permanent delete error:',
            err
        )

        alert(
            err.response?.data?.message ||
            'Unable to permanently delete selected students.'
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

onMounted( async() => {
    applyDarkMode()

   await fetchTrash()

})

</script>


<template>

<div
    class="min-h-screen bg-gray-100 dark:bg-gray-950 p-4 sm:p-6 lg:p-8"
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
                    class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white"
                >
                    Trash
                </h1>

                <p
                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                >
                    Restore deleted students or permanently remove them.
                </p>

            </div>


            <!-- BACK BUTTON -->

            <button
                type="button"
                @click="goBack"
                class="px-5 py-2.5 rounded-xl bg-gray-800 dark:bg-gray-700 text-white font-medium hover:bg-gray-900 dark:hover:bg-gray-600 transition"
            >
                ← Back to Students
            </button>

        </div>


        <!-- ERROR -->

        <div
            v-if="error"
            class="mb-6 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl"
        >

            {{ error }}

        </div>


        <!-- LOADING -->

        <div
            v-if="loading"
            class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-10 text-center shadow-sm"
        >

            <p
                class="text-gray-500 dark:text-gray-400"
            >
                Loading trash...
            </p>

        </div>


        <!-- EMPTY TRASH -->

        <div
            v-else-if="trashedStudents.length === 0"
            class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-12 text-center shadow-sm"
        >

            <div
                class="text-5xl mb-4"
            >
                🗑️
            </div>


            <h2
                class="text-xl font-bold text-gray-800 dark:text-white"
            >
                Trash is empty
            </h2>


            <p
                class="mt-2 text-sm text-gray-500 dark:text-gray-400"
            >
                Deleted students will appear here.
            </p>

        </div>


        <!-- TRASH CONTENT -->

        <div
            v-else
        >

            <!-- BULK ACTIONS -->

            <div
                v-if="selectedStudents.length > 0"
                class="mb-4 flex flex-wrap items-center gap-3"
            >

                <!-- SELECTED COUNT -->

                <span
                    class="text-sm font-medium text-gray-600 dark:text-gray-300"
                >
                    {{ selectedStudents.length }} selected
                </span>


                <!-- RESTORE SELECTED -->

                <button
                    type="button"
                    @click="bulkRestore"
                    class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition"
                >
                    ↩ Restore Selected
                </button>


                <!-- DELETE SELECTED -->

                <button
                    type="button"
                    @click="bulkForceDelete"
                    class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition"
                >
                    🗑 Delete Selected Permanently
                </button>

            </div>


            <!-- TRASH TABLE -->

            <div
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-x-auto"
            >

                <table
                    class="w-full text-left"
                >

                    <!-- TABLE HEADER -->

                    <thead
                        class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700"
                    >

                        <tr>

                            <!-- SELECT ALL -->

                            <th
                                class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >

                                <input
                                    type="checkbox"
                                    :checked="
                                        selectedStudents.length === trashedStudents.length &&
                                        trashedStudents.length > 0
                                    "
                                    @change="toggleALlStudents"
                                    class="w-4 h-4 rounded"
                                />

                            </th>


                            <!-- ID -->

                            <th
                                class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                ID
                            </th>


                            <!-- NAME -->

                            <th
                                class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                Name
                            </th>


                            <!-- EMAIL -->

                            <th
                                class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                Email
                            </th>


                            <!-- PHONE -->

                            <th
                                class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                Phone
                            </th>


                            <!-- STATUS -->

                            <th
                                class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                Status
                            </th>


                            <!-- ACTIONS -->

                            <th
                                class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <!-- TABLE BODY -->

                    <tbody>

                        <tr
                            v-for="student in trashedStudents"
                            :key="student.id"
                            class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >

                            <!-- SELECT -->

                            <td
                                class="px-5 py-4"
                            >

                                <input
                                    type="checkbox"
                                    :checked="isSelected(student.id)"
                                    @change="toggleStudent(student.id)"
                                    class="w-4 h-4 rounded"
                                />

                            </td>


                            <!-- ID -->

                            <td
                                class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                #{{ student.id }}
                            </td>


                            <!-- NAME -->

                            <td
                                class="px-5 py-4 text-sm font-semibold text-gray-800 dark:text-white"
                            >
                                {{ student.name }}
                            </td>


                            <!-- EMAIL -->

                            <td
                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300"
                            >
                                {{ student.email }}
                            </td>


                            <!-- PHONE -->

                            <td
                                class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300"
                            >
                                {{ student.phone }}
                            </td>


                            <!-- STATUS -->

                            <td
                                class="px-5 py-4"
                            >

                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                >
                                    Deleted
                                </span>

                            </td>


                            <!-- ACTIONS -->

                            <td
                                class="px-5 py-4"
                            >

                                <div
                                    class="flex flex-wrap gap-3"
                                >

                                    <!-- RESTORE -->

                                    <button
                                        type="button"
                                        @click="restoreStudent(student)"
                                        class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium text-sm"
                                    >
                                        Restore
                                    </button>


                                    <!-- PERMANENT DELETE -->

                                    <button
                                        type="button"
                                        @click="forceDeleteStudent(student)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium text-sm"
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

</div>

</template>