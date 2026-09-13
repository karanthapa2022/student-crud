<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { createApiClient, getAuthToken } from '../../services/apiConfig'


const router = useRouter()
const api = createApiClient()

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

        const token = getAuthToken()

        const response = await api.get(
            '/students/trash',
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

        const token = getAuthToken()

        await api.post(
            `/students/${student.id}/restore`,
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

        const token = getAuthToken()

        await api.delete(
            `/students/${student.id}/force-delete`,
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
            const token = getAuthToken()
            await api.post(
                '/students/bulk-restore',{
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

        const token = getAuthToken()

        await api.post(
            '/students/bulk-force-delete',
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
    <div class="min-h-screen bg-paper px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-7xl">

            <!-- LETTERHEAD -->
            <header class="border-b border-hairline pb-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center border border-forest bg-surface text-sm font-semibold text-forest"
                            >
                                TR
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-ink">
                                    Student Records
                                </p>
                                <p class="text-xs text-ink-soft">
                                    Administration
                                </p>
                            </div>
                        </div>

                        <h1
                            class="mt-6 text-4xl font-medium tracking-tight text-ink sm:text-5xl"
                        >
                            Trash
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-ink-soft">
                            Restore deleted students or permanently remove records
                            from the system.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="goBack"
                        class="inline-flex w-fit items-center border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                    >
                        ← Back to Students
                    </button>
                </div>
            </header>


            <!-- ERROR -->
            <div
                v-if="error"
                class="mt-6 border border-sienna/30 bg-surface px-4 py-3 text-sm text-sienna"
            >
                {{ error }}
            </div>


            <!-- LOADING -->
            <div
                v-if="loading"
                class="mt-8 border border-hairline bg-surface px-6 py-14 text-center"
            >
                <div
                    class="mx-auto mb-4 h-7 w-7 animate-spin rounded-full border-2 border-hairline border-t-forest"
                ></div>

                <p class="text-sm text-ink-soft">
                    Loading trash...
                </p>
            </div>


            <!-- EMPTY TRASH -->
            <div
                v-else-if="trashedStudents.length === 0"
                class="mt-8 border border-hairline bg-surface px-6 py-16 text-center"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center border border-hairline text-2xl text-ink-soft"
                >
                    ∅
                </div>

                <h2
                    class="mt-5 text-2xl font-medium text-ink"
                >
                    Trash is empty
                </h2>

                <p class="mt-2 text-sm text-ink-soft">
                    Deleted students will appear here.
                </p>
            </div>


            <!-- TRASH CONTENT -->
            <div v-else class="mt-8">

                <!-- LEDGER SUMMARY -->
                <section
                    class="border border-hairline bg-surface"
                >
                    <div
                        class="grid grid-cols-2 divide-x divide-y divide-hairline sm:grid-cols-3 sm:divide-y-0"
                    >
                        <div class="px-5 py-4">
                            <p class="text-xs font-medium">
                                Deleted records
                            </p>
                            <p class="mt-1 text-2xl">
                                {{ trashedStudents.length }}
                            </p>
                        </div>

                        <div class="px-5 py-4">
                            <p class="text-xs font-medium">
                                Selected
                            </p>
                            <p class="mt-1 text-2xl">
                                {{ selectedStudents.length }}
                            </p>
                        </div>

                        <div class="col-span-2 px-5 py-4 sm:col-span-1">
                            <p class="text-xs font-medium">
                                Record status
                            </p>
                            <p class="mt-1 text-sm font-medium text-sienna">
                                Archived
                            </p>
                        </div>
                    </div>
                </section>


                <!-- BULK ACTIONS -->
                <div
                    v-if="selectedStudents.length > 0"
                    class="mt-6 border border-hairline bg-surface"
                >
                    <div
                        class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-medium text-ink">
                                {{ selectedStudents.length }} student{{
                                    selectedStudents.length === 1 ? '' : 's'
                                }}
                                selected
                            </p>

                            <p class="mt-1 text-xs text-ink-soft">
                                Choose an action for the selected records.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="bulkRestore"
                                class="border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-forest/90"
                            >
                                Restore selected
                            </button>

                            <button
                                type="button"
                                @click="bulkForceDelete"
                                class="border border-sienna bg-surface px-4 py-2.5 text-sm font-medium text-sienna hover:bg-sienna hover:text-white"
                            >
                                Delete permanently
                            </button>
                        </div>
                    </div>
                </div>


                <!-- TABLE -->
                <section class="mt-6 border border-hairline bg-surface">

                    <!-- TABLE HEADER -->
                    <div
                        class="flex flex-col gap-2 border-b border-hairline px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class=" text-xl font-medium text-ink"
                            >
                                Deleted students
                            </h2>

                            <p class="mt-1 text-xs text-ink-soft">
                                Archived records currently available for recovery.
                            </p>
                        </div>

                        <p class="text-xs text-ink-soft">
                            {{ trashedStudents.length }} record{{
                                trashedStudents.length === 1 ? '' : 's'
                            }}
                        </p>
                    </div>


                    <!-- RESPONSIVE TABLE -->
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-left">

                            <thead>
                                <tr
                                    class="border-b border-hairline bg-paper/60"
                                >
                                    <th class="w-12 px-5 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="
                                                selectedStudents.length === trashedStudents.length &&
                                                trashedStudents.length > 0
                                            "
                                            @change="toggleALlStudents"
                                            class="h-4 w-4 rounded-none border-hairline text-forest focus:ring-forest"
                                        />
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium"
                                    >
                                        ID
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium"
                                    >
                                        Student
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium"
                                    >
                                        Email
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium"
                                    >
                                        Phone
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-medium"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>


                            <tbody>
                                <tr
                                    v-for="student in trashedStudents"
                                    :key="student.id"
                                    class="border-b border-hairline last:border-b-0 hover:bg-paper/40"
                                >

                                    <!-- SELECT -->
                                    <td class="px-5 py-4">
                                        <input
                                            type="checkbox"
                                            :checked="isSelected(student.id)"
                                            @change="toggleStudent(student.id)"
                                            class="h-4 w-4 rounded-none border-hairline text-forest focus:ring-forest"
                                        />
                                    </td>


                                    <!-- ID -->
                                    <td
                                        class="px-5 py-4 text-sm text-ink-soft"
                                    >
                                        #{{ student.id }}
                                    </td>


                                    <!-- NAME -->
                                    <td class="px-5 py-4">
                                        <p
                                            class=" text-lg font-medium"
                                        >
                                            {{ student.name }}
                                        </p>
                                    </td>


                                    <!-- EMAIL -->
                                    <td
                                        class="px-5 py-4 text-sm"
                                    >
                                        {{ student.email }}
                                    </td>


                                    <!-- PHONE -->
                                    <td
                                        class="px-5 py-4 text-sm"
                                    >
                                        {{ student.phone || '—' }}
                                    </td>


                                    <!-- STATUS -->
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-sienna"
                                        >
                                            Deleted
                                        </span>
                                    </td>


                                    <!-- ACTIONS -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-4">

                                            <button
                                                type="button"
                                                @click="restoreStudent(student)"
                                                class="text-sm font-medium text-forest hover:underline"
                                            >
                                                Restore
                                            </button>

                                            <button
                                                type="button"
                                                @click="forceDeleteStudent(student)"
                                                class="text-sm font-medium text-sienna hover:underline"
                                            >
                                                Delete permanently
                                            </button>

                                        </div>
                                    </td>

                                </tr>
                            </tbody>

                        </table>
                    </div>
                </section>


                <!-- FOOTER NOTE -->
                <div
                    class="border-t border-hairline py-5"
                >
                    <p class="text-xs leading-5 text-ink-soft">
                        Permanently deleted records cannot be recovered.
                        Restore a student if you only want to remove them from
                        the archive.
                    </p>
                </div>

            </div>

        </div>
    </div>
</template>