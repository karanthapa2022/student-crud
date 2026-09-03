<script setup>

import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getMarksheet } from '../../services/marksheets/marksheetApi'

const route = useRoute()
const router = useRouter()

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

    <div class="p-6">
        <div class="flex justify-end mb-6">
        <button
            type="button"
            @click="router.push('/marksheets')"
            class="mb-6 px-4 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 transition "
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

            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Marksheet
            </h1>

            <div class="mt-6">
                <p class="text-gray-700 dark:text-gray-300">
                    <strong>Student:</strong>
                    {{ marksheet.student?.name || '-' }}
                </p>

                <p class="mt-2 text-gray-700 dark:text-gray-300">
                    <strong>Class:</strong>
                    {{ marksheet.student?.class || '-' }}
                </p>
            </div>

            <div class="mt-6 overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                S.N.
                            </th>

                            <th class="px-4 py-3 text-left">
                                Subject
                            </th>

                            <th class="px-4 py-3 text-left">
                                Marks
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr
                            v-for="(item, index) in marksheet.items"
                            :key="item.id"
                            class="border-t dark:border-gray-700"
                        >

                            <td class="px-4 py-3">
                                {{ index + 1 }}
                            </td>

                            <td class="px-4 py-3">
                                {{ item.subject?.name || '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ item.marks }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <div class="mt-6 space-y-2">

                <p>
                    <strong>Total:</strong>
                    {{ marksheet.total }}
                </p>

                <p>
                    <strong>Percentage:</strong>
                    {{ marksheet.percentage }}%
                </p>

                <p>
                    <strong>Grade:</strong>
                    {{ marksheet.grade || '-' }}
                </p>

                <p>
                    <strong>Result:</strong>
                    {{ marksheet.result || '-' }}
                </p>

            </div>

        </div>

    </div>

</template>