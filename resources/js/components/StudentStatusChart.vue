<script setup>
import{ref, onMounted, onBeforeUnmount } from 'vue'
import{chart } from 'chart.js/auto'

const chartCanvas =ref(null)

let chart =null

const props = defineProps({
    students:{
        type: Arrya,
        required: true
    }
})

const createChart=() => {
    const activeStudents=
    props.students.filter(
        student=> student.status ==='active'
    ).length

    const inactiveStudents=
    props.students.filter(
        student => student.status ==='inactive'
    ).length
    
    chart = new Chart(
        chartCanvas.value,
        {
            type: 'doughnut',
            data :{
                labels:[
                    'Active',
                    'Inactive'
                ],

                datasets:[{
                    data:[
                        activeStudents,
                        inactiveStudents
                    ]
                }]
            },

            options:{
                responsive: true,
                maintainAspectRation: false,
                plugins: {
                    legend:{
                        position: 'botton'
                    }
                }
            }
        }
    )
}
onMounted(()=>{
    createChart()
})
onBeforeUnmount(()=>{
    if (chart){
        chart.destroy()
    }
})

</script>

<template>
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6"> Student Status</h2>

        <div class="h-72">
            <canvas ref="chartCanvas">

            </canvas>
        </div>

    </div>

</template>