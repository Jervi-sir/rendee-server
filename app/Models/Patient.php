<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'date_of_birth',
    'gender',
    'wilaya_code',
    'commune_id',
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
    use SoftDeletes;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
