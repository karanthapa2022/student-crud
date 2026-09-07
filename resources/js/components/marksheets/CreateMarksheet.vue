<script setup>

import { ref, onMounted, computed } from 'vue'
import { useStudentStore } from '../../stores/students/student'
import { createMarksheet } from '../../services/marksheets/marksheetApi'

const studentStore = useStudentStore()

const selectedStudent = ref(null)

const subjects = ref([
    {
        subject_name: '',
        full_marks: 100,
        pass_marks: 40,
        marks: ''
    }
])

const saving = ref(false)
const error = ref('')
const successMessage = ref('')


// =========================================================
// ADD SUBJECT
// =========================================================

const addSubject = () => {

    subjects.value.push({
        subject_name: '',
        full_marks: 100,
        pass_marks: 40,
        marks: ''
    })

}


// =========================================================
// REMOVE SUBJECT
// =========================================================

const removeSubject = (index) => {

    if (subjects.value.length === 1) {
        return
    }

    subjects.value.splice(index, 1)

}


// =========================================================
// TOTAL FULL MARKS
// =========================================================

const totalFullMarks = computed(() => {

    return subjects.value.reduce((total, subject) => {

        return total + (Number(subject.full_marks) || 0)

    }, 0)

})


// =========================================================
// TOTAL OBTAINED MARKS
// =========================================================

const totalMarks = computed(() => {

    return subjects.value.reduce((total, subject) => {

        const value = String(subject.marks)
            .trim()
            .toUpperCase()

        if (value === 'A' || value === '') {
            return total
        }

        return total + (Number(value) || 0)

    }, 0)

})


// =========================================================
// PERCENTAGE
// =========================================================

const percentage = computed(() => {

    if (totalFullMarks.value <= 0) {
        return '0.00'
    }

    return (
        (totalMarks.value / totalFullMarks.value) * 100
    ).toFixed(2)

})


// =========================================================
// GRADE
// =========================================================

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


// =========================================================
// RESULT
// =========================================================

const result = computed(() => {

    if (subjects.value.length === 0) {
        return 'Fail'
    }

    const hasIncompleteSubject = subjects.value.some(subject => {

        return (
            !String(subject.subject_name).trim() ||
            subject.full_marks === '' ||
            subject.pass_marks === '' ||
            subject.marks === ''
        )

    })

    if (hasIncompleteSubject) {
        return 'Fail'
    }

    const hasFailedSubject = subjects.value.some(subject => {

        const marks = String(subject.marks)
            .trim()
            .toUpperCase()

        const passMarks = Number(subject.pass_marks) || 0

        if (marks === 'A') {
            return true
        }

        return Number(marks) < passMarks

    })

    return hasFailedSubject
        ? 'Fail'
        : 'Pass'

})


// =========================================================
// VALIDATE SUBJECTS
// =========================================================

const validateSubjects = () => {

    const names = []

    for (let index = 0; index < subjects.value.length; index++) {

        const subject = subjects.value[index]

        const subjectNumber = index + 1

        const name = String(subject.subject_name).trim()

        if (!name) {
            error.value =
                `Please enter the subject name for subject ${subjectNumber}.`

            return false
        }

        if (
            subject.full_marks === '' ||
            subject.full_marks === null ||
            subject.full_marks === undefined
        ) {
            error.value =
                `Please enter full marks for ${name}.`

            return false
        }

        if (Number(subject.full_marks) <= 0) {
            error.value =
                `Full marks must be greater than 0 for ${name}.`

            return false
        }

        if (
            subject.pass_marks === '' ||
            subject.pass_marks === null ||
            subject.pass_marks === undefined
        ) {
            error.value =
                `Please enter pass marks for ${name}.`

            return false
        }

        if (Number(subject.pass_marks) < 0) {
            error.value =
                `Pass marks cannot be negative for ${name}.`

            return false
        }

        if (
            Number(subject.pass_marks) >
            Number(subject.full_marks)
        ) {
            error.value =
                `Pass marks cannot be greater than full marks for ${name}.`

            return false
        }

        if (
            subject.marks === '' ||
            subject.marks === null ||
            subject.marks === undefined
        ) {
            error.value =
                `Please enter obtained marks for ${name}.`

            return false
        }

        const marks = String(subject.marks)
            .trim()
            .toUpperCase()

        if (marks !== 'A') {

            if (
                !isFinite(Number(marks)) ||
                Number(marks) < 0
            ) {
                error.value =
                    `Please enter valid marks for ${name}, or A for absent.`

                return false
            }

            if (
                Number(marks) >
                Number(subject.full_marks)
            ) {
                error.value =
                    `Obtained marks cannot be greater than full marks for ${name}.`

                return false
            }

        }

        const normalizedName = name.toLowerCase()

        if (names.includes(normalizedName)) {

            error.value =
                `The subject "${name}" has been added more than once.`

            return false
        }

        names.push(normalizedName)

    }

    return true

}


