<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <div class="max-w-2xl mx-auto py-10 px-6">

        <div class="bg-white shadow-md rounded-lg p-8">

            <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Edit Student
            </h1>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('students.update', $student->id) }}" method="POST">

                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $student->name) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $student->email) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                {{-- Phone --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone', $student->phone) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                {{-- Status --}}
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Status
                    </label>

                    <select name="status" required>
    <option value="active"
        {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>
        Active
    </option>

    <option value="inactive"
        {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>
        Inactive
    </option>
</select>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg"
                    >
                        Update Student
                    </button>

                    <a
                        href="{{ route('students.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-2 rounded-lg"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</body>
</html>