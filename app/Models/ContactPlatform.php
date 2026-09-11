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
class ContactPlatform extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public function userContacts(): HasMany
    {
        return $this->hasMany(UserContact::class, 'platform_code', 'code');
    }
}
