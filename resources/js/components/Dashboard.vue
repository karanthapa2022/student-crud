<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(true)
const error = ref('')
const dashboard = ref({
  role: 'admin',
  user: null,
  stats: {},
  profile: null,
})

const roleLabel = {
  admin: 'Admin',
  teacher: 'Teacher',
  student: 'Student',
  parent: 'Parent',
}

const loadDashboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const token = localStorage.getItem('token') || localStorage.getItem('parent_token')

    if (!token) {
      router.push('/login')
      return
    }

    const response = await fetch('http://127.0.0.1:8000/api/dashboard', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    if (response.status === 401) {
      localStorage.clear()
      router.push('/login')
      return
    }

    if (!response.ok) {
      throw new Error('Failed to load dashboard.')
    }

    dashboard.value = await response.json()
  } catch (err) {
    error.value = err.message || 'Unable to load dashboard.'
  } finally {
    loading.value = false
  }
}

const logout = () => {
  localStorage.clear()
  router.push('/login')
}

const goToStudents = () => router.push('/students')
const goToTeachers = () => router.push('/teachers')
const goToUsers = () => router.push('/users')
const goToSubjects = () => router.push(dashboard.value.role === 'teacher' ? '/teacher/subjects' : '/subjects')
const goToMarksheets = () => router.push('/marksheets')
const goToProfile = () => router.push('/profile')

onMounted(loadDashboard)
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-800">
    <header class="border-b border-slate-200 bg-white shadow-sm">
      <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Role-Based Access</p>
          <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">
            {{ roleLabel[dashboard.role] || 'Dashboard' }} Dashboard
          </h1>
        </div>

        <div class="flex items-center gap-3">
          <button @click="goToProfile" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
            Profile
          </button>
          <button @click="logout" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
            Logout
          </button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:py-8">
      <div v-if="loading" class="py-10 text-center text-slate-500">Loading dashboard...</div>
      <div v-else-if="error" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </div>

      <div v-else class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-indigo-600 to-blue-500 p-6 text-white shadow-lg sm:p-8">
          <p class="text-sm uppercase tracking-[0.2em] text-indigo-100">Welcome back</p>
          <h2 class="mt-3 text-2xl font-bold sm:text-3xl">{{ dashboard.user?.name || 'User' }}</h2>
          <p class="mt-2 text-sm text-indigo-100">{{ dashboard.user?.email }}</p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div v-if="dashboard.role === 'admin' || dashboard.role === 'teacher' || dashboard.role === 'student'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Subjects</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.subjects ?? 0 }}</p>
          </div>

          <div v-if="dashboard.role === 'admin'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Students</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.totalStudents ?? 0 }}</p>
          </div>

          <div v-if="dashboard.role === 'admin'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Teachers</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.totalTeachers ?? 0 }}</p>
          </div>

          <div v-if="dashboard.role === 'admin'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Parents</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.totalParents ?? 0 }}</p>
          </div>

          <div v-if="dashboard.role === 'teacher'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Students</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.students ?? 0 }}</p>
          </div>

          <div v-if="dashboard.role === 'student'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Status</p>
            <p class="mt-3 text-3xl font-bold uppercase text-slate-900">{{ dashboard.stats.status || 'Active' }}</p>
          </div>

          <div v-if="dashboard.role === 'student'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Marksheets</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.marksheets ?? 0 }}</p>
          </div>

          <div v-if="dashboard.role === 'parent'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Children</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.children ?? 0 }}</p>
          </div>

          <div v-if="dashboard.role === 'parent'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Marksheets</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ dashboard.stats.marksheets ?? 0 }}</p>
          </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
          <h3 class="text-xl font-semibold text-slate-900">Quick actions</h3>
          <div class="mt-4 flex flex-wrap gap-3">
            <button v-if="dashboard.role === 'admin'" @click="goToStudents" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
              Manage Students
            </button>
            <button v-if="dashboard.role === 'admin'" @click="goToTeachers" class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-900">
              Manage Teachers
            </button>
            <button v-if="dashboard.role === 'admin'" @click="goToUsers" class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700">
              Manage Users
            </button>
            <button v-if="dashboard.role === 'admin' || dashboard.role === 'teacher'" @click="goToSubjects" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
              Subjects
            </button>
            <button v-if="dashboard.role === 'admin' || dashboard.role === 'teacher'" @click="goToMarksheets" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
              {{ dashboard.role === 'teacher' ? 'View / Edit Marksheets' : 'Manage Marksheets' }}
            </button>
            <button v-if="dashboard.role === 'student'" @click="goToMarksheets" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
              View My Marksheets
            </button>
            <button v-if="dashboard.role === 'parent'" @click="router.push('/parents/marksheet/' + (dashboard.children?.[0]?.id || ''))" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
              View Child Marksheets
            </button>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>
