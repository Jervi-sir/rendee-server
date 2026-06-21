<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'center_id',
    'slot_date',
    'start_time',
    'end_time',
    'is_available',
])]

class CenterWorkingHour extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot_date' => 'date:Y-m-d',
            'start_time' => 'string',
            'end_time' => 'string',
            'is_available' => 'boolean',
        ];
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'center_working_hour_id');
    }
}
