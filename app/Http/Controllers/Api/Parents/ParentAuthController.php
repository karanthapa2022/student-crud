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

// =========================================================
// PARENT PROFILE
// =========================================================

public function profile(Request $request)
{
    $user = $request->user();

    if (!$user || $user->role !== 'parent') {
        return response()->json([
            'message' => 'Unauthorized.'
        ], 403);
    }

    $parent = $user->parent;

    if (!$parent) {
        return response()->json([
            'message' => 'Parent record not found.'
        ], 404);
    }

    return response()->json([
        'parent' => [
            'id' => $parent->id,
            'name' => $parent->name,
            'email' => $parent->email,
            'phone' => $parent->phone,
            'relationship' => $parent->relationship,
        ],
    ]);
}


// =========================================================
// UPDATE PARENT PROFILE
// =========================================================

public function updateProfile(Request $request)
{
    $user = $request->user();

    if (!$user || $user->role !== 'parent') {
        return response()->json([
            'message' => 'Unauthorized.'
        ], 403);
    }

    $parent = $user->parent;

    if (!$parent) {
        return response()->json([
            'message' => 'Parent record not found.'
        ], 404);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:parents,email,' . $parent->id,
        'phone' => 'nullable|string|max:30',
        'relationship' => 'nullable|string|max:100',
    ]);

    $parent->update($validated);

    return response()->json([
        'message' => 'Profile updated successfully.',
        'parent' => [
            'id' => $parent->id,
            'name' => $parent->name,
            'email' => $parent->email,
            'phone' => $parent->phone,
            'relationship' => $parent->relationship,
        ],
    ]);
}


}