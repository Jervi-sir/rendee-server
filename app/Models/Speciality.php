<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'en',
    'fr',
    'ar',
])]

class Speciality extends Model
{
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'speciality_code', 'code');
    }
}
