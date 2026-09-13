
<script setup>

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

const darkMode = ref(false)

const form = ref({
    name: '',
    email: '',
    phone: '',
    relationship: ''
})

const fetchProfile = async () => {

    loading.value = true
    errorMessage.value = ''

    try {

        const token = localStorage.getItem('parent_token')

        if (!token) {
            router.push('/parents/login')
            return
        }

        const response = await axios.get(
            'http://127.0.0.1:8000/api/parent/profile',
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        const parent = response.data.parent

        form.value = {
            name: parent.name || '',
            email: parent.email || '',
            phone: parent.phone || '',
            relationship: parent.relationship || ''
        }

    } catch (error) {

        console.error('Parent profile error:', error)

        if (error.response?.status === 401) {

            localStorage.removeItem('parent_token')
            localStorage.removeItem('parent_user')

            router.push('/parents/login')

            return
        }

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to load profile.'

    } finally {

        loading.value = false

    }
}

const updateProfile = async () => {

    saving.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {

        const token = localStorage.getItem('parent_token')

        const response = await axios.put(
            'http://127.0.0.1:8000/api/parent/profile',
            form.value,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json'
                }
            }
        )

        successMessage.value =
            response.data.message || 'Profile updated successfully.'

        // Update stored parent user
        const storedUser = localStorage.getItem('parent_user')

        if (storedUser) {

            const user = JSON.parse(storedUser)

            user.parent = response.data.parent

            localStorage.setItem(
                'parent_user',
                JSON.stringify(user)
            )
        }

    } catch (error) {

        console.error('Update parent profile error:', error)

        if (error.response?.status === 401) {

            localStorage.removeItem('parent_token')
            localStorage.removeItem('parent_user')

            router.push('/parents/login')

            return
        }

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to update profile.'

    } finally {

        saving.value = false

    }
}

const goBack = () => {
            router.push('/dashboard')
}

const toggleDarkMode = () => {

    darkMode.value = !darkMode.value

    document.documentElement.classList.toggle(
        'dark',
        darkMode.value
    )

    localStorage.setItem(
        'theme',
        darkMode.value ? 'dark' : 'light'
    )
}

onMounted(() => {

    const savedTheme = localStorage.getItem('theme')

    if (savedTheme === 'dark') {

        darkMode.value = true
        document.documentElement.classList.add('dark')

    } else {

        darkMode.value = false
        document.documentElement.classList.remove('dark')

    }

    fetchProfile()

})

</script>

<<template>
    <div class="min-h-screen bg-paper text-ink">
        <!-- HEADER / LETTERHEAD -->

        <header class="border-b border-hairline">
            <div
                class="mx-auto flex max-w-5xl flex-col gap-5 px-4 py-6 sm:px-6 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center border border-hairline bg-surface text-lg"
                        >
                            👤
                        </div>

                        <span class="text-sm font-medium text-forest">
                            Parent Portal
                        </span>
                    </div>

                    <h1
                        class="font-serif text-4xl font-medium leading-tight text-ink"
                    >
                        My Profile
                    </h1>

                    <p class="mt-2 text-sm text-ink-soft">
                        Manage your parent account information.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- DARK MODE -->

                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                    >
                        <span v-if="darkMode">
                            ☀️ Light
                        </span>

                        <span v-else>
                            🌙 Dark
                        </span>
                    </button>

                    <!-- DASHBOARD -->

                    <button
                        @click="goBack"
                        type="button"
                        class="border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-ink"
                    >
                        ← Dashboard
                    </button>
                </div>
            </div>
        </header>


        <!-- MAIN -->

        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- LOADING -->

            <div
                v-if="loading"
                class="border border-hairline bg-surface px-6 py-16 text-center"
            >
                <p class="text-sm text-ink-soft">
                    Loading profile...
                </p>
            </div>


            <!-- ERROR -->

            <div
                v-else-if="errorMessage"
                class="border border-sienna bg-surface p-5"
            >
                <div
                    class="border border-sienna bg-paper px-4 py-3 text-sm leading-6 text-sienna"
                >
                    {{ errorMessage }}
                </div>
            </div>


            <!-- PROFILE -->

            <div
                v-else
                class="border border-hairline bg-surface"
            >

                <!-- PROFILE HEADER -->

                <section
                    class="border-b border-hairline px-6 py-8 sm:px-8"
                >
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center"
                    >
                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center border border-hairline bg-paper text-4xl"
                        >
                            👤
                        </div>

                        <div>
                            <p
                                class="mb-1 text-sm font-medium text-forest"
                            >
                                Account Holder
                            </p>

                            <h2
                                class="font-serif text-3xl font-medium text-ink"
                            >
                                {{ form.name || 'Parent' }}
                            </h2>

                            <p class="mt-1 text-sm text-ink-soft">
                                Parent Account
                            </p>
                        </div>
                    </div>
                </section>


                <!-- SUCCESS -->

                <div
                    v-if="successMessage"
                    class="mx-6 mt-6 border border-forest bg-paper px-4 py-3 text-sm text-forest sm:mx-8"
                >
                    {{ successMessage }}
                </div>


                <!-- FORM -->

                <form
                    @submit.prevent="updateProfile"
                    class="p-6 sm:p-8"
                >

                    <div class="mb-7">
                        <p class="text-sm font-medium text-forest">
                            Account Details
                        </p>

                        <h3
                            class="mt-1 font-serif text-2xl font-medium text-ink"
                        >
                            Personal Information
                        </h3>

                        <p class="mt-2 text-sm text-ink-soft">
                            Keep your parent account information up to date.
                        </p>
                    </div>


                    <!-- FORM FIELDS -->

                    <div
                        class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2"
                    >

                        <!-- NAME -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Full Name
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="Enter your full name"
                            >
                        </div>


                        <!-- EMAIL -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Email
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="Enter your email"
                            >
                        </div>


                        <!-- PHONE -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Phone
                            </label>

                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="Enter your phone number"
                            >
                        </div>


                        <!-- RELATIONSHIP -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-ink"
                            >
                                Relationship
                            </label>

                            <input
                                v-model="form.relationship"
                                type="text"
                                class="w-full border border-hairline bg-paper px-4 py-3 text-sm text-ink outline-none placeholder:text-ink-soft focus:border-forest"
                                placeholder="e.g. Father, Mother, Guardian"
                            >
                        </div>

                    </div>


                    <!-- ERROR -->

                    <div
                        v-if="errorMessage"
                        class="mt-6 border border-sienna bg-paper px-4 py-3 text-sm leading-6 text-sienna"
                    >
                        {{ errorMessage }}
                    </div>


                    <!-- ACTION -->

                    <div
                        class="mt-8 flex justify-end border-t border-hairline pt-6"
                    >
                        <button
                            type="submit"
                            :disabled="saving"
                            class="border border-forest bg-forest px-6 py-3 text-sm font-medium text-white hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="saving">
                                Saving...
                            </span>

                            <span v-else>
                                Save Changes
                            </span>
                        </button>
                    </div>

                </form>

            </div>


            <!-- FOOTER NOTE -->

            <div
                class="mt-6 border-t border-hairline pt-5 text-xs leading-5 text-ink-soft"
            >
                Your profile information is used to maintain your parent
                account and provide access to your child's academic records.
            </div>

        </main>
    </div>
</template>

