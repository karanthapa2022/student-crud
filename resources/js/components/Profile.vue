<script setup>

import { ref, onMounted } from 'vue'

import {
    getUser,
    updateProfile,
    updateProfilePhoto
} from '../services/authApi'


// =========================================================
// USER
// =========================================================

const user = ref(null)


// =========================================================
// LOADING / ERROR / SUCCESS
// =========================================================

const loading = ref(true)
const saving = ref(false)
const uploadingPhoto = ref(false)

const error = ref('')
const successMessage = ref('')


// =========================================================
// EDIT PROFILE
// =========================================================

const editing = ref(false)

const editName = ref('')
const editEmail = ref('')


// =========================================================
// PHOTO
// =========================================================

const photoInput = ref(null)


// =========================================================
// GET USER
// =========================================================

const fetchUser = async () => {

    try {

        const token = localStorage.getItem('token')

        if (!token) {
            error.value = 'You are not logged in.'
            return
        }

        const response = await getUser(token)

        user.value = response.data

        // Keep localStorage updated
        localStorage.setItem(
            'user',
            JSON.stringify(response.data)
        )

    } catch (err) {

        console.error('Profile error:', err)

        error.value =
            err.response?.data?.message ||
            'Failed to load profile.'

    } finally {

        loading.value = false

    }

}


// =========================================================
// EDIT PROFILE
// =========================================================

const editProfile = () => {

    if (!user.value) return

    editName.value = user.value.name
    editEmail.value = user.value.email

    editing.value = true

    error.value = ''
    successMessage.value = ''

}


// =========================================================
// CANCEL EDIT
// =========================================================

const cancelEdit = () => {

    editing.value = false

    editName.value = ''
    editEmail.value = ''

    error.value = ''

}


// =========================================================
// SAVE PROFILE
// =========================================================

const saveProfile = async () => {

    if (!editName.value.trim() || !editEmail.value.trim()) {

        error.value =
            'Name and email are required.'

        return

    }

    saving.value = true

    error.value = ''
    successMessage.value = ''


    try {

        const token = localStorage.getItem('token')

        const response = await updateProfile(
            token,
            {
                name: editName.value.trim(),
                email: editEmail.value.trim(),
            }
        )


        // Update user
        user.value = response.data.user


        // Update localStorage
        localStorage.setItem(
            'user',
            JSON.stringify(response.data.user)
        )


        // Close edit mode
        editing.value = false


        successMessage.value =
            'Profile updated successfully.'


        // Hide success message after 3 seconds
        setTimeout(() => {

            successMessage.value = ''

        }, 3000)


    } catch (err) {

        console.error('Update profile error:', err)

        error.value =
            err.response?.data?.message ||
            'Failed to update profile.'

    } finally {

        saving.value = false

    }

}


// =========================================================
// OPEN PHOTO SELECTOR
// =========================================================

const selectPhoto = () => {

    if (uploadingPhoto.value) return

    photoInput.value?.click()

}


// =========================================================
// UPLOAD PROFILE PHOTO
// =========================================================

const handlePhotoUpload = async (event) => {

    const file = event.target.files?.[0]

    if (!file) return


    // =====================================================
    // CHECK FILE TYPE
    // =====================================================

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ]

    if (!allowedTypes.includes(file.type)) {

        error.value =
            'Please select a JPG, PNG, or WEBP image.'

        event.target.value = ''

        return

    }


    // =====================================================
    // CHECK FILE SIZE
    // =====================================================

    if (file.size > 2 * 1024 * 1024) {

        error.value =
            'Photo must be smaller than 2MB.'

        event.target.value = ''

        return

    }


    uploadingPhoto.value = true

    error.value = ''
    successMessage.value = ''


    try {

        const token = localStorage.getItem('token')


        if (!token) {

            error.value =
                'You are not logged in.'

            return

        }


        // =================================================
        // CREATE FORM DATA
        // =================================================

        const formData = new FormData()

        formData.append(
            'profile_photo',
            file
        )


        // =================================================
        // SEND PHOTO TO LARAVEL
        // IMPORTANT: authApi.js MUST USE POST
        // =================================================

        const response =
            await updateProfilePhoto(
                token,
                formData
            )


        // =================================================
        // UPDATE USER
        // =================================================

        user.value =
            response.data.user


        // =================================================
        // UPDATE LOCAL STORAGE
        // =================================================

        localStorage.setItem(
            'user',
            JSON.stringify(response.data.user)
        )


        successMessage.value =
            'Profile photo updated successfully.'


        // Hide success message
        setTimeout(() => {

            successMessage.value = ''

        }, 3000)


    } catch (err) {

        console.error(
            'Profile photo upload error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Failed to upload profile photo.'

    } finally {

        uploadingPhoto.value = false

        // Reset input
        event.target.value = ''

    }

}


// =========================================================
// PROFILE PHOTO URL
// =========================================================

