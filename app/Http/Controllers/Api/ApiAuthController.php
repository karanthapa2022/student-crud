<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
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

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('vue-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user,
            'token' => $token,
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

        $user = User::where(
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
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

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


        // -----------------------------------------------------
        // Delete old profile photo
        // -----------------------------------------------------

        if ($user->profile_photo) {

            $oldPhoto = public_path(
                'storage/' . $user->profile_photo
            );

            if (file_exists($oldPhoto)) {
                unlink($oldPhoto);
            }
        }


        // -----------------------------------------------------
        // Store new profile photo
        // -----------------------------------------------------

        $path = $request
            ->file('profile_photo')
            ->store('profile-photos', 'public');


        // -----------------------------------------------------
        // Save photo path
        // -----------------------------------------------------

        $user->update([
            'profile_photo' => $path,
        ]);


        // Refresh user data
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
            'message' => 'No document found.'
        ], 404);
    }

    $filePath = storage_path(
        'app/public/' . $user->document
    );

    // Delete the actual file
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

//===================
// Change Password
//===================
 
public function changePassword(Request $request)
{
    $request->validate([
        'current_password'=>'required',
        'new_password'=>'required|min:8|confirmed',

    ]);
    $user=$request->user();

    //check current password
    if(!Hash::check($request->current_password,$user->password)){
        return response()->json([
            'message'=>'current password is incorrect.',
        ], 422);
    }
    //update password
    $user->update([
        'password'=>Hash::make($request->new_password),
    ]);
    return response()->json([
        'message'=>'Password changed successfully.',
    ]);
}
}