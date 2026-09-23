<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {
    }

    // =========================================================
    // REGISTER
    // =========================================================

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $result = $this->authService->register($validated);

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $result['user'],
            'token' => $result['token'],
        ], 201);
    }


    // =========================================================
    // LOGIN
    // =========================================================

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where(
            'email',
            $validated['email']
        )->first();

        if (
            !$user ||
            !Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $user
            ->createToken('vue-app')
            ->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
        ]);
    }


    // =========================================================
    // LOGOUT
    // =========================================================

        public function logout(Request $request)
        {
            $this->authService->logout(
                $request->user()
            );

            return response()->json([
                'message' => 'Logout successful.',
            ]);
        }



    // =========================================================
    // UPDATE PROFILE
    // =========================================================

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'unique:users,email,' . $user->id,
            ],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }


    // =========================================================
    // UPLOAD PROFILE PHOTO
    // =========================================================

    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $user = $request->user();

        // Delete old profile photo
        if ($user->profile_photo) {
            $oldPhoto = public_path(
                'storage/' . $user->profile_photo
            );

            if (file_exists($oldPhoto)) {
                unlink($oldPhoto);
            }
        }

        // Store new profile photo
        $path = $request
            ->file('profile_photo')
            ->store('profile-photos', 'public');

        // Save photo path
        $user->update([
            'profile_photo' => $path,
        ]);

        $user->refresh();

        return response()->json([
            'message' => 'Profile photo updated successfully.',
            'user' => $user,
        ]);
    }


    // =========================================================
    // UPLOAD PDF DOCUMENT
    // =========================================================

    public function updateDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:5120',
        ]);

        $user = $request->user();

        // Delete old document
        if ($user->document) {
            $oldDocument = public_path(
                'storage/' . $user->document
            );

            if (file_exists($oldDocument)) {
                unlink($oldDocument);
            }
        }

        // Store new PDF
        $path = $request
            ->file('document')
            ->store('documents', 'public');

        // Save document path
        $user->update([
            'document' => $path,
        ]);

        $user->refresh();

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'user' => $user,
        ]);
    }


    // =========================================================
    // DELETE PDF DOCUMENT
    // =========================================================

    public function deleteDocument(Request $request)
    {
        $user = $request->user();

        if (!$user->document) {
            return response()->json([
                'message' => 'No document found.',
            ], 404);
        }

        $filePath = storage_path(
            'app/public/' . $user->document
        );

        // Delete actual file
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove document path from database
        $user->update([
            'document' => null,
        ]);

        $user->refresh();

        return response()->json([
            'message' => 'Document deleted successfully.',
            'user' => $user,
        ]);
    }


    // =========================================================
    // CHANGE PASSWORD
    // =========================================================

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        // Check current password
        if (
            !Hash::check(
                $request->current_password,
                $user->password
            )
        ) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false,
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
