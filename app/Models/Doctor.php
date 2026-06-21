<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'speciality_code',
    'license_number',
    'years_experience',
    'phone_public',
    'bio',
    'address',
    'city',
    'is_available'
])]

class Doctor extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Speciality::class, 'speciality_code', 'code');
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function contacts()
    {
        return $this->hasMany(DoctorContact::class);
    }

    public function services()
    {
        return $this->hasMany(DoctorService::class);
    }
}
