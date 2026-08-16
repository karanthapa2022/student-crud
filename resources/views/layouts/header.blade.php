<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">

    <div class="max-w-6xl mx-auto px-6 py-4">

        <div class="flex items-center justify-between">

            <!-- Logo -->
            <div>

                <h1 class="text-xl font-bold text-gray-800">
                    Student CRUD
                </h1>

                <p class="text-sm text-gray-500">
                    Admin Panel
                </p>

            </div>


            <!-- Navigation -->
            <nav class="flex items-center gap-6">

                <a
                    href="{{ route('students.index') }}"
                    class="text-gray-600 hover:text-blue-600 font-medium transition"
                >
                    Students
                </a>

                <a
                    href="{{ route('students.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition"
                >
                    + Add Student
                </a>

            </nav>

        </div>

    </div>

</header>