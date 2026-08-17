<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students - Student Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100">

    <!-- ================================================= -->
    <!-- HEADER -->
    <!-- ================================================= -->

    <header class="bg-white border-b border-gray-200">

        <div class="max-w-7xl mx-auto px-6 py-4">

            <div class="flex items-center justify-between">

                <!-- Brand -->

                <div class="flex items-center gap-3">

                    <div>

                        <h1 class="text-xl font-bold text-gray-800">
                            Student Management
                        </h1>

                        <p class="text-xs text-gray-500">
                            Manage your students easily
                        </p>

                    </div>

                </div>


                <!-- Header Actions -->

                <div class="flex items-center gap-3">

                    <!-- Add Student -->

                    <a
                        href="{{ route('students.create') }}"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition shadow-sm">
                        <span>+</span>
                        Add Student
                    </a>


                    <!-- Logout -->

                    <form
                        action="{{ route('logout') }}"
                        method="POST">

                        @csrf

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 bg-gray-100 hover:bg-red-50 text-gray-600 hover:text-red-600 px-4 py-2.5 rounded-lg font-medium transition">
                            Logout
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </header>


    <!-- ================================================= -->
    <!-- MAIN CONTENT -->
    <!-- ================================================= -->

    <main class="max-w-7xl mx-auto px-6 py-8">


        <!-- ================================================= -->
        <!-- PAGE INTRO -->
        <!-- ================================================= -->

        <div class="mb-8">

            <h2 class="text-2xl font-bold text-gray-800">
                Students
            </h2>

            <p class="text-gray-500 mt-1">
                View, edit and manage all registered students.
            </p>

        </div>


        <!-- ================================================= -->
        <!-- SUCCESS MESSAGE -->
        <!-- ================================================= -->

        @if(session('success'))

        <div
            class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl">

            <div
                class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                ✓
            </div>

            <p class="font-medium">
                {{ session('success') }}
            </p>

        </div>

        @endif


        <!-- ================================================= -->
        <!-- ERROR MESSAGE -->
        <!-- ================================================= -->

        @if(session('error'))

        <div
            class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl">

            <div
                class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                !
            </div>

            <p class="font-medium">
                {{ session('error') }}
            </p>

        </div>

        @endif


        @if($students->count() > 0)


        <!-- ================================================= -->
        <!-- STAT CARD -->
        <!-- ================================================= -->

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

            <!-- Total Students -->

            <div class="bg-white rounded-2xl border border-gray-500 p-5 shadow-sm ">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-gray-500">
                            Total Students
                        </p>

                        <p class="text-3xl font-bold text-gray-800 mt-1">
                            {{ $students->count() }}
                        </p>

                    </div>



                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- BULK EDIT FORM -->
        <!-- ================================================= -->

        <form
            action="{{ route('students.bulkEdit') }}"
            method="POST"
            id="bulkEditForm">

            @csrf


            <!-- ================================================= -->
            <!-- ACTION BAR -->
            <!-- ================================================= -->

            <div
                class="bg-white border border-gray-200 rounded-2xl p-5 mb-5 shadow-sm">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div>

                        <h3 class="font-semibold text-gray-800">
                            Student List
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Select students to perform bulk actions.
                        </p>

                    </div>


                    <!-- Bulk Actions -->

                    <div class="flex items-center gap-3">

                        <span
                            id="selectedCount"
                            class="text-sm font-medium text-gray-500 hidden">
                            0 selected
                        </span>


                        <!-- Edit Selected -->

                        <button
                            type="submit"
                            id="editSelectedBtn"
                            class="hidden items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition">
                            Edit Selected
                        </button>


                        <!-- Delete Selected -->

                        <button
                            type="button"
                            id="deleteSelectedBtn"
                            class="hidden items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-medium transition">
                            Delete Selected
                        </button>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- TABLE -->
            <!-- ================================================= -->

            <div
                class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <!-- Table Header -->

                        <thead class="bg-gray-50 border-b border-gray-200">

                            <tr>

                                <th class="px-6 py-4">

                                    <input
                                        type="checkbox"
                                        id="selectAll"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                                </th>

                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>

                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Student
                                </th>

                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>

                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Phone
                                </th>

                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <!-- Table Body -->

                        <tbody class="divide-y divide-gray-100">

                            @foreach($students as $student)

                            <tr
                                class="student-row hover:bg-gray-50 transition duration-150">

                                <!-- Checkbox -->

                                <td class="px-6 py-5">

                                    <input
                                        type="checkbox"
                                        name="students[]"
                                        value="{{ $student->id }}"
                                        class="student-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                                </td>


                                <!-- ID -->

                                <td class="px-6 py-5">

                                    <span
                                        class="text-sm font-medium text-gray-500">
                                        #{{ $student->id }}
                                    </span>

                                </td>


                                <!-- Student -->

                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center font-semibold">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>

                                        <div>

                                            <p class="font-semibold text-gray-800">
                                                {{ $student->name }}
                                            </p>

                                            <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full
    {{ $student->status === 'active'
        ? 'text-green-600 bg-green-50'
        : 'text-red-600 bg-red-50' }}">

                                                {{ ucfirst($student->status) }}

                                            </span>

                                        </div>

                                    </div>

                                </td>


                                <!-- Email -->

                                <td class="px-6 py-5">

                                    <span class="text-sm text-gray-600">
                                        {{ $student->email }}
                                    </span>

                                </td>


                                <!-- Phone -->

                                <td class="px-6 py-5">

                                    <span class="text-sm text-gray-600">
                                        {{ $student->phone ?? 'N/A' }}
                                    </span>

                                </td>


                                <!-- Actions -->

                                <td class="px-6 py-5">

                                    <div class="flex justify-end items-center gap-3">

                                        <!-- Edit -->

                                        <a
                                            href="{{ route('students.edit', $student->id) }}"
                                            class="px-3 py-1.5 text-sm font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            Edit
                                        </a>


                                        <!-- Delete -->

                                        <button
                                            type="button"
                                            onclick="deleteStudent({{ $student->id }})"
                                            class="px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition">
                                            Delete
                                        </button>

                                    </div>

                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </form>


        @else


        <!-- ================================================= -->
        <!-- EMPTY STATE -->
        <!-- ================================================= -->

        <div
            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-14 text-center">

            <h2 class="text-2xl font-bold text-gray-800">
                No students yet
            </h2>

            <p class="text-gray-500 mt-2 max-w-md mx-auto">
                Start building your student list by adding your first student.
            </p>

            <a
                href="{{ route('students.create') }}"
                class="inline-flex items-center gap-2 mt-6 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition">
                + Add First Student
            </a>

        </div>

        @endif

    </main>


    <!-- ================================================= -->
    <!-- INDIVIDUAL DELETE FORM -->
    <!-- ================================================= -->

    <form
        id="individualDeleteForm"
        method="POST"
        class="hidden">

        @csrf

        @method('DELETE')

    </form>


    <!-- ================================================= -->
    <!-- BULK DELETE FORM -->
    <!-- ================================================= -->

    <form
        id="bulkDeleteForm"
        action="{{ route('students.bulkDelete') }}"
        method="POST"
        class="hidden">

        @csrf

        @method('DELETE')

    </form>


    <!-- ================================================= -->
    <!-- JAVASCRIPT -->
    <!-- ================================================= -->

    <script>
        const checkboxes =
            document.querySelectorAll('.student-checkbox');

        const selectAll =
            document.getElementById('selectAll');

        const selectedCount =
            document.getElementById('selectedCount');

        const editSelectedBtn =
            document.getElementById('editSelectedBtn');

        const deleteSelectedBtn =
            document.getElementById('deleteSelectedBtn');

        const bulkDeleteForm =
            document.getElementById('bulkDeleteForm');


        // ==========================================
        // UPDATE SELECTION
        // ==========================================

        function updateSelection() {

            const selected =
                document.querySelectorAll(
                    '.student-checkbox:checked'
                );

            const count = selected.length;


            if (count === 0) {

                selectedCount.classList.add('hidden');

                editSelectedBtn.classList.add('hidden');

                deleteSelectedBtn.classList.add('hidden');

            } else {

                selectedCount.classList.remove('hidden');

                editSelectedBtn.classList.remove('hidden');

                deleteSelectedBtn.classList.remove('hidden');

                selectedCount.textContent =
                    `${count} selected`;

            }


            // Highlight selected rows

            checkboxes.forEach((checkbox) => {

                const row =
                    checkbox.closest('.student-row');

                if (checkbox.checked) {

                    row.classList.add('bg-blue-50');

                } else {

                    row.classList.remove('bg-blue-50');

                }

            });


            // Select All

            selectAll.checked =
                count === checkboxes.length &&
                count > 0;

        }


        // ==========================================
        // INDIVIDUAL CHECKBOX
        // ==========================================

        checkboxes.forEach((checkbox) => {

            checkbox.addEventListener(
                'change',
                updateSelection
            );

        });


        // ==========================================
        // SELECT ALL
        // ==========================================

        selectAll.addEventListener(
            'change',
            function() {

                checkboxes.forEach((checkbox) => {

                    checkbox.checked =
                        this.checked;

                });

                updateSelection();

            }
        );


        // ==========================================
        // DELETE SELECTED
        // ==========================================

        deleteSelectedBtn.addEventListener(
            'click',
            function() {

                const selected =
                    document.querySelectorAll(
                        '.student-checkbox:checked'
                    );

                const count =
                    selected.length;


                if (count === 0) {
                    return;
                }


                const confirmed =
                    confirm(
                        `Are you sure you want to delete ${count} selected student(s)? This action cannot be undone.`
                    );


                if (!confirmed) {
                    return;
                }


                selected.forEach((checkbox) => {

                    const input =
                        document.createElement('input');

                    input.type = 'hidden';

                    input.name = 'students[]';

                    input.value = checkbox.value;

                    bulkDeleteForm.appendChild(input);

                });


                bulkDeleteForm.submit();

            }
        );


        // ==========================================
        // INDIVIDUAL DELETE
        // ==========================================

        function deleteStudent(id) {

            const confirmed =
                confirm(
                    'Are you sure you want to delete this student?'
                );


            if (!confirmed) {
                return;
            }


            const form =
                document.getElementById(
                    'individualDeleteForm'
                );


            form.action =
                `/students/${id}`;

            form.submit();

        }


        // ==========================================
        // INITIAL STATE
        // ==========================================

        updateSelection();
    </script>

</body>

</html>