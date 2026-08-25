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
        class="px-5 py-2.5 rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
        :class="{
            'bg-blue-600 text-white hover:bg-blue-700':
                variant === 'primary',

            'bg-red-600 text-white hover:bg-red-700':
                variant === 'danger',

            'bg-green-600 text-white hover:bg-green-700':
                variant === 'success',

            'bg-gray-800 text-white hover:bg-gray-900':
                variant === 'dark',

            'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50':
                variant === 'secondary'
        }"
    >

        <!-- Slot allows different button text/content -->

        <slot>
            Button
        </slot>

    </button>

</template>