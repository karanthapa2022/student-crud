<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    /**
     * Display the authenticated teacher's dashboard data.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        /*
         * At the moment teachers are stored in the teachers table,
         * while authentication uses the users table.
         *
         * We therefore match the authenticated teacher account
         * using the teacher's email address.
         */
        $teacher = Teacher::with('subjects')
            ->where('email', $user->email)
            ->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher profile not found.'
            ], 404);
        }

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
            ],

            'statistics' => [
                'students' => 0,
                'subjects' => $teacher->subjects->count(),
                'marksheets' => 0,
            ],

            'subjects' => $teacher->subjects,
        ]);
    }
}
