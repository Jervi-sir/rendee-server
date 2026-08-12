<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'en',
    'fr',
    'ar',
])]
class CenterCatalog extends Model
{
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'center_catalog_code', 'code');
    }

    public function centers(): HasMany
    {
        return $this->partners();
    }
}
