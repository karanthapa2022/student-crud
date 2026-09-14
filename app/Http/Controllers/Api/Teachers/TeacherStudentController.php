<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherStudentController extends Controller
{
    // =========================================================
    // LIST TEACHER'S ASSIGNED STUDENTS
    // =========================================================

    public function index(Request $request)
    {
        $user = $request->user();

        // -----------------------------------------------------
        // FIND TEACHER PROFILE
        // -----------------------------------------------------

        $teacher = Teacher::where(
            'email',
            $user->email
        )->first();

        if (!$teacher) {

            return response()->json([
                'message' => 'Teacher profile not found.'
            ], 404);

        }


        // -----------------------------------------------------
        // GET SUBJECT IDS ASSIGNED TO TEACHER
        // -----------------------------------------------------

        $subjectIds = $teacher->subjects()
            ->pluck('subjects.id');


        // -----------------------------------------------------
        // GET STUDENTS WHO TAKE THOSE SUBJECTS
        // -----------------------------------------------------

        $query = Student::with([
            'parent',
            'address',
            'teachers',
            'studentSubjectAssignments.subject',
        ])
        ->where(function ($query) use ($subjectIds, $teacher) {
            $query->whereHas('teachers', function ($query) use ($teacher) {
                $query->where('teachers.id', $teacher->id);
            })->orWhereHas('studentSubjectAssignments', function ($query) use ($subjectIds) {
                $query->whereIn('subject_id', $subjectIds);
            });
        });


        // -----------------------------------------------------
        // SEARCH
        // -----------------------------------------------------

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'email',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'phone',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'symbol_no',
                    'like',
                    "%{$search}%"
                );

            });

        }


        // -----------------------------------------------------
        // STATUS FILTER
        // -----------------------------------------------------

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {

            $query->where(
                'status',
                $request->status
            );

        }


        // -----------------------------------------------------
        // PAGINATION
        // -----------------------------------------------------

        $students = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();


        // -----------------------------------------------------
        // FORMAT SUBJECTS
        // -----------------------------------------------------

        $students->getCollection()->transform(
            function ($student) {

                $student->setRelation(
                    'subjects',
                    $student->studentSubjectAssignments
                        ->map(function ($assignment) {

                            return [

                                'id' =>
                                    $assignment->subject_id,

                                'name' =>
                                    $assignment->subject_name
                                    ?: (
                                        $assignment
                                            ->subject
                                            ?->name
                                        ?? ''
                                    ),

                                'code' =>
                                    $assignment
                                        ->subject
                                        ?->code,

                                'description' =>
                                    $assignment
                                        ->subject
                                        ?->description,

                                'pivot' => [

                                    'subject_name' =>
                                        $assignment
                                            ->subject_name,

                                    'subject_code' =>
                                        $assignment
                                            ->subject_code,

                                ],

                            ];

                        })
                        ->values()
                );

                return $student;

            }
        );


        // -----------------------------------------------------
        // RESPONSE
        // -----------------------------------------------------

        return response()->json(
            $students
        );
    }


    // =========================================================
    // VIEW SINGLE ASSIGNED STUDENT
    // =========================================================

    public function show(
        Request $request,
        Student $student
    ) {

        $user = $request->user();


        // -----------------------------------------------------
        // FIND TEACHER
        // -----------------------------------------------------

        $teacher = Teacher::where(
            'email',
            $user->email
        )->first();

        if (!$teacher) {

            return response()->json([
                'message' => 'Teacher profile not found.'
            ], 404);

        }


        // -----------------------------------------------------
        // CHECK WHETHER STUDENT IS ASSIGNED
        // -----------------------------------------------------

        $isAssigned = $student->teachers()
            ->where('teachers.id', $teacher->id)
            ->exists()
            || $student->studentSubjectAssignments()
                ->whereIn(
                    'subject_id',
                    $teacher->subjects()->pluck('subjects.id')
                )
                ->exists();


        if (!$isAssigned) {

            return response()->json([
                'message' =>
                    'You do not have permission to view this student.'
            ], 403);

        }


        // -----------------------------------------------------
        // LOAD STUDENT
        // -----------------------------------------------------

        $student->load([
            'parent',
            'address',
            'teachers',
            'studentSubjectAssignments.subject',
        ]);


        // -----------------------------------------------------
        // FORMAT SUBJECTS
        // -----------------------------------------------------

        $student->setRelation(
            'subjects',
            $student->studentSubjectAssignments
                ->map(function ($assignment) {

                    return [

                        'id' =>
                            $assignment->subject_id,

                        'name' =>
                            $assignment->subject_name
                            ?: (
                                $assignment
                                    ->subject
                                    ?->name
                                ?? ''
                            ),

                        'code' =>
                            $assignment
                                ->subject
                                ?->code,

                        'description' =>
                            $assignment
                                ->subject
                                ?->description,

                    ];

                })
                ->values()
        );


        return response()->json(
            $student
        );
    }
}

