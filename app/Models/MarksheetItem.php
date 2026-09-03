<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarksheetItem extends Model
{
    protected $fillable = [
        'marksheet_id',
        'subject_id',
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