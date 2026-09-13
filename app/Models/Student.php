<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'name',
        'class',
        'symbol_no',
        'date_of_birth',
        'email',
        'phone',
        'status',
        'photo',
        'parent_id',
        'address_id',
    ];

    // Student belongs to one parent
    public function parent()
    {
        return $this->belongsTo(
            StudentParent::class,
            'parent_id'
        );
    }

    // Student belongs to one address
    public function address()
    {
        return $this->belongsTo(
            Address::class,
            'address_id'
        );
    }

    public function marksheets()
    {
        return $this->hasMany(Marksheet::class, 'student_id');
    }

    // Student can have many subjects
    public function subjects()
{
    return $this->belongsToMany(
        Subject::class,
        'student_subject',
        'student_id',
        'subject_id'
    )->withPivot('subject_name');
}

public function studentSubjectAssignments()
{
    return $this->hasMany(
        StudentSubject::class,
        'student_id'
    );
}

public function manualSubjects()
{
    return $this->hasMany(
        StudentSubject::class,
        'student_id'
    )->whereNull('subject_id');
}
}