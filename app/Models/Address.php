<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'province',
        'district',
        'municipality',
        'ward',
        'city',
        'street',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'address_id');
    }
}