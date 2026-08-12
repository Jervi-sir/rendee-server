<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'en',
    'fr',
    'ar',
])]
class ProfessionalSpeciality extends Model
{
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'professional_speciality_code', 'code');
    }

    public function professionals(): HasMany
    {
        return $this->partners();
    }
}
