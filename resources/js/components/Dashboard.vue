<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { API_BASE_URL } from '../services/apiConfig'
import { getUnreadNotificationCount } from '../services/notifications/notificationApi'

const router = useRouter()
const loading = ref(true)
const error = ref('')
const unreadNotificationCount = ref(0)
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

const roleInitial = computed(() =>
  (roleLabel[dashboard.value.role] || 'D').charAt(0)
)

const loadUnreadNotificationCount = async () => {
  try {
    const response = await getUnreadNotificationCount()

    unreadNotificationCount.value = response.data.count
  } catch (err) {
    console.error(
      'Failed to load unread notification count:',
      err
    )
  }
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

    const response = await fetch(`${API_BASE_URL}/dashboard`, {
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
    const responseText = await response.text()

    console.error('Dashboard API error:', {
        status: response.status,
        statusText: response.statusText,
        response: responseText,
    })

    throw new Error(
        `Dashboard request failed (${response.status}).`
    )
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
const goToParents = () => router.push('/parents')
const goToAddresses = () => router.push('/addresses')
const goToSubjects = () => router.push(dashboard.value.role === 'teacher' ? '/teacher/subjects' : '/subjects')
const goToMarksheets = () => router.push('/marksheets')
const goToComplaints= () => router.push('student/complaint')
const goToProfile = () => router.push('/profile')
const goToNotifications= () => router.push('/notifications')

onMounted(()=>{
  loadDashboard()
})
</script>


<template>
  <div class="min-h-screen bg-[#F4F6F1] text-[#1C2B24]">


    <!-- ===================================================== -->
    <!-- MAIN CONTENT -->
    <!-- ===================================================== -->

    <main class="mx-auto max-w-7xl px-5 py-7 sm:px-6 sm:py-9">

      <!-- LOADING -->

      <div
        v-if="loading"
        class="flex min-h-[60vh] items-center justify-center"
      >
        <div class="text-center">

          <div
            class="mx-auto h-8 w-8 animate-spin border-2 border-[#D8DDD3] border-t-[#2F6F4E]"
          ></div>

          <p class="mt-4 text-sm text-[#6B776F]">
            Loading your dashboard...
          </p>

        </div>
      </div>


      <!-- ERROR -->

      <div
        v-else-if="error"
        class="border border-[#B5563C]/30 bg-white p-5"
      >
        <div class="flex items-start gap-3">

          <div
            class="flex h-8 w-8 shrink-0 items-center justify-center bg-[#B5563C]/10 font-semibold text-[#B5563C]"
          >
            !
          </div>

          <div>
            <p class="text-sm font-semibold text-[#8A3E2A]">
              Something went wrong
            </p>

            <p class="mt-1 text-sm text-[#8A3E2A]">
              {{ error }}
            </p>
          </div>

        </div>
      </div>


      <!-- ===================================================== -->
      <!-- DASHBOARD -->
      <!-- ===================================================== -->

      <div
        v-else
        class="space-y-7"
      >

        <!-- ================================================= -->
        <!-- WELCOME PANEL -->
        <!-- ================================================= -->

        <section
          class="relative overflow-hidden border border-[#D8DDD3] bg-white text-black"
        >

          <div class="absolute right-0 top-0 h-full w-1/3 bg-white/5"></div>

          <div
            class="relative flex flex-col justify-between gap-7 px-6 py-8 sm:px-8 sm:py-10 lg:flex-row lg:items-end"
          >

            <div>

              <div class="mb-4 flex items-center gap-2">



                <span class="mt-5 text-sm dark:text-white font-semibold">
                  Dashboard
                </span>

              </div>


              <h1
                class="text-3xl font-semibold dark:text-white sm:text-4xl"
              >
                Welcome back, {{ dashboard.user?.name || 'User' }}
              </h1>


              <p
                class="mt-3 max-w-xl text-sm dark:text-white leading-6 text-black/75"
              >
                Manage your school activities, records and academic
                information from one place.
              </p>

            </div>


            <!-- USER INFO -->

            <div
              class="border border-white/15 bg-black/10 px-5 py-4 lg:min-w-[260px]"
            >

              <p class="text-xs dark:text-white text-black/55">
                Signed in as
              </p>

              <p class="mt-1 text-sm dark:text-white font-semibold">
                {{ dashboard.user?.name || 'Unnamed user' }}
              </p>

              <p
                v-if="dashboard.user?.email"
                class="mt-1 break-all text-xs dark:text-white text-black/65"
              >
                {{ dashboard.user.email }}
              </p>

            </div>

          </div>

        </section>


        <!-- ================================================= -->
        <!-- OVERVIEW -->
        <!-- ================================================= -->

        <section>

          <div class="mb-4">
            <h2 class="text-lg font-semibold">
              Overview
            </h2>

            <p class="mt-1 text-sm text-[#6B776F]">
              Your current records.
            </p>
          </div>


          <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
          >

            <!-- SUBJECTS -->

            <button
              v-if="dashboard.role === 'admin'"
              @click="goToParents"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >
              <span class="text-lg text-[#9AA59E]">→</span>
              <h3 class="mt-5 text-sm font-semibold">Manage parents</h3>
              <p class="mt-1 text-xs leading-5 text-[#6B776F]">Assign parent addresses and children.</p>
            </button>

            <button
              v-if="dashboard.role === 'admin'"
              @click="goToAddresses"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >
              <span class="text-lg text-[#9AA59E]">→</span>
              <h3 class="mt-5 text-sm font-semibold">Manage addresses</h3>
              <p class="mt-1 text-xs leading-5 text-[#6B776F]">Create and maintain family addresses.</p>
            </button>

            <!-- SUBJECTS -->

            <div
              v-if="
                dashboard.role === 'admin' ||
                dashboard.role === 'teacher' ||
                dashboard.role === 'student'
              "
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Subjects
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.subjects ?? 0 }}
                  </p>
                </div>



              </div>

              <div class="mt-5 h-1 bg-[#EFF1EA]">
                <div class="h-full w-2/3 bg-[#2F6F4E]"></div>
              </div>

            </div>


            <!-- ADMIN STUDENTS -->

            <div
              v-if="dashboard.role === 'admin'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Students
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.totalStudents ?? 0 }}
                  </p>
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Total registered students
              </p>

            </div>


            <!-- ADMIN TEACHERS -->

            <div
              v-if="dashboard.role === 'admin'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Teachers
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.totalTeachers ?? 0 }}
                  </p>
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Teaching staff
              </p>

            </div>


            <!-- ADMIN PARENTS -->

            <div
              v-if="dashboard.role === 'admin'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Parents
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.totalParents ?? 0 }}
                  </p>
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Registered parents
              </p>

            </div>


            <!-- TEACHER STUDENTS -->

            <div
              v-if="dashboard.role === 'teacher'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Students
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.students ?? 0 }}
                  </p>
                </div>

                <div
                  class="flex h-9 w-9 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
                >
                  ST
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Assigned students
              </p>

            </div>


            <!-- STUDENT STATUS -->

            <div
              v-if="dashboard.role === 'student'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Status
                  </p>

                  <p
                    class="mt-3 text-2xl font-semibold capitalize text-[#2F6F4E]"
                  >
                    {{ dashboard.stats.status || 'Active' }}
                  </p>
                </div>

                <div
                  class="flex h-9 w-9 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
                >
                  ✓
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Current student status
              </p>

            </div>


            <!-- STUDENT MARKSHEETS -->

            <div
              v-if="dashboard.role === 'student'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Marksheets
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.marksheets ?? 0 }}
                  </p>
                </div>

                <div
                  class="flex h-9 w-9 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
                >
                  M
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Academic records
              </p>

            </div>


            <!-- PARENT CHILDREN -->

            <div
              v-if="dashboard.role === 'parent'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Children
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.children ?? 0 }}
                  </p>
                </div>

                <div
                  class="flex h-9 w-9 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
                >
                  C
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Linked children
              </p>

            </div>


            <!-- PARENT MARKSHEETS -->

            <div
              v-if="dashboard.role === 'parent'"
              class="border border-[#D8DDD3] bg-white p-5"
            >

              <div class="flex items-start justify-between">

                <div>
                  <p class="text-sm text-[#6B776F]">
                    Marksheets
                  </p>

                  <p class="mt-3 text-3xl font-semibold">
                    {{ dashboard.stats.marksheets ?? 0 }}
                  </p>
                </div>

                <div
                  class="flex h-9 w-9 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
                >
                  M
                </div>

              </div>

              <p class="mt-4 text-xs text-[#6B776F]">
                Academic records
              </p>

            </div>

          </div>
        </section>


        <!-- ================================================= -->
        <!-- QUICK ACCESS -->
        <!-- ================================================= -->

        <section>

          <div class="mb-4">
            <h2 class="text-lg font-semibold">
              Quick access
            </h2>

            <p class="mt-1 text-sm text-[#6B776F]">
              Go directly to the areas you use most.
            </p>
          </div>


          <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
          >

            <!-- STUDENTS -->

            <button
              v-if="dashboard.role === 'admin'"
              @click="goToStudents"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >

              <div class="flex items-start justify-between">

                <span
                  class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
                >
                  →
                </span>

              </div>

              <h3 class="mt-5 text-sm font-semibold">
                Manage students
              </h3>

              <p class="mt-1 text-xs leading-5 text-[#6B776F]">
                Add, edit and manage student records.
              </p>

            </button>


            <!-- TEACHERS -->

            <button
              v-if="dashboard.role === 'admin'"
              @click="goToTeachers"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >

              <div class="flex items-start justify-between">


                <span
                  class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
                >
                  →
                </span>

              </div>

              <h3 class="mt-5 text-sm font-semibold">
                Manage teachers
              </h3>

              <p class="mt-1 text-xs leading-5 text-[#6B776F]">
                Manage teachers and teaching staff.
              </p>

            </button>


            <!-- USERS -->

            <button
              v-if="dashboard.role === 'admin'"
              @click="goToUsers"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >

              <div class="flex items-start justify-between">


                <span
                  class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
                >
                  →
                </span>

              </div>

              <h3 class="mt-5 text-sm font-semibold">
                Manage users
              </h3>

              <p class="mt-1 text-xs leading-5 text-[#6B776F]">
                Manage accounts and access.
              </p>

            </button>


            <!-- SUBJECTS -->

            <button
              v-if="
                dashboard.role === 'admin' ||
                dashboard.role === 'teacher'
              "
              @click="goToSubjects"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >

              <div class="flex items-start justify-between">

                <span
                  class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
                >
                  →
                </span>

              </div>

              <h3 class="mt-5 text-sm font-semibold">
                Subjects
              </h3>

              <p class="mt-1 text-xs leading-5 text-[#6B776F]">
                View and manage academic subjects.
              </p>

            </button>


            <!-- MARKSHEETS -->

            <button
              v-if="
                dashboard.role === 'admin' ||
                dashboard.role === 'teacher'
              "
              @click="goToMarksheets"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >

              <div class="flex items-start justify-between">

                <span
                  class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
                >
                  →
                </span>

              </div>

              <h3 class="mt-5 text-sm font-semibold">
                {{
                  dashboard.role === 'teacher'
                    ? 'View and edit marksheets'
                    : 'Manage marksheets'
                }}
              </h3>

              <p class="mt-1 text-xs leading-5 text-[#6B776F]">
                Access academic performance records.
              </p>

            </button>


            <!-- STUDENT MARKSHEETS -->

            <button
              v-if="dashboard.role === 'student'"
              @click="goToMarksheets"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >

              <div class="flex items-start justify-between">

                <div
                  class="flex h-11 w-11 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
                >
                  M
                </div>

                <span
                  class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
                >
                  →
                </span>

              </div>

              <h3 class="mt-5 text-sm font-semibold">
                View my marksheets
              </h3>

              <p class="mt-1 text-xs leading-5 text-[#6B776F]">
                Review your academic performance.
              </p>

            </button>
            <!-- STUDENT COMPLAINT / FEEDBACK -->