// =========================================================
// SAVE MARKSHEET
// =========================================================

const saveMarksheet = async () => {

    error.value = ''
    successMessage.value = ''

    if (!selectedStudent.value) {

        error.value =
            'Please select a student.'

        return

    }

    if (!validateSubjects()) {
        return
    }

    saving.value = true

    try {

        const items = subjects.value.map(subject => {

            const marks = String(subject.marks)
                .trim()
                .toUpperCase()

            return {
                subject_name: String(subject.subject_name).trim(),
                full_marks: Number(subject.full_marks),
                pass_marks: Number(subject.pass_marks),
                marks: marks === 'A'
                    ? 'A'
                    : Number(marks)
            }

        })

        await createMarksheet({
            student_id: selectedStudent.value.id,
            items: items
        })

        successMessage.value =
            'Marksheet saved successfully.'

        selectedStudent.value = null

        subjects.value = [
            {
                subject_name: '',
                full_marks: 100,
                pass_marks: 40,
                marks: ''
            }
        ]

        setTimeout(() => {

            successMessage.value = ''

        }, 3000)

    } catch (err) {

        console.error(err)

        if (err.response?.data?.message) {

            error.value =
                err.response.data.message

        } else {

            error.value =
                'Failed to save marksheet.'

        }

    } finally {

        saving.value = false

    }

}


// =========================================================
// LOAD STUDENTS
// =========================================================

onMounted(async () => {

    await studentStore.fetchStudents()

})

</script>


<template>

<div
    class="p-6 lg:p-8 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors"
>

    <div
        class="w-full max-w-7xl mx-auto"
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

                <div
                    class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5"
                >

                    <div>

                        <h2
                            class="text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Subjects & Marks
                        </h2>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                        >
                            Add the subjects and marking scheme for this marksheet.
                        </p>

                    </div>


                    <!-- ADD SUBJECT -->

                    <button
                        type="button"
                        @click="addSubject"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold shadow-sm hover:bg-green-700 transition"
                    >
                        + Add Subject
                    </button>

                </div>


                <!-- SUBJECT TABLE -->

                <div
                    class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl"
                >

                    <table
                        class="w-full min-w-[1000px]"
                    >

                        <!-- HEADER -->

                        <thead
                            class="bg-gray-50 dark:bg-gray-700"
                        >

                            <tr>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200 w-16"
                                >
                                    S.N.
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200"
                                >
                                    Subject
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200 w-40"
                                >
                                    Full Marks
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200 w-40"
                                >
                                    Pass Marks
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-200 w-48"
                                >
                                    Obtained Marks
                                </th>

                                <th
                                    class="px-4 py-3 text-center text-sm font-semibold text-gray-600 dark:text-gray-200 w-24"
                                >
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <!-- BODY -->

                        <tbody>

                            <tr
                                v-for="(subject, index) in subjects"
                                :key="index"
                                class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                            >

                                <!-- S.N. -->

                                <td
                                    class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium"
                                >
                                    {{ index + 1 }}
                                </td>


                                <!-- SUBJECT -->

                                <td
                                    class="px-4 py-3"
                                >

                                    <input
                                        v-model="subject.subject_name"
                                        type="text"
                                        placeholder="e.g. Mathematics"
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                                    >

                                </td>


                                <!-- FULL MARKS -->

                                <td
                                    class="px-4 py-3"
                                >

                                    <input
                                        v-model="subject.full_marks"
                                        type="number"
                                        min="1"
                                        step="0.01"
                                        placeholder="100"
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                                    >

                                </td>


                                <!-- PASS MARKS -->

                                <td
                                    class="px-4 py-3"
                                >

                                    <input
                                        v-model="subject.pass_marks"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="40"
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                                    >

                                </td>


                                <!-- OBTAINED MARKS -->

                                <td
                                    class="px-4 py-3"
                                >

                                    <input
                                        v-model="subject.marks"
                                        type="text"
                                        inputmode="decimal"
                                        placeholder="e.g. 78 or A"
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                                    >

                                </td>


                                <!-- REMOVE -->

                                <td
                                    class="px-4 py-3 text-center"
                                >

                                    <button
                                        type="button"
                                        @click="removeSubject(index)"
                                        :disabled="subjects.length === 1"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                        title="Remove subject"
                                    >
                                        🗑️
                                    </button>

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
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4"
                    >

                        <!-- TOTAL MARKS -->

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
                                {{ totalMarks }} / {{ totalFullMarks }}
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


                        <!-- SUBJECT COUNT -->

                        <div
                            class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800"
                        >

                            <p
                                class="text-xs font-medium uppercase tracking-wide text-blue-600 dark:text-blue-300"
                            >
                                Subjects
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-200"
                            >
                                {{ subjects.length }}
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