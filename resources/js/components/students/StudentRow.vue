
<script setup>

defineProps({
    student: {
        type: Object,
        required: true
    },

    index: {
        type: Number,
        required: true
    },

    selected: {
        type: Boolean,
        default: false
    }
})


const emit = defineEmits([
    'toggle',
    'edit',
    'delete'
])

</script>


<template>

<tr
    class="
        border-b
        border-gray-200
        dark:border-gray-700
        bg-white dark:bg-gray-900
        hover:bg-gray-50
        dark:hover:bg-gray-800
        transition
    "
>

    <!-- CHECKBOX -->

    <td class="px-5 py-4 text-center">

        <input
            type="checkbox"
            :checked="selected"
            @change="emit('toggle', student.id)"
            class="
                w-4 h-4 accent-blue-600
            "
        />

    </td>


    <!-- S.N. -->

    <td
        class="
            px-5
            py-4
            text-sm
            text-gray-700
            dark:text-gray-300
        "
    >

        {{ index + 1 }}

    </td>


    <!-- ID -->

    <td
        class="
            px-5
            py-4
            text-sm
            text-gray-700
            dark:text-gray-300
        "
    >

        {{ student.id }}

    </td>


    <!-- NAME -->

    <td
        class="
            px-5
            py-4
            text-sm
            text-gray-900
            dark:text-white
            font-semibold
        "
    >

        {{ student.name }}

    </td>


    <!-- EMAIL -->

    <td
        class="
            px-5
            py-4
            text-sm
            text-gray-700
            dark:text-gray-300
        "
    >

        {{ student.email }}

    </td>


    <!-- PHONE -->

    <td
        class="
            px-5
            py-4
            text-sm
            text-gray-700
            dark:text-gray-300
        "
    >

        {{ student.phone }}

    </td>


    <!-- STATUS -->

    <td class="px-5 py-4">

        <slot
            name="status"
            :student="student"
        >

            <span
                class="
                    px-3
                    py-1
                    rounded-full
                    text-xs
                    font-semibold
                "
                :class="
                    student.status === 'active'
                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                        : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400'
                "
            >

                {{ student.status }}

            </span>

        </slot>

    </td>


    <!-- ACTIONS -->

    <td class="px-5 py-4">

        <slot
            name="actions"
            :student="student"
        >

            <div class="flex gap-4">

                <!-- EDIT -->

                <button
                    @click="emit('edit', student)"
                    class="
                        text-blue-600
                        dark:text-blue-400
                        hover:text-blue-800
                        dark:hover:text-blue-300
                        font-medium
                        transition
                    "
                >
                    Edit
                </button>


                <!-- DELETE -->

                <button
                    @click="emit('delete', student)"
                    class="
                        text-red-600
                        dark:text-red-400
                        hover:text-red-800
                        dark:hover:text-red-300
                        font-medium
                        transition
                    "
                >
                    Delete
                </button>

            </div>

        </slot>

    </td>

</tr>

</template>

