<script setup>

import { ref, onMounted, computed } from 'vue'
import { useStudentStore } from '../../stores/students/student'
import { useSubjectStore } from '../../stores/subjects/subject'
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

        if (value === 'A') {
            return true
        }

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
        .filter(([_, mark]) =>
            mark !== '' &&
            mark !== null &&
            mark !== undefined
        )
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

        successMessage.value =
            'Marksheet saved successfully.'

        selectedStudent.value = null
        marks.value = {}

        setTimeout(() => {
            successMessage.value = ''
        }, 3000)

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
    class="p-6 lg:p-8 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors"
>

    <div
        class="w-full max-w-6xl mx-auto"
    >

        <!-- =================================================
             PAGE HEADER
        ================================================== -->

        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7"
        >

            <div>

                <h1
                    class="text-2xl font-bold text-gray-900 dark:text-white"
                >
                    Create Marksheet
                </h1>

                <p
                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                >
                    Create a new marksheet for a student
                </p>

            </div>


            <!-- BACK BUTTON -->

            <button
                type="button"
                @click="$router.push('/marksheets')"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-gray-800 dark:bg-gray-700 text-white text-sm font-semibold shadow-sm hover:bg-gray-900 dark:hover:bg-gray-600 transition"
            >
                ← Marksheets
            </button>

        </div>


        <!-- =================================================
             ERROR
        ================================================== -->

        <div
            v-if="error"
            class="mb-5 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-xl"
        >

            <div class="flex items-center gap-2">

                <span>
                    ⚠️
                </span>

                <span>
                    {{ error }}
                </span>

            </div>

        </div>


        <!-- =================================================
             SUCCESS
        ================================================== -->

        <div
            v-if="successMessage"
            class="mb-5"
        >

            <div
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800 text-sm font-medium"
            >

                <span>
                    ✓
                </span>

                {{ successMessage }}

            </div>

        </div>


        <!-- =================================================
             MAIN FORM CARD
        ================================================== -->

        <div
            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden transition-colors"
        >

            <!-- =================================================
                 STUDENT INFORMATION
            ================================================== -->

            <div
                class="p-6 border-b border-gray-200 dark:border-gray-700"
            >

                <h2
                    class="text-xl font-semibold text-gray-900 dark:text-white"
                >
                    Student Information
                </h2>

                <p
                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                >
                    Select the student for whom you want to create the marksheet.
                </p>


                <!-- STUDENT + CLASS -->

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6"
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
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                        >

                            <option
                                :value="null"
                            >
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
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 placeholder-gray-400 dark:placeholder-gray-500"
                        >

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SUBJECTS & MARKS
            ================================================== -->

            <div class="p-6">

                <div class="mb-5">

                    <h2
                        class="text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        Subjects & Marks
                    </h2>

                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                    >
                        Enter the marks obtained for each subject.
                    </p>

                </div>


                <!-- SUBJECT TABLE -->

                <div
                    class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl"
                >

                    <table
                        class="w-full min-w-[650px]"
                    >

                        <!-- HEADER -->

                        <thead
                            class="bg-gray-50 dark:bg-gray-700"
                        >

                            <tr>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                                >
                                    S.N.
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                                >
                                    Subject
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                                >
                                    Marks
                                </th>

                            </tr>

                        </thead>


                        <!-- BODY -->

                        <tbody>

                            <tr
                                v-for="(subject, index) in subjectStore.subjects"
                                :key="subject.id"
                                class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                            >

                                <!-- S.N. -->

                                <td
                                    class="px-4 py-3 text-gray-700 dark:text-gray-300"
                                >
                                    {{ index + 1 }}
                                </td>


                                <!-- SUBJECT -->

                                <td
                                    class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100"
                                >
                                    {{ subject.name }}
                                </td>


                                <!-- MARKS -->

                                <td
                                    class="px-4 py-3"
                                >

                                    <input
                                        v-model="marks[subject.id]"
                                        type="text"
                                        inputmode="decimal"
                                        placeholder="Enter marks or A"
                                        class="w-full max-w-xs px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                                    >

                                </td>

                            </tr>


                            <!-- NO SUBJECTS -->

                            <tr
                                v-if="subjectStore.subjects.length === 0"
                            >

                                <td
                                    colspan="3"
                                    class="px-4 py-10 text-center text-gray-500 dark:text-gray-400"
                                >

                                    <div
                                        class="flex flex-col items-center"
                                    >

                                        <div
                                            class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3 text-xl"
                                        >
                                            📚
                                        </div>

                                        <p
                                            class="font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            No subjects found
                                        </p>

                                        <p
                                            class="text-sm mt-1"
                                        >
                                            Add subjects before creating a marksheet.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- =================================================
                     RESULT PREVIEW
                ================================================== -->

                <div class="mt-8">

                    <div class="mb-4">

                        <h2
                            class="text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Result Preview
                        </h2>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                        >
                            The result is calculated automatically as you enter marks.
                        </p>

                    </div>


                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                    >

                        <!-- TOTAL -->

                        <div
                            class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                        >

                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Total Marks
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ totalMarks }}
                            </p>

                        </div>


                        <!-- PERCENTAGE -->

                        <div
                            class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                        >

                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Percentage
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ percentage }}%
                            </p>

                        </div>


                        <!-- GRADE -->

                        <div
                            class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/30 border border-purple-100 dark:border-purple-800"
                        >

                            <p
                                class="text-xs font-medium uppercase tracking-wide text-purple-600 dark:text-purple-300"
                            >
                                Grade
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-purple-700 dark:text-purple-200"
                            >
                                {{ grade }}
                            </p>

                        </div>


                        <!-- RESULT -->

                        <div
                            class="p-4 rounded-xl border"
                            :class="
                                result === 'Pass'
                                    ? 'bg-green-50 dark:bg-green-900/30 border-green-100 dark:border-green-800'
                                    : 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-800'
                            "
                        >

                            <p
                                class="text-xs font-medium uppercase tracking-wide"
                                :class="
                                    result === 'Pass'
                                        ? 'text-green-600 dark:text-green-300'
                                        : 'text-red-600 dark:text-red-300'
                                "
                            >
                                Result
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold"
                                :class="
                                    result === 'Pass'
                                        ? 'text-green-700 dark:text-green-200'
                                        : 'text-red-700 dark:text-red-200'
                                "
                            >
                                {{ result }}
                            </p>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div
                    class="mt-7 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3"
                >

                    <!-- CANCEL -->

                    <button
                        type="button"
                        @click="$router.push('/marksheets')"
                        class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        Cancel
                    </button>


                    <!-- SAVE -->

                    <button
                        type="button"
                        @click="saveMarksheet"
                        :disabled="saving"
                        class="px-6 py-2.5 rounded-lg bg-green-600 text-white font-semibold shadow-sm hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >

                        <span v-if="saving">
                            Saving...
                        </span>

                        <span v-else>
                            Save Marksheet
                        </span>

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

</template>