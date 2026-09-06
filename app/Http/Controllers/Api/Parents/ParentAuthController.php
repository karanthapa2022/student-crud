<?php

namespace App\Http\Controllers\Api\Parents;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentAuthController extends Controller
{
    // =========================================================
    // PARENT LOGIN
    // =========================================================

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])
            ->where('role', 'parent')
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {

            return response()->json([
                'message' => 'Invalid parent email or password.'
            ], 401);
        }

        $token = $user->createToken('parent-token')->plainTextToken;

        return response()->json([
            'message' => 'Parent login successful.',
            'token' => $token,
            'user' => $user->load('parent'),
        ]);
    }
}