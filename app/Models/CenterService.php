<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'center_id',
    'service_catalog_code',
    'description',
    'price',
    'duration_minutes',
    'is_active',
])]

class CenterService extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function serviceCatalog()
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_code', 'code');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'center_service_id');
    }
}
