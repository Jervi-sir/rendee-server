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

class Status extends Model
{
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'status_code', 'code');
    }

    public function bookingHistories()
    {
        return $this->hasMany(BookingHistory::class, 'status_code', 'code');
    }
}
