<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarksheetItem extends Model
{
    protected $fillable = [
        'marksheet_id',
        'subject_id',
        'subject_name',
        'full_marks',
        'pass_marks',
        'marks',
    ];

    public function marksheet()
    {
        return $this->belongsTo(Marksheet::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
