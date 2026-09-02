<?php

namespace App\Http\Controllers\Api\Subjects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = Subject::with(['teachers'])
        ->withCount('students');

    // SEARCH
    if ($request->filled('search')) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");

        });
    }

    // TEACHER FILTER
    if ($request->teacher_filter === 'with_teacher') {

        $query->whereHas('teachers');

    } elseif ($request->teacher_filter === 'without_teacher') {

        $query->whereDoesntHave('teachers');

    }

    $subjects = $query
        ->latest()
        ->paginate(5);

    return response()->json($subjects);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'message' => 'Subject created successfully.',
            'subject' => $subject,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subject = Subject::with(['teachers'])
            ->withCount('students')
            ->findOrFail($id);

        return response()->json($subject);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,

            'description' => 'nullable|string',
        ]);

        $subject->update($validated);

        $subject->loadCount('students');

        return response()->json([
            'message' => 'Subject updated successfully.',
            'subject' => $subject,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subject = Subject::findOrFail($id);

        $subject->delete();

        return response()->json([
            'message' => 'Subject deleted successfully.',
        ]);
    }
}
