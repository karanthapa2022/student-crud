```vue
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
        class="bg-white rounded-xl shadow-md overflow-x-auto"
    >

        <table
            class="w-full min-w-[1000px]"
        >

            <!-- ================================================= -->
            <!-- TABLE HEADER -->
            <!-- ================================================= -->

            <thead>

                <tr class="bg-gray-50 border-b">

                    <th
                        v-for="header in headers"
                        :key="header.key"
                        class="px-5 py-4 text-left text-sm font-semibold text-gray-600"
                    >

                        {{ header.label }}

                    </th>

                </tr>

            </thead>


            <!-- ================================================= -->
            <!-- TABLE BODY -->
            <!-- ================================================= -->

            <tbody>

                <slot />

            </tbody>

        </table>

    </div>

</template>
```
