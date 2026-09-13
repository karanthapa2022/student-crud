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
        class="bg-white dark:bg-[#1E2B24]
               rounded-md
               overflow-x-auto
               border border-[#D8DDD3] dark:border-[#2E3B33]"
    >

        <table
            class="w-full bg-white dark:bg-[#1E2B24]"
        >

            <!-- ================================================= -->
            <!-- TABLE HEADER -->
            <!-- ================================================= -->

            <thead>

                <tr
                    class="bg-[#F7F8F3] dark:bg-[#16211B]
                           border-b border-[#D8DDD3] dark:border-[#2E3B33]"
                >

                    <th
                        v-for="header in headers"
                        :key="header.key"
                        class="px-5 py-4
                               text-left text-sm font-medium
                               text-[#5B6B62] dark:text-[#9AA79E]"
                    >

                        {{ header.label }}

                    </th>

                </tr>

            </thead>


            <!-- ================================================= -->
            <!-- TABLE BODY -->
            <!-- ================================================= -->

            <tbody
                class="bg-white dark:bg-[#1E2B24] divide-y divide-[#D8DDD3] dark:divide-[#2E3B33]"
            >

                <slot />

            </tbody>

        </table>

    </div>

</template>