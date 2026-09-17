<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Marksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? 'admin';

        $payload = [
            'role' => $role,
            'message' => 'Dashboard loaded successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
            ],
            'stats' => [],
        ];

        if ($role === 'teacher') {
            $teacher = Teacher::with('subjects')
                ->where('email', $user->email)
                ->first();

            $payload['stats'] = [
                'students' => Student::count(),
                'subjects' => $teacher?->subjects()->count() ?? 0,
                'marksheets' => Marksheet::count(),
            ];

            $payload['profile'] = $teacher ? [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
            ] : null;

            return response()->json($payload);
        }

        if ($role === 'student') {
            $student = Student::with('subjects')
                ->where('email', $user->email)
                ->first();

            $payload['stats'] = [
                'subjects' => $student?->subjects()->count() ?? 0,
                'marksheets' => $student
                    ? Marksheet::where('student_id', $student->id)->count()
                    : 0,
                'status' => $student?->status ?? 'active',
            ];

            $payload['profile'] = $student ? [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'class' => $student->class,
                'status' => $student->status,
            ] : null;

            $payload['marksheets'] = $student
                ? Marksheet::where('student_id', $student->id)
                    ->latest()
                    ->take(5)
                    ->get(['id', 'percentage', 'grade', 'result', 'created_at'])
                : [];

            return response()->json($payload);
        }

        if ($role === 'parent') {
            $parent = $user->parent;
            $students = $parent?->students()->get() ?? collect();
            $studentIds = $students->pluck('id');

            $payload['stats'] = [
                'children' => $students->count(),
                'marksheets' => Marksheet::whereIn('student_id', $studentIds)->count(),
            ];
            $payload['profile'] = $parent ? [
                'id' => $parent->id,
                'name' => $parent->name,
                'email' => $parent->email,
                'phone' => $parent->phone,
                'relationship' => $parent->relationship,
            ] : null;
            $payload['children'] = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'class' => $student->class,
                    'status' => $student->status,
                    'marksheets' => Marksheet::where('student_id', $student->id)->count(),
                ];
            })->values();

            return response()->json($payload);
        }

        $payload['stats'] = [
            'totalStudents' => Student::count(),
            'totalParents' => StudentParent::count(),
            'totalTeachers' => Teacher::count(),
            'totalSubjects' => Subject::count(),
        ];

        return response()->json($payload);
    }

    public function statistics()
    {
        $statistics=Cache::remember('dashboard_statistics',60,function(){
        return[
            'totalStudents'=> Student::count(),
            'totalParents'=> StudentParent::count(),
            'totalTeachers'=> Teacher::count(),
            'TotalSubjects'=> Subject::count(),

        ];
        });
        return response()->json($statistics);
    }
}