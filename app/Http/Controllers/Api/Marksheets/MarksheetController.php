<?php

namespace App\Http\Controllers\Api\Marksheets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marksheet;
use App\Models\Student;

class MarksheetController extends Controller
{
    public function index()
{
    $marksheets = Marksheet::with([
        'student',
        'items.subject'
    ])
    ->latest()
    ->get();

    return response()->json($marksheets);
}
public function show(Marksheet $marksheet)
{
    $marksheet->load([
        'student',
        'items.subject'
    ]);

    return response()->json($marksheet);
}

public function store(Request $request)
{
    $validated=$request->validate([
        'student_id'=>'required|exists:students,id',
        'items'=>'required|array',
        'items.*.subject_id'=>'required | exists:subjects,id',
        'items.*.marks' => [
    'required',
    function ($attribute, $value, $fail) {

        if (strtoupper(trim($value)) === 'A') {
            return;
        }

        if (!is_numeric($value) || $value < 0 || $value > 100) {
            $fail('Marks must be between 0 and 100, or A for absent.');
        }

    },
],
    ]);
    $total = collect($validated['items'])
    ->sum(function ($item) {

        return strtoupper(trim($item['marks'])) === 'A'
            ? 0
            : (float) $item['marks'];

    });
    $subjectCount= count($validated['items']);

    $percentage=$subjectCount>0
    ? ($total/($subjectCount*100))*100
    :0;
    if ($percentage>=80){
        $grade='A+';
    }else if ($percentage>=70){
        $grade='A';
} else if($percentage>=60){
    $grade='B+';
}else if($percentage>=50){
    $grade='B';
}else if($percentage>=40){
    $grade='C';
}else if($percentage>=30){
    $grade='D';
}else{
    $grade='E';
}
$result=$percentage>=40
?'pass'
:'Fail';

$marksheet=Marksheet::create([
    'student_id' => $validated['student_id'],
        'total' => $total,
        'percentage' => $percentage,
        'grade' => $grade,
        'result' => $result,
    ]);
    foreach ($validated['items'] as $item) {
        $marksheet->items()->create([
            'subject_id' => $item['subject_id'],
            'marks' => $item['marks'],
        ]);
    }
    $marksheet->load([
        'student',
        'items.subject'
    ]);
    return response()->json($marksheet, 201);
}

public function update(Request $request, Marksheet $marksheet)
{
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',

        'items' => 'required|array|min:1',

        'items.*.subject_id' => 'required|exists:subjects,id',

        'items.*.marks' => [
            'required',
            function ($attribute, $value, $fail) {

                if (strtoupper(trim($value)) === 'A') {
                    return;
                }

                if (!is_numeric($value) || $value < 0 || $value > 100) {
                    $fail('Marks must be between 0 and 100, or A for absent.');
                }

            },
        ],
    ]);

    $total = collect($validated['items'])
        ->sum(function ($item) {

            return strtoupper(trim($item['marks'])) === 'A'
                ? 0
                : (float) $item['marks'];

        });

    $subjectCount = count($validated['items']);

    $percentage = $subjectCount > 0
        ? ($total / ($subjectCount * 100)) * 100
        : 0;

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

    $hasFailedSubject = collect($validated['items'])
        ->contains(function ($item) {

            $value = strtoupper(trim($item['marks']));

            if ($value === 'A') {
                return true;
            }

            return (float) $value < 40;

        });

    $result = $hasFailedSubject
        ? 'Fail'
        : 'Pass';

    $marksheet->update([
        'student_id' => $validated['student_id'],
        'total' => $total,
        'percentage' => $percentage,
        'grade' => $grade,
        'result' => $result,
    ]);

    $marksheet->items()->delete();

    foreach ($validated['items'] as $item) {

        $marksheet->items()->create([
            'subject_id' => $item['subject_id'],
            'marks' => strtoupper(trim($item['marks'])),
        ]);

    }

    $marksheet->load([
        'student',
        'items.subject'
    ]);

    return response()->json($marksheet);
}
public function destroy(Marksheet $marksheet)
{
    $marksheet->delete();

    return response()->json([
        'message' => 'Marksheet deleted successfully.'
    ]);
}
public function parentSearch(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'symbol_no' => 'required|string',
        'date_of_birth' => 'required|date',
    ]);

    $student = Student::where('name', $validated['name'])
        ->where('symbol_no', $validated['symbol_no'])
        ->whereDate('date_of_birth', $validated['date_of_birth'])
        ->first();

    if (!$student) {
        return response()->json([
            'message' => 'No student found with the provided details.'
        ], 404);
    }

    $marksheet = Marksheet::with([
        'student',
        'items.subject'
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