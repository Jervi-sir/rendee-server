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

    public function contacts()
    {
        return $this->hasMany(CenterContact::class);
    }

    public function services()
    {
        return $this->hasMany(CenterService::class);
    }
}
