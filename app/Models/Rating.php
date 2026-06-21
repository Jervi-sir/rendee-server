<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'booking_id',
    'patient_id',
    'rating',
    'review',
    'reviewable_type',
    'reviewable_id',
])]

class Rating extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function reviewable()
    {
        return $this->morphTo();
    }
}
