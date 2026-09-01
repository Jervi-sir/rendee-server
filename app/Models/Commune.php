<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'wilaya_id',
    'wilaya_code',
    'code',
    'postal_code',
    'en',
    'fr',
    'ar',
    'latitude',
    'longitude',
    'lat',
    'lng',
])]
class Commune extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }

    public function getLatAttribute(): ?float
    {
        return $this->getLatitudeAttribute();
    }

    public function getLngAttribute(): ?float
    {
        return $this->getLongitudeAttribute();
    }

    public function getLatitudeAttribute(): ?float
    {
        if (isset($this->attributes['latitude']) && $this->attributes['latitude'] !== null) {
            return (float) $this->attributes['latitude'];
        }
        if (isset($this->attributes['lat']) && $this->attributes['lat'] !== null) {
            return (float) $this->attributes['lat'];
        }

        return null;
    }

    public function getLongitudeAttribute(): ?float
    {
        if (isset($this->attributes['longitude']) && $this->attributes['longitude'] !== null) {
            return (float) $this->attributes['longitude'];
        }
        if (isset($this->attributes['lng']) && $this->attributes['lng'] !== null) {
            return (float) $this->attributes['lng'];
        }

        return null;
    }
}
