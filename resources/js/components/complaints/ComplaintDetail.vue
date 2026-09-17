<script setup>

import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getComplaint } from '../../services/complaints/complaintApi'

const route = useRoute()
const router = useRouter()

const goBack = () => {
    router.push('/notifications')
}

const complaint = ref(null)
const loading = ref(false)
const error = ref('')

const fetchComplaint = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await getComplaint(route.params.id)
        complaint.value = response.data
    } catch (err) {
        console.error(err)
        if (err.response?.status === 403) {
            error.value = 'You are not authorized to view this complaint.'
        } else if (err.response?.status === 404) {
            error.value = 'Complaint not found.'
        } else {
            error.value = 'Failed to load complaint.'
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchComplaint()
})

</script>

<template>
    <div class="min-h-screen bg-[#F4F6F1] text-[#1C2B24]">
        <div class="mx-auto w-full max-w-4xl px-5 py-7 lg:px-8">

            <!-- HEADER -->
            <header class="mb-8">
                <button
                    type="button"
                    @click="goBack"
                    class="mb-4 inline-flex items-center text-sm font-medium text-[#2F6F4E] hover:underline"
                >
                    ← Back to notifications
                </button>

                <h1 class="text-3xl font-semibold">
                    Complaint Details
                </h1>
            </header>

            <!-- LOADING -->
            <div
                v-if="loading"
                class="border border-[#D8DDD3] bg-white py-16 text-center"
            >
                <div class="mx-auto mb-4 h-7 w-7 animate-spin border-2 border-[#D8DDD3] border-t-[#2F6F4E]"></div>
                <p class="text-sm text-[#5B6B62]">
                    Loading complaint...
                </p>
            </div>

            <!-- ERROR -->
            <div
                v-else-if="error"
                class="border border-[#B5563C]/30 bg-[#B5563C]/5 px-5 py-4 text-sm text-[#8F3F2B]"
            >
                {{ error }}
            </div>

            <!-- COMPLAINT -->
            <article
                v-else-if="complaint"
                class="overflow-hidden border border-[#D8DDD3] bg-white"
            >

                <!-- TITLE BAR -->
                <div class="border-b border-[#D8DDD3] bg-[#2F6F4E] px-6 py-5 text-white lg:px-8">
                    <p class="text-xs font-medium uppercase tracking-wide opacity-80">
                        Complaint #{{ complaint.id }}
                    </p>
                    <h2 class="mt-1 text-2xl font-semibold">
                        {{ complaint.subject }}
                    </h2>
                </div>

                <!-- BODY -->
                <div class="px-6 py-6 lg:px-8 lg:py-8">

                    <!-- META GRID -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                        <div class="border border-[#D8DDD3] p-4">
                            <p class="text-xs text-[#5B6B62]">
                                Student
                            </p>
                            <p class="mt-1 font-medium">
                                {{ complaint.student?.name || '-' }}
                            </p>
                        </div>

                        <div class="border border-[#D8DDD3] p-4">
                            <p class="text-xs text-[#5B6B62]">
                                Teacher
                            </p>
                            <p class="mt-1 font-medium">
                                {{ complaint.teacher?.name || 'No specific teacher' }}
                            </p>
                        </div>

                        <div class="border border-[#D8DDD3] p-4 sm:col-span-2 lg:col-span-1">
                            <p class="text-xs text-[#5B6B62]">
                                Status
                            </p>
                            <p class="mt-1 font-medium capitalize">
                                {{ complaint.status }}
                            </p>
                        </div>

                    </div>

                    <!-- MESSAGE -->
                    <div class="mt-6">
                        <h3 class="mb-2 text-sm font-semibold text-[#1C2B24]">
                            Message
                        </h3>
                        <div class="border border-[#D8DDD3] bg-[#F4F6F1] p-4 text-sm leading-6 whitespace-pre-wrap">
                            {{ complaint.message }}
                        </div>
                    </div>

                    <!-- DATE -->
                    <div class="mt-6 text-xs text-[#5B6B62]">
                        Submitted on {{ complaint.created_at }}
                    </div>

                </div>

            </article>

        </div>
    </div>
</template>