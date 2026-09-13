<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import {
    getUser,
    updateProfile,
    updateProfilePhoto,
    updateDocument,
    deleteDocument,
    changePassword
} from '../services/authApi'

const router = useRouter()

const user = ref(null)

const loading = ref(true)
const saving = ref(false)
const uploadingPhoto = ref(false)
const passwordLoading = ref(false)

const error = ref('')
const successMessage = ref('')

const editing = ref(false)
const changingPassword = ref(false)

const editName = ref('')
const editEmail = ref('')

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const photoInput = ref(null)


// =========================================================
// NAVIGATION
// =========================================================

const goToStudents = () => {
    router.push('/students')
}


// =========================================================
// HELPERS
// =========================================================

const clearMessages = () => {
    error.value = ''
    successMessage.value = ''
}

const showSuccess = (message) => {

    successMessage.value = message

    setTimeout(() => {
        successMessage.value = ''
    }, 3000)
}

const getInitial = () => {
    return user.value?.name?.charAt(0)?.toUpperCase() || 'U'
}

const formatDate = (date) => {

    if (!date) return 'N/A'

    return new Date(date).toLocaleDateString(
        'en-US',
        {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }
    )
}

const profilePhotoUrl = () => {

    if (!user.value?.profile_photo) {
        return null
    }

    return `http://127.0.0.1:8000/storage/${user.value.profile_photo}`
}

const documentUrl = () => {

    if (!user.value?.document) {
        return null
    }

    return `http://127.0.0.1:8000/storage/${user.value.document}`
}


// =========================================================
// FETCH USER
// =========================================================

