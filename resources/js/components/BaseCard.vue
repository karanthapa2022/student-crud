<script setup>
</script>

<template>

    <div
        class="bg-white dark:bg-[#1E2B24] rounded-md border border-[#D8DDD3] dark:border-[#2E3B33] p-5"
    >

        <!-- Card Header -->

        <div
            v-if="$slots.header"
            class="mb-4 pb-4 border-b border-[#D8DDD3] dark:border-[#2E3B33]"
        >
            <slot name="header" />
        </div>


        <!-- Card Content -->

        <div>
            <slot />
        </div>


        <!-- Card Footer -->

        <div
            v-if="$slots.footer"
            class="mt-5 pt-4 border-t border-[#D8DDD3] dark:border-[#2E3B33]"
        >
            <slot name="footer" />
        </div>

    </div>

</template>