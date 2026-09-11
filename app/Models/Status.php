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
class Status extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'status_code', 'code');
    }

    public function bookingHistories(): HasMany
    {
        return $this->hasMany(BookingHistory::class, 'status_code', 'code');
    }
}
