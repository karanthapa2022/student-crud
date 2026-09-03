<script setup>

import { ref, onMounted, computed } from 'vue'
import { useStudentStore } from '../../stores/students/student'
import {useSubjectStore} from '../../stores/subjects/subject'
import { createMarksheet } from '../../services/marksheets/marksheetApi'

const studentStore = useStudentStore()
const subjectStore = useSubjectStore()

const selectedStudent = ref(null)
const marks = ref({})

const totalMarks = computed(() => {
    return Object.values(marks.value)
        .reduce((total, mark) => total + (Number(mark) || 0), 0)
})

const totalSubjects = computed(() => {
    return subjectStore.subjects.length
})

const percentage = computed(() => {

    if (totalSubjects.value === 0) {
        return 0
    }

    return (
        (totalMarks.value / (totalSubjects.value * 100)) * 100
    ).toFixed(2)
})

const grade = computed(() => {

    const value = Number(percentage.value)

    if (value >= 80) {
        return 'A+'
    } else if (value >= 70) {
        return 'A'
    } else if (value >= 60) {
        return 'B+'
    } else if (value >= 50) {
        return 'B'
    } else if (value >= 40) {
        return 'C'
    } else if (value >= 30) {
        return 'D'
    }

    return 'F'
})

const result = computed(() => {

    const subjectMarks = Object.values(marks.value)

    if (subjectMarks.length === 0) {
        return 'Fail'
    }

    const hasFailedSubject = subjectMarks.some(mark => {

        const value = String(mark).trim().toUpperCase()

        // Absent = Fail
        if (value === 'A') {
            return true
        }

        // Less than 40 = Fail
        return Number(value) < 40

    })

    return hasFailedSubject
        ? 'Fail'
        : 'Pass'

})

const saving = ref(false)
const error = ref('')
const successMessage = ref('')

const saveMarksheet = async () => {

    error.value = ''
    successMessage.value = ''

    if (!selectedStudent.value) {
        error.value = 'Please select a student.'
        return
    }

    const items = Object.entries(marks.value)
    .filter(([_, mark]) => mark !== '' && mark !== null && mark !== undefined)
    .map(([subjectId, mark]) => {

        const value = String(mark).trim().toUpperCase()

        return {
            subject_id: Number(subjectId),
            marks: value === 'A' ? 'A' : Number(value)
        }

    })

    if (items.length !== subjectStore.subjects.length) {
        error.value = 'Please enter marks for every subject.'
        return
    }

    saving.value = true

    try {

        await createMarksheet({
            student_id: selectedStudent.value.id,
            items: items
        })

        successMessage.value = 'Marksheet saved successfully.'
        selectedStudent.value=null
        marks.value={}
        setTimeout(()=>{
            successMessage.value=''
        },3000)

    } catch (err) {

        console.error(err)

        error.value = 'Failed to save marksheet.'

    } finally {

        saving.value = false

    }

}

onMounted(async () => {
    await subjectStore.fetchSubjects()
    await studentStore.fetchStudents()
})

</script>


<template>

    <div
        class="min-h-screen bg-gray-100 dark:bg-gray-950 p-4 sm:p-6 lg:p-8"
    >

        <div
            class="w-full max-w-5xl mx-auto"
        >

            <!-- HEADER -->

            <div class="mb-6">

                <h1
                    class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white"
                >
                    Create Marksheet
                </h1>

                <p
                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                >
                    Create a new marksheet for a student
                </p>
                <div class="flex justify-end mb-6">
                <button
        type="button"
        @click="$router.push('/marksheets')"
        class="px-4 py-2 rounded-lg bg-purple-600 text-white font-medium hover:bg-purple-700 transition"
    >
        Go to Marksheets
    </button>
    </div>


            </div>
            


            <!-- FORM CARD -->

            <div
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm"
            >

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-5"
                >

                    <!-- STUDENT -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Student
                        </label>

                        <select
    v-model="selectedStudent"
    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
>

    <option :value="null">
        Select Student
    </option>

    <option
        v-for="student in studentStore.students"
        :key="student.id"
        :value="student"
    >
        {{ student.name }}
    </option>

</select>

                    </div>


                    <!-- CLASS -->

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Class
                        </label>

                        <input
    type="text"
    :value="selectedStudent?.class || ''"
    disabled
    placeholder="Class will appear here"
    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400"
>

                    </div>

                </div>

                <!-- SUCCESS -->

<div
    v-if="successMessage"
    class="mt-6 flex justify-start"
>
    <button
        type="button"
        class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium shadow-sm cursor-default"
    >
        ✓ {{ successMessage }}
    </button>
    
</div>
<!-- ERROR -->

<div
    v-if="error"
    class="mt-6 p-3 bg-red-100 text-red-700 rounded-xl"
>
    {{ error }}
</div>


                <!-- SUBJECTS SECTION -->

                <div class="mt-8">

                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white"
                    >
                        Subjects & Marks
                    </h2>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Select subjects and enter the marks obtained.
                    </p>

                </div>


                <!-- SUBJECT TABLE -->

                <div
                    class="mt-4 overflow-x-auto"
                >

                    <table class="w-full">

                        <thead class="bg-gray-100 dark:bg-gray-800">

                            <tr>

                                <th class="px-4 py-3 text-left">
                                    S.N.
                                </th>

                                <th class="px-4 py-3 text-left">
                                    Subject
                                </th>

                                <th class="px-4 py-3 text-left">
                                    Marks
                                </th>

                            </tr>

                        </thead>

                        <tbody>

    <tr
        v-for="(subject, index) in subjectStore.subjects"
        :key="subject.id"
        class="border-t dark:border-gray-800"
    >

        <td class="px-4 py-3">
            {{ index + 1 }}
        </td>

        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">
            {{ subject.name }}
        </td>

        <td class="px-4 py-3">

            <input
    v-model="marks[subject.id]"
    type="text"
    inputmode="decimal"
    placeholder="Enter marks or A"
    class="w-full max-w-xs px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
>

        </td>

    </tr>


    <tr
        v-if="subjectStore.subjects.length === 0"
    >

        <td
            colspan="3"
            class="px-4 py-8 text-center text-gray-500"
        >
            No subjects found.
        </td>

    </tr>

</tbody>

                    </table>
                    <div
    class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
>

    <!-- TOTAL -->

    <div
        class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
    >

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Total Marks
        </p>

        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
            {{ totalMarks }}
        </p>

    </div>


    <!-- PERCENTAGE -->

    <div
        class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
    >

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Percentage
        </p>

        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
            {{ percentage }}%
        </p>



    </div>

        <!-- GRADE -->

<div
    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Grade
    </p>

    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
        {{ grade }}
    </p>

</div>

        <!-- RESULT -->

<div
    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Result
    </p>

    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">
        {{ result }}
    </p>

</div>

            

</div>

                </div>

            </div>
            





            <div class="mt-8 flex justify-end">

    <button
    type="button"
    @click="saveMarksheet"
    :disabled="saving"
    class="px-6 py-3 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
>
    {{ saving ? 'Saving...' : 'Save Marksheet' }}
</button>

</div>

        </div>
        

    </div>

</template>