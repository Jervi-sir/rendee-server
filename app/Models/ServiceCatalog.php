<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'en',
    'fr',
    'ar',
])]

class ServiceCatalog extends Model
{
    public function doctorServices()
    {
        return $this->hasMany(DoctorService::class, 'service_catalog_code', 'code');
    }

    public function centerServices()
    {
        return $this->hasMany(CenterService::class, 'service_catalog_code', 'code');
    }
}
