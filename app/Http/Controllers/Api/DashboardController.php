<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Teacher;
use App\Models\Subject;

class DashboardController extends Controller
{
    public function statistics()
    {
        return response()->json([
            'totalStudents' => Student::count(),
            'totalParents' => StudentParent::count(),
            'totalTeachers' => Teacher::count(),
            'totalSubjects' => Subject::count(),
        ]);
    }
}