<button
    v-if="dashboard.role === 'student'"
    @click="goToComplaints"
    class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
>
    <div class="flex items-start justify-between">

        <div
            class="flex h-11 w-11 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
        >
            C
        </div>

        <span
            class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
        >
            →
        </span>

    </div>

    <h3 class="mt-5 text-sm font-semibold">
        Submit complaint / feedback
    </h3>

    <p class="mt-1 text-xs leading-5 text-[#6B776F]">
        Send a complaint or feedback to your teacher or admin.
    </p>

</button>


            <!-- PARENT MARKSHEETS -->

            <button
              v-if="dashboard.role === 'parent' && dashboard.children?.length"
              @click="router.push('/parents/marksheet/' + (dashboard.children?.[0]?.id || ''))"
              class="group border border-[#D8DDD3] bg-white p-5 text-left transition hover:border-[#2F6F4E]/40 hover:bg-[#EFF1EA]"
            >

              <div class="flex items-start justify-between">

                <div
                  class="flex h-11 w-11 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
                >
                  M
                </div>

                <span
                  class="text-lg text-[#9AA59E] transition group-hover:translate-x-1 group-hover:text-[#2F6F4E]"
                >
                  →
                </span>

              </div>

              <h3 class="mt-5 text-sm font-semibold">
                View child's marksheets
              </h3>

              <p class="mt-1 text-xs leading-5 text-[#6B776F]">
                Review your child's academic records.
              </p>

            </button>

          </div>
        </section>


        <!-- ================================================= -->
        <!-- BOTTOM PROFILE STRIP -->
        <!-- ================================================= -->

        <section
          class="flex flex-col gap-4 border border-[#D8DDD3] bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >

          <div class="flex items-center gap-3">

            <div
              class="flex h-9 w-9 items-center justify-center bg-[#EFF1EA] text-sm font-semibold text-[#2F6F4E]"
            >
              {{ roleInitial }}
            </div>

            <div>
              <p class="text-sm font-medium">
                {{ dashboard.user?.name || 'Unnamed user' }}
              </p>

              <p
                v-if="dashboard.user?.email"
                class="text-xs text-[#6B776F]"
              >
                {{ dashboard.user.email }}
              </p>
            </div>

          </div>


          <button
            @click="goToProfile"
            class="border border-[#D8DDD3] px-4 py-2 text-sm font-medium transition hover:bg-[#F4F6F1]"
          >
            View profile
          </button>

        </section>

      </div>
    </main>
  </div>
</template>
