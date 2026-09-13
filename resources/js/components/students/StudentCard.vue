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
    <!-- STUDENT CARD -->
    <!-- ================================================= -->

    <BaseCard
        class="!rounded-none !border-hairline !bg-surface !shadow-none"
    >

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <template #header>

            <slot name="header">

                <div class="border-b border-hairline px-5 py-4">

                    <div
                        class="flex items-start justify-between gap-4"
                    >

                        <!-- STUDENT IDENTITY -->

                        <div class="min-w-0">

                            <p
                                class="mb-1 text-[10px] font-bold uppercase tracking-[0.2em] text-ink-soft"
                            >
                                Student
                            </p>

                            <h3
                                class="truncate text-xl font-semibold tracking-tight text-ink"
                            >
                                {{ student.name }}
                            </h3>

                        </div>


                        <!-- STUDENT NUMBER -->

                        <div
                            class="shrink-0 border-l border-hairline pl-4 text-right"
                        >

                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-soft"
                            >
                                ID
                            </p>

                            <p
                                class="mt-1 font-mono text-sm font-semibold text-ink"
                            >
                                #{{ student.id }}
                            </p>

                        </div>

                    </div>

                </div>

            </slot>

        </template>


        <!-- ================================================= -->
        <!-- STUDENT INFORMATION -->
        <!-- ================================================= -->

        <div class="px-5 py-5">

            <div
                class="grid grid-cols-1 divide-y divide-hairline sm:grid-cols-2 sm:divide-x sm:divide-y-0"
            >

                <!-- EMAIL -->

                <div class="pb-4 sm:pr-5 sm:pb-0">

                    <p
                        class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-soft"
                    >
                        Email
                    </p>

                    <p
                        class="mt-2 break-all text-sm leading-6 text-ink"
                    >
                        {{ student.email || 'Not provided' }}
                    </p>

                </div>


                <!-- PHONE -->

                <div class="pt-4 sm:pl-5 sm:pt-0">

                    <p
                        class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-soft"
                    >
                        Phone
                    </p>

                    <p
                        class="mt-2 text-sm leading-6 text-ink"
                    >
                        {{ student.phone || 'Not provided' }}
                    </p>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- STATUS -->
            <!-- ================================================= -->

            <div
                class="mt-5 border-t border-hairline pt-4"
            >

                <div
                    class="flex items-center justify-between gap-4"
                >

                    <div>

                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-soft"
                        >
                            Account status
                        </p>

                    </div>


                    <span
                        class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.12em]"
                        :class="
                            student.status === 'active'
                                ? 'text-forest'
                                : 'text-sienna'
                        "
                    >

                        <span
                            class="h-2 w-2"
                            :class="
                                student.status === 'active'
                                    ? 'bg-forest'
                                    : 'bg-sienna'
                            "
                        ></span>

                        {{ student.status }}

                    </span>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- DEFAULT SLOT -->
            <!-- ================================================= -->

            <div
                v-if="$slots.default"
                class="mt-5 border-t border-hairline pt-5"
            >

                <slot />

            </div>

        </div>


        <!-- ================================================= -->
        <!-- ACTIONS -->
        <!-- ================================================= -->

        <div
            class="flex border-t border-hairline"
        >

            <!-- EDIT -->

            <button
                type="button"
                class="flex-1 border-r border-hairline px-5 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] text-forest transition-colors hover:bg-forest hover:text-white"
                @click="emit('edit', student)"
            >
                Edit student
            </button>


            <!-- DELETE -->

            <button
                type="button"
                class="px-5 py-3 text-xs font-bold uppercase tracking-[0.14em] text-sienna transition-colors hover:bg-sienna hover:text-white"
                @click="emit('delete', student)"
            >
                Delete
            </button>

        </div>

    </BaseCard>

</template>
