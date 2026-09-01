<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'student_subject',
            'subject_id',
            'student_id'
        );
    }

    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'subject_teacher',
            'subject_id',
            'teacher_id'
        );
    }
}