const profilePhotoUrl = () => {

    if (!user.value?.profile_photo) {
        return null
    }

    return `http://127.0.0.1:8000/storage/${user.value.profile_photo}`

}


// =========================================================
// FORMAT DATE
// =========================================================

const formatDate = (date) => {

    if (!date) {
        return 'N/A'
    }

    return new Date(date).toLocaleDateString(
        'en-US',
        {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }
    )

}


// =========================================================
// MOUNT
// =========================================================

onMounted(() => {

    fetchUser()

})

</script>


<template>

<div class="min-h-screen bg-gray-100">


    <!-- ================================================= -->
    <!-- LOADING -->
    <!-- ================================================= -->

    <div
        v-if="loading"
        class="min-h-screen flex items-center justify-center"
    >

        <div class="text-center">

            <div
                class="w-10 h-10 border-4 border-gray-300 border-t-gray-900 rounded-full animate-spin mx-auto mb-4"
            ></div>

            <p class="text-gray-500">
                Loading profile...
            </p>

        </div>

    </div>


    <!-- ================================================= -->
    <!-- ERROR WITHOUT USER -->
    <!-- ================================================= -->

    <div
        v-else-if="error && !user"
        class="max-w-4xl mx-auto px-4 pt-10"
    >

        <div
            class="bg-red-50 border border-red-200 text-red-600 rounded-xl p-4"
        >

            {{ error }}

        </div>

    </div>


    <!-- ================================================= -->
    <!-- PROFILE -->
    <!-- ================================================= -->

    <div
        v-else-if="user"
        class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
    >


        <!-- ================================================= -->
        <!-- PAGE HEADER -->
        <!-- ================================================= -->

        <div class="mb-8">

            <h1
                class="text-3xl font-bold text-gray-900"
            >
                Profile
            </h1>

            <p class="mt-1 text-gray-500">
                View and manage your account information
            </p>

        </div>


        <!-- ================================================= -->
        <!-- SUCCESS MESSAGE -->
        <!-- ================================================= -->

        <div
            v-if="successMessage"
            class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl flex items-center gap-3"
        >

            <div
                class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center font-bold"
            >
                ✓
            </div>

            <span>
                {{ successMessage }}
            </span>

        </div>


        <!-- ================================================= -->
        <!-- ERROR MESSAGE -->
        <!-- ================================================= -->

        <div
            v-if="error"
            class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl"
        >

            {{ error }}

        </div>


        <!-- ================================================= -->
        <!-- MAIN PROFILE CARD -->
        <!-- ================================================= -->

        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
        >


            <!-- ================================================= -->
            <!-- COVER -->
            <!-- ================================================= -->

            <div
                class="h-40 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"
            ></div>


            <!-- ================================================= -->
            <!-- USER SECTION -->
            <!-- ================================================= -->

            <div class="px-6 sm:px-10">


                <!-- ================================================= -->
                <!-- AVATAR -->
                <!-- ================================================= -->

                <div class="-mt-14">

                    <div class="relative w-28 h-28">


                        <!-- WHITE AVATAR BORDER -->

                        <div
                            class="w-28 h-28 rounded-full bg-white p-2 shadow-lg"
                        >


                            <!-- ================================================= -->
                            <!-- UPLOADED PHOTO -->
                            <!-- ================================================= -->

                            <img
                                v-if="profilePhotoUrl()"
                                :src="profilePhotoUrl()"
                                alt="Profile Photo"
                                class="w-full h-full rounded-full object-cover"
                            />


                            <!-- ================================================= -->
                            <!-- DEFAULT AVATAR -->
                            <!-- ================================================= -->

                            <div
                                v-else
                                class="w-full h-full rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center"
                            >

                                <span
                                    class="text-4xl font-bold text-white"
                                >

                                    {{
                                        user.name
                                            .charAt(0)
                                            .toUpperCase()
                                    }}

                                </span>

                            </div>

                        </div>


                        <!-- ================================================= -->
                        <!-- CAMERA BUTTON -->
                        <!-- ================================================= -->

                        <button
                            type="button"
                            @click="selectPhoto"
                            :disabled="uploadingPhoto"
                            class="absolute bottom-0 right-0 w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center shadow-lg hover:bg-gray-700 transition border-2 border-white disabled:opacity-60"
                            title="Change profile photo"
                        >

                            <span
                                v-if="!uploadingPhoto"
                                class="text-lg"
                            >
                                📷
                            </span>

                            <span
                                v-else
                                class="text-xs"
                            >
                                ...
                            </span>

                        </button>


                        <!-- ================================================= -->
                        <!-- HIDDEN FILE INPUT -->
                        <!-- ================================================= -->

                        <input
                            ref="photoInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            @change="handlePhotoUpload"
                        />

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- NAME + EDIT BUTTON -->
                <!-- ================================================= -->

                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 pt-5 pb-7"
                >


                    <div class="min-w-0">

                        <h2
                            class="text-3xl font-bold text-gray-900 leading-tight break-words"
                        >
                            {{ user.name }}
                        </h2>

                        <p
                            class="text-gray-500 mt-2 break-all"
                        >
                            {{ user.email }}
                        </p>

                        <!-- Photo helper -->

                        <p
                            class="text-xs text-gray-400 mt-2"
                        >
                            Click the camera icon to change your photo
                        </p>

                    </div>


                    <!-- ================================================= -->
                    <!-- EDIT PROFILE BUTTON -->
                    <!-- ================================================= -->

                    <button
                        v-if="!editing"
                        @click="editProfile"
                        class="px-6 py-3 bg-gray-900 text-white rounded-xl font-medium hover:bg-gray-800 transition shadow-sm"
                    >

                        Edit Profile

                    </button>

                </div>


                <!-- ================================================= -->
                <!-- EDIT PROFILE -->
                <!-- ================================================= -->

                <div
                    v-if="editing"
                    class="mb-8 bg-gray-50 border border-gray-200 rounded-2xl p-6"
                >

                    <h3
                        class="text-lg font-semibold text-gray-900 mb-5"
                    >
                        Edit Profile
                    </h3>


                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-5"
                    >


                        <!-- NAME -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Full Name
                            </label>

                            <input
                                v-model="editName"
                                type="text"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter your name"
                            />

                        </div>


                        <!-- EMAIL -->

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Email Address
                            </label>

                            <input
                                v-model="editEmail"
                                type="email"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter your email"
                            />

                        </div>

                    </div>


                    <!-- BUTTONS -->

                    <div
                        class="flex flex-col sm:flex-row gap-3 mt-6"
                    >

                        <button
                            @click="saveProfile"
                            :disabled="saving"
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                        >

                            {{
                                saving
                                    ? 'Saving...'
                                    : 'Save Changes'
                            }}

                        </button>


                        <button
                            @click="cancelEdit"
                            :disabled="saving"
                            class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition"
                        >

                            Cancel

                        </button>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- DIVIDER -->
                <!-- ================================================= -->

                <div
                    class="border-t border-gray-200"
                ></div>


                <!-- ================================================= -->
                <!-- ACCOUNT INFORMATION -->
                <!-- ================================================= -->

                <div class="py-8">

                    <h3
                        class="text-xl font-semibold text-gray-900 mb-6"
                    >
                        Account Information
                    </h3>


                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
                    >


                        <!-- USER ID -->

                        <div
                            class="rounded-xl border border-gray-200 p-5 hover:shadow-sm transition"
                        >

                            <p
                                class="text-sm text-gray-500 mb-2"
                            >
                                User ID
                            </p>

                            <p
                                class="text-xl font-semibold text-gray-900"
                            >
                                #{{ user.id }}
                            </p>

                        </div>


                        <!-- EMAIL -->

                        <div
                            class="rounded-xl border border-gray-200 p-5 hover:shadow-sm transition"
                        >

                            <p
                                class="text-sm text-gray-500 mb-2"
                            >
                                Email Address
                            </p>

                            <p
                                class="font-semibold text-gray-900 break-all"
                            >
                                {{ user.email }}
                            </p>

                        </div>


                        <!-- STATUS -->

                        <div
                            class="rounded-xl border border-gray-200 p-5 hover:shadow-sm transition"
                        >

                            <p
                                class="text-sm text-gray-500 mb-2"
                            >
                                Account Status
                            </p>

                            <div
                                class="flex items-center gap-2"
                            >

                                <span
                                    class="w-2.5 h-2.5 rounded-full bg-green-500"
                                ></span>

                                <span
                                    class="font-semibold text-green-600"
                                >
                                    Active
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- ACCOUNT DETAILS -->
                <!-- ================================================= -->

                <div
                    class="border-t border-gray-200 py-8"
                >

                    <h3
                        class="text-xl font-semibold text-gray-900 mb-6"
                    >
                        Account Details
                    </h3>


                    <div class="space-y-6">


                        <!-- NAME -->

                        <div>

                            <p
                                class="text-sm text-gray-500"
                            >
                                Full Name
                            </p>

                            <p
                                class="font-medium text-gray-900 mt-1 break-words"
                            >
                                {{ user.name }}
                            </p>

                        </div>


                        <!-- EMAIL -->

                        <div>

                            <p
                                class="text-sm text-gray-500"
                            >
                                Email
                            </p>

                            <p
                                class="font-medium text-gray-900 mt-1 break-all"
                            >
                                {{ user.email }}
                            </p>

                        </div>


                        <!-- MEMBER SINCE -->

                        <div>

                            <p
                                class="text-sm text-gray-500"
                            >
                                Member Since
                            </p>

                            <p
                                class="font-medium text-gray-900 mt-1"
                            >
                                {{ formatDate(user.created_at) }}
                            </p>

                        </div>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>

</template>