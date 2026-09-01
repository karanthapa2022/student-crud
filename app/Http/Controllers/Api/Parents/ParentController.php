<?php

namespace App\Http\Controllers\Api\Parents;

use App\Http\Controllers\Controller;
use App\Models\StudentParent;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    // =========================================================
    // GET ALL PARENTS
    // =========================================================

    public function index(Request $request)
    {
        $parents = StudentParent::with('students')
            ->latest()
            ->paginate(10);

        return response()->json($parents);
    }


    // =========================================================
    // CREATE PARENT
    // =========================================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'relationship' => 'required|string|max:100',
        ]);

        $parent = StudentParent::create($validated);

        return response()->json([
            'message' => 'Parent created successfully.',
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
    // DELETE PARENT
    // =========================================================

    public function destroy(StudentParent $parent)
    {
        $parent->delete();

        return response()->json([
            'message' => 'Parent deleted successfully.',
        ]);
    }
}
