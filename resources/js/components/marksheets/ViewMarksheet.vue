<script setup>

import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getMarksheet } from '../../services/marksheets/marksheetApi'

const route = useRoute()
const router = useRouter()

const goBackToMarksheets = () => {
    router.push('/marksheets')
}

const marksheet = ref(null)
const loading = ref(false)
const error = ref('')

const fetchMarksheet = async () => {

    loading.value = true
    error.value = ''

    try {

        const response = await getMarksheet(route.params.id)

        marksheet.value = response.data

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

</script>


<template>
    <div class="min-h-screen bg-[#EFF1EA] text-[#1C2B24] dark:bg-[#17221D] dark:text-[#F5F7F3]">
        <div class="mx-auto w-full max-w-6xl px-5 py-7 lg:px-8">

            <!-- HEADER -->
            <header class="mb-8 border-b border-[#D8DDD3] pb-6 dark:border-[#39483F]">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-2 text-sm font-medium text-[#2F6F4E] dark:text-[#75B28F]">
                            Academic record
                        </p>

                        <h1 class=" text-3xl font-semibold">
                            Marksheet
                        </h1>

                        <p class="mt-2 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                            Student marks and final result.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="goBackToMarksheets"
                        class="border border-[#D8DDD3] bg-white px-4 py-2.5 text-sm font-semibold hover:border-[#2F6F4E] hover:text-[#2F6F4E] dark:border-[#39483F] dark:bg-[#202D26]"
                    >
                        Back to marksheets
                    </button>
                </div>
            </header>

            <!-- LOADING -->
            <div
                v-if="loading"
                class="border border-[#D8DDD3] bg-white py-16 text-center dark:border-[#39483F] dark:bg-[#202D26]"
            >
                <div class="mx-auto mb-4 h-7 w-7 animate-spin border-2 border-[#D8DDD3] border-t-[#2F6F4E]"></div>

                <p class="text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                    Loading marksheet...
                </p>
            </div>

            <!-- ERROR -->
            <div
                v-else-if="error"
                class="border border-[#B5563C]/30 bg-[#B5563C]/5 px-5 py-4 text-sm text-[#8F3F2B] dark:bg-[#B5563C]/10 dark:text-[#E6A18E]"
            >
                {{ error }}
            </div>

            <!-- MARKSHEET -->
            <article
                v-else-if="marksheet"
                class="border border-[#D8DDD3] bg-white dark:border-[#39483F] dark:bg-[#202D26]"
            >

                <!-- STUDENT HEADER -->
                <div class="border-b border-[#D8DDD3] p-6 dark:border-[#39483F] lg:p-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">

                        <div>
                            <p class="text-sm font-medium text-[#2F6F4E] dark:text-[#75B28F]">
                                Student
                            </p>

                            <h2 class="mt-1 text-4xl font-semibold">
                                {{ marksheet.student?.name || '-' }}
                            </h2>

                            <p class="mt-2 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                                Class {{ marksheet.student?.class || '-' }}
                            </p>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">
                                Marksheet ID
                            </p>

                            <p class="mt-1 font-mono text-sm font-semibold">
                                #{{ marksheet.id }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SUBJECTS -->
                <div class="p-6 lg:p-8">
                    <div class="mb-5">
                        <h2 class=" text-2xl font-semibold">
                            Subject marks
                        </h2>

                        <p class="mt-1 text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                            Marks recorded for each subject.
                        </p>
                    </div>

                    <div class="border-y border-[#D8DDD3] dark:border-[#39483F]">
                        <div class="grid grid-cols-[60px_1fr_140px] border-b border-[#D8DDD3] bg-[#EFF1EA] px-4 py-3 text-xs font-semibold text-[#5B6B62] dark:border-[#39483F] dark:bg-[#17221D] dark:text-[#AEBBB3]">
                            <span>S.N.</span>
                            <span>Subject</span>
                            <span class="text-right">Marks</span>
                        </div>

                        <div
                            v-for="(item, index) in marksheet.items"
                            :key="item.id"
                            class="grid grid-cols-[60px_1fr_140px] border-b border-[#D8DDD3] px-4 py-4 last:border-b-0 dark:border-[#39483F]"
                        >
                            <span class="text-sm text-[#5B6B62] dark:text-[#AEBBB3]">
                                {{ index + 1 }}
                            </span>

                            <span class="font-medium">
                                {{ item.subject?.name || '-' }}
                            </span>

                            <span class="text-right font-semibold">
                                {{ item.marks }}
                            </span>
                        </div>
                    </div>

                    <!-- RESULT -->
                    <div class="mt-8">
                        <h2 class="mb-5 text-2xl font-semibold">
                            Result summary
                        </h2>

                        <div class="border-y border-[#D8DDD3] dark:border-[#39483F]">
                            <div class="grid grid-cols-2 md:grid-cols-4">
                                <div class="border-r border-[#D8DDD3] p-5 dark:border-[#39483F]">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Total
                                    </p>

                                    <p class="mt-1 text-2xl font-semibold">
                                        {{ marksheet.total }}
                                    </p>
                                </div>

                                <div class="border-r border-[#D8DDD3] p-5 dark:border-[#39483F]">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Percentage
                                    </p>

                                    <p class="mt-1 text-2xl font-semibold">
                                        {{ marksheet.percentage }}%
                                    </p>
                                </div>

                                <div class="border-t border-[#D8DDD3] p-5 dark:border-[#39483F] md:border-t-0 md:border-r">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Grade
                                    </p>

                                    <p class="mt-1 text-2xl font-semibold text-[#2F6F4E] dark:text-[#75B28F]">
                                        {{ marksheet.grade || '-' }}
                                    </p>
                                </div>

                                <div class="border-t border-[#D8DDD3] p-5 dark:border-[#39483F] md:border-t-0">
                                    <p class="text-xs text-[#5B6B62] dark:text-[#AEBBB3]">
                                        Result
                                    </p>

                                    <p
                                        class="mt-1 text-2xl font-semibold"
                                        :class="
                                            marksheet.result === 'Pass'
                                                ? 'text-[#2F6F4E] dark:text-[#75B28F]'
                                                : marksheet.result === 'Fail'
                                                    ? 'text-[#B5563C]'
                                                    : 'text-[#5B6B62]'
                                        "
                                    >
                                        {{ marksheet.result || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

        </div>
    </div>
</template>