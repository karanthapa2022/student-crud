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
  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-800 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Admin only</p>
          <h1 class="text-3xl font-bold text-slate-900">User management</h1>
        </div>
        <button @click="router.push('/dashboard')" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Dashboard</button>
      </header>

      <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ error }}</div>

      <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Create user</h2>
        <form @submit.prevent="createUser" class="mt-4 grid gap-3 md:grid-cols-4">
          <input v-model="form.name" required placeholder="Name" class="rounded-xl border border-slate-300 px-3 py-2.5" />
          <input v-model="form.email" required type="email" placeholder="Email" class="rounded-xl border border-slate-300 px-3 py-2.5" />
          <input v-model="form.password" required type="password" minlength="8" placeholder="Password" class="rounded-xl border border-slate-300 px-3 py-2.5" />
          <div class="flex gap-3">
            <select v-model="form.role" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2.5">
              <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
            </select>
            <button :disabled="saving" class="rounded-xl bg-indigo-600 px-4 py-2.5 font-semibold text-white disabled:opacity-60">Add</button>
          </div>
        </form>
      </section>

      <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div v-if="loading" class="p-6 text-slate-500">Loading users...</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
              <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="user in users" :key="user.id">
                <td class="px-5 py-4 font-medium text-slate-900">{{ user.name }}</td>
                <td class="px-5 py-4">{{ user.email }}</td>
                <td class="px-5 py-4"><span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ user.role }}</span></td>
                <td class="px-5 py-4"><button @click="deleteUser(user)" class="text-sm font-semibold text-red-600 hover:text-red-700">Delete</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</template>
