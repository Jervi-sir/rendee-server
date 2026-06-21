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

class CenterCatalog extends Model
{
    public function centers()
    {
        return $this->hasMany(Center::class, 'center_catalog_code', 'code');
    }
}
