<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\ComplaintSubmitted;

class ComplaintController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'teacher_id' => 'nullable|exists:teachers,id',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    $student = $request->user()->student;

    if (!$student) {
        return response()->json([
            'message' => 'Student profile not found.',
        ], 404);
    }

    $complaint = \App\Models\Complaint::create([
        'student_id' => $student->id,
        'teacher_id' => $validated['teacher_id'] ?? null,
        'subject' => $validated['subject'],
        'message' => $validated['message'],
    ]);
    $admins = User::where('role','admin')->get();

    foreach($admins as $admin){
        $admin->notify(new ComplaintSubmitted($complaint));
    }
    if ($complaint->teacher_id) {
    $teacher = \App\Models\Teacher::find($complaint->teacher_id);

    if ($teacher) {
        $teacherUser = User::where('email', $teacher->email)
            ->where('role', 'teacher')
            ->first();

        if ($teacherUser) {
            $teacherUser->notify(new ComplaintSubmitted($complaint));
        }
    }
}

    return response()->json([
        'message' => 'Complaint submitted successfully.',
        'complaint' => $complaint,
    ], 201);
}
}
