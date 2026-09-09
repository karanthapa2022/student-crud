<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Marksheet;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherMarksheetController extends Controller
{
    /**
     * Get marksheets belonging to the logged-in teacher's
     * assigned subjects and students.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Find the teacher profile using the authenticated user's email
        $teacher = Teacher::where('email', $user->email)->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher profile not found.'
            ], 404);
        }

        // Get subjects assigned to this teacher
        $subjectIds = $teacher->subjects()->pluck('subjects.id');

        /*
         * Get marksheets where at least one marksheet item
         * belongs to one of the teacher's assigned subjects.
         */
        $query = Marksheet::with([
    'student',
    'items.subject'
])->whereHas('items', function ($query) use ($subjectIds) {
            $query->whereIn('subject_id', $subjectIds);
        });

        // Optional student search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('symbol_no', 'like', "%{$search}%");
            });
        }

        $marksheets = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json($marksheets);
    }
}
