<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request)
    {
        $query = Teacher::with('subjects');

        // Search by name, email or phone
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');

            });
        }

        $teachers = $query
            ->latest()
            ->paginate(10);

        return response()->json($teachers);
    }

    public function forStudents()
    {
        $teachers=Teacher::query()
        ->select('id','name')
        ->orderBy('name')
        ->get();
        return response()->json($teachers);
    }


    /**
     * Store a newly created teacher.
     *
     * This creates:
     * 1. Teacher profile
     * 2. User login account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:teachers,email',
                'unique:users,email',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'class' => [
                'required',
                'integer',
                'between:1,10',
                'unique:teachers,class',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

        ]);

        $teacher = DB::transaction(function () use ($validated) {

            /*
             * Create teacher profile.
             */
            $teacher = Teacher::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'class' => $validated['class'],
            ]);


            /*
             * Create teacher login account.
             */
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(
                    $validated['password']
                ),
                'role' => 'teacher',
            ]);


            return $teacher;
        });

        return response()->json([
            'message' =>
                'Teacher and login account created successfully.',
            'teacher' => $teacher->load('subjects'),
        ], 201);
    }


    /**
     * Display the specified teacher.
     */
    public function show(string $id)
    {
        $teacher = Teacher::with('subjects')
            ->findOrFail($id);

        return response()->json($teacher);
    }


    /**
     * Update the specified teacher.
     */
    public function update(Request $request, string $id)
    {
        $teacher = Teacher::findOrFail($id);

        $user = User::where(
            'email',
            $teacher->email
        )->first();

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                Rule::unique('teachers', 'email')
                    ->ignore($teacher->id),

                Rule::unique('users', 'email')
                    ->ignore($user?->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'class' => [
                'required',
                'integer',
                'between:1,10',
                Rule::unique('teachers', 'class')->ignore($teacher->id),
            ],

            /*
             * Optional while editing.
             * Empty means keep the existing password.
             */
            'password' => [
                'nullable',
                'string',
                'min:8',
            ],

        ]);

        DB::transaction(function () use (
            $teacher,
            $user,
            $validated
        ) {

            $oldEmail = $teacher->email;

            /*
             * Update teacher profile.
             */
            $teacher->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'class' => $validated['class'],
            ]);


            /*
             * Update existing login account.
             */
            if ($user) {

                $user->name =
                    $validated['name'];

                $user->email =
                    $validated['email'];

                $user->role = 'teacher';

                /*
                 * Only change password if a new
                 * password was provided.
                 */
                if (
                    !empty($validated['password'])
                ) {

                    $user->password =
                        Hash::make(
                            $validated['password']
                        );
                }

                $user->save();

            } else {

                /*
                 * Safety fallback:
                 * If the teacher exists but has no
                 * login account, create one.
                 */
                if (
                    empty($validated['password'])
                ) {

                    throw new \Exception(
                        'This teacher does not have a login account. Please enter a password.'
                    );
                }

                User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make(
                        $validated['password']
                    ),
                    'role' => 'teacher',
                ]);
            }
        });

        return response()->json([
            'message' =>
                'Teacher updated successfully.',
            'teacher' => $teacher->load('subjects'),
        ]);
    }


    
/**
 * Assign subjects to a teacher.
 */
public function assignSubjects(Request $request, string $id)
{
    $teacher = Teacher::findOrFail($id);

    $validated = $request->validate([
        'subject_ids' => ['required', 'array'],
        'subject_ids.*' => ['integer', 'exists:subjects,id'],
    ]);

    $teacher->subjects()->sync($validated['subject_ids']);

    return response()->json([
        'message' => 'Teacher subjects updated successfully.',
        'teacher' => $teacher->load('subjects'),
    ]);
}



    /**
     * Remove the specified teacher.
     */
    public function destroy(string $id)
    {
        $teacher = Teacher::findOrFail($id);

        DB::transaction(function () use ($teacher) {

            /*
             * Delete teacher login account.
             */
            User::where(
                'email',
                $teacher->email
            )->delete();


            /*
             * Delete teacher profile.
             */
            $teacher->delete();
        });

        return response()->json([
            'message' =>
                'Teacher and login account deleted successfully.',
        ]);
    }
}