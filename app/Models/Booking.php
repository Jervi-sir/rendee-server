<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'reference',
    'patient_id',
    'bookable_type',
    'bookable_id',
    'center_service_id',
    'doctor_service_id',
    'doctor_schedule_id',
    'center_working_hour_id',
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
    | Center
    |--------------------------------------------------------------------------
    */
    public function centerService()
    {
        return $this->belongsTo(CenterService::class);
    }

    public function centerWorkingHour()
    {
        return $this->belongsTo(CenterWorkingHour::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Doctor
    |--------------------------------------------------------------------------
    */
    public function doctorService()
    {
        return $this->belongsTo(DoctorService::class);
    }

    public function doctorSchedule()
    {
        return $this->belongsTo(DoctorSchedule::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Other
    |--------------------------------------------------------------------------
    */
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_code', 'code');
    }

    public function bookable()
    {
        return $this->morphTo();
    }

    public function bookingHistories()
    {
        return $this->hasMany(BookingHistory::class);
    }

}
