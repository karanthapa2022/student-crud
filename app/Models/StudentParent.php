<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'relationship',
        'address_id',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
    public function user()
{
    return $this->hasOne(User::class, 'parent_id');
}

        public function address()
        {
            return $this->belongsTo(Address::class, 'address_id');
        }
}
