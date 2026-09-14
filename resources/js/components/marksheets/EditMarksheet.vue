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
    return marksheet.value?.items?.length || 0
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

    const items = marksheet.value.items
        .filter(item => marks.value[item.subject_id] !== '' && marks.value[item.subject_id] !== null && marks.value[item.subject_id] !== undefined)
        .map(item => {

            const value = String(marks.value[item.subject_id]).trim().toUpperCase()

            return {
                subject_id: item.subject_id,
                subject_name: item.subject_name,
                full_marks: item.full_marks,
                pass_marks: item.pass_marks,
                marks: value === 'A' ? 'A' : Number(value)
            }

        })

    if (items.length !== totalSubjects.value) {
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
    <div class="min-h-screen bg-[#EFF1EA] text-[#1C2B24] dark:bg-[#17221D] dark:text-[#F5F7F3]">
        <div class="mx-auto w-full max-w-6xl px-5 py-7 lg:px-8">

            <header class="mb-8 border-b border-[#D8DDD3] pb-6 dark:border-[#39483F]">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-2 text-sm font-medium text-[#2F6F4E] dark:text-[#75B28F]">
                            Academic records
                        </p>

                        <h1 class=" text-3xl font-semibold tracking-tight">
                            Edit Marksheet
                        </h1>

                        <p class="mt-2 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                            Update the student's subject marks and result.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="router.push('/marksheets')"
                        class="border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-semibold hover:border-[#2F6F4E] hover:text-[#2F6F4E] dark:border-[#39483F] dark:bg-[#202D26] dark:hover:border-[#75B28F] dark:hover:text-[#75B28F]"
                    >
                        Back to marksheets
                    </button>
                </div>
            </header>

            <div
                v-if="error"
                class="mb-5 border border-[#B5563C]/30 bg-[#B5563C]/5 px-4 py-3 text-sm text-[#8F3F2B] dark:bg-[#B5563C]/10 dark:text-[#E6A18E]"
            >
                {{ error }}
            </div>

            <div
                v-if="loading"
                class="border border-[#D8DDD3] bg-white py-16 text-center dark:border-[#39483F] dark:bg-[#202D26]"
            >
                <div class="mx-auto mb-4 h-7 w-7 animate-spin border-2 border-[#D8DDD3] border-t-[#2F6F4E] dark:border-[#39483F] dark:border-t-[#75B28F]"></div>
                <p class="text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                    Loading marksheet...
                </p>
            </div>

            <section
                v-else-if="marksheet"
                class="border border-[#D8DDD3] bg-white dark:border-[#39483F] dark:bg-[#202D26]"
            >

                <!-- STUDENT -->
                <div class="border-b border-[#D8DDD3] p-6 dark:border-[#39483F] lg:p-7">
                    <p class="text-sm font-medium text-[#2F6F4E] dark:text-[#75B28F]">
                        Student
                    </p>

                    <div class="mt-2 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class=" text-3xl font-semibold">
                                {{ marksheet.student?.name || '-' }}
                            </h2>

                            <p class="mt-1 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                                Class {{ marksheet.student?.class || '-' }}
                            </p>
                        </div>

                        <div class="text-left sm:text-right">
                            <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">
                                Marksheet ID
                            </p>

                            <p class="mt-1 font-mono text-sm font-semibold">
                                #{{ marksheet.id }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- MARKS -->
                <div class="p-6 lg:p-7">
                    <div class="mb-6">
                        <h2 class=" text-2xl font-semibold">
                            Update subject marks
                        </h2>

                        <p class="mt-1 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                            Enter marks for every subject. Use
                            <strong class="text-[#1C2B24] dark:text-white">A</strong>
                            for absent.
                        </p>
                    </div>

                    <div class="border-y border-[#D8DDD3] dark:border-[#39483F]">
                        <div
                            v-for="subject in subjectStore.subjects"
                            :key="subject.id"
                            class="grid grid-cols-1 gap-3 border-b border-[#D8DDD3] py-4 last:border-b-0 dark:border-[#39483F] sm:grid-cols-[1fr_280px] sm:items-center"
                        >
                            <div>
                                <p class="font-medium">
                                    {{ subject.name }}
                                </p>
                                <p class="mt-1 text-xs text-[#5B6B62] dark:text-[#AEBBB3]">
                                    Subject {{ subject.id }}
                                </p>
                            </div>

                            <input
                                v-model="marks[subject.id]"
                                type="text"
                                inputmode="decimal"
                                placeholder="Enter marks or A"
                                class="w-full border border-[#D8DDD3] bg-white px-4 py-3 text-sm outline-none focus:border-[#2F6F4E] focus:ring-1 focus:ring-[#2F6F4E] dark:border-[#39483F] dark:bg-[#17221D]"
                            >
                        </div>
                    </div>

                    <!-- RESULT LEDGER -->
                    <div class="mt-7">
                        <h2 class="mb-4 text-2xl font-semibold">
                            Result preview
                        </h2>

                        <div class="border-y border-[#D8DDD3] dark:border-[#39483F]">
                            <div class="grid grid-cols-2 md:grid-cols-4">
                                <div class="border-r border-[#D8DDD3] p-5 dark:border-[#39483F]">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Total</p>
                                    <p class="mt-1 text-2xl font-semibold">{{ totalMarks }}</p>
                                </div>

                                <div class="border-r border-[#D8DDD3] p-5 dark:border-[#39483F]">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Percentage</p>
                                    <p class="mt-1 text-2xl font-semibold">{{ percentage }}%</p>
                                </div>

                                <div class="border-t border-[#D8DDD3] p-5 dark:border-[#39483F] md:border-t-0 md:border-r">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Grade</p>
                                    <p class="mt-1 text-2xl font-semibold text-[#2F6F4E] dark:text-[#75B28F]">
                                        {{ grade }}
                                    </p>
                                </div>

                                <div class="border-t border-[#D8DDD3] p-5 dark:border-[#39483F] md:border-t-0">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">Result</p>
                                    <p
                                        class="mt-1 text-2xl font-semibold"
                                        :class="result === 'Pass' ? 'text-[#2F6F4E] dark:text-[#75B28F]' : 'text-[#B5563C]'"
                                    >
                                        {{ result }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="successMessage"
                        class="mt-6 border border-[#2F6F4E]/30 bg-[#2F6F4E]/5 px-4 py-3 text-sm font-medium text-[#2F6F4E] dark:text-[#A8D1B8]"
                    >
                        ✓ {{ successMessage }}
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-[#D8DDD3] pt-6 dark:border-[#39483F] sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="router.push('/marksheets')"
                            class="border border-[#D8DDD3] px-5 py-2.5 text-sm font-semibold text-[#5B6B62] hover:border-[#1C2B24] hover:text-[#1C2B24] dark:border-[#39483F] dark:text-[#AEBBB3]"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            :disabled="saving"
                            @click="saveChanges"
                            class="bg-[#2F6F4E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#275E42] disabled:opacity-50"
                        >
                            {{ saving ? 'Saving...' : 'Save changes' }}
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>