<script setup>

import { ref, onMounted } from 'vue'

import {
    createComplaint
} from '../../services/complaints/complaintApi'

import NotificationList from '../notifications/NotificationList.vue'



const teachers = ref([])

const loadingTeachers = ref(false)

const form = ref({

    teacher_id: null,

    subject: '',

    message: ''

})


const submitting = ref(false)

const successMessage = ref('')

const errorMessage = ref('')

const submitComplaint = async () => {

    submitting.value = true

    successMessage.value = ''

    errorMessage.value = ''

    try {

        await createComplaint({

            teacher_id: form.value.teacher_id,

            subject: form.value.subject,

            message: form.value.message

        })

        successMessage.value =
            'Your complaint has been submitted successfully.'

        form.value = {

            teacher_id: null,

            subject: '',

            message: ''

        }

    } catch (error) {

        console.error(
            'Failed to submit complaint:',
            error
        )

        if (error.response?.data?.message) {

            errorMessage.value =
                error.response.data.message

        } else {

            errorMessage.value =
                'Failed to submit complaint. Please try again.'

        }

    } finally {

        submitting.value = false

    }

}


const loadTeachers = async () => {

    loadingTeachers.value = true

    try {

        const token = localStorage.getItem('student_token')

        const response = await fetch('/api/teachers-for-complaint', {
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
            },
        })

        teachers.value = await response.json()

    } catch (error) {

        console.error(
            'Failed to load teachers:',
            error
        )

    } finally {

        loadingTeachers.value = false

    }

}


onMounted(() => {
    loadTeachers()
})
</script>

<template>

    <div class="min-h-screen bg-paper text-ink px-6 py-8">

        <div class="mx-auto max-w-3xl">

            <!-- HEADER -->

            <div class="mb-8">

                <h1 class="text-2xl font-semibold">
                    Complaint & Feedback
                </h1>

                <p class="mt-2 text-sm opacity-60">
                    Share your complaint or feedback with the administration.
                </p>

            </div>


            <!-- SUCCESS -->

            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-300"
            >
                {{ successMessage }}
            </div>


            <!-- ERROR -->

            <div
                v-if="errorMessage"
                class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300"
            >
                {{ errorMessage }}
            </div>


            <!-- FORM -->

            <form
                @submit.prevent="submitComplaint"
                class="space-y-6 rounded-2xl border border-black/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5"
            >

                <!-- TEACHER -->

                <div>

                    <label
                        for="teacher"
                        class="mb-2 block text-sm font-medium"
                    >
                        Teacher
                    </label>

                    <select
                        id="teacher"
                        v-model="form.teacher_id"
                        class="w-full rounded-xl border border-black/10 bg-transparent px-4 py-3 text-sm outline-none focus:border-black/30 dark:border-white/10 dark:focus:border-white/30"
                    >

                        <option :value="null">
                            General complaint / No specific teacher
                        </option>

                        <option
                            v-for="teacher in teachers"
                            :key="teacher.id"
                            :value="teacher.id"
                        >
                            {{ teacher.name }}
                        </option>

                    </select>

                    <p class="mt-2 text-xs opacity-50">
                        Select a teacher only if your complaint is specifically about that teacher.
                    </p>

                </div>


                <!-- SUBJECT -->

                <div>

                    <label
                        for="subject"
                        class="mb-2 block text-sm font-medium"
                    >
                        Subject
                    </label>

                    <input
                        id="subject"
                        v-model="form.subject"
                        type="text"
                        maxlength="255"
                        placeholder="Enter complaint subject"
                        class="w-full rounded-xl border border-black/10 bg-transparent px-4 py-3 text-sm outline-none placeholder:opacity-40 focus:border-black/30 dark:border-white/10 dark:focus:border-white/30"
                    >

                </div>


                <!-- MESSAGE -->

                <div>

                    <label
                        for="message"
                        class="mb-2 block text-sm font-medium"
                    >
                        Message
                    </label>

                    <textarea
                        id="message"
                        v-model="form.message"
                        rows="7"
                        placeholder="Describe your complaint or feedback..."
                        class="w-full resize-none rounded-xl border border-black/10 bg-transparent px-4 py-3 text-sm outline-none placeholder:opacity-40 focus:border-black/30 dark:border-white/10 dark:focus:border-white/30"
                    ></textarea>

                </div>


                <!-- SUBMIT -->

                <div class="flex justify-end">

                    <button
                        type="submit"
                        class="rounded-xl bg-ink px-5 py-3 text-sm font-medium text-paper transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="submitting"
                    >
                        {{ submitting ? 'Submitting...' : 'Submit Complaint' }}
                    </button>

                </div>

            </form>

            <NotificationList />

        </div>

    </div>

</template>