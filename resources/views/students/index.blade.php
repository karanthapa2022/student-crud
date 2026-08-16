<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <div class="max-w-6xl mx-auto px-6 py-10">

        <!-- ================= HEADER ================= -->

        <div class="flex items-center justify-between mb-8">

            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Student Management
                </h1>

                <p class="text-gray-500 mt-1">
                    Manage your students easily
                </p>
            </div>

            <a
                href="{{ route('students.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition"
            >
                + Add Student
            </a>

        </div>


        <!-- ================= MESSAGES ================= -->

        @if(session('success'))

            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-lg">
                {{ session('success') }}
            </div>

        @endif


        @if(session('error'))

            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-lg">
                {{ session('error') }}
            </div>

        @endif


        @if($students->count() > 0)


            <!-- ================================================= -->
            <!-- BULK EDIT FORM -->
            <!-- ================================================= -->

            <form
                action="{{ route('students.bulkEdit') }}"
                method="POST"
                id="bulkEditForm"
            >

                @csrf


                

                <!-- ================= BULK ACTION BAR ================= -->

<div class="bg-white rounded-xl shadow-sm p-5 mb-5">

    <div class="flex items-center justify-between">

        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Select Students
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Select students to edit or delete
            </p>
        </div>


        <div class="flex items-center gap-3">

            <!-- Selected Count -->

            <span
                id="selectedCount"
                class="text-sm font-medium text-gray-500 hidden"
            >
                0 selected
            </span>


            <!-- Edit Selected -->

            <button
                type="submit"
                id="editSelectedBtn"
                class="hidden bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition"
            >
                 Edit Selected
            </button>


            <!-- Delete Selected -->

            <button
                type="button"
                id="deleteSelectedBtn"
                class="hidden bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-medium transition"
            >
                 Delete Selected
            </button>

        </div>

    </div>

</div>


                <!-- ================= STUDENT TABLE ================= -->

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">

                    <div class="overflow-x-auto">

                        <table class="w-full text-left">

                            <!-- Table Header -->

                            <thead class="bg-gray-50 border-b border-gray-200">

                                <tr>

                                    <!-- Select All -->

                                    <th class="px-6 py-4">

                                        <input
                                            type="checkbox"
                                            id="selectAll"
                                            class="w-4 h-4 rounded border-gray-300"
                                        >

                                    </th>


                                    <th class="px-6 py-4 text-sm font-semibold text-gray-600">
                                        ID
                                    </th>


                                    <th class="px-6 py-4 text-sm font-semibold text-gray-600">
                                        Name
                                    </th>


                                    <th class="px-6 py-4 text-sm font-semibold text-gray-600">
                                        Email
                                    </th>


                                    <th class="px-6 py-4 text-sm font-semibold text-gray-600">
                                        Phone
                                    </th>


                                    <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-right">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <!-- Table Body -->

                            <tbody class="divide-y divide-gray-100">

                                @foreach($students as $student)

                                    <tr
                                        class="student-row hover:bg-gray-50 transition"
                                    >

                                        <!-- Checkbox -->

                                        <td class="px-6 py-4">

                                            <input
                                                type="checkbox"
                                                name="students[]"
                                                value="{{ $student->id }}"
                                                class="student-checkbox w-4 h-4 rounded border-gray-300"
                                            >

                                        </td>


                                        <!-- ID -->

                                        <td class="px-6 py-4 text-gray-600">
                                            {{ $student->id }}
                                        </td>


                                        <!-- Name -->

                                        <td class="px-6 py-4 font-medium text-gray-800">
                                            {{ $student->name }}
                                        </td>


                                        <!-- Email -->

                                        <td class="px-6 py-4 text-gray-600">
                                            {{ $student->email }}
                                        </td>


                                        <!-- Phone -->

                                        <td class="px-6 py-4 text-gray-600">
                                            {{ $student->phone ?? 'N/A' }}
                                        </td>


                                        <!-- Actions -->

                                        <td class="px-6 py-4">

                                            <div class="flex justify-end gap-3">

                                                <!-- Individual Edit -->

                                                <a
                                                    href="{{ route('students.edit', $student->id) }}"
                                                    class="text-blue-600 hover:text-blue-800 font-medium transition"
                                                >
                                                    Edit
                                                </a>


                                                <!-- Individual Delete -->

                                                <button
                                                    type="button"
                                                    onclick="deleteStudent({{ $student->id }})"
                                                    class="text-red-600 hover:text-red-800 font-medium transition"
                                                >
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


            <!-- ================= EMPTY STATE ================= -->

            <div class="bg-white rounded-xl shadow-sm p-12 text-center">

                <div class="text-5xl mb-4">
                    👨‍🎓
                </div>

                <h2 class="text-xl font-semibold text-gray-700">
                    No students found
                </h2>

                <p class="text-gray-500 mt-2">
                    Start by adding your first student.
                </p>

                <a
                    href="{{ route('students.create') }}"
                    class="inline-block mt-5 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition"
                >
                    + Add Student
                </a>

            </div>

        @endif

    </div>


    <!-- ================================================= -->
    <!-- INDIVIDUAL DELETE FORM -->
    <!-- ================================================= -->

    <form
        id="individualDeleteForm"
        method="POST"
        class="hidden"
    >

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
        class="hidden"
    >

        @csrf

        @method('DELETE')

    </form>


    
    <!-- JAVASCRIPT -->
  

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


        /*
        |--------------------------------------------------------------------------
        | Update Selection
        |--------------------------------------------------------------------------
        */

        function updateSelection() {

    const selected =
        document.querySelectorAll(
            '.student-checkbox:checked'
        );

    const count = selected.length;


    // =========================
    // Nothing selected
    // =========================

    if (count === 0) {

        selectedCount.classList.add('hidden');

        editSelectedBtn.classList.add('hidden');

        deleteSelectedBtn.classList.add('hidden');

    }


    // =========================
    // Students selected
    // =========================

    else {

        selectedCount.classList.remove('hidden');

        editSelectedBtn.classList.remove('hidden');

        deleteSelectedBtn.classList.remove('hidden');


        selectedCount.textContent =
            `${count} selected`;

    }


    // =========================
    // Highlight selected rows
    // =========================

    checkboxes.forEach((checkbox) => {

        const row =
            checkbox.closest('.student-row');


        if (checkbox.checked) {

            row.classList.add('bg-blue-50');

        } else {

            row.classList.remove('bg-blue-50');

        }

    });


    // =========================
    // Select All
    // =========================

    selectAll.checked =
        count === checkboxes.length &&
        count > 0;

}


        /*
        |--------------------------------------------------------------------------
        | Individual Checkbox
        |--------------------------------------------------------------------------
        */

        checkboxes.forEach((checkbox) => {

            checkbox.addEventListener(
                'change',
                updateSelection
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Select All
        |--------------------------------------------------------------------------
        */

        selectAll.addEventListener(
            'change',
            function () {

                checkboxes.forEach((checkbox) => {

                    checkbox.checked =
                        this.checked;

                });

                updateSelection();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Delete Selected
        |--------------------------------------------------------------------------
        */

        deleteSelectedBtn.addEventListener(
            'click',
            function () {

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


                // Add selected IDs

                selected.forEach((checkbox) => {

                    const input =
                        document.createElement('input');

                    input.type = 'hidden';

                    input.name = 'students[]';

                    input.value = checkbox.value;

                    bulkDeleteForm.appendChild(input);

                });


                // Submit

                bulkDeleteForm.submit();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Individual Delete
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Initial State
        |--------------------------------------------------------------------------
        */

        updateSelection();

    </script>

</body>

</html>