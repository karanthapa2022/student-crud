
<script setup>

import BaseButton from '../BaseButton.vue'
import BaseCard from '../BaseCard.vue'


// =========================================================
// PROPS
// =========================================================

defineProps({

    student: {
        type: Object,
        required: true
    }

})


// =========================================================
// EMITS
// =========================================================

const emit = defineEmits([
    'edit',
    'delete'
])

</script>


<template>

    <!-- ================================================= -->
    <!-- BASE CARD -->
    <!-- ================================================= -->

    <BaseCard>


        <!-- ================================================= -->
        <!-- HEADER SLOT -->
        <!-- ================================================= -->

        <template #header>

            <!--
                Forward the parent's header slot.

                If StudentList provides #header,
                that content will be displayed here.

                Otherwise, show the student's name.
            -->

            <slot name="header">

                <div
                    class="flex justify-between items-center"
                >

                    <h3
                        class="font-bold text-lg text-gray-900"
                    >
                        {{ student.name }}
                    </h3>


                    <span
                        class="text-sm text-gray-500"
                    >
                        #{{ student.id }}
                    </span>

                </div>

            </slot>

        </template>


        <!-- ================================================= -->
        <!-- STUDENT INFORMATION -->
        <!-- ================================================= -->

        <div class="space-y-3">

            <!-- EMAIL -->

            <div>

                <p
                    class="text-xs font-medium text-gray-500 uppercase"
                >
                    Email
                </p>

                <p
                    class="text-sm text-gray-800 break-all mt-1"
                >
                    {{ student.email }}
                </p>

            </div>


            <!-- PHONE -->

            <div>

                <p
                    class="text-xs font-medium text-gray-500 uppercase"
                >
                    Phone
                </p>

                <p
                    class="text-sm text-gray-800 mt-1"
                >
                    {{ student.phone }}
                </p>

            </div>


            <!-- STATUS -->

            <div>

                <p
                    class="text-xs font-medium text-gray-500 uppercase"
                >
                    Status
                </p>

                <span
                    class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold"
                    :class="
                        student.status === 'active'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700'
                    "
                >
                    {{ student.status }}
                </span>

            </div>


            <!-- ================================================= -->
            <!-- DEFAULT SLOT -->
            <!-- ================================================= -->

            <div
                v-if="$slots.default"
                class="pt-2"
            >

                <slot />

            </div>

        </div>


        <!-- ================================================= -->
        <!-- BUTTONS -->
        <!-- ================================================= -->

        <div
            class="flex gap-3 mt-6"
        >

            <!-- EDIT -->

            <BaseButton
                @click="emit('edit', student)"
            >
                Edit
            </BaseButton>


            <!-- DELETE -->

            <BaseButton
                variant="danger"
                @click="emit('delete', student)"
            >
                Delete
            </BaseButton>

        </div>

    </BaseCard>

</template>