const fetchUser = async () => {

    try {

        const token = localStorage.getItem('token')

        if (!token) {

            error.value =
                'You are not logged in.'

            return
        }

        const response =
            await getUser(token)

        user.value =
            response.data

        localStorage.setItem(
            'user',
            JSON.stringify(response.data)
        )

    } catch (err) {

        console.error(
            'Profile error:',
            err
        )

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

    editName.value =
        user.value.name

    editEmail.value =
        user.value.email

    editing.value = true

    clearMessages()
}

const cancelEdit = () => {

    editing.value = false

    editName.value = ''
    editEmail.value = ''

    error.value = ''
}

const saveProfile = async () => {

    if (
        !editName.value.trim() ||
        !editEmail.value.trim()
    ) {

        error.value =
            'Name and email are required.'

        return
    }

    saving.value = true
    clearMessages()

    try {

        const token =
            localStorage.getItem('token')

        if (!token) {

            error.value =
                'You are not logged in.'

            return
        }

        const response =
            await updateProfile(
                token,
                {
                    name:
                        editName.value.trim(),

                    email:
                        editEmail.value.trim()
                }
            )

        user.value =
            response.data.user

        localStorage.setItem(
            'user',
            JSON.stringify(response.data.user)
        )

        editing.value = false

        showSuccess(
            'Profile updated successfully.'
        )

    } catch (err) {

        console.error(
            'Update profile error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Failed to update profile.'

    } finally {

        saving.value = false
    }
}


// =========================================================
// PHOTO
// =========================================================

const selectPhoto = () => {

    if (uploadingPhoto.value) {
        return
    }

    photoInput.value?.click()
}

const handlePhotoUpload = async (event) => {

    const file =
        event.target.files?.[0]

    if (!file) return

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

    if (file.size > 2 * 1024 * 1024) {

        error.value =
            'Photo must be smaller than 2MB.'

        event.target.value = ''

        return
    }

    uploadingPhoto.value = true
    clearMessages()

    try {

        const token =
            localStorage.getItem('token')

        if (!token) {

            error.value =
                'You are not logged in.'

            return
        }

        const formData =
            new FormData()

        formData.append(
            'profile_photo',
            file
        )

        const response =
            await updateProfilePhoto(
                token,
                formData
            )

        user.value =
            response.data.user

        localStorage.setItem(
            'user',
            JSON.stringify(response.data.user)
        )

        showSuccess(
            'Profile photo updated successfully.'
        )

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

        event.target.value = ''
    }
}


// =========================================================
// PASSWORD
// =========================================================

const openChangePassword = () => {

    changingPassword.value = true

    clearMessages()
}

const cancelChangePassword = () => {

    changingPassword.value = false

    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''

    error.value = ''
}

const savePassword = async () => {

    if (
        !currentPassword.value ||
        !newPassword.value ||
        !confirmPassword.value
    ) {

        error.value =
            'All password fields are required.'

        return
    }

    if (newPassword.value.length < 8) {

        error.value =
            'New password must be at least 8 characters.'

        return
    }

    if (
        newPassword.value !==
        confirmPassword.value
    ) {

        error.value =
            'New passwords do not match.'

        return
    }

    passwordLoading.value = true
    clearMessages()

    try {

        const token =
            localStorage.getItem('token')

        if (!token) {

            error.value =
                'You are not logged in.'

            return
        }

        await changePassword(
            token,
            {
                current_password:
                    currentPassword.value,

                new_password:
                    newPassword.value,

                new_password_confirmation:
                    confirmPassword.value
            }
        )

        currentPassword.value = ''
        newPassword.value = ''
        confirmPassword.value = ''

        changingPassword.value = false

        showSuccess(
            'Password changed successfully.'
        )

    } catch (err) {

        console.error(
            'Change password error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Failed to change password.'

    } finally {

        passwordLoading.value = false
    }
}


// =========================================================
// DOCUMENT
// =========================================================

const handleDocumentChange = async (event) => {

    const file =
        event.target.files?.[0]

    if (!file) return

    if (file.type !== 'application/pdf') {

        error.value =
            'Please select a PDF file.'

        event.target.value = ''

        return
    }

    if (file.size > 5 * 1024 * 1024) {

        error.value =
            'PDF must be smaller than 5MB.'

        event.target.value = ''

        return
    }

    try {

        const token =
            localStorage.getItem('token')

        if (!token) {

            error.value =
                'You are not logged in.'

            return
        }

        const formData =
            new FormData()

        formData.append(
            'document',
            file
        )

        const response =
            await updateDocument(
                token,
                formData
            )

        user.value =
            response.data.user

        localStorage.setItem(
            'user',
            JSON.stringify(response.data.user)
        )

        showSuccess(
            'Document uploaded successfully.'
        )

    } catch (err) {

        console.error(
            'Document upload error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Failed to upload document.'

    } finally {

        event.target.value = ''
    }
}

const deleteDocumentFile = async () => {

    if (!user.value?.document) {
        return
    }

    const confirmed =
        confirm(
            'Are you sure you want to delete this document?'
        )

    if (!confirmed) {
        return
    }

    try {

        const token =
            localStorage.getItem('token')

        if (!token) {

            error.value =
                'You are not logged in.'

            return
        }

        const response =
            await deleteDocument(token)

        user.value =
            response.data.user

        localStorage.setItem(
            'user',
            JSON.stringify(response.data.user)
        )

        showSuccess(
            'Document deleted successfully.'
        )

    } catch (err) {

        console.error(
            'Document delete error:',
            err
        )

        error.value =
            err.response?.data?.message ||
            'Failed to delete document.'
    }
}


// =========================================================
// MOUNT
// =========================================================

onMounted(() => {

    const savedTheme =
        localStorage.getItem('theme')

    if (savedTheme === 'dark') {

        document.documentElement.classList.add('dark')

    } else {

        document.documentElement.classList.remove('dark')
    }

    fetchUser()
})

</script>
<template>
    <div class="min-h-screen bg-paper text-ink">

        <!-- =====================================================
             LOADING
        ====================================================== -->

        <div
            v-if="loading"
            class="flex min-h-screen items-center justify-center"
        >
            <div class="text-center">

                <div
                    class="mx-auto h-8 w-8 animate-spin border-2 border-hairline border-t-forest"
                ></div>

                <p class="mt-4 text-sm text-ink-soft">
                    Loading profile...
                </p>

            </div>
        </div>


        <!-- =====================================================
             ERROR
        ====================================================== -->

        <div
            v-else-if="error && !user"
            class="flex min-h-screen items-center justify-center px-5"
        >

            <div
                class="w-full max-w-md border border-sienna bg-surface p-6"
            >

                <div
                    class="mb-4 flex h-10 w-10 items-center justify-center bg-[#FFF3EF] text-sienna"
                >
                    !
                </div>

                <h2 class="text-lg font-semibold text-ink">
                    Unable to load profile
                </h2>

                <p class="mt-2 text-sm leading-6 text-ink-soft">
                    {{ error }}
                </p>

            </div>

        </div>


        <!-- =====================================================
             MAIN PAGE
        ====================================================== -->

        <div
            v-else-if="user"
            class="mx-auto max-w-7xl px-5 py-6 sm:px-8 lg:py-9"
        >

            <!-- =================================================
                 PAGE HEADER
            ================================================== -->

            <header
                class="mb-7 flex flex-col gap-5 border-b border-hairline pb-6 sm:flex-row sm:items-end sm:justify-between"
            >

                <div>

                    <p class="text-sm font-medium text-forest">
                        Student Portal
                    </p>

                    <h1
                        class="mt-1 text-3xl font-semibold text-ink"
                    >
                        My Profile
                    </h1>

                    <p
                        class="mt-2 text-sm text-ink-soft"
                    >
                        Manage your account information, documents and security.
                    </p>

                </div>


                <button
                    @click="goToStudents"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 self-start border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink transition hover:border-forest hover:text-forest sm:self-auto"
                >
                    <span>←</span>
                    Back to Students
                </button>

            </header>


            <!-- =================================================
                 NOTIFICATIONS
            ================================================== -->

            <div
                v-if="successMessage"
                class="mb-5 flex items-center gap-3 border border-[#C9DDCE] bg-[#F1F7F2] px-4 py-3 text-sm font-medium text-forest"
            >

                <span
                    class="flex h-6 w-6 shrink-0 items-center justify-center bg-forest text-xs text-white"
                >
                    ✓
                </span>

                {{ successMessage }}

            </div>


            <div
                v-if="error"
                class="mb-5 flex items-center gap-3 border border-[#E9C9BE] bg-[#FFF5F2] px-4 py-3 text-sm font-medium text-sienna"
            >

                <span
                    class="flex h-6 w-6 shrink-0 items-center justify-center bg-sienna text-xs text-white"
                >
                    !
                </span>

                {{ error }}

            </div>


            <!-- =================================================
                 PROFILE HEADER
            ================================================== -->

<section
    class="border border-hairline bg-surface"
>

    <div class="px-5 py-6 sm:px-8 sm:py-7">

        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

            <!-- PHOTO + USER INFO -->

            <div class="flex items-center gap-5">

                <!-- PHOTO -->

                <div class="relative shrink-0">

                    <div
                        class="h-24 w-24 overflow-hidden border-4 border-paper bg-paper sm:h-28 sm:w-28"
                    >

                        <img
                            v-if="profilePhotoUrl()"
                            :src="profilePhotoUrl()"
                            alt="Profile photo"
                            class="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-[#EDF4EE] text-4xl font-semibold text-forest"
                        >
                            {{ getInitial() }}
                        </div>

                    </div>


                    <!-- CHANGE PHOTO -->

                    <button
                        type="button"
                        @click="selectPhoto"
                        :disabled="uploadingPhoto"
                        title="Change profile photo"
                        class="absolute -bottom-2 -right-2 flex h-9 w-9 items-center justify-center border-2 border-surface bg-forest text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                    >

                        <span v-if="!uploadingPhoto">
                            +
                        </span>

                        <span v-else>
                            …
                        </span>

                    </button>


                    <input
                        ref="photoInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="handlePhotoUpload"
                    />

                </div>


                <!-- USER INFORMATION -->

                <div class="min-w-0">

                    <div class="flex flex-wrap items-center gap-3">

                        <h2
                            class="text-2xl font-semibold text-ink sm:text-3xl"
                        >
                            {{ user.name }}
                        </h2>

                        <span
                            class="inline-flex items-center gap-2 bg-[#EDF4EE] px-3 py-1.5 text-xs font-medium text-forest"
                        >

                            <span
                                class="h-1.5 w-1.5 bg-forest"
                            ></span>

                            Active

                        </span>

                    </div>

                    <p
                        class="mt-1 break-all text-sm text-ink-soft"
                    >
                        {{ user.email }}
                    </p>

                </div>

            </div>


            <!-- EDIT BUTTON -->

            <button
                v-if="!editing"
                @click="editProfile"
                type="button"
                class="inline-flex items-center justify-center gap-2 self-start border border-hairline bg-white px-5 py-2.5 text-sm font-medium text-ink transition hover:border-forest hover:text-forest sm:self-center"
            >
                <span>✎</span>
                Edit Profile
            </button>

        </div>

    </div>

</section>


            <!-- =================================================
                 ACCOUNT SUMMARY
            ================================================== -->

            <section
                class="mt-5 grid grid-cols-1 border border-hairline bg-surface sm:grid-cols-3"
            >

                <!-- MEMBER -->

                <div
                    class="border-b border-hairline p-5 sm:border-b-0 sm:border-r"
                >

                    <p class="text-sm text-ink-soft">
                        Member since
                    </p>

                    <p class="mt-2 text-lg font-semibold text-ink">
                        {{ formatDate(user.created_at) }}
                    </p>

                </div>


                <!-- USER ID -->

                <div
                    class="border-b border-hairline p-5 sm:border-b-0 sm:border-r"
                >

                    <p class="text-sm text-ink-soft">
                        User ID
                    </p>

                    <p class="mt-2 text-lg font-semibold text-ink">
                        #{{ user.id }}
                    </p>

                </div>


                <!-- DOCUMENT -->

                <div class="p-5">

                    <p class="text-sm text-ink-soft">
                        Document
                    </p>

                    <p
                        class="mt-2 text-lg font-semibold"
                        :class="user.document ? 'text-forest' : 'text-ink'"
                    >
                        {{ user.document ? 'Uploaded' : 'Not uploaded' }}
                    </p>

                </div>

            </section>


            <!-- =================================================
                 MAIN GRID
            ================================================== -->

            <div
                class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3"
            >


                <!-- =================================================
                     LEFT COLUMN
                ================================================== -->

                <div class="space-y-5 lg:col-span-2">


                    <!-- =============================================
                         EDIT PROFILE
                    ============================================== -->

                    <section
                        v-if="editing"
                        class="border border-forest bg-surface"
                    >

                        <div
                            class="border-b border-hairline px-6 py-5"
                        >

                            <h3
                                class="text-lg font-semibold text-ink"
                            >
                                Edit Profile
                            </h3>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                Update your basic account information.
                            </p>

                        </div>


                        <div class="p-6">

                            <div
                                class="grid grid-cols-1 gap-5 md:grid-cols-2"
                            >

                                <!-- NAME -->

                                <div>

                                    <label
                                        class="mb-2 block text-sm font-medium text-ink"
                                    >
                                        Full name
                                    </label>

                                    <input
                                        v-model="editName"
                                        type="text"
                                        placeholder="Enter your name"
                                        class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                    />

                                </div>


                                <!-- EMAIL -->

                                <div>

                                    <label
                                        class="mb-2 block text-sm font-medium text-ink"
                                    >
                                        Email address
                                    </label>

                                    <input
                                        v-model="editEmail"
                                        type="email"
                                        placeholder="Enter your email"
                                        class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                    />

                                </div>

                            </div>


                            <div
                                class="mt-6 flex flex-wrap gap-3"
                            >

                                <button
                                    @click="saveProfile"
                                    :disabled="saving"
                                    type="button"
                                    class="bg-forest px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{ saving ? 'Saving...' : 'Save Changes' }}
                                </button>


                                <button
                                    @click="cancelEdit"
                                    :disabled="saving"
                                    type="button"
                                    class="border border-hairline bg-surface px-5 py-2.5 text-sm font-medium text-ink transition hover:border-forest hover:text-forest disabled:opacity-50"
                                >
                                    Cancel
                                </button>

                            </div>

                        </div>

                    </section>


                    <!-- =============================================
                         PERSONAL INFORMATION
                    ============================================== -->

                    <section
                        class="border border-hairline bg-surface"
                    >

                        <div
                            class="border-b border-hairline px-6 py-5"
                        >

                            <h3
                                class="text-lg font-semibold text-ink"
                            >
                                Personal Information
                            </h3>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                Information associated with your account.
                            </p>

                        </div>


                        <div class="p-6">

                            <div class="grid grid-cols-1 gap-0 border border-hairline sm:grid-cols-2">

                                <!-- NAME -->

                                <div
                                    class="border-b border-hairline p-5 sm:border-r"
                                >

                                    <p class="text-xs font-medium text-ink-soft">
                                        Full name
                                    </p>

                                    <p
                                        class="mt-2 break-words text-sm font-semibold text-ink"
                                    >
                                        {{ user.name }}
                                    </p>

                                </div>


                                <!-- EMAIL -->

                                <div
                                    class="border-b border-hairline p-5"
                                >

                                    <p class="text-xs font-medium text-ink-soft">
                                        Email address
                                    </p>

                                    <p
                                        class="mt-2 break-all text-sm font-semibold text-ink"
                                    >
                                        {{ user.email }}
                                    </p>

                                </div>


                                <!-- MEMBER -->

                                <div
                                    class="p-5 sm:border-r"
                                >

                                    <p class="text-xs font-medium text-ink-soft">
                                        Member since
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-semibold text-ink"
                                    >
                                        {{ formatDate(user.created_at) }}
                                    </p>

                                </div>


                                <!-- PHOTO -->

                                <div class="p-5">

                                    <p class="text-xs font-medium text-ink-soft">
                                        Profile photo
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-semibold"
                                        :class="user.profile_photo ? 'text-forest' : 'text-ink'"
                                    >
                                        {{
                                            user.profile_photo
                                                ? 'Uploaded'
                                                : 'Not uploaded'
                                        }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </section>


                    <!-- =============================================
                         DOCUMENTS
                    ============================================== -->

                    <section
                        class="border border-hairline bg-surface"
                    >

                        <div
                            class="border-b border-hairline px-6 py-5"
                        >

                            <h3
                                class="text-lg font-semibold text-ink"
                            >
                                Documents
                            </h3>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                Manage your uploaded PDF document.
                            </p>

                        </div>


                        <div class="p-6">

                            <!-- DOCUMENT EXISTS -->

                            <div
                                v-if="user.document"
                                class="border border-hairline bg-paper"
                            >

                                <div
                                    class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between"
                                >

                                    <div
                                        class="flex items-center gap-4"
                                    >

                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center bg-[#FFF0EB] text-xs font-semibold text-sienna"
                                        >
                                            PDF
                                        </div>

                                        <div>

                                            <p
                                                class="text-sm font-semibold text-ink"
                                            >
                                                Student Document
                                            </p>

                                            <p
                                                class="mt-1 text-sm text-ink-soft"
                                            >
                                                Your document is currently uploaded.
                                            </p>

                                        </div>

                                    </div>


                                    <div
                                        class="flex flex-wrap gap-2"
                                    >

                                        <a
                                            :href="documentUrl()"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="border border-hairline bg-surface px-4 py-2 text-sm font-medium text-ink transition hover:border-forest hover:text-forest"
                                        >
                                            View
                                        </a>


                                        <a
                                            :href="documentUrl()"
                                            download
                                            class="border border-hairline bg-surface px-4 py-2 text-sm font-medium text-ink transition hover:border-forest hover:text-forest"
                                        >
                                            Download
                                        </a>


                                        <button
                                            type="button"
                                            @click="deleteDocumentFile"
                                            class="border border-[#E9C9BE] bg-surface px-4 py-2 text-sm font-medium text-sienna transition hover:bg-sienna hover:text-white"
                                        >
                                            Delete
                                        </button>

                                    </div>

                                </div>

                            </div>


                            <!-- NO DOCUMENT -->

                            <div
                                v-else
                                class="border border-dashed border-hairline bg-paper px-6 py-8 text-center"
                            >

                                <div
                                    class="mx-auto flex h-11 w-11 items-center justify-center bg-[#F5F1EB] text-ink"
                                >
                                    PDF
                                </div>

                                <p
                                    class="mt-4 text-sm font-semibold text-ink"
                                >
                                    No document uploaded
                                </p>

                                <p
                                    class="mx-auto mt-1 max-w-md text-sm leading-5 text-ink-soft"
                                >
                                    Upload a PDF document to keep it attached to your profile.
                                </p>

                            </div>


                            <!-- UPLOAD -->

                            <label
                                class="mt-4 inline-flex cursor-pointer items-center justify-center bg-forest px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90"
                            >
                                Upload PDF

                                <input
                                    type="file"
                                    accept=".pdf,application/pdf"
                                    class="hidden"
                                    @change="handleDocumentChange"
                                />

                            </label>

                        </div>

                    </section>

                </div>


                <!-- =================================================
                     RIGHT COLUMN
                ================================================== -->

                <aside class="space-y-5">


                    <!-- =============================================
                         ACCOUNT STATUS
                    ============================================== -->

                    <section
                        class="border border-hairline bg-surface"
                    >

                        <div class="p-6">

                            <p class="text-sm font-medium text-ink-soft">
                                Account status
                            </p>

                            <div
                                class="mt-3 flex items-center gap-3"
                            >

                                <span
                                    class="h-3 w-3 bg-forest"
                                ></span>

                                <span
                                    class="text-lg font-semibold text-forest"
                                >
                                    Active
                                </span>

                            </div>

                            <p
                                class="mt-4 border-t border-hairline pt-4 text-sm leading-6 text-ink-soft"
                            >
                                Your student account is active and ready to use.
                            </p>

                        </div>

                    </section>


                    <!-- =============================================
                         SECURITY
                    ============================================== -->

                    <section
                        class="border border-hairline bg-surface"
                    >

                        <div
                            class="border-b border-hairline px-6 py-5"
                        >

                            <h3
                                class="text-lg font-semibold text-ink"
                            >
                                Security
                            </h3>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                Manage your account password.
                            </p>

                        </div>


                        <div class="p-6">

                            <!-- CLOSED -->

                            <div
                                v-if="!changingPassword"
                            >

                                <div
                                    class="border border-hairline bg-paper p-4"
                                >

                                    <p
                                        class="text-sm font-medium text-ink"
                                    >
                                        Password protection
                                    </p>

                                    <p
                                        class="mt-1 text-sm leading-5 text-ink-soft"
                                    >
                                        Keep your account secure by using a strong password.
                                    </p>

                                </div>


                                <button
                                    @click="openChangePassword"
                                    type="button"
                                    class="mt-4 w-full border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink transition hover:border-forest hover:text-forest"
                                >
                                    Change Password
                                </button>

                            </div>


                            <!-- PASSWORD FORM -->

                            <div
                                v-else
                                class="space-y-4"
                            >

                                <!-- CURRENT -->

                                <div>

                                    <label
                                        class="mb-2 block text-sm font-medium text-ink"
                                    >
                                        Current password
                                    </label>

                                    <input
                                        v-model="currentPassword"
                                        type="password"
                                        autocomplete="current-password"
                                        placeholder="Current password"
                                        class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                    />

                                </div>


                                <!-- NEW -->

                                <div>

                                    <label
                                        class="mb-2 block text-sm font-medium text-ink"
                                    >
                                        New password
                                    </label>

                                    <input
                                        v-model="newPassword"
                                        type="password"
                                        autocomplete="new-password"
                                        placeholder="New password"
                                        class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                    />

                                    <p class="mt-1.5 text-xs text-ink-soft">
                                        Minimum 8 characters.
                                    </p>

                                </div>


                                <!-- CONFIRM -->

                                <div>

                                    <label
                                        class="mb-2 block text-sm font-medium text-ink"
                                    >
                                        Confirm password
                                    </label>

                                    <input
                                        v-model="confirmPassword"
                                        type="password"
                                        autocomplete="new-password"
                                        placeholder="Confirm password"
                                        class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                    />

                                </div>


                                <!-- ACTIONS -->

                                <div class="space-y-2 pt-2">

                                    <button
                                        @click="savePassword"
                                        :disabled="passwordLoading"
                                        type="button"
                                        class="w-full bg-forest px-4 py-2.5 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{
                                            passwordLoading
                                                ? 'Changing...'
                                                : 'Update Password'
                                        }}
                                    </button>


                                    <button
                                        @click="cancelChangePassword"
                                        :disabled="passwordLoading"
                                        type="button"
                                        class="w-full border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink transition hover:border-forest hover:text-forest disabled:opacity-50"
                                    >
                                        Cancel
                                    </button>

                                </div>

                            </div>

                        </div>

                    </section>


                    <!-- =============================================
                         QUICK ACTIONS
                    ============================================== -->

                    <section
                        class="border border-hairline bg-surface"
                    >

                        <div
                            class="border-b border-hairline px-6 py-5"
                        >

                            <h3
                                class="text-lg font-semibold text-ink"
                            >
                                Quick Actions
                            </h3>

                            <p
                                class="mt-1 text-sm text-ink-soft"
                            >
                                Common account actions.
                            </p>

                        </div>


                        <div>

                            <!-- EDIT -->

                            <button
                                v-if="!editing"
                                @click="editProfile"
                                type="button"
                                class="flex w-full items-center justify-between border-b border-hairline px-6 py-4 text-left transition hover:bg-paper"
                            >

                                <div>

                                    <p class="text-sm font-medium text-ink">
                                        Edit Profile
                                    </p>

                                    <p class="mt-1 text-xs text-ink-soft">
                                        Update your information
                                    </p>

                                </div>

                                <span
                                    class="text-lg text-ink-soft"
                                >
                                    →
                                </span>

                            </button>


                            <!-- PASSWORD -->

                            <button
                                @click="openChangePassword"
                                type="button"
                                class="flex w-full items-center justify-between border-b border-hairline px-6 py-4 text-left transition hover:bg-paper"
                            >

                                <div>

                                    <p class="text-sm font-medium text-ink">
                                        Change Password
                                    </p>

                                    <p class="mt-1 text-xs text-ink-soft">
                                        Update account security
                                    </p>

                                </div>

                                <span
                                    class="text-lg text-ink-soft"
                                >
                                    →
                                </span>

                            </button>


                            <!-- STUDENTS -->

                            <button
                                @click="goToStudents"
                                type="button"
                                class="flex w-full items-center justify-between px-6 py-4 text-left transition hover:bg-paper"
                            >

                                <div>

                                    <p class="text-sm font-medium text-ink">
                                        Student Records
                                    </p>

                                    <p class="mt-1 text-xs text-ink-soft">
                                        Return to student list
                                    </p>

                                </div>

                                <span
                                    class="text-lg text-ink-soft"
                                >
                                    →
                                </span>

                            </button>

                        </div>

                    </section>

                </aside>

            </div>


            <!-- =================================================
                 FOOTER
            ================================================== -->

            <footer
                class="mt-8 flex flex-col gap-2 border-t border-hairline pt-5 text-xs text-ink-soft sm:flex-row sm:items-center sm:justify-between"
            >

                <span>
                    Student Registry
                </span>

                <span>
                    Account #{{ user.id }}
                </span>

            </footer>

        </div>

    </div>
</template>
