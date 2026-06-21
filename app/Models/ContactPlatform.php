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

class ContactPlatform extends Model
{
    public function doctorContacts()
    {
        return $this->hasMany(DoctorContact::class, 'platform_code', 'code');
    }

    public function centerContacts()
    {
        return $this->hasMany(CenterContact::class, 'platform_code', 'code');
    }
}
