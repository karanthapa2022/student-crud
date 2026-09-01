<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Address;

class StudentController extends Controller
{
    /**
     * Display all students.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');

        $query = Student::with([
            'parent',
            'address',
            'subjects',
        ]);

        // Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Pagination
        $students = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

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

            // Student
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',

            // Parent
            'parent.name' => 'required|string|max:255',
            'parent.email' => 'nullable|email|max:255',
            'parent.phone' => 'nullable|string|max:20',
            'parent.relationship' => 'required|string|max:100',

            // Address
            'address.province' => 'required|string|max:255',
            'address.district' => 'required|string|max:255',
            'address.municipality' => 'required|string|max:255',
            'address.ward' => 'required|string|max:50',
            'address.city' => 'required|string|max:255',
            'address.street' => 'nullable|string|max:255',

            // Subjects
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'integer|exists:subjects,id',

            // Photo
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {

            // ==========================================
            // CREATE PARENT
            // ==========================================

            $parent = StudentParent::create(
                $validated['parent']
            );


            // ==========================================
            // CREATE ADDRESS
            // ==========================================

            $address = Address::create(
                $validated['address']
            );


            // ==========================================
            // CREATE STUDENT
            // ==========================================

            $studentData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
                'parent_id' => $parent->id,
                'address_id' => $address->id,
            ];


            // Upload photo
            if ($request->hasFile('photo')) {
                $studentData['photo'] =
                    $request->file('photo')->store(
                        'students',
                        'public'
                    );
            }


            $student = Student::create($studentData);


            // ==========================================
            // ATTACH SUBJECTS
            // ==========================================

            if (!empty($validated['subject_ids'])) {
                $student->subjects()->sync(
                    $validated['subject_ids']
                );
            }


            DB::commit();


            // Return complete student
            $student->load([
                'parent',
                'address',
                'subjects',
            ]);


            return response()->json([
                'message' => 'Student created successfully.',
                'student' => $student,
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display one student.
     */
    public function show(Student $student)
    {
        $student->load([
            'parent',
            'address',
            'subjects',
        ]);

        return response()->json($student);
    }


    /**
     * Update one student.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([

            // Student
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email,' . $student->id,
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',

            // Parent
            'parent.name' => 'required|string|max:255',
            'parent.email' => 'nullable|email|max:255',
            'parent.phone' => 'nullable|string|max:20',
            'parent.relationship' => 'required|string|max:100',

            // Address
            'address.province' => 'required|string|max:255',
            'address.district' => 'required|string|max:255',
            'address.municipality' => 'required|string|max:255',
            'address.ward' => 'required|string|max:50',
            'address.city' => 'required|string|max:255',
            'address.street' => 'nullable|string|max:255',

            // Subjects
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'integer|exists:subjects,id',

            // Photo
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {

            // ==========================================
            // UPDATE PARENT
            // ==========================================

            if ($student->parent) {
                $student->parent->update(
                    $validated['parent']
                );
            }


            // ==========================================
            // UPDATE ADDRESS
            // ==========================================

            if ($student->address) {
                $student->address->update(
                    $validated['address']
                );
            }


            // ==========================================
            // UPDATE STUDENT
            // ==========================================

            $studentData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
            ];


            // Replace photo
            if ($request->hasFile('photo')) {

                if ($student->photo) {
                    Storage::disk('public')->delete(
                        $student->photo
                    );
                }

                $studentData['photo'] =
                    $request->file('photo')->store(
                        'students',
                        'public'
                    );
            }


            $student->update($studentData);


            // ==========================================
            // UPDATE SUBJECTS
            // ==========================================

            $student->subjects()->sync(
                $validated['subject_ids'] ?? []
            );


            DB::commit();


            $student->load([
                'parent',
                'address',
                'subjects',
            ]);


            return response()->json([
                'message' => 'Student updated successfully.',
                'student' => $student,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Move one student to Trash.
     */
    public function destroy(Student $student)
    {
        // Keep photo because the student can be restored.
        $student->delete();

        return response()->json([
            'message' => 'Student moved to trash successfully.',
        ]);
    }


    /**
     * Bulk move students to Trash.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:students,id',
        ]);

        Student::whereIn(
            'id',
            $request->ids
        )->delete();

        return response()->json([
            'message' => 'Students moved to trash successfully.',
        ]);
    }


    /**
     * Display all trashed students.
     */
    public function trash()
    {
        $students = Student::onlyTrashed()
            ->with([
                'parent',
                'address',
                'subjects',
            ])
            ->latest('deleted_at')
            ->get();

        return response()->json($students);
    }


    /**
     * Restore a trashed student.
     */
    public function restore($id)
    {
        $student = Student::onlyTrashed()
            ->findOrFail($id);

        $student->restore();

        $student->load([
            'parent',
            'address',
            'subjects',
        ]);

        return response()->json([
            'message' => 'Student restored successfully.',
            'student' => $student,
        ]);
    }


    /**
     * Permanently delete a student.
     */
    public function forceDelete($id)
    {
        $student = Student::onlyTrashed()
            ->findOrFail($id);

        if ($student->photo) {
            Storage::disk('public')->delete(
                $student->photo
            );
        }

        $student->forceDelete();

        return response()->json([
            'message' => 'Student permanently deleted.',
        ]);
    }


    /**
     * Restore multiple trashed students.
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $students = Student::onlyTrashed()
            ->whereIn('id', $request->ids)
            ->get();

        foreach ($students as $student) {
            $student->restore();
        }

        return response()->json([
            'message' => 'Students restored successfully.',
        ]);
    }


    /**
     * Permanently delete multiple trashed students.
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $students = Student::onlyTrashed()
            ->whereIn('id', $request->ids)
            ->get();

        foreach ($students as $student) {

            if ($student->photo) {
                Storage::disk('public')->delete(
                    $student->photo
                );
            }

            $student->forceDelete();
        }

        return response()->json([
            'message' => 'Students permanently deleted successfully.',
        ]);
    }


    /**
     * Bulk update students.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:students,id',
            'status' => 'required|in:active,inactive',
        ]);

        Student::whereIn(
            'id',
            $validated['ids']
        )->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Students updated successfully.',
        ]);
    }
}