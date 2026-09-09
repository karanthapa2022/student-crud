<?php

namespace App\Http\Controllers\Api\Marksheets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marksheet;
use App\Models\Student;

class MarksheetController extends Controller
{
    // =========================================================
    // INDEX
    // =========================================================

    public function index(Request $request)
    {
        $user = $request->user();

        // Parent can only see their children's marksheets
        if ($user->role === 'parent') {

            if (!$user->parent_id) {
                return response()->json([
                    'message' => 'Parent account is not properly linked.'
                ], 403);
            }

            $studentIds = Student::where(
                'parent_id',
                $user->parent_id
            )->pluck('id');

            $marksheets = Marksheet::with([
                'student',
                'items'
            ])
            ->whereIn('student_id', $studentIds)
            ->latest()
            ->get();

            return response()->json($marksheets);
        }

        // Admin and teacher can view marksheets
        if (in_array($user->role, ['admin', 'teacher'])) {

            $marksheets = Marksheet::with([
                'student',
                'items'
            ])
            ->latest()
            ->get();

            return response()->json($marksheets);
        }

        return response()->json([
            'message' => 'Unauthorized.'
        ], 403);
    }


    // =========================================================
    // SHOW
    // =========================================================

    public function show(
        Request $request,
        Marksheet $marksheet
    ) {
        $user = $request->user();

        // Parent can only view their own child's marksheet
        if ($user->role === 'parent') {

            if (!$user->parent_id) {
                return response()->json([
                    'message' => 'Parent account is not properly linked.'
                ], 403);
            }

            $belongsToParent = Student::where('id', $marksheet->student_id)
                ->where('parent_id', $user->parent_id)
                ->exists();

            if (!$belongsToParent) {
                return response()->json([
                    'message' => 'You do not have permission to view this marksheet.'
                ], 403);
            }
        }

        // Only admin, teacher and authorized parent can continue
        if (!in_array($user->role, ['admin', 'teacher', 'parent'])) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $marksheet->load([
            'student',
            'items'
        ]);

        return response()->json($marksheet);
    }


    // =========================================================
    // STORE
    // =========================================================

    public function store(Request $request)
    {
        $user = $request->user();

        // Only admin and teacher can create marksheets
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'message' => 'You do not have permission to create marksheets.'
            ], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',

            'items' => 'required|array|min:1',

            'items.*.subject_name' => 'required|string|max:255',

            'items.*.full_marks' => [
                'required',
                'numeric',
                'min:1',
            ],

