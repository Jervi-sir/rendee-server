<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'professional_id',
    'day_of_week',
    'start_time',
    'end_time',
    'is_active',
])]
class ProfessionalSchedule extends Model
{
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'start_time' => 'string',
            'end_time' => 'string',
            'is_active' => 'boolean',
        ];
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }
}
