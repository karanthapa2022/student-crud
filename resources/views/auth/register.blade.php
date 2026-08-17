<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Student Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4 py-10">

    <!-- Main Container -->
    <div class="w-full max-w-md">

        <!-- Header -->
        <div class="text-center mb-8">

            <h1 class="text-3xl font-bold text-gray-800">
                Student Management
            </h1>

            <p class="text-gray-500 mt-2">
                Create your account to get started
            </p>

        </div>


        <!-- Register Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">

            <!-- Card Heading -->
            <div class="mb-6">

                <h2 class="text-2xl font-semibold text-gray-800">
                    Create Account
                </h2>

                <p class="text-gray-500 mt-1">
                    Fill in your details below.
                </p>

            </div>


            <!-- Validation Errors -->
            @if ($errors->any())

                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">

                    <p class="font-medium mb-2">
                        Please fix the following errors:
                    </p>

                    <ul class="list-disc list-inside text-sm space-y-1">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- Register Form -->
            <form
                action="{{ route('register.store') }}"
                method="POST"
            >

                @csrf


                <!-- Name -->
                <div class="mb-5">

                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Enter your full name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >

                </div>


                <!-- Email -->
                <div class="mb-5">

                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >

                </div>


                <!-- Password -->
                <div class="mb-5">

                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >

                    <p class="text-xs text-gray-500 mt-2">
                        Password must be at least 8 characters.
                    </p>

                </div>


                <!-- Confirm Password -->
                <div class="mb-6">

                    <label
                        for="password_confirmation"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm your password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >

                </div>


                <!-- Register Button -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200"
                >
                    Create Account
                </button>

            </form>


            <!-- Login Link -->
            <div class="text-center mt-6">

                <p class="text-sm text-gray-500">

                    Already have an account?

                    <a
                        href="{{ route('login') }}"
                        class="text-blue-600 hover:text-blue-800 font-medium"
                    >
                        Login
                    </a>

                </p>

            </div>

        </div>


        <!-- Footer -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Student Management System
        </p>

    </div>

</body>

</html>