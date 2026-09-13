<script setup>

import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js'

import { Bar } from 'vue-chartjs'
import { computed } from 'vue'
import { useStudentStore } from '../../stores/students/student'


ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
)


const studentStore = useStudentStore()


const chartData = computed(() => {

    return {

        labels: [
            'Active',
            'Inactive'
        ],

        datasets: [

            {
                label: 'Students',

                data: [
                    studentStore.statistics.active,
                    studentStore.statistics.inactive
                ],

                borderWidth: 1

            }

        ]

    }

})


const chartOptions = {

    responsive: true,

    maintainAspectRatio: false,

    plugins: {

        legend: {
            display: false
        },

        title: {
            display: true,
            text: 'Student Status'
        }

    },

    scales: {

        y: {

            beginAtZero: true,

            ticks: {
                precision: 0
            }

        }

    }

}

</script>


<template>

    <div
        class="border border-hairline bg-surface"
    >
        <div
            class="flex items-center justify-between border-b border-hairline px-5 py-4"
        >
            <div>
                <h2 class="font-serif text-xl font-medium text-ink">
                    Student Performance
                </h2>

                <p class="mt-1 text-xs text-ink-soft">
                    Marks distribution across students
                </p>
            </div>
        </div>

        <div class="h-72 p-5">
            <Bar
                :data="chartData"
                :options="chartOptions"
            />
        </div>
    </div>

</template>