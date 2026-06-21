<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'doctor_id',
    'platform_code',
    'url',
])]

class DoctorContact extends Model
{
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function platform()
    {
        return $this->belongsTo(ContactPlatform::class, 'platform_code', 'code');
    }
}
