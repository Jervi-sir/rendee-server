<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'partner_id',
    'day_of_week',
    'start_time',
    'end_time',
    'is_active',
    'morning_start_time',
    'morning_end_time',
    'morning_is_active',
    'evening_start_time',
    'evening_end_time',
    'evening_is_active',
])]
class PartnerSchedule extends Model
{
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
            'morning_is_active' => 'boolean',
            'evening_is_active' => 'boolean',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
