<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'doctor_id',
    'service_catalog_code',
    'price',
    'duration_minutes',
])]

class DoctorService extends Model
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
        ];
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function serviceCatalog()
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_code', 'code');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'doctor_service_id');
    }
}
