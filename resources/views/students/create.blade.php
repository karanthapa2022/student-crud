<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Student</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <div class="max-w-2xl mx-auto px-6 py-10">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Add Student
            </h1>

            <p class="text-gray-500 mt-1">
                Add a new student to the system
            </p>
        </div>


        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm p-8">

            <form action="{{ route('students.store') }}" method="POST">

                @csrf
                @if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

                <!-- Name -->
                <div class="mb-6">

                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Enter student name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >

                </div>


                <!-- Email -->
                <div class="mb-6">

                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter student email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >

                </div>


                <!-- Phone -->
                <div class="mb-8">

                    <label
                        for="phone"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Phone
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Enter phone number"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >

                </div>
                <select name="status">
    <option value="active">Active</option>
    <option value="inactive">Inactive</option>
</select>


                <!-- Buttons -->
                <div class="flex items-center gap-4">

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition"
                    >
                        Save Student
                    </button>

                    <a
                        href="{{ route('students.index') }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</body>
</html>