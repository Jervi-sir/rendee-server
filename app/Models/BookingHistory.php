<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'status_code',
    'notes',
    'changed_by',
])]
class BookingHistory extends Model
{
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_code', 'code');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
