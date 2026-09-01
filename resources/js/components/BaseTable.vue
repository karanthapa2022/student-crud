<script setup>

// =========================================================
// PROPS
// =========================================================

defineProps({

    headers: {
        type: Array,
        required: true
    }

})

</script>


<template>

    <div
        class="bg-white dark:bg-gray-900
               rounded-xl shadow-md
               overflow-x-auto
               border border-gray-200 dark:border-gray-700"
    >

        <table
            class="w-full bg-white dark:bg-gray-900"
        >

            <!-- ================================================= -->
            <!-- TABLE HEADER -->
            <!-- ================================================= -->

            <thead>

                <tr
                    class="bg-gray-50 dark:bg-gray-800
                           border-b border-gray-200 dark:border-gray-700"
                >

                    <th
                        v-for="header in headers"
                        :key="header.key"
                        class="px-5 py-4
                               text-left text-sm font-semibold
                               text-gray-700 dark:text-gray-200"
                    >

                        {{ header.label }}

                    </th>

                </tr>

            </thead>


            <!-- ================================================= -->
            <!-- TABLE BODY -->
            <!-- ================================================= -->

            <tbody
                class="bg-white dark:bg-gray-900"
            >

                <slot />

            </tbody>

        </table>

    </div>

</template>