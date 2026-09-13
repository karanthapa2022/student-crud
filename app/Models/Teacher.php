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
}