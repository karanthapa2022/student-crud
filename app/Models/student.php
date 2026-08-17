<?php

namespace App\Models;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable=[
        'name',
        'email',
        'phone',
        'status',
        ];
}
