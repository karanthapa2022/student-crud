<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const users = ref([])
const roles = ref([])
const loading = ref(true)
const error = ref('')
const saving = ref(false)
const form = ref({ name: '', email: '', password: '', role: 'student' })

const token = () => localStorage.getItem('token')

const loadUsers = async () => {
  try {
    const response = await fetch('/api/users', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token()}` },
    })
    if (!response.ok) throw new Error('Unable to load users.')
    const data = await response.json()
    users.value = data.users
    roles.value = data.roles
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
  }
}

const createUser = async () => {
  saving.value = true
  error.value = ''
  try {
    const response = await fetch('/api/users', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token()}`,
      },
      body: JSON.stringify(form.value),
    })
    const data = await response.json()
    if (!response.ok) throw new Error(data.message || 'Unable to create user.')
    users.value.unshift(data)
    form.value = { name: '', email: '', password: '', role: 'student' }
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    saving.value = false
  }
}

const deleteUser = async (user) => {
  if (!window.confirm(`Delete ${user.name}?`)) return
  const response = await fetch(`/api/users/${user.id}`, {
    method: 'DELETE',
    headers: { Accept: 'application/json', Authorization: `Bearer ${token()}` },
  })
  if (response.ok) users.value = users.value.filter((item) => item.id !== user.id)
}

onMounted(loadUsers)
</script>

<template>
    <main class="min-h-screen bg-paper px-4 py-8 text-ink sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-7xl">

            <!-- LETTERHEAD -->
            <header class="border-b border-hairline pb-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center border border-forest bg-surface text-sm font-semibold text-forest"
                            >
                                UM
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-ink">
                                    Student Records
                                </p>

                                <p class="text-xs text-ink-soft">
                                    Administration
                                </p>
                            </div>
                        </div>

                        <h1
                            class="mt-6 text-4xl font-medium tracking-tight text-ink sm:text-5xl"
                        >
                            User management
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-ink-soft">
                            Create and manage accounts, roles, and access to the
                            student management system.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="router.push('/dashboard')"
                        class="inline-flex w-fit items-center border border-hairline bg-surface px-4 py-2.5 text-sm font-medium text-ink hover:border-forest hover:text-forest"
                    >
                        ← Dashboard
                    </button>
                </div>
            </header>


            <!-- ERROR -->
            <div
                v-if="error"
                class="mt-6 border border-sienna/30 bg-surface px-4 py-3 text-sm text-sienna"
            >
                {{ error }}
            </div>


            <!-- CREATE USER -->
            <section class="mt-8 border border-hairline bg-surface">

                <div class="border-b border-hairline px-5 py-5">
                    <h2
                        class=" text-2xl font-medium text-ink"
                    >
                        Create user
                    </h2>

                    <p class="mt-1 text-sm text-ink-soft">
                        Add a new account and assign its system role.
                    </p>
                </div>

                <form
                    @submit.prevent="createUser"
                    class="grid gap-4 p-5 md:grid-cols-2 lg:grid-cols-4"
                >

                    <!-- NAME -->
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Name
                        </label>

                        <input
                            v-model="form.name"
                            required
                            type="text"
                            placeholder="Full name"
                            class="w-full border border-hairline bg-surface px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                        />
                    </div>


                    <!-- EMAIL -->
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            required
                            type="email"
                            placeholder="Email address"
                            class="w-full border border-hairline bg-surface px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                        />
                    </div>


                    <!-- PASSWORD -->
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Password
                        </label>

                        <input
                            v-model="form.password"
                            required
                            type="password"
                            minlength="8"
                            placeholder="Minimum 8 characters"
                            class="w-full border border-hairline bg-surface px-3 py-2.5 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-forest focus:ring-1 focus:ring-forest"
                        />
                    </div>


                    <!-- ROLE + ADD -->
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-medium text-ink-soft"
                        >
                            Role
                        </label>

                        <div class="flex gap-2">
                            <select
                                v-model="form.role"
                                class="min-w-0 flex-1 border border-hairline bg-surface px-3 py-2.5 text-sm text-ink outline-none focus:border-forest focus:ring-1 focus:ring-forest"
                            >
                                <option
                                    v-for="role in roles"
                                    :key="role"
                                    :value="role"
                                >
                                    {{ role }}
                                </option>
                            </select>

                            <button
                                type="submit"
                                :disabled="saving"
                                class="border border-forest bg-forest px-4 py-2.5 text-sm font-medium text-white hover:bg-forest/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ saving ? 'Adding...' : 'Add' }}
                            </button>
                        </div>
                    </div>

                </form>
            </section>


            <!-- USER LEDGER -->
            <section class="mt-6 border border-hairline bg-surface">

                <!-- SECTION HEADER -->
                <div
                    class="flex flex-col gap-2 border-b border-hairline px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-2xl font-medium text-ink"
                        >
                            System users
                        </h2>

                        <p class="mt-1 text-sm text-ink-soft">
                            Accounts currently registered in the system.
                        </p>
                    </div>

                    <p
                        v-if="!loading"
                        class="text-xs text-ink-soft"
                    >
                        {{ users.length }} user{{ users.length === 1 ? '' : 's' }}
                    </p>
                </div>


                <!-- LOADING -->
                <div
                    v-if="loading"
                    class="px-5 py-12 text-center"
                >
                    <div
                        class="mx-auto mb-4 h-7 w-7 animate-spin rounded-full border-2 border-hairline border-t-forest"
                    ></div>

                    <p class="text-sm text-ink-soft">
                        Loading users...
                    </p>
                </div>


                <!-- TABLE -->
                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table class="w-full min-w-[700px] text-left">

                        <thead>
                            <tr class="border-b border-hairline bg-paper/60">

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Name
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Email
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Role
                                </th>

                                <th
                                    class="px-5 py-3 text-xs font-medium text-ink-soft"
                                >
                                    Action
                                </th>

                            </tr>
                        </thead>


                        <tbody>

                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="border-b border-hairline last:border-b-0 hover:bg-paper/40"
                            >

                                <!-- NAME -->
                                <td class="px-5 py-4">
                                    <p
                                        class=" text-lg font-medium text-ink"
                                    >
                                        {{ user.name }}
                                    </p>
                                </td>


                                <!-- EMAIL -->
                                <td
                                    class="px-5 py-4 text-sm text-ink-soft"
                                >
                                    {{ user.email }}
                                </td>


                                <!-- ROLE -->
                                <td class="px-5 py-4">

                                    <span
                                        class="inline-flex px-2.5 py-1 text-xs font-medium text-forest"
                                    >
                                        {{ user.role }}
                                    </span>

                                </td>


                                <!-- ACTION -->
                                <td class="px-5 py-4">

                                    <button
                                        type="button"
                                        @click="deleteUser(user)"
                                        class="text-sm font-medium text-sienna hover:underline"
                                    >
                                        Delete
                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>
                </div>

            </section>


            <!-- FOOTER NOTE -->
            <div class="border-t border-hairline py-5">
                <p class="text-xs leading-5 text-ink-soft">
                    User accounts control access to the student management
                    system. Assign roles carefully when creating new accounts.
                </p>
            </div>

        </div>
    </main>
</template>