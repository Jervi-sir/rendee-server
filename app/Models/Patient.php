<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'date_of_birth',
    'gender',
    'wilaya_code',
    'commune_code',
    'address',
    'city',
    'medical_notes',
    'blood_type',
    'allergies',
    'chronic_diseases',
    'medications',
    'emergency_contacts',
])]

class Patient extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date:Y-m-d',
            'allergies' => 'array',
            'chronic_diseases' => 'array',
            'medications' => 'array',
            'emergency_contacts' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_code', 'code');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
