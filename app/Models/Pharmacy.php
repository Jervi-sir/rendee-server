<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'name',
    'wilaya_code',
    'location',
    'bio',
    'latitude',
    'longitude',
    'is_available',
])]
class Pharmacy extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format pharmacy details for patient-facing APIs.
     *
     * @param bool $detailed
     * @return array
     */
    public function formatForPatient(bool $detailed = false): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? $this->user?->name ?? 'صيدلية',
            'location' => $this->location,
            'bio' => $this->bio ?? 'صيدلية تقدم الخدمات الدوائية والصيدلانية للمرضى.',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_available' => $this->is_available,
            'wilaya_code' => $this->wilaya_code,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
            ] : null,
        ];
    }
}
