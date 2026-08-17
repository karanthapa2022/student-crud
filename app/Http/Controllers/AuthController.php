<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show login page
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Login user
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            return redirect()
                ->route('students.index')
                ->with('success', 'Login successful.');
        }

        return back()
            ->withErrors([
                'email' => 'The email or password is incorrect.',
            ])
            ->withInput();
    }

    // Show register page
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Register new user
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email',

            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],

            'email' => $validated['email'],

            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('students.index')
            ->with('success', 'Account created successfully.');
    }

    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Logged out successfully.');
    }
}