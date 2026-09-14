<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'class',
    ];

    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'subject_teacher',
            'teacher_id',
            'subject_id'
        );
    }

    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'student_teacher',
            'teacher_id',
            'student_id'
        );
    }
}