<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display all students.
     */
    public function index()
    {
        $students = Student::all();

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|string|max:20',
        ]);

        Student::create($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing a student.
     */
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update a single student.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'phone' => 'required|string|max:20',
        ]);

        $student->update($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Delete a single student.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Show the bulk edit page for selected students.
     */
    public function bulkEdit(Request $request)
    {
        // Get selected student IDs
        $selectedIds = $request->input('students', []);

        // Check if at least one student is selected
        if (empty($selectedIds)) {
            return redirect()
                ->route('students.index')
                ->with('error', 'Please select at least one student.');
        }

        // Get selected students
        $students = Student::whereIn('id', $selectedIds)->get();

        // Show bulk edit page
        return view('students.bulk-edit', compact('students'));
    }

    /**
     * Update multiple selected students.
     */
    public function bulkUpdate(Request $request)
    {
        $students = $request->input('students', []);

        // Check if students were submitted
        if (empty($students)) {
            return redirect()
                ->route('students.index')
                ->with('error', 'No students were selected.');
        }

        // Update each selected student
        foreach ($students as $id => $data) {

            $student = Student::find($id);

            if ($student) {

                $student->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ]);
            }
        }

        return redirect()
            ->route('students.index')
            ->with('success', 'Selected students updated successfully.');
    }

    /**
     * Delete multiple selected students.
     */
    public function bulkDelete(Request $request)
    {
        // Get selected student IDs
        $selectedIds = $request->input('students', []);

        // Check if at least one student is selected
        if (empty($selectedIds)) {
            return redirect()
                ->route('students.index')
                ->with('error', 'Please select at least one student.');
        }

        // Delete selected students
        Student::whereIn('id', $selectedIds)->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Selected students deleted successfully.');
    }
}