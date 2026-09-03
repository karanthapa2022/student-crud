<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marksheet extends Model
{
    protected $fillable = [
        'student_id',
        'total',
        'percentage',
        'grade',
        'result',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function items()
    {
        return $this->hasMany(MarksheetItem::class);
    }
}