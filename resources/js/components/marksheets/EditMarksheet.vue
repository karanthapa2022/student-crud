<script setup>

import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { getMarksheet } from '../../services/marksheets/marksheetApi'
import { useSubjectStore } from '../../stores/subjects/subject'
import { updateMarksheet } from '../../services/marksheets/marksheetApi'

const route = useRoute()
const router = useRouter()

const subjectStore = useSubjectStore()

const marksheet = ref(null)
const marks = ref({})

const loading = ref(false)
const saving = ref(false)

const error = ref('')
const successMessage = ref('')

const totalMarks = computed(() => {

    return Object.values(marks.value)
        .reduce((total, mark) => {

            const value = String(mark).trim().toUpperCase()

            if (value === 'A') {
                return total
            }

            return total + (Number(value) || 0)

        }, 0)

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

const fetchMarksheet = async () => {

    loading.value = true
    error.value = ''

    try {

        await subjectStore.fetchSubjects()

        const response = await getMarksheet(route.params.id)

        marksheet.value = response.data

        response.data.items.forEach(item => {
            marks.value[item.subject_id] = item.marks
        })

    } catch (err) {

        console.error(err)

        error.value = 'Failed to load marksheet.'

    } finally {

        loading.value = false

    }

}

onMounted(() => {
    fetchMarksheet()
})

const saveChanges = async () => {

    error.value = ''
    successMessage.value = ''

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

        await updateMarksheet(
            route.params.id,
            {
                student_id: marksheet.value.student_id,
                items: items
            }
        )

        await fetchMarksheet()

        successMessage.value =
            'Marksheet updated successfully.'

        setTimeout(() => {
            successMessage.value = ''
        }, 3000)

    } catch (err) {

        console.error(err)

        error.value = 'Failed to update marksheet.'

    } finally {

        saving.value = false

    }

}

</script>


<template>

<div
    class="p-6 lg:p-8 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors"
>

    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7"
    >

        <div>

            <h1
                class="text-2xl font-bold text-gray-900 dark:text-white"
            >
                Edit Marksheet
            </h1>

            <p
                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
            >
                Update student marks and result
            </p>

        </div>


        <!-- BACK BUTTON -->

        <button
            type="button"
            @click="router.push('/marksheets')"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-gray-800 dark:bg-gray-700 text-white text-sm font-semibold shadow-sm hover:bg-gray-900 dark:hover:bg-gray-600 transition"
        >
            ← Back to Marksheets
        </button>

    </div>


    <!-- =====================================================
         LOADING
    ====================================================== -->

    <div
        v-if="loading"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm py-12 transition-colors"
    >

        <div
            class="flex flex-col items-center justify-center"
        >

            <div
                class="w-8 h-8 border-4 border-gray-200 dark:border-gray-600 border-t-purple-600 rounded-full animate-spin mb-3"
            ></div>

            <p
                class="text-sm text-gray-500 dark:text-gray-400"
            >
                Loading marksheet...
            </p>

        </div>

    </div>


    <!-- =====================================================
         ERROR
    ====================================================== -->

    <div
        v-else-if="error"
        class="p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-xl"
    >
        {{ error }}
    </div>


    <!-- =====================================================
         EDIT MARKSHEET CARD
    ====================================================== -->

    <div
        v-else-if="marksheet"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-colors"
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
                Student whose marksheet is being updated
            </p>


            <!-- STUDENT INFO -->

            <div
                class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6"
            >

                <!-- STUDENT -->

                <div
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Student
                    </p>

                    <p
                        class="mt-1 text-base font-semibold text-gray-900 dark:text-white"
                    >
                        {{ marksheet.student?.name || '-' }}
                    </p>

                </div>


                <!-- CLASS -->

                <div
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600"
                >

                    <p
                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                    >
                        Class
                    </p>

                    <p
                        class="mt-1 text-base font-semibold text-gray-900 dark:text-white"
                    >
                        {{ marksheet.student?.class || '-' }}
                    </p>

                </div>

            </div>

        </div>


        <!-- =================================================
             SUBJECT MARKS
        ================================================== -->

        <div class="p-6">

            <div class="mb-5">

                <h2
                    class="text-xl font-semibold text-gray-900 dark:text-white"
                >
                    Update Subject Marks
                </h2>

                <p
                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                >
                    Enter marks for each subject. Use <strong class="text-gray-700 dark:text-gray-200">A</strong> for an absent student.
                </p>

            </div>


            <!-- SUBJECT LIST -->

            <div
                class="space-y-3"
            >

                <div
                    v-for="subject in subjectStore.subjects"
                    :key="subject.id"
                    class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 transition-colors"
                >

                    <!-- SUBJECT NAME -->

                    <label
                        class="w-full sm:w-56 font-medium text-gray-800 dark:text-gray-100"
                    >
                        {{ subject.name }}
                    </label>


                    <!-- MARK INPUT -->

                    <input
                        v-model="marks[subject.id]"
                        type="text"
                        inputmode="decimal"
                        placeholder="Enter marks or A"
                        class="w-full sm:max-w-xs px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                    >

                </div>

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
                        The result is calculated automatically from the marks above.
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
                            Total
                        </p>

                        <p
                            class="mt-1 text-xl font-bold text-gray-900 dark:text-white"
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
                            class="mt-1 text-xl font-bold text-gray-900 dark:text-white"
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
                            class="mt-1 text-xl font-bold text-purple-700 dark:text-purple-200"
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
                            class="mt-1 text-xl font-bold"
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
                 SUCCESS MESSAGE
            ================================================== -->

            <div
                v-if="successMessage"
                class="mt-6"
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
                 SAVE BUTTON
            ================================================== -->

            <div
                class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3"
            >

                <button
                    type="button"
                    @click="router.push('/marksheets')"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    :disabled="saving"
                    @click="saveChanges"
                    class="px-5 py-2.5 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >

                    <span v-if="saving">
                        Saving...
                    </span>

                    <span v-else>
                        Save Changes
                    </span>

                </button>

            </div>

        </div>

    </div>

</div>

</template>