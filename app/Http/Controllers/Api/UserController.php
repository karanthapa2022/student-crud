<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'users' => User::query()
                ->select('id', 'name', 'email', 'role', 'parent_id', 'created_at')
                ->latest()
                ->get(),
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(Role::all())],
            'parent_id' => ['nullable', 'exists:parents,id'],
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json($user->only('id', 'name', 'email', 'role', 'parent_id'), 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(Role::all())],
            'parent_id' => ['nullable', 'exists:parents,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->only('id', 'name', 'email', 'role', 'parent_id'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
    public function changePassword(Request $request){
        $validated = $request->validate([
            'current_password'=>['required','string'],
            'new_password'=>['required','string','min-8','confirmed'],
        ]);
        $user =$request->user();
        if (!Hash::check($validated['current_password'],$user->password)){
            return response()->json([
                'message'=>'current password is incorrect.',
            ], 422);  
        }
        $user->update([
            'password'=>$validated['new_password'],
            'must_change_password'=> false,
        ]);
        return response()->json([
            'message'=>'Password changed successfully.',
        ]);
    }
}
