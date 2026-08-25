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

<tr class="border-b hover:bg-gray-50">

    <!-- CHECKBOX -->

    <td class="px-5 py-4 text-center">

        <input
            type="checkbox"
            :checked="selected"
            @change="emit('toggle', student.id)"
        />

    </td>


    <!-- S.N. -->

    <td class="px-5 py-4 text-sm text-gray-700">

        {{ index + 1 }}

    </td>


    <!-- ID -->

    <td class="px-5 py-4 text-sm text-gray-700">

        {{ student.id }}

    </td>


    <!-- NAME -->

    <td
        class="px-5 py-4 text-sm text-gray-700 font-medium"
    >

        {{ student.name }}

    </td>


    <!-- EMAIL -->

    <td class="px-5 py-4 text-sm text-gray-700">

        {{ student.email }}

    </td>


    <!-- PHONE -->

    <td class="px-5 py-4 text-sm text-gray-700">

        {{ student.phone }}

    </td>


    <!-- STATUS -->

    <td class="px-5 py-4">

        <!-- STATUS SLOT -->

        <slot
            name="status"
            :student="student"
        >

            <span
                class="px-3 py-1 rounded-full text-xs font-semibold"
                :class="
                    student.status === 'active'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'
                "
            >

                {{ student.status }}

            </span>

        </slot>

    </td>


    <!-- ACTIONS -->

    <td class="px-5 py-4">

        <!-- ACTIONS SLOT -->

        <slot
            name="actions"
            :student="student"
        >

            <div class="flex gap-4">

                <button
                    @click="emit('edit', student)"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                >
                    Edit
                </button>


                <button
                    @click="emit('delete', student)"
                    class="text-red-600 hover:text-red-800 font-medium"
                >
                    Delete
                </button>

            </div>

        </slot>

    </td>

</tr>

</template>