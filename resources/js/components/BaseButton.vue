<script setup>

defineProps({
    type: {
        type: String,
        default: 'button'
    },

    variant: {
        type: String,
        default: 'primary'
    },

    disabled: {
        type: Boolean,
        default: false
    }
})

</script>


<template>

    <button
        :type="type"
        :disabled="disabled"
        class="px-5 py-2.5 rounded-md font-medium text-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
        :class="{
            'bg-[#2F6F4E] text-white hover:bg-[#24573E]':
                variant === 'primary' || variant === 'success',

            'bg-[#B5563C] text-white hover:bg-[#9A4530]':
                variant === 'danger',

            'bg-[#1C2B24] text-white hover:bg-[#14201A] dark:bg-[#E8EBE4] dark:text-[#16211B] dark:hover:bg-white':
                variant === 'dark',

            'bg-white border border-[#D8DDD3] text-[#1C2B24] hover:bg-[#EFF1EA] dark:bg-[#1E2B24] dark:border-[#2E3B33] dark:text-[#E8EBE4] dark:hover:bg-[#243329]':
                variant === 'secondary'
        }"
    >

        <!-- Slot allows different button text/content -->

        <slot>
            Button
        </slot>

    </button>

</template>