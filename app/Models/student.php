<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
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

    // Student can have many subjects
    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'student_subject',
            'student_id',
            'subject_id'
        );
    }
}