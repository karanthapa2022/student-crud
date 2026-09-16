<script setup>
import {ref} from 'vue'
import { changePassword } from '../../services/authApi'
import {useRouter} from 'vue-router'

const router = useRouter()

const currentPassword= ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const errorMessage = ref('')

const submit = async () =>{
    try{
        const token = localStorage.getItem('token')
        await changePassword(token,{
            current_password: currentPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmPassword.value,
        })
        router.push('/dashboard')

    } catch (error){
       errorMessage.value =
    error.response?.data?.message ||
    'Failed to change password.'
    }
    
}

</script>
<template>
    <div class="min-h-screen flex items-center justify-center bg-[#F7F8F4] px-6">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold text-[#1C2B24]">
                Change Your Password
            </h1>
            <p class="mt-2 text-sm text-[#1C2B24]/60">
                You must change your temporary password before continuing.
            </p>
            <p 
            v-if="errorMessage"
            class="mt-3 text-sm text-[#8A3E2A]">
                {{ errorMessage }}
            </p>
            <div>
                <form class="mt-6 space-y-4"
                @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium text-[#1C2B24]">
                    Current Password
                </label>

                <input
                    v-model="currentPassword"
                    type="password"
                    class="mt-1 w-full rounded-md border border-[#D8DDD3] px-3 py-2 outline-none focus:border-[#1C2B24]"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-[#1C2B24]">
                    New Password
                </label>

                <input
                    v-model="newPassword"
                    type="password"
                    class="mt-1 w-full rounded-md border border-[#D8DDD3] px-3 py-2 outline-none focus:border-[#1C2B24]"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-[#1C2B24]">
                    Confirm New Password
                </label>

                <input
                    v-model="confirmPassword"
                    type="password"
                    class="mt-1 w-full rounded-md border border-[#D8DDD3] px-3 py-2 outline-none focus:border-[#1C2B24]"
                />
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-[#1C2B24] px-4 py-2 text-sm font-medium text-white"
            >
                Change Password
            </button>
                </form>
            </div>

        </div>

    </div>
</template>