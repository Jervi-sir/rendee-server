<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'name',
    'center_catalog_code',
    'license_number',
    'phone_public',
    'description',
    'address',
    'city',
    'emergency_24_7',
    'is_active'
])]

class Center extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'emergency_24_7' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function catalog()
    {
        return $this->belongsTo(CenterCatalog::class, 'center_catalog_code', 'code');
    }

    public function workingHours()
    {
        return $this->hasMany(CenterWorkingHour::class);
    }


    public function services()
    {
        return $this->hasMany(CenterService::class);
    }

    /**
     * Format center details for patient-facing APIs.
     *
     * @param bool $detailed
     * @return array
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name ?? $this->user?->name ?? 'مركز طبي',
            'type' => $this->catalog?->ar ?? $this->catalog?->en ?? 'مركز طبي',
            'description' => $this->description,
            'rating' => '4.8',
            'reviews_count' => 127,
            'emergency_label' => $this->emergency_24_7 ? 'متاح' : 'غير متاح',
            'distance' => '1.2 كم',
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone_public,
            'working_hours' => '٨:٠٠ ص - ١٠:٠٠ م',
        ];

        if ($detailed) {
            $data['services'] = $this->relationLoaded('services') ? $this->services->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->serviceCatalog?->ar ?? $s->serviceCatalog?->en ?? 'خدمة طبية',
                    'price' => $s->price,
                    'duration_minutes' => $s->duration_minutes,
                ];
            }) : [];
            $data['contacts'] = $this->relationLoaded('contacts') ? $this->contacts : [];
            $data['working_hours_slots'] = $this->relationLoaded('workingHours') ? $this->workingHours : [];
        }

        return $data;
    }
}
