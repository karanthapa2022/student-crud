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

        successMessage.value = 'Marksheet updated successfully.'

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

    <div class="p-6">

        <div class="flex justify-between items-center mb-6">

            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Edit Marksheet
                </h1>

                <p class="text-gray-500 dark:text-gray-400">
                    Update student marks
                </p>
            </div>

            <button
                type="button"
                @click="router.push('/marksheets')"
                class="px-4 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 transition"
            >
                ← Back to Marksheets
            </button>

        </div>

        <div
            v-if="loading"
            class="text-gray-500"
        >
            Loading marksheet...
        </div>

        <div
            v-else-if="error"
            class="p-4 bg-red-100 text-red-700 rounded-lg"
        >
            {{ error }}
        </div>

        <div
            v-else-if="marksheet"
            class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6"
        >

            <div class="mb-6">

                <p class="text-gray-700 dark:text-gray-300">
                    <strong>Student:</strong>
                    {{ marksheet.student?.name || '-' }}
                </p>

                <p class="mt-2 text-gray-700 dark:text-gray-300">
                    <strong>Class:</strong>
                    {{ marksheet.student?.class || '-' }}
                </p>

            </div>

            <div class="space-y-4">

                <div
                    v-for="subject in subjectStore.subjects"
                    :key="subject.id"
                    class="flex flex-col sm:flex-row sm:items-center gap-3"
                >

                    <label
                        class="w-full sm:w-48 font-medium text-gray-700 dark:text-gray-300"
                    >
                        {{ subject.name }}
                    </label>

                    <input
                        v-model="marks[subject.id]"
                        type="text"
                        inputmode="decimal"
                        placeholder="Enter marks or A"
                        class="w-full max-w-xs px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
                    >

                </div>

            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="p-4 rounded-xl bg-gray-100 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total
                    </p>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ totalMarks }}
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-gray-100 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Percentage
                    </p>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ percentage }}%
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-gray-100 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Grade
                    </p>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ grade }}
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-gray-100 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Result
                    </p>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ result }}
                    </p>
                </div>

            </div>

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

            <div class="mt-6 flex justify-end">

                <button
                    type="button"
                    :disabled="saving"
                    @click="saveChanges"
                    class="px-5 py-2.5 rounded-lg bg-purple-600 text-white font-medium hover:bg-purple-700 transition disabled:opacity-50"
                >
                    {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>

            </div>

        </div>

    </div>

</template>