<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'relationship',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}