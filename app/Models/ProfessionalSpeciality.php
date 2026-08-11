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
class ProfessionalSpeciality extends Model
{
    public function professionals()
    {
        return $this->hasMany(Professional::class, 'professional_speciality_code', 'code');
    }
}
