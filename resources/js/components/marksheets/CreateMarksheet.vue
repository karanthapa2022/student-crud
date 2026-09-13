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
    <div class="min-h-screen bg-[#EFF1EA] text-[#1C2B24] dark:bg-[#17221D] dark:text-[#F5F7F3]">
        <div class="mx-auto w-full max-w-7xl px-5 py-7 lg:px-8">

            <!-- HEADER -->
            <header class="mb-8 border-b border-[#D8DDD3] dark:border-[#39483F] pb-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-2 text-sm font-medium text-[#2F6F4E] dark:text-[#75B28F]">
                            Academic records
                        </p>

                        <h1 class="text-3xl font-semibold tracking-tight text-[#1C2B24] dark:text-white">
                            Create Marksheet
                        </h1>

                        <p class="mt-2 max-w-xl text-sm leading-6 text-[#5B6B62] dark:text-[#AEBBB3]">
                            Create a new academic result for a student.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="$router.push('/marksheets')"
                        class="inline-flex items-center justify-center border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-semibold text-[#1C2B24] hover:border-[#2F6F4E] hover:text-[#2F6F4E] dark:border-[#39483F] dark:bg-[#202D26] dark:text-white dark:hover:border-[#75B28F] dark:hover:text-[#75B28F]"
                    >
                        Back to marksheets
                    </button>
                </div>
            </header>

            <!-- MESSAGES -->
            <div
                v-if="error"
                class="mb-5 border border-[#B5563C]/30 bg-[#B5563C]/5 px-4 py-3 text-sm text-[#8F3F2B] dark:border-[#B5563C]/40 dark:bg-[#B5563C]/10 dark:text-[#E6A18E]"
            >
                <div class="flex items-start gap-3">
                    <span class="font-semibold">!</span>
                    <span>{{ error }}</span>
                </div>
            </div>

            <div
                v-if="successMessage"
                class="mb-5 border border-[#2F6F4E]/30 bg-[#2F6F4E]/5 px-4 py-3 text-sm font-medium text-[#2F6F4E] dark:border-[#75B28F]/30 dark:bg-[#75B28F]/10 dark:text-[#A8D1B8]"
            >
                ✓ {{ successMessage }}
            </div>

            <!-- MAIN SURFACE -->
            <section class="overflow-hidden border border-[#D8DDD3] bg-white dark:border-[#39483F] dark:bg-[#202D26]">

                <!-- STUDENT -->
                <div class="border-b border-[#D8DDD3] p-6 dark:border-[#39483F] lg:p-7">
                    <div class="mb-6">
                

                        <h2 class="mt-1 text-2xl font-semibold text-[#1C2B24] dark:text-white">
                            Student information
                        </h2>

                        <p class="mt-1 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                            Select the student who will receive this marksheet.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-[#1C2B24] dark:text-white">
                                Student
                            </label>

                            <select
                                v-model="selectedStudent"
                                class="w-full border border-[#D8DDD3] bg-white px-4 py-3 text-sm text-[#1C2B24] outline-none focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#39483F] dark:bg-[#17221D] dark:text-white"
                            >
                                <option :value="null">
                                    Select student
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

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-[#1C2B24] dark:text-white">
                                Class
                            </label>

                            <input
                                type="text"
                                :value="selectedStudent?.class || ''"
                                disabled
                                placeholder="Class will appear here"
                                class="w-full border border-[#D8DDD3] bg-[#EFF1EA] px-4 py-3 text-sm text-[#5B6B62] outline-none dark:border-[#39483F] dark:bg-[#17221D] dark:text-[#7F9086]"
                            />
                        </div>
                    </div>
                </div>

                <!-- SUBJECTS -->
                <div class="p-6 lg:p-7">
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>

                            <h2 class="mt-1 text-2xl font-semibold text-[#1C2B24] dark:text-white">
                                Subjects & marks
                            </h2>

                            <p class="mt-1 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                                Add each subject and its marking scheme.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="addSubject"
                            class="inline-flex items-center justify-center gap-2 bg-[#2F6F4E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#275E42]"
                        >
                            <span class="text-lg leading-none">+</span>
                            Add subject
                        </button>
                    </div>

                    <!-- TABLE -->
                    <div class="overflow-x-auto border border-[#D8DDD3] dark:border-[#39483F]">
                        <table class="w-full min-w-[950px]">
                            <thead class="border-b border-[#D8DDD3] bg-[#EFF1EA] dark:border-[#39483F] dark:bg-[#17221D]">
                                <tr>
                                    <th class="w-16 px-4 py-3 text-left text-xs font-semibold text-[#5B6B62] dark:text-[#AEBBB3]">
                                        S.N.
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Subject
                                    </th>

                                    <th class="w-40 px-4 py-3 text-left text-xs font-semibold text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Full marks
                                    </th>

                                    <th class="w-40 px-4 py-3 text-left text-xs font-semibold text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Pass marks
                                    </th>

                                    <th class="w-48 px-4 py-3 text-left text-xs font-semibold text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Obtained
                                    </th>

                                    <th class="w-24 px-4 py-3 text-center text-xs font-semibold text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="(subject, index) in subjects"
                                    :key="index"
                                    class="border-b border-[#D8DDD3] last:border-b-0 dark:border-[#39483F]"
                                >
                                    <td class="px-4 py-4 text-sm font-semibold text-[#5B6B62] dark:text-[#AEBBB3]">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <input
                                            v-model="subject.subject_name"
                                            type="text"
                                            placeholder="e.g. Mathematics"
                                            class="w-full border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm text-[#1C2B24] outline-none focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#39483F] dark:bg-[#17221D] dark:text-white"
                                        >
                                    </td>

                                    <td class="px-4 py-4">
                                        <input
                                            v-model="subject.full_marks"
                                            type="number"
                                            min="1"
                                            step="0.01"
                                            placeholder="100"
                                            class="w-full border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm text-[#1C2B24] outline-none focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#39483F] dark:bg-[#17221D] dark:text-white"
                                        >
                                    </td>

                                    <td class="px-4 py-4">
                                        <input
                                            v-model="subject.pass_marks"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            placeholder="40"
                                            class="w-full border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm text-[#1C2B24] outline-none focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#39483F] dark:bg-[#17221D] dark:text-white"
                                        >
                                    </td>

                                    <td class="px-4 py-4">
                                        <input
                                            v-model="subject.marks"
                                            type="text"
                                            inputmode="decimal"
                                            placeholder="Marks or A"
                                            class="w-full border border-[#D8DDD3] bg-white px-3 py-2.5 text-sm text-[#1C2B24] outline-none focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#39483F] dark:bg-[#17221D] dark:text-white"
                                        >
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="removeSubject(index)"
                                            :disabled="subjects.length === 1"
                                            class="text-sm font-medium text-[#B5563C] hover:underline disabled:cursor-not-allowed disabled:opacity-30"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- LEDGER SUMMARY -->
                    <div class="mt-6 border-y border-[#D8DDD3] dark:border-[#39483F]">
                        <div class="grid grid-cols-2 divide-x divide-[#D8DDD3] dark:divide-[#39483F] md:grid-cols-4">
                            <div class="p-4">
                                <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Full marks</p>
                                <p class="mt-1 text-xl font-semibold">{{ totalFullMarks }}</p>
                            </div>

                            <div class="p-4">
                                <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Obtained</p>
                                <p class="mt-1 text-xl font-semibold">{{ totalMarks }}</p>
                            </div>

                            <div class="border-t border-[#D8DDD3] p-4 dark:border-[#39483F] md:border-t-0">
                                <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Percentage</p>
                                <p class="mt-1 text-xl font-semibold">{{ percentage }}%</p>
                            </div>

                            <div class="border-t border-[#D8DDD3] p-4 dark:border-[#39483F] md:border-t-0">
                                <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Result</p>
                                <p
                                    class="mt-1 text-xl font-semibold"
                                    :class="result === 'Pass' ? 'text-[#2F6F4E] dark:text-[#75B28F]' : 'text-[#B5563C]'"
                                >
                                    {{ result }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- SAVE -->
                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-[#D8DDD3] pt-6 dark:border-[#39483F] sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="$router.push('/marksheets')"
                            class="border border-[#D8DDD3] px-5 py-2.5 text-sm font-semibold text-[#5B6B62] hover:border-[#1C2B24] hover:text-[#1C2B24] dark:border-[#39483F] dark:text-[#AEBBB3] dark:hover:border-white dark:hover:text-white"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            :disabled="saving"
                            @click="saveMarksheet"
                            class="bg-[#2F6F4E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#275E42] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ saving ? 'Saving...' : 'Save marksheet' }}
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>