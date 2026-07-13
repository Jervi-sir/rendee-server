<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'reference',
    'patient_id',
    'bookable_type',
    'bookable_id',
    'service_type',
    'service_id',
    'schedule_type',
    'schedule_id',
    'patient_name',
    'patient_phone',
    'booking_date',
    'booking_time',
    'status_code',
    'is_center',
    'proposed_date',
    'proposed_time',
    'has_pending_proposal',
    'notes',
])]

class Booking extends Model
{
    public const TYPE_PROFESSIONAL = 'professional';
    public const TYPE_CENTER = 'center';

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'booking_time' => 'string',
            'proposed_date' => 'date:Y-m-d',
            'proposed_time' => 'string',
            'is_center' => 'boolean',
            'has_pending_proposal' => 'boolean',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Polymorphic Relations
    |--------------------------------------------------------------------------
    */
    public function bookable()
    {
        return $this->morphTo();
    }

    public function service()
    {
        return $this->morphTo();
    }

    public function schedule()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Common Relations
    |--------------------------------------------------------------------------
    */
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_code', 'code');
    }

    public function bookingHistories()
    {
        return $this->hasMany(BookingHistory::class);
    }
}
