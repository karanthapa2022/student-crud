<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherSubjectController extends Controller
{
    /**
     * Get subjects assigned to the logged-in teacher.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        /*
         * Find the teacher profile using
         * the email of the logged-in user.
         */
        $teacher = Teacher::where(
            'email',
            $user->email
        )->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher profile not found.'
            ], 404);
        }

        /*
         * Get only subjects assigned
         * to this teacher.
         */
        $subjects = $teacher->subjects()
            ->withCount('students')
            ->latest()
            ->get();

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
            ],
            'subjects' => $subjects,
        ]);
    }


    /**
     * Display one subject assigned to
     * the logged-in teacher.
     */
    public function show(
        Request $request,
        string $id
    ) {
        $user = $request->user();

        /*
         * Find teacher profile.
         */
        $teacher = Teacher::where(
            'email',
            $user->email
        )->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher profile not found.'
            ], 404);
        }

        /*
         * Check whether this subject
         * actually belongs to the teacher.
         */
        $subject = $teacher->subjects()
            ->withCount('students')
            ->where(
                'subjects.id',
                $id
            )
            ->first();

        if (!$subject) {
            return response()->json([
                'message' =>
                    'You do not have permission to view this subject.'
            ], 403);
        }

        /*
         * Load students assigned to
         * this subject.
         */
        $subject->load([
            'students'
        ]);

        return response()->json($subject);
    }
}


