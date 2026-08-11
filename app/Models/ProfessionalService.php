<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'professional_id',
    'service_catalog_code',
    'price',
    'duration_minutes',
])]
class ProfessionalService extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function serviceCatalog()
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_code', 'code');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'service_id');
    }
}
