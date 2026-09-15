<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\StudentSubject;
use App\Models\StudentParent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
            'teachers',
            'subjects',
            'studentSubjectAssignments.subject',
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
            ->paginate(10)
            ->withQueryString();

        $students->getCollection()->transform(function ($student) {

            $student->setRelation(
                'subjects',
                $this->formatStudentSubjects($student)
            );

            return $student;
        });

        return response()->json($students);
    }


    // =========================================================
    // CREATE STUDENT
    // =========================================================

    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => 'required|string|max:255',

            'class' => 'required|integer|between:1,10',

            'symbol_no' => 'nullable|string|max:100|unique:students,symbol_no',

            'date_of_birth' => 'nullable|date',

            'email' => 'required|email|unique:students,email',

            'phone' => 'required|string|max:20',

            'status' => 'nullable|in:active,inactive',

            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            'parent_id' => 'nullable|exists:parents,id',

            'teacher_ids' => 'required|array|size:1',

            'teacher_ids.*' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id')->where(
                    fn ($query) => $query->where('class', $request->input('class'))
                ),
            ],

            'address_id' => 'nullable|exists:addresses,id',

            'subjects' => 'required|array|min:3',

            'subjects.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->where(function ($query) use ($request) {
                        $query->where('class', $request->input('class'))
                            ->orWhereNull('class');
                    })
                ),
            ],
        ]);


        // Upload photo
        if ($request->hasFile('photo')) {

            $validated['photo'] =
                $request->file('photo')
                    ->store('students', 'public');
        }

        $parent = !empty($validated['parent_id'])
            ? StudentParent::find($validated['parent_id'])
            : null;
        $validated['address_id'] = $parent?->address_id;


        $student = Student::create($validated);
        $temporaryPassword= Str::random(10);
        User::create([
    'name' => $student->name,
    'email' => $student->email,
    'password' => $temporaryPassword,
    'role' => 'student',
    'student_id' => $student->id,
]);


        $student->subjects()->sync($validated['subjects']);

        $student->teachers()->sync($request->input('teacher_ids', []));


        $student->load([
            'parent',
            'address',
            'teachers',
            'subjects'
        ]);


        return response()->json([
    'message' => 'Student created successfully.',
    'student' => $student,
    'login' => [
        'email' => $student->email,
        'temporary_password' => $temporaryPassword,
    ],
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
            'teachers',
            'subjects',
            'studentSubjectAssignments.subject',
        ]);

        $student->setRelation(
            'subjects',
            $this->formatStudentSubjects($student)
        );

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
                'required|integer|between:1,10',

            'symbol_no' =>
                'nullable|string|max:100|unique:students,symbol_no,' . $student->id,

            'date_of_birth' =>
                'nullable|date',

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

            'teacher_ids' =>
                'required|array|size:1',

            'teacher_ids.*' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id')->where(
                    fn ($query) => $query->where('class', $request->input('class'))
                ),
            ],

            'address_id' =>
                'nullable|exists:addresses,id',

            'subjects' =>
                'required|array|min:3',

            'subjects.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->where(function ($query) use ($request) {
                        $query->where('class', $request->input('class'))
                            ->orWhereNull('class');
                    })
                ),
            ],
        ]);


        // =====================================================
        // REPLACE PHOTO
        // =====================================================

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

        if ($request->exists('parent_id')) {
            $validated['parent_id'] = $request->input('parent_id') ?: null;
        }

        $parent = $validated['parent_id']
            ? StudentParent::find($validated['parent_id'])
            : null;
        $validated['address_id'] = $parent?->address_id;


        // =====================================================
        // UPDATE STUDENT
        // =====================================================

        $student->update($validated);


        // =====================================================
        // UPDATE SUBJECTS
        // =====================================================

        $student->subjects()->sync($validated['subjects']);

        $student->teachers()->sync($request->input('teacher_ids', []));


        // =====================================================
        // LOAD UPDATED STUDENT
        // =====================================================

        $student->load([
            'parent',
            'address',
            'teachers',
            'studentSubjectAssignments.subject',
        ]);

        $student->setRelation(
            'subjects',
            $this->formatStudentSubjects($student)
        );


        return response()->json([
            'message' => 'Student updated successfully.',
            'student' => $student
        ]);
    }

    public function assignParent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:parents,id'],
        ]);

        $parent = !empty($validated['parent_id'])
            ? StudentParent::find($validated['parent_id'])
            : null;

        $student->update([
            'parent_id' => $parent?->id,
            'address_id' => $parent?->address_id,
        ]);

        return response()->json([
            'message' => $student->parent_id
                ? 'Parent assigned to student successfully.'
                : 'Parent removed from student successfully.',
            'student' => $student->load('parent'),
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
            ->whereIn('id', $validated['ids'])
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
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:students,id'],
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:parents,id'],
        ]);

        if (!$request->exists('status') && !$request->exists('parent_id')) {
            return response()->json([
                'message' => 'Select a status or parent change before saving.'
            ], 422);
        }

        $changes = [];
        if ($request->exists('status')) {
            $changes['status'] = $validated['status'] ?? null;
        }
        if ($request->exists('parent_id')) {
            $changes['parent_id'] = $validated['parent_id'] ?? null;
        }

        Student::whereIn('id', $validated['ids'])->update($changes);

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


    // =========================================================
    // FORMAT STUDENT SUBJECTS
    // =========================================================

    private function formatStudentSubjects(Student $student)
    {
        $teacherName = $student->teachers->first()?->name;

        return $student->studentSubjectAssignments
            ->unique('subject_id')
            ->map(function ($assignment) use ($teacherName) {

                return [
                    'id' => $assignment->subject_id,

                    'name' => $assignment->subject_name
                        ?: ($assignment->subject?->name ?? ''),

                    'code' => $assignment->subject?->code,

                    'teacher' => $teacherName,

                    'description' => $assignment->subject?->description,

                    'pivot' => [
                        'subject_name' => $assignment->subject_name,
                        'subject_code' => $assignment->subject_code,
                    ],
                ];

            })
            ->values();
    }
}