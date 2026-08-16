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

        <!-- Header -->
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


        <!-- Student Table -->
        @if($students->count() > 0)

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead class="bg-gray-50 border-b border-gray-200">

                            <tr>
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


                        <tbody class="divide-y divide-gray-100">

                            @foreach($students as $student)

                                <tr class="hover:bg-gray-50 transition">

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $student->id }}
                                    </td>

                                    <td class="px-6 py-4 font-medium text-gray-800">
                                        {{ $student->name }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $student->email }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $student->phone ?? 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="flex justify-end gap-3">

                                            <!-- Edit -->
                                            <a
                                                href="{{ route('students.edit', $student->id) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium"
                                            >
                                                Edit
                                            </a>


                                            <!-- Delete -->
                                            <form
                                                action="{{ route('students.destroy', $student->id) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="text-red-600 hover:text-red-800 font-medium"
                                                >
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>
                                

                            @endforeach
                            

                        </tbody>

                    </table>

                </div>

            </div>

        @else

            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm p-10 text-center">

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
                    Add Student
                </a>

            </div>

        @endif

    </div>

</body>
</html>