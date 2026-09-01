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
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5"
    >

        <div
            class="h-72"
        >

            <Bar
                :data="chartData"
                :options="chartOptions"
            />

        </div>

    </div>

</template>