            'items.*.pass_marks' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.marks' => [
                'required',
                function ($attribute, $value, $fail) {

                    if (strtoupper(trim($value)) === 'A') {
                        return;
                    }

                    if (!is_numeric($value) || $value < 0) {
                        $fail(
                            'Marks must be a valid number or A for absent.'
                        );
                    }
                },
            ],
        ]);

        // Validate pass marks and obtained marks against full marks
        foreach ($validated['items'] as $index => $item) {

            $fullMarks = (float) $item['full_marks'];
            $passMarks = (float) $item['pass_marks'];
            $marks = strtoupper(trim($item['marks']));

            if ($passMarks > $fullMarks) {
                return response()->json([
                    'message' => "Pass marks cannot be greater than full marks for subject " . ($index + 1) . "."
                ], 422);
            }

            if ($marks !== 'A' && (float) $marks > $fullMarks) {
                return response()->json([
                    'message' => "Obtained marks cannot be greater than full marks for subject " . ($index + 1) . "."
                ], 422);
            }
        }

        // =====================================================
        // CALCULATE TOTAL
        // =====================================================

        $total = collect($validated['items'])
            ->sum(function ($item) {

                return strtoupper(trim($item['marks'])) === 'A'
                    ? 0
                    : (float) $item['marks'];
            });

        // Total possible marks
        $totalFullMarks = collect($validated['items'])
            ->sum(function ($item) {
                return (float) $item['full_marks'];
            });

        // =====================================================
        // CALCULATE PERCENTAGE
        // =====================================================

        $percentage = $totalFullMarks > 0
            ? ($total / $totalFullMarks) * 100
            : 0;

        // =====================================================
        // GRADE
        // =====================================================

        if ($percentage >= 80) {
            $grade = 'A+';
        } elseif ($percentage >= 70) {
            $grade = 'A';
        } elseif ($percentage >= 60) {
            $grade = 'B+';
        } elseif ($percentage >= 50) {
            $grade = 'B';
        } elseif ($percentage >= 40) {
            $grade = 'C';
        } elseif ($percentage >= 30) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }

        // =====================================================
        // RESULT
        // =====================================================

        $hasFailedSubject = collect($validated['items'])
            ->contains(function ($item) {

                $marks = strtoupper(trim($item['marks']));
                $passMarks = (float) $item['pass_marks'];

                // Absent = Fail
                if ($marks === 'A') {
                    return true;
                }

                return (float) $marks < $passMarks;
            });

        $result = $hasFailedSubject
            ? 'Fail'
            : 'Pass';

        // =====================================================
        // CREATE MARKSHEET
        // =====================================================

        $marksheet = Marksheet::create([
            'student_id' => $validated['student_id'],
            'total' => $total,
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'result' => $result,
        ]);

        // =====================================================
        // CREATE ITEMS
        // =====================================================

        foreach ($validated['items'] as $item) {

            $marksheet->items()->create([
                'subject_id' => null,
                'subject_name' => trim($item['subject_name']),
                'full_marks' => $item['full_marks'],
                'pass_marks' => $item['pass_marks'],
                'marks' => strtoupper(trim($item['marks'])),
            ]);
        }

        $marksheet->load([
            'student',
            'items'
        ]);

        return response()->json($marksheet, 201);
    }


    // =========================================================
    // UPDATE
    // =========================================================

    public function update(
        Request $request,
        Marksheet $marksheet
    ) {
        $user = $request->user();

        // Only admin and teacher can update
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'message' => 'You do not have permission to update marksheets.'
            ], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',

            'items' => 'required|array|min:1',

            'items.*.subject_name' => 'required|string|max:255',

            'items.*.full_marks' => [
                'required',
                'numeric',
                'min:1',
            ],

            'items.*.pass_marks' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.marks' => [
                'required',
                function ($attribute, $value, $fail) {

                    if (strtoupper(trim($value)) === 'A') {
                        return;
                    }

                    if (!is_numeric($value) || $value < 0) {
                        $fail(
                            'Marks must be a valid number or A for absent.'
                        );
                    }
                },
            ],
        ]);

        // Validate pass marks and obtained marks
        foreach ($validated['items'] as $index => $item) {

            $fullMarks = (float) $item['full_marks'];
            $passMarks = (float) $item['pass_marks'];
            $marks = strtoupper(trim($item['marks']));

            if ($passMarks > $fullMarks) {
                return response()->json([
                    'message' => "Pass marks cannot be greater than full marks for subject " . ($index + 1) . "."
                ], 422);
            }

            if ($marks !== 'A' && (float) $marks > $fullMarks) {
                return response()->json([
                    'message' => "Obtained marks cannot be greater than full marks for subject " . ($index + 1) . "."
                ], 422);
            }
        }

        // =====================================================
        // CALCULATE TOTAL
        // =====================================================

        $total = collect($validated['items'])
            ->sum(function ($item) {

                return strtoupper(trim($item['marks'])) === 'A'
                    ? 0
                    : (float) $item['marks'];
            });

        // Total possible marks
        $totalFullMarks = collect($validated['items'])
            ->sum(function ($item) {
                return (float) $item['full_marks'];
            });

        // =====================================================
        // CALCULATE PERCENTAGE
        // =====================================================

        $percentage = $totalFullMarks > 0
            ? ($total / $totalFullMarks) * 100
            : 0;

        // =====================================================
        // GRADE
        // =====================================================

        if ($percentage >= 80) {
            $grade = 'A+';
        } elseif ($percentage >= 70) {
            $grade = 'A';
        } elseif ($percentage >= 60) {
            $grade = 'B+';
        } elseif ($percentage >= 50) {
            $grade = 'B';
        } elseif ($percentage >= 40) {
            $grade = 'C';
        } elseif ($percentage >= 30) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }

        // =====================================================
        // RESULT
        // =====================================================

        $hasFailedSubject = collect($validated['items'])
            ->contains(function ($item) {

                $marks = strtoupper(trim($item['marks']));
                $passMarks = (float) $item['pass_marks'];

                if ($marks === 'A') {
                    return true;
                }

                return (float) $marks < $passMarks;
            });

        $result = $hasFailedSubject
            ? 'Fail'
            : 'Pass';

        // =====================================================
        // UPDATE MARKSHEET
        // =====================================================

        $marksheet->update([
            'student_id' => $validated['student_id'],
            'total' => $total,
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'result' => $result,
        ]);

        // Delete old items
        $marksheet->items()->delete();

        // Create new items
        foreach ($validated['items'] as $item) {

            $marksheet->items()->create([
                'subject_id' => null,
                'subject_name' => trim($item['subject_name']),
                'full_marks' => $item['full_marks'],
                'pass_marks' => $item['pass_marks'],
                'marks' => strtoupper(trim($item['marks'])),
            ]);
        }

        $marksheet->load([
            'student',
            'items'
        ]);

        return response()->json($marksheet);
    }


    // =========================================================
    // DESTROY
    // =========================================================

    public function destroy(
        Request $request,
        Marksheet $marksheet
    ) {
        $user = $request->user();

        // Only admin can delete
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'You do not have permission to delete marksheets.'
            ], 403);
        }

        $marksheet->delete();

        return response()->json([
            'message' => 'Marksheet deleted successfully.'
        ]);
    }


    // =========================================================
    // PARENT SEARCH
    // =========================================================

    public function parentSearch(Request $request)
    {
        $user = $request->user();

        // Only parents can use this endpoint
        if ($user->role !== 'parent') {
            return response()->json([
                'message' => 'Only parents can use this search.'
            ], 403);
        }

        if (!$user->parent_id) {
            return response()->json([
                'message' => 'Parent account is not properly linked.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'symbol_no' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        // Search only among this parent's children
        $student = Student::where(
                'parent_id',
                $user->parent_id
            )
            ->where('name', $validated['name'])
            ->where('symbol_no', $validated['symbol_no'])
            ->whereDate(
                'date_of_birth',
                $validated['date_of_birth']
            )
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'No student found with the provided details.'
            ], 404);
        }

        $marksheet = Marksheet::with([
            'student',
            'items'
        ])
        ->where('student_id', $student->id)
        ->latest()
        ->first();

        if (!$marksheet) {
            return response()->json([
                'message' => 'No marksheet found for this student.'
            ], 404);
        }

        return response()->json($marksheet);
    }
    
// =========================================================
// PARENT STUDENT MARKSHEET
// =========================================================

public function parentStudentMarksheet(
    Request $request,
    Student $student
) {
    $user = $request->user();

    // Only parents can use this endpoint
    if ($user->role !== 'parent') {
        return response()->json([
            'message' => 'Only parents can view student marksheets.'
        ], 403);
    }

    // Parent account must be linked
    if (!$user->parent_id) {
        return response()->json([
            'message' => 'Parent account is not properly linked.'
        ], 403);
    }

    // Make sure this student belongs to the logged-in parent
    if ($student->parent_id !== $user->parent_id) {
        return response()->json([
            'message' => 'You do not have permission to view this student.'
        ], 403);
    }

    // Get the latest marksheet for this student
    $marksheet = Marksheet::with([
        'student',
        'items'
    ])
    ->where('student_id', $student->id)
    ->latest()
    ->first();

    if (!$marksheet) {
        return response()->json([
            'message' => 'No marksheet found for this student.'
        ], 404);
    }

    return response()->json($marksheet);
}


}