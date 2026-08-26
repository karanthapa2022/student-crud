<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;

class StudentController extends Controller
{
    /**
     * Display all students.
     */
    public function index()
    {
        $students = Student::latest()->get();

        return response()->json($students);
    }

    /**
 * Student statistics.
 */
public function statistics()
{
    $totalStudents = Student::count();

    $activeStudents = Student::where(
        'status',
        'active'
    )->count();

    $inactiveStudents = Student::where(
        'status',
        'inactive'
    )->count();

    return response()->json([
        'total' => $totalStudents,
        'active' => $activeStudents,
        'inactive' => $inactiveStudents,
    ]);
}

    /**
     * Store a new student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',

            // Student photo
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Upload photo
        if ($request->hasFile('photo')) {
            $validated['photo'] =
                $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Student created successfully.',
            'student' => $student,
        ], 201);
    }

    /**
     * Display one student.
     */
    public function show(Student $student)
    {
        return response()->json($student);
    }

    /**
     * Update one student.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email,' . $student->id,
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',

            // Student photo
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Replace old photo if a new one is uploaded
        if ($request->hasFile('photo')) {

            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }

            $validated['photo'] =
                $request->file('photo')->store('students', 'public');
        }

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully.',
            'student' => $student,
        ]);
    }

    /**
     * Delete one student.
     */
    public function destroy(Student $student)
    {
        // Delete student's photo
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully.',
        ]);
    }

    /**
     * Bulk delete students.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:students,id',
        ]);

        $students = Student::whereIn(
            'id',
            $request->ids
        )->get();

        // Delete photos
        foreach ($students as $student) {

            if ($student->photo) {
                Storage::disk('public')->delete(
                    $student->photo
                );
            }
        }

        Student::whereIn(
            'id',
            $request->ids
        )->delete();

        return response()->json([
            'message' => 'Students deleted successfully.',
        ]);
    }
}