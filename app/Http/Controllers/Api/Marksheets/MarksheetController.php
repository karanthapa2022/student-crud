<?php

namespace App\Http\Controllers\Api\Marksheets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

        if ($user->role === 'student') {
            $student = Student::where('email', $user->email)->first();

            if (!$student) {
                return response()->json([]);
            }

            return response()->json(Marksheet::with(['student', 'items'])
                ->where('student_id', $student->id)
                ->latest()
                ->get());
        }

        // Admin and teacher can view all marksheets
        if (in_array($user->role, ['admin', 'teacher'])) {

            $query = Marksheet::with([
                'student',
                'items'
            ])
            ->when($request->filled('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->integer('student_id'));
            })
            ->latest();

            return response()->json($query->get());
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

        // Student can only view their own marksheet
        if ($user->role === 'student') {
            $student = Student::where('email', $user->email)->first();

            if (!$student || $marksheet->student_id !== $student->id) {
                return response()->json([
                    'message' => 'You do not have permission to view this marksheet.'
                ], 403);
            }
        }

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

        // Only admin, teacher, student and authorized parent can continue
        if (!in_array($user->role, ['admin', 'teacher', 'student', 'parent'])) {
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

            'items.*.subject_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('student_subject', 'subject_id')->where(
                    fn ($query) => $query->where('student_id', $request->input('student_id'))
                ),
            ],

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
                'subject_id' => $item['subject_id'],
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

            'items.*.subject_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('student_subject', 'subject_id')->where(
                    fn ($query) => $query->where('student_id', $request->input('student_id'))
                ),
            ],

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
                'subject_id' => $item['subject_id'],
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

    public function import(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'teacher'], true)) {
            return response()->json(['message' => 'Only admin and teacher can import marksheets.'], 403);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $sheet = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        $headers = array_map(static fn ($header) => strtolower(trim((string) $header)), array_shift($rows) ?? []);
        $required = ['subject_name', 'full_marks', 'pass_marks', 'marks'];
        $missing = array_values(array_diff($required, $headers));
        if (!in_array('student_id', $headers, true) && !in_array('student_email', $headers, true)) {
            $missing[] = 'student_id or student_email';
        }

        if ($missing) {
            return response()->json(['message' => 'Missing required columns: ' . implode(', ', $missing)], 422);
        }

        $column = array_flip($headers);
        $groups = [];
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            $line = $rowNumber + 2;
            $studentId = trim((string) ($row[$this->excelColumn($column, 'student_id')] ?? ''));
            $studentEmail = strtolower(trim((string) ($row[$this->excelColumn($column, 'student_email')] ?? '')));
            $subjectName = trim((string) ($row[$this->excelColumn($column, 'subject_name')] ?? ''));
            $fullMarks = trim((string) ($row[$this->excelColumn($column, 'full_marks')] ?? ''));
            $passMarks = trim((string) ($row[$this->excelColumn($column, 'pass_marks')] ?? ''));
            $marks = strtoupper(trim((string) ($row[$this->excelColumn($column, 'marks')] ?? '')));

            if (!$studentId && !$studentEmail) {
                $errors[] = "Row {$line}: student_id or student_email is required.";
                continue;
            }

            $student = $studentId
                ? Student::find($studentId)
                : Student::where('email', $studentEmail)->first();

            if (!$student) {
                $errors[] = "Row {$line}: student was not found.";
                continue;
            }

            if (!$subjectName || !is_numeric($fullMarks) || (float) $fullMarks <= 0 || !is_numeric($passMarks) || (float) $passMarks < 0) {
                $errors[] = "Row {$line}: subject and valid full_marks/pass_marks are required.";
                continue;
            }

            if ((float) $passMarks > (float) $fullMarks || ($marks !== 'A' && (!is_numeric($marks) || (float) $marks < 0 || (float) $marks > (float) $fullMarks))) {
                $errors[] = "Row {$line}: marks must be A or a number between 0 and full_marks, and pass_marks cannot exceed full_marks.";
                continue;
            }

            $groupKey = (string) $student->id;
            $subjectKey = strtolower($subjectName);
            if (isset($groups[$groupKey]['subjects'][$subjectKey])) {
                $errors[] = "Row {$line}: duplicate subject '{$subjectName}' for student {$student->id}.";
                continue;
            }

            $groups[$groupKey]['student'] = $student;
            $groups[$groupKey]['subjects'][$subjectKey] = true;
            $groups[$groupKey]['items'][] = [
                'subject_name' => $subjectName,
                'full_marks' => (float) $fullMarks,
                'pass_marks' => (float) $passMarks,
                'marks' => $marks,
            ];
        }

        foreach ($groups as $group) {
            if (Marksheet::where('student_id', $group['student']->id)->exists()) {
                $errors[] = "Student {$group['student']->id} already has a marksheet. Duplicate entry rejected.";
            }
        }

        if ($errors) {
            return response()->json([
                'message' => 'Import rejected. No marksheets were saved.',
                'errors' => $errors,
            ], 422);
        }

        DB::transaction(function () use ($groups) {
            foreach ($groups as $group) {
                $this->createFromItems($group['student']->id, $group['items']);
            }
        });

        return response()->json([
            'message' => count($groups) . ' marksheet(s) imported successfully.',
            'count' => count($groups),
        ], 201);
    }

    public function export(Request $request, ?Marksheet $marksheet = null)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'teacher'], true)) {
            return response()->json(['message' => 'Only admin and teacher can export marksheets.'], 403);
        }

        $marksheets = $marksheet
            ? collect([$marksheet->load(['student', 'items'])])
            : Marksheet::with(['student', 'items'])->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([['marksheet_id', 'student_id', 'student_email', 'student_name', 'class', 'subject_name', 'full_marks', 'pass_marks', 'marks', 'total', 'percentage', 'grade', 'result']], null, 'A1');
        $outputRow = 2;

        foreach ($marksheets as $item) {
            foreach ($item->items as $markItem) {
                $sheet->fromArray([[
                    $item->id,
                    $item->student_id,
                    $item->student?->email,
                    $item->student?->name,
                    $item->student?->class,
                    $markItem->subject_name,
                    $markItem->full_marks,
                    $markItem->pass_marks,
                    $markItem->marks,
                    $item->total,
                    $item->percentage,
                    $item->grade,
                    $item->result,
                ]], null, "A{$outputRow}");
                $outputRow++;
            }
        }

        $sheet->freezePane('A2');
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = $marksheet ? "marksheet-{$marksheet->id}.xlsx" : 'marksheets.xlsx';
        return response()->streamDownload(static function () use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function excelColumn(array $columns, string $name): string
    {
        return (string) ($columns[$name] ?? '');
    }

    private function createFromItems(int $studentId, array $items): Marksheet
    {
        $total = collect($items)->sum(fn ($item) => $item['marks'] === 'A' ? 0 : (float) $item['marks']);
        $fullMarks = collect($items)->sum(fn ($item) => (float) $item['full_marks']);
        $percentage = $fullMarks > 0 ? ($total / $fullMarks) * 100 : 0;
        $grade = match (true) {
            $percentage >= 80 => 'A+',
            $percentage >= 70 => 'A',
            $percentage >= 60 => 'B+',
            $percentage >= 50 => 'B',
            $percentage >= 40 => 'C',
            $percentage >= 30 => 'D',
            default => 'F',
        };
        $result = collect($items)->contains(fn ($item) => $item['marks'] === 'A' || (float) $item['marks'] < (float) $item['pass_marks']) ? 'Fail' : 'Pass';
        $marksheet = Marksheet::create([
            'student_id' => $studentId,
            'total' => $total,
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'result' => $result,
        ]);
        foreach ($items as $item) {
            $marksheet->items()->create($item + ['subject_id' => null]);
        }
        return $marksheet;
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