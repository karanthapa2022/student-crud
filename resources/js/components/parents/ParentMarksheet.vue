
<script setup>

import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getParentStudentMarksheet } from '../../services/marksheets/marksheetApi'
import { downloadMarksheetPdf } from '../../utils/marksheetPdf'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const marksheet = ref(null)

const fetchMarksheet = async () => {

    loading.value = true
    error.value = ''
    marksheet.value = null

    try {

        const studentId = route.params.studentId

        if (!studentId) {
            error.value = 'Student information is missing.'
            return
        }

        const response = await getParentStudentMarksheet(studentId)

        marksheet.value = response.data

    } catch (err) {

        console.error('Parent marksheet error:', err)

        if (err.response?.data?.message) {
            error.value = err.response.data.message
        } else {
            error.value = 'Failed to load marksheet.'
        }

    } finally {

        loading.value = false

    }
}

const goBack = () => {
    router.push('/dashboard')
}

onMounted(() => {
    fetchMarksheet()
})

</script>

<template>
    <div class="min-h-screen bg-paper px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl">

            <!-- ================================================= -->
            <!-- HEADER / LETTERHEAD -->
            <!-- ================================================= -->

            <header class="border-b border-hairline pb-6">
                <div class="flex items-start justify-between gap-6">

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center border border-hairline bg-surface text-lg"
                            >
                                🎓
                            </div>

                            <span class="text-sm font-medium text-forest">
                                Academic Records
                            </span>
                        </div>

                        <h1
                            class="font-serif text-4xl font-medium leading-tight text-ink sm:text-5xl"
                        >
                            Student Marksheet
                        </h1>

                        <p class="mt-2 text-sm text-ink-soft">
                            View your child's academic results.
                        </p>
                    </div>

                    <button
                        @click="goBack"
                        class="shrink-0 border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                    >
                        ← Back
                    </button>

                </div>
            </header>


            <!-- ================================================= -->
            <!-- LOADING -->
            <!-- ================================================= -->

            <div
                v-if="loading"
                class="mt-8 border border-hairline bg-surface px-6 py-16 text-center"
            >
                <div class="text-sm text-ink-soft">
                    Loading marksheet...
                </div>
            </div>


            <!-- ================================================= -->
            <!-- ERROR -->
            <!-- ================================================= -->

            <div
                v-else-if="error"
                class="mt-8 border border-hairline bg-surface p-6 sm:p-8"
            >
                <div
                    class="border border-sienna bg-paper px-4 py-3 text-sm leading-6 text-sienna"
                >
                    {{ error }}
                </div>

                <button
                    @click="goBack"
                    class="mt-5 border border-forest bg-forest px-5 py-3 text-sm font-medium text-white hover:bg-ink"
                >
                    Back to Dashboard
                </button>
            </div>


            <!-- ================================================= -->
            <!-- MARKSHEET -->
            <!-- ================================================= -->

            <div
                v-else-if="marksheet"
                class="mt-8 space-y-6"
            >

                <!-- ================================================= -->
                <!-- STUDENT INFORMATION -->
                <!-- ================================================= -->

                <section
                    class="border border-hairline bg-surface"
                >
                    <div class="border-b border-hairline p-6 sm:p-8">

                        <div class="flex items-start justify-between gap-6">

                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-forest"
                                >
                                    Academic Record
                                </p>

                                <h2
                                    class="font-serif text-3xl font-medium text-ink sm:text-4xl"
                                >
                                    {{ marksheet.student?.name || '-' }}
                                </h2>

                                <p class="mt-2 text-sm text-ink-soft">
                                    Academic Marksheet
                                </p>
                            </div>

                            <div
                                class="hidden h-12 w-12 shrink-0 items-center justify-center border border-hairline bg-paper text-xl sm:flex"
                            >
                                🎓
                            </div>

                        </div>
                    </div>


                    <!-- STUDENT DETAILS LEDGER -->

                    <div
                        class="grid grid-cols-1 divide-y divide-hairline sm:grid-cols-3 sm:divide-x sm:divide-y-0"
                    >

                        <div class="px-6 py-5 sm:px-8">
                            <p class="text-xs text-ink-soft">
                                Symbol No.
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-ink"
                            >
                                {{ marksheet.student?.symbol_no || '-' }}
                            </p>
                        </div>


                        <div class="px-6 py-5 sm:px-8">
                            <p class="text-xs text-ink-soft">
                                Date of Birth
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-ink"
                            >
                                {{ marksheet.student?.date_of_birth || '-' }}
                            </p>
                        </div>


                        <div class="px-6 py-5 sm:px-8">
                            <p class="text-xs text-ink-soft">
                                Class
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-ink"
                            >
                                {{ marksheet.student?.class || '-' }}
                            </p>
                        </div>

                    </div>
                </section>


                <!-- ================================================= -->
                <!-- SUBJECT MARKS -->
                <!-- ================================================= -->

                <section
                    class="border border-hairline bg-surface"
                >

                    <div
                        class="border-b border-hairline px-6 py-5 sm:px-8"
                    >
                        <p class="text-sm font-medium text-forest">
                            Assessment
                        </p>

                        <h2
                            class="mt-1 font-serif text-2xl font-medium text-ink"
                        >
                            Subject Results
                        </h2>
                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full min-w-[700px] text-sm">

                            <thead>
                                <tr
                                    class="border-b border-hairline bg-paper"
                                >
                                    <th
                                        class="px-6 py-4 text-left text-xs font-medium text-ink-soft sm:px-8"
                                    >
                                        S.N.
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-medium text-ink-soft"
                                    >
                                        Subject
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-medium text-ink-soft"
                                    >
                                        Full Marks
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-medium text-ink-soft"
                                    >
                                        Pass Marks
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-medium text-ink-soft"
                                    >
                                        Obtained
                                    </th>
                                </tr>
                            </thead>


                            <tbody>

                                <tr
                                    v-for="(item, index) in marksheet.items"
                                    :key="item.id"
                                    class="border-b border-hairline last:border-b-0"
                                >

                                    <td
                                        class="px-6 py-4 text-ink-soft sm:px-8"
                                    >
                                        {{ index + 1 }}
                                    </td>

                                    <td
                                        class="px-6 py-4 font-medium text-ink"
                                    >
                                        {{
                                            item.subject_name ||
                                            item.subject?.name ||
                                            '-'
                                        }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-ink-soft"
                                    >
                                        {{ item.full_marks }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-ink-soft"
                                    >
                                        {{ item.pass_marks }}
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-ink"
                                    >
                                        {{ item.marks }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>
                </section>


                <!-- ================================================= -->
                <!-- RESULT SUMMARY -->
                <!-- ================================================= -->

                <section
                    class="border border-hairline bg-surface"
                >

                    <div
                        class="border-b border-hairline px-6 py-5 sm:px-8"
                    >
                        <p class="text-sm font-medium text-forest">
                            Final Assessment
                        </p>

                        <h2
                            class="mt-1 font-serif text-2xl font-medium text-ink"
                        >
                            Result Summary
                        </h2>
                    </div>


                    <!-- RESULT LEDGER -->

                    <div
                        class="grid grid-cols-1 divide-y divide-hairline sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-y-0"
                    >

                        <!-- TOTAL -->

                        <div class="px-6 py-6 sm:px-8">
                            <p class="text-xs text-ink-soft">
                                Total
                            </p>

                            <p
                                class="mt-2 font-serif text-3xl font-medium text-ink"
                            >
                                {{ marksheet.total }}
                            </p>
                        </div>


                        <!-- PERCENTAGE -->

                        <div class="px-6 py-6 sm:px-8">
                            <p class="text-xs text-ink-soft">
                                Percentage
                            </p>

                            <p
                                class="mt-2 font-serif text-3xl font-medium text-ink"
                            >
                                {{ marksheet.percentage }}%
                            </p>
                        </div>


                        <!-- GRADE -->

                        <div class="px-6 py-6 sm:px-8">
                            <p class="text-xs text-ink-soft">
                                Grade
                            </p>

                            <p
                                class="mt-2 font-serif text-3xl font-medium text-ink"
                            >
                                {{ marksheet.grade || '-' }}
                            </p>
                        </div>


                        <!-- RESULT -->

                        <div class="px-6 py-6 sm:px-8">
                            <p class="text-xs text-ink-soft">
                                Result
                            </p>

                            <p
                                class="mt-2 font-serif text-3xl font-medium text-ink"
                            >
                                {{ marksheet.result || '-' }}
                            </p>
                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- DOWNLOAD -->
                    <!-- ================================================= -->

                    <div class="border-t border-hairline p-6 sm:p-8">

                        <button
                            type="button"
                            @click="downloadMarksheetPdf(marksheet)"
                            class="w-full border border-forest bg-forest px-5 py-3 text-sm font-medium text-white hover:bg-ink"
                        >
                            Download Marksheet
                        </button>

                    </div>

                </section>

            </div>

        </div>
    </div>
</template>

