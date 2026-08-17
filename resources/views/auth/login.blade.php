<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Student Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">

    <!-- Login Container -->

    <div class="w-full max-w-md">

        <!-- Logo / Brand -->

        <div class="text-center mb-8">


            <h1 class="text-3xl font-bold text-gray-800">
                Student Management
            </h1>

            <p class="text-gray-500 mt-2">
                Sign in to manage your students
            </p>

        </div>


        <!-- Login Card -->

        <div class="bg-white rounded-2xl shadow-xl p-8">

            <h2 class="text-2xl font-bold text-gray-800">
                Welcome back
            </h2>

            <p class="text-gray-500 text-sm mt-1 mb-6">
                Enter your credentials to continue.
            </p>


            <!-- Validation Errors -->

            @if ($errors->any())

                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">

                    <p class="font-medium text-sm">
                        {{ $errors->first() }}
                    </p>

                </div>

            @endif


            <!-- Login Form -->

            <form
                action="{{ route('login') }}"
                method="POST"
                class="space-y-5"
            >

                @csrf


                <!-- Email -->

                <div>

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
                        placeholder="you@example.com"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >

                </div>


                <!-- Password -->

                <div>

                    <div class="flex items-center justify-between mb-2">

                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Password
                        </label>

                    </div>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >

                </div>


                <!-- Login Button -->

                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white py-3 rounded-lg font-semibold transition duration-200 shadow-sm"
                >
                    Sign In
                </button>

            </form>


            <!-- Register -->

            <div class="mt-6 pt-6 border-t border-gray-100 text-center">

                <p class="text-sm text-gray-500">

                    Don't have an account?

                    <a
                        href="{{ route('register') }}"
                        class="text-blue-600 hover:text-blue-800 font-semibold transition"
                    >
                        Create an account
                    </a>

                </p>

            </div>

        </div>


        <!-- Footer -->

        <p class="text-center text-xs text-gray-400 mt-6">
            © {{ date('Y') }} Student Management System
        </p>

    </div>

</body>

</html>