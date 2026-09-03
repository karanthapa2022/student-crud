<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // =========================================================
    // LIST STUDENTS
    // =========================================================

    public function index(Request $request)
    {
        $query = Student::with([
            'parent',
            'address',
            'subjects'
        ]);

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");

            });
        }

        // Status filter
        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {
            $query->where('status', $request->status);
        }

        $students = $query
            ->latest()
            ->paginate(
                $request->get('per_page', 10)
            );

        return response()->json($students);
    }


    // =========================================================
    // CREATE STUDENT
    // =========================================================

    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'class' => 'required|string|max:100',

            'symbol_no'=>'nullable|string|max:100|unique:students,symbol_no',

            'date_of_birth' =>'nullable|date',

            'email' => 'required|email|unique:students,email',

            'phone' => 'required|string|max:20',

            'status' => 'nullable|in:active,inactive',

            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            'parent_id' => 'nullable|exists:parents,id',

            'address_id' => 'nullable|exists:addresses,id',

            'subjects' => 'nullable |array',

            'subjects.*' => 'exists:subjects,id',
        ]);


        // Upload photo
        if ($request->hasFile('photo')) {                                               

            $validated['photo'] =
                $request->file('photo')
                    ->store('students', 'public');
        }


        $student = Student::create($validated);


        // Attach subjects
        if ($request->has('subjects')) {

            $student->subjects()->sync(
                $request->subjects
            );
        }


        $student->load([
            'parent',
            'address',
            'subjects'
        ]);


        return response()->json([
            'message' => 'Student created successfully.',
            'student' => $student
        ], 201);
    }


    // =========================================================
    // SHOW STUDENT
    // =========================================================

    public function show(Student $student)
    {
        $student->load([
            'parent',
            'address',
            'subjects'
        ]);

        return response()->json($student);
    }


    // =========================================================
    // UPDATE STUDENT
    // =========================================================

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([

            'name' =>
                'required|string|max:255',

            'class' =>
                'required|string|max:100',

            'symbol_no'=>
                'nullable|string|max:100|unique:students,symbol_no,'. $student->id, 
                
            'date_of_birth' =>'nullable|date',

            'email' =>
                'required|email|unique:students,email,' . $student->id,

            'phone' =>
                'required|string|max:20',

            'status' =>
                'nullable|in:active,inactive',

            'photo' =>
                'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            'parent_id' =>
                'nullable|exists:parents,id',

            'address_id' =>
                'nullable|exists:addresses,id',

            'subjects' =>
                'nullable|array',

            'subjects.*' =>
                'exists:subjects,id',
        ]);


        // Replace photo
        if ($request->hasFile('photo')) {

            if (
                $student->photo &&
                Storage::disk('public')->exists($student->photo)
            ) {
                Storage::disk('public')
                    ->delete($student->photo);
            }


            $validated['photo'] =
                $request->file('photo')
                    ->store('students', 'public');
        }


        $student->update($validated);


        // Update subjects
        if ($request->has('subjects')) {

    $student->subjects()->sync(
        $request->input('subjects', [])
    );

} else {

    $student->subjects()->sync([]);

}

        $student->load([
            'parent',
            'address',
            'subjects'
        ]);


        return response()->json([
            'message' => 'Student updated successfully.',
            'student' => $student
        ]);
    }


    // =========================================================
    // SOFT DELETE
    // =========================================================

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json([
            'message' => 'Student moved to trash successfully.'
        ]);
    }


    // =========================================================
    // TRASH
    // =========================================================

    public function trash(Request $request)
    {
        $students = Student::onlyTrashed()
            ->with([
                'parent',
                'address',
                'subjects'
            ])
            ->latest('deleted_at')
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }


    // =========================================================
    // RESTORE
    // =========================================================

    public function restore($id)
    {
        $student = Student::onlyTrashed()
            ->findOrFail($id);

        $student->restore();


        $student->load([
            'parent',
            'address',
            'subjects'
        ]);


        return response()->json([
            'message' => 'Student restored successfully.',
            'student' => $student
        ]);
    }


    // =========================================================
    // FORCE DELETE
    // =========================================================

    public function forceDelete($id)
    {
        $student = Student::onlyTrashed()
            ->findOrFail($id);


        if (
            $student->photo &&
            Storage::disk('public')->exists($student->photo)
        ) {
            Storage::disk('public')
                ->delete($student->photo);
        }


        $student->subjects()->detach();

        $student->forceDelete();


        return response()->json([
            'message' => 'Student permanently deleted.'
        ]);
    }


    // =========================================================
    // BULK DELETE
    // =========================================================

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:students,id',
        ]);


        Student::whereIn(
            'id',
            $validated['ids']
        )->delete();


        return response()->json([
            'message' => 'Students moved to trash successfully.'
        ]);
    }


    // =========================================================
    // BULK RESTORE
    // =========================================================

    public function bulkRestore(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);


        Student::onlyTrashed()
            ->whereIn(
                'id',
                $validated['ids']
            )
            ->restore();


        return response()->json([
            'message' => 'Students restored successfully.'
        ]);
    }


    // =========================================================
    // BULK FORCE DELETE
    // =========================================================

    public function bulkForceDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);


        $students = Student::onlyTrashed()
            ->whereIn(
                'id',
                $validated['ids']
            )
            ->get();


        foreach ($students as $student) {

            if (
                $student->photo &&
                Storage::disk('public')->exists($student->photo)
            ) {
                Storage::disk('public')
                    ->delete($student->photo);
            }

            $student->subjects()->detach();

            $student->forceDelete();
        }


        return response()->json([
            'message' => 'Students permanently deleted.'
        ]);
    }


    // =========================================================
    // BULK UPDATE
    // =========================================================

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([

            'ids' =>
                'required|array|min:1',

            'ids.*' =>
                'integer|exists:students,id',

            'status' =>
                'required|in:active,inactive',
        ]);


        Student::whereIn(
            'id',
            $validated['ids']
        )->update([
            'status' => $validated['status']
        ]);


        return response()->json([
            'message' => 'Students updated successfully.'
        ]);
    }


    // =========================================================
    // STATISTICS
    // =========================================================

    public function statistics()
    {
        $total = Student::count();

        $active = Student::where(
            'status',
            'active'
        )->count();

        $inactive = Student::where(
            'status',
            'inactive'
        )->count();


        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive
        ]);
    }
}