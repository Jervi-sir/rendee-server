<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'source',
    'en',
    'fr',
    'ar',
])]
class ServiceCatalog extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public function partnerServices(): HasMany
    {
        return $this->hasMany(PartnerService::class, 'service_catalog_code', 'code');
    }

    public function services(): HasMany
    {
        return $this->partnerServices();
    }
}
