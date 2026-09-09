<?php

namespace App\Http\Controllers\Api\Parents;

use App\Http\Controllers\Controller;
use App\Models\StudentParent;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    // =========================================================
    // GET ALL PARENTS
    // =========================================================

    public function index(Request $request)
    {
        $query = StudentParent::with('students');

        // SEARCH
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");

            });
        }

        // RELATIONSHIP FILTER
        if (
            $request->filled('relationship_filter') &&
            $request->relationship_filter !== 'all'
        ) {

            $query->where(
                'relationship',
                $request->relationship_filter
            );
        }

        $parents = $query
            ->latest()
            ->paginate(5);

        return response()->json($parents);
    }


    // =========================================================
    // CREATE PARENT
    // =========================================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'relationship' => 'required|string|max:100',
            'password' => 'required|string|min:8',
        ]);

        $parent = StudentParent::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'relationship' => $validated['relationship'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'parent',
            'parent_id' => $parent->id,
        ]);

        return response()->json([
            'message' => 'Parent and login account created successfully.',
            'parent' => $parent,
        ], 201);
    }


    // =========================================================
    // GET SINGLE PARENT
    // =========================================================

    public function show(StudentParent $parent)
    {
        $parent->load('students');

        return response()->json($parent);
    }


    // =========================================================
    // UPDATE PARENT
    // =========================================================

    public function update(Request $request, StudentParent $parent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'relationship' => 'required|string|max:100',
        ]);

        $parent->update($validated);

        return response()->json([
            'message' => 'Parent updated successfully.',
            'parent' => $parent,
        ]);
    }


    // =========================================================
    // UPDATE PARENT'S CHILDREN
    // =========================================================

    public function updateChildren(
        Request $request,
        StudentParent $parent
    ) {
        $validated = $request->validate([
            'student_ids' => 'array',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        // Remove this parent from all currently assigned students
        Student::where('parent_id', $parent->id)
            ->update([
                'parent_id' => null
            ]);

        // Assign selected students to this parent
        if (!empty($validated['student_ids'])) {

            Student::whereIn(
                'id',
                $validated['student_ids']
            )->update([
                'parent_id' => $parent->id
            ]);
        }

        $parent->load('students');

        return response()->json([
            'message' => 'Children updated successfully.',
            'parent' => $parent,
        ]);
    }


    // =========================================================
    // DELETE PARENT
    // =========================================================

    public function destroy(StudentParent $parent)
    {
        // Remove parent assignment from all children
        Student::where('parent_id', $parent->id)
            ->update([
                'parent_id' => null
            ]);

        // Delete parent
        $parent->delete();

        return response()->json([
            'message' => 'Parent deleted successfully.',
        ]);
    }
    
// =========================================================
// CHANGE PARENT PASSWORD
// =========================================================

public function changePassword(Request $request, $parent)
{
    $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ]);

    $parentModel = StudentParent::findOrFail($parent);

    $user = $parentModel->user;

    if (!$user) {
        return response()->json([
            'message' => 'No login account is associated with this parent.'
        ], 404);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json([
        'message' => 'Parent password changed successfully.'
    ]);